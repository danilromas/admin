<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$motherboard_id = $_POST['motherboard'];
$processor_id = $_POST['processor'];
$ram_id = $_POST['ram'];
$gpu_id = $_POST['gpu'];
$psu_id = $_POST['psu'];
$ssd_id = $_POST['ssd'];
$hdd_id = $_POST['hdd'];
$case_id = $_POST['case'];
$cpu_cooler_id = $_POST['cpu_cooler'];
$extra_cooler_id = $_POST['extra_cooler'];
$shop = $_POST['shop'];
$base_price = floatval($_POST['base_price']);
$markup = floatval($_POST['markup']);
$final_price = $base_price + $markup;

// Directory for file uploads
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Handle file upload
$case_photo_name = '';
if (isset($_FILES["case_photo"]) && $_FILES["case_photo"]["error"] == UPLOAD_ERR_OK) {
    $fileTmpName = $_FILES["case_photo"]["tmp_name"];
    $original_file_name = basename($_FILES["case_photo"]["name"]);
    $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);

    // Generate new file name without extension
    $case_photo_name = pathinfo($original_file_name, PATHINFO_FILENAME);

    // Add .jpg extension
    $case_photo_name .= ".jpg";

    $target_file = $target_dir . $case_photo_name;

    // Move uploaded file to target directory
    if (move_uploaded_file($fileTmpName, $target_file)) {
        echo "File successfully uploaded to: " . $target_file . "<br>";
    } else {
        echo "Sorry, there was an error uploading your file. Error details: " . print_r($_FILES, true) . "<br>";
    }
} else {
    echo "No file was uploaded or there was an upload error. Error details: " . print_r($_FILES, true) . "<br>";
}

// SQL query for inserting data into computers table
$sql = "INSERT INTO computers (name, motherboard_id, processor_id, ram_id, gpu_id, psu_id, ssd_id, hdd_id, case_id, cpu_cooler_id, extra_cooler_id, case_photo, shop, base_price, markup, final_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Check data types and values

// Bind parameters
// Note: `s` is for string, `i` is for integer, and `d` is for double (float).
$stmt->bind_param("siiiiiiiiiiisdds", $name, $motherboard_id, $processor_id, $ram_id, $gpu_id, $psu_id, $ssd_id, $hdd_id, $case_id, $cpu_cooler_id, $extra_cooler_id, $case_photo_name, $shop, $base_price, $markup, $final_price);

if ($stmt->execute()) {
    echo "New computer build created successfully<br>";

    // Verify insertion
    $result = $conn->query("SELECT * FROM computers WHERE id = LAST_INSERT_ID()");
    if ($result) {
        $row = $result->fetch_assoc();
        print_r($row);
    } else {
        echo "Error fetching data: " . $conn->error;
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>