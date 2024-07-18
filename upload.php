<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Directory for file uploads
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Handle file upload
if (isset($_FILES["case_photo"]) && $_FILES["case_photo"]["error"] == UPLOAD_ERR_OK) {
    $fileTmpName = $_FILES["case_photo"]["tmp_name"];
    $fileName = basename($_FILES["case_photo"]["name"]);
    $target_file = $target_dir . $fileName;

    // Move uploaded file to target directory
    if (move_uploaded_file($fileTmpName, $target_file)) {
        echo "File successfully uploaded to: " . $target_file . "<br>";
    } else {
        echo "Sorry, there was an error uploading your file. Error details: " . print_r($_FILES, true) . "<br>";
    }
} else {
    echo "No file was uploaded or there was an upload error. Error details: " . print_r($_FILES, true) . "<br>";
}
?>