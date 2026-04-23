<?php
// This path is then passed by JavaScript to index.php (add_pet function) as image_path

header("Content-Type: application/json");

// Only accept POST requests 
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// Check a file was actually sent 
if (!isset($_FILES["image"]) || $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(["status" => "error", "message" => "No file uploaded"]);
    exit();
}

$file = $_FILES["image"];

// Check for upload errors 
if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Upload error. Please try again"]);
    exit();
}

// SERVER-SIDE: Validate file size (max 2MB) 
if ($file["size"] > 2 * 1024 * 1024) {
    echo json_encode(["status" => "error", "message" => "File too large. Maximum size is 2MB"]);
    exit();
}

// SERVER-SIDE: Validate file extension
$allowed_exts = ["jpg", "jpeg", "png"];
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_exts)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Allowed: JPG, JPEG, PNG"]);
    exit();
}

//  SERVER-SIDE: Validate real MIME type (not just extension) 
$allowed_types = ["image/jpeg", "image/png", "image/jpg"];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file["tmp_name"]);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file content. Only real images are allowed"]);
    exit();
}

// Create img/ folder if it doesn't exist
$uploadDir = "img/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename to avoid overwriting 
$fileName = time() . "_" . bin2hex(random_bytes(4)) . "_" . basename($file["name"]);
$targetPath = $uploadDir . $fileName;   // e.g. "img/1714000000_mydog.jpg"
                                        // this value goes into image_path column in DB

// Move file from temp to img/ folder 
if (move_uploaded_file($file["tmp_name"], $targetPath)) {
    echo json_encode([
        "status"     => "success",
        "image_path" => $targetPath    // JS passes this to index.php as image_path
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Upload failed. Please try again"]);
}
?>
