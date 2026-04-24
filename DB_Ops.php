<?php
/* ════════════════════════════════════════════
   DATABASE OPERATIONS
   Handles CRUD operations for pets database
════════════════════════════════════════════ */

// Detect API calls so normal page loads are not forced to JSON.
$isAjaxRequest = (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && (($_GET['action'] ?? '') === 'list'))
);

if ($isAjaxRequest) {
    header("Content-Type: application/json");
}

/* ════════════════════════════════════════════
   DATABASE CONNECTION
════════════════════════════════════════════ */
require_once "config.php";

/**local host*/
//$conn = mysqli_connect($servername, $username, $password, $dbname);

/**infinity host*/
$conn = mysqli_connect("sql107.infinityfree.com", "if0_41745508", "qOiQZFnTaYeuYY", "if0_41745508_pet_app");
// Check connection
if (!$conn) {
    if ($isAjaxRequest) {
        echo json_encode([
            "status" => "error",
            "message" => "Connection failed: " . mysqli_connect_error()
        ]);
    } else {
        echo "Connection failed: " . mysqli_connect_error();
    }
    exit();
}

/* ════════════════════════════════════════════
   DATABASE QUERIES
════════════════════════════════════════════ */

/** Get all pets from database */
function getAllPets($conn)
{
    $sql = "SELECT * FROM pets ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);

    $pets = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $pets[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $pets
    ]);
}

/** Add new pet to database */
function add_pet($conn, $name, $type, $breed, $age, $description, $image_path) {
    // Validation
    if (empty($name) || empty($type) || empty($age)) {
        echo json_encode(["status" => "error", "message" => "Name, type, and age are required"]);
        return;
    }

    if (!is_numeric($age) || $age < 0) {
        echo json_encode(["status" => "error", "message" => "Age must be a positive number"]);
        return;
    }

    // Insert
    $stmt = mysqli_prepare($conn, "INSERT INTO pets (name, type, breed, age, description, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiss", $name, $type, $breed, $age, $description, $image_path);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Pet added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add pet"]);
    }
}

