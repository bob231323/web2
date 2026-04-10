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

?>
<?php
//show pets
function getAllPets($conn) {

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
?>
<?php
function searchPets($conn, $type) {

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
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['action'])) {

        if ($_GET['action'] == "read") {
            getAllPets($conn);
        }

        if ($_GET['action'] == "search" && isset($_GET['type'])) {
            searchPets($conn, $_GET['type']);
        }
    }
}
?> 

