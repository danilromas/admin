<?php
require 'auth.php';
check_login();
check_role(['admin']);
?>

<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $component_id = intval($_POST['component_id']);
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    $sql = "INSERT INTO component_arrivals (component_id, quantity, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iid", $component_id, $quantity, $price);

    if ($stmt->execute()) {
        echo "New component arrival added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: component_arrivals.php");
exit;
?>