/** Delete pet from database */
function delete_pet($conn, $id)
{
    $delete_query = mysqli_prepare($conn, "DELETE FROM pets where id = ?");
    mysqli_stmt_bind_param($delete_query, "i", $id);    // i --> int


    if (mysqli_stmt_execute($delete_query )) {
        if (mysqli_stmt_affected_rows($delete_query ) > 0) {
            echo json_encode(["status" => "success", "message" => "Pet deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Pet not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
}

/** Update pet in database */
function update_pet($conn, $id, $record)
{
    $update_record = mysqli_prepare($conn, "UPDATE pets SET name=?, type=?, breed=?, age=?, description=?, image_path=?  Where id=?");
    //sssissi -->  string string string int string string int
    mysqli_stmt_bind_param(
        $update_record,
        "sssissi",
        $record['name'],
        $record['type'],
        $record['breed'],
        $record['age'],
        $record['description'],
        $record['image_path'],
        $id
    );

    if (mysqli_stmt_execute($update_record)) {
        if (mysqli_stmt_affected_rows($update_record) > 0) {
            echo json_encode(["status" => "success", "message" => "Pet updated"]);
            return;
        }

        // MySQL may return 0 affected rows even on a valid request.
        $check_query = mysqli_prepare($conn, "SELECT id FROM pets WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($check_query, "i", $id);
        mysqli_stmt_execute($check_query);
        $exists_result = mysqli_stmt_get_result($check_query);

        if ($exists_result && mysqli_num_rows($exists_result) > 0) {
            echo json_encode(["status" => "success", "message" => "Pet updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Pet not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
}

/* ════════════════════════════════════════════
   FILE UPLOAD HANDLER
════════════════════════════════════════════ */

/** Handle uploaded image files with validation */
function handle_uploaded_image($file, $existingPath = "")
{
    // 1. EARLY EXIT: If no new file is uploaded, keep the old one.
    // This prevents the "Move failed" error during edits.
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ["status" => "success", "image_path" => $existingPath];
    }

    // 2. CHECK FOR OTHER UPLOAD ERRORS
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["status" => "error", "message" => "Upload error code: " . $file["error"]];
    }

    // ... (Keep your validation code for size/type here) ...

    $subDir = "img/uploads/";
    $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subDir;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . bin2hex(random_bytes(4)) . "_" . basename($file["name"]);
    $targetPath = $uploadDir . $fileName;

    // 3. ONLY MOVE IF WE HAVE A FILE
    if (move_uploaded_file($file["tmp_name"], $targetPath)) {

        // OPTIONAL: Delete the old file from the server to save space
        if (!empty($existingPath) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $existingPath)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $existingPath);
        }

        return ["status" => "success", "image_path" => $subDir . $fileName];
    } else {
        return ["status" => "error", "message" => "Move failed. Check permissions on " . $subDir];
    }
}

// ── LIST (AJAX) ───────────────────────────────────────────────────────────────
// AJAX: return all pets for frontend re-render after CRUD.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (($_GET['action'] ?? '') === 'list')) {
    getAllPets($conn);
    exit();
}

/* ════════════════════════════════════════════════════════════════════════
   FORM SUBMISSION HANDLER
   Calls the exact same functions above, captures their JSON echo via
   output buffering, checks the status, then redirects with ?msg=...
   so the page can show a toast notification (handled by app.js).
════════════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // AJAX actions: create | update | delete
    $action = $_POST['action'];

    // ── CREATE ───────────────────────────────────────────────────────────────
    if ($action === "create") {
        $name        = trim($_POST["name"]        ?? "");
        $type        = trim($_POST["type"]        ?? "");
        $breed       = trim($_POST["breed"]       ?? "");
        $age         = trim($_POST["age"]         ?? "");
        $description = trim($_POST["description"] ?? "");
        $upload = handle_uploaded_image($_FILES["image"] ?? null);
        if (($upload["status"] ?? "error") !== "success") {
            echo json_encode($upload);
            exit();
        }
        $image_path = $upload["image_path"] ?? "";

        // Call add_pet() exactly as defined above — capture its echo
        ob_start();
        add_pet($conn, $name, $type, $breed, $age, $description, $image_path);
        $result = json_decode(ob_get_clean(), true);

        echo json_encode($result);
       exit();
    }

    // ── UPDATE ───────────────────────────────────────────────────────────────
    if ($action === "update") {
        $id = (int)($_POST["id"] ?? 0);

        // Keep the existing image path by default (preserves photo when no new one is uploaded)
        $existingImage = $_POST["existing_image"] ?? "";
        $upload = handle_uploaded_image($_FILES["image"] ?? null, $existingImage);
        if (($upload["status"] ?? "error") !== "success") {
            echo json_encode($upload);
            exit();
        }
        $image_path = $upload["image_path"] ?? $existingImage;

        // If no file uploaded, $image_path stays as existing_image — old photo is kept.

        // Build $record exactly as root index.php does in its GET handler
        $record = [
            'name'        => trim($_POST['name']        ?? ''),
            'type'        => trim($_POST['type']        ?? ''),
            'breed'       => trim($_POST['breed']       ?? ''),
            'age'         => trim($_POST['age']         ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image_path'  => $image_path                        // existing or new
        ];

        // Call update_pet() exactly as defined above — capture its echo
        ob_start();
        update_pet($conn, $id, $record);
        $result = json_decode(ob_get_clean(), true);

        echo json_encode($result);
exit();
    }

    // ── DELETE ───────────────────────────────────────────────────────────────
    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        // Call delete_pet() exactly as defined above — capture its echo
        ob_start();
        delete_pet($conn, $id);
        $result = json_decode(ob_get_clean(), true);

        echo json_encode($result);
exit();
    }

    echo json_encode(["status" => "error", "message" => "Invalid action"]);
    exit();
}

/* ════════════════════════════════════════════════════════════════════════
   HELPER: getPets()
   Used by pawmatch/index.php for the initial page render.
   Calls getAllPets() above, captures its JSON echo, returns PHP array.
════════════════════════════════════════════════════════════════════════ */
function getPets($conn) {
    ob_start();
    getAllPets($conn);
    $result = json_decode(ob_get_clean(), true);
    return $result["data"] ?? [];
}
?>
