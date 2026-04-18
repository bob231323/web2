<?php
// Database connection 
require_once "config.php";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . mysqli_connect_error()
    ]);
    exit();
    //die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";


//show pets
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


function searchPets($conn, $type)
{

    $stmt = mysqli_prepare($conn, "SELECT * FROM pets WHERE type = ?");
    mysqli_stmt_bind_param($stmt, "s", $type);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $pets = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $pets[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $pets
    ]);
}

function add_pet($conn, $name, $type, $breed, $age, $description, $image_path) {

    // Validation
    if (empty($name) || empty($type) || empty($age)) {
        echo json_encode(["status" => "error", "message" => "Name, type, and age are required"]);
        return;
    }

    if (!is_numeric($age) || $age <= 0) {
        echo json_encode(["status" => "error", "message" => "Age must be a positive number"]);
        return;
    }

    // Insert
    $stmt = mysqli_prepare($conn, "INSERT INTO pets (name, type, breed, age, description, image_path) VALUES (?, ?, ?, ?, ?, ?)");
   mysqli_stmt_bind_param($stmt, "sssiis", $name, $type, $breed, $age, $description, $image_path);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Pet added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add pet"]);
    }
}



function delete_pet($conn, $name)
{
    $delete_query = mysqli_prepare($conn, "DELETE FROM pets where name = ?");
    mysqli_stmt_bind_param($delete_query, "s", $name);    // s --> string


    if (mysqli_stmt_execute($delete_query)) {
        echo json_encode(
            ["status" => "success"]
        );
    } else {
        echo json_encode(
            ["status" => "failed"]
        );
    }


}


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
        $record['id']
    );

    if (mysqli_stmt_execute($update_record)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "failed"]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['action'])) {

        if ($_GET['action'] == "read") {
            getAllPets($conn);
        }

        if ($_GET['action'] == "search" && isset($_GET['type'])) {
            searchPets($conn, $_GET['type']);
        }

        if ($_GET['action'] == "delete" && isset($_GET['name'])) {
            delete_pet($conn, $_GET['name']);
        }

        if ($_GET['action'] == "update" && isset($_GET['record'])) {
            $record = [
                'name' => $_GET['name'],
                'type' => $_GET['type'],
                'breed' => $_GET['breed'],
                'age' => $_GET['age'],
                'description' => $_GET['description'],
                'image_path' => $_GET['image_path']
            ];
            update_pet($conn, $_GET['id'], $record);
        }
    }
}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == "create") {
        $name        = trim($_POST["name"] ?? "");
        $type        = trim($_POST["type"] ?? "");
        $breed       = trim($_POST["breed"] ?? "");
        $age         = trim($_POST["age"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $image_path  = trim($_POST["image_path"] ?? "");

        add_pet($conn, $name, $type, $breed, $age, $description, $image_path);
    }
}
?>
