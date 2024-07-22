<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $motherboard = $_POST['motherboard'];
    $processor = $_POST['processor'];
    $ram = $_POST['ram'];
    $gpu = $_POST['gpu'];
    $psu = $_POST['psu'];
    $ssd = $_POST['ssd'];
    $hdd = $_POST['hdd'];
    $case = $_POST['case'];
    $cpu_cooler = $_POST['cpu_cooler'];
    $extra_cooler = $_POST['extra_cooler'];
    $base_price = $_POST['base_price'];
    $markup = $_POST['markup'];
    $shop = $_POST['shop'];

    $sql = "UPDATE computers SET 
                name = ?, 
                motherboard_id = ?, 
                processor_id = ?, 
                ram_id = ?, 
                gpu_id = ?, 
                psu_id = ?, 
                ssd_id = ?, 
                hdd_id = ?, 
                case_id = ?, 
                cpu_cooler_id = ?, 
                extra_cooler_id = ?, 
                base_price = ?, 
                markup = ?, 
                shop = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiiiiiiiiiisi", 
        $name, 
        $motherboard, 
        $processor, 
        $ram, 
        $gpu, 
        $psu, 
        $ssd, 
        $hdd, 
        $case, 
        $cpu_cooler, 
        $extra_cooler, 
        $base_price, 
        $markup, 
        $shop, 
        $id);

    if ($stmt->execute()) {
        echo "Computer build updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
