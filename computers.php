<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Computers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
        }
        .computer {
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .computer img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .computer h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .computer p {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .computer .price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .computer .availability {
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "computer_sales";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM computers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="computer">';
            echo '<img src="/uploads/' . $row['case_photo'] . '.jpg" alt="Computer Photo">';
            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        
            // Display components
            echo '<ul>';
            
                   // Fetch motherboard name
        $motherboard_id = $row['motherboard_id'];
        $motherboard_query = "SELECT name FROM components WHERE id = ?";
        $motherboard_stmt = $conn->prepare($motherboard_query);
        $motherboard_stmt->bind_param("i", $motherboard_id);
        $motherboard_stmt->execute();
        $motherboard_result = $motherboard_stmt->get_result();
        $motherboard_row = $motherboard_result->fetch_assoc();
        echo '<li><strong>Материнская плата:</strong> ' . htmlspecialchars($motherboard_row['name']) . '</li>';

        // Fetch RAM name
        $ram_id = $row['ram_id'];
        $ram_query = "SELECT name FROM components WHERE id = ?";
        $ram_stmt = $conn->prepare($ram_query);
        $ram_stmt->bind_param("i", $ram_id);
        $ram_stmt->execute();
        $ram_result = $ram_stmt->get_result();
        $ram_row = $ram_result->fetch_assoc();
        echo '<li><strong>Оперативная память:</strong> ' . htmlspecialchars($ram_row['name']) . '</li>';

        // Fetch GPU name
        $gpu_id = $row['gpu_id'];
        $gpu_query = "SELECT name FROM components WHERE id = ?";
        $gpu_stmt = $conn->prepare($gpu_query);
        $gpu_stmt->bind_param("i", $gpu_id);
        $gpu_stmt->execute();
        $gpu_result = $gpu_stmt->get_result();
        $gpu_row = $gpu_result->fetch_assoc();
        echo '<li><strong>Видеокарта:</strong> ' . htmlspecialchars($gpu_row['name']) . '</li>';

        // Fetch PSU name
        $psu_id = $row['psu_id'];
        $psu_query = "SELECT name FROM components WHERE id = ?";
        $psu_stmt = $conn->prepare($psu_query);
        $psu_stmt->bind_param("i", $psu_id);
        $psu_stmt->execute();
        $psu_result = $psu_stmt->get_result();
        $psu_row = $psu_result->fetch_assoc();
        echo '<li><strong>Блок питания:</strong> ' . htmlspecialchars($psu_row['name']) . '</li>';

        // Fetch SSD name
        $ssd_id = $row['ssd_id'];
        $ssd_query = "SELECT name FROM components WHERE id = ?";
        $ssd_stmt = $conn->prepare($ssd_query);
        $ssd_stmt->bind_param("i", $ssd_id);
        $ssd_stmt->execute();
        $ssd_result = $ssd_stmt->get_result();
        $ssd_row = $ssd_result->fetch_assoc();
        echo '<li><strong>SSD:</strong> ' . htmlspecialchars($ssd_row['name']) . '</li>';

        // Fetch HDD name
        $hdd_id = $row['hdd_id'];
        $hdd_query = "SELECT name FROM components WHERE id = ?";
        $hdd_stmt = $conn->prepare($hdd_query);
        $hdd_stmt->bind_param("i", $hdd_id);
        $hdd_stmt->execute();
        $hdd_result = $hdd_stmt->get_result();
        $hdd_row = $hdd_result->fetch_assoc();
        echo '<li><strong>HDD:</strong> ' . htmlspecialchars($hdd_row['name']) . '</li>';

        // Fetch Case name
        $case_id = $row['case_id'];
        $case_query = "SELECT name FROM components WHERE id = ?";
        $case_stmt = $conn->prepare($case_query);
        $case_stmt->bind_param("i", $case_id);
        $case_stmt->execute();
        $case_result = $case_stmt->get_result();
        $case_row = $case_result->fetch_assoc();
        echo '<li><strong>Корпус:</strong> ' . htmlspecialchars($case_row['name']) . '</li>';

        echo '</ul>';
        
        // Additional details
        echo '<p class="price">Final Price: $' . htmlspecialchars($row['final_price']) . '</p>';
        
        // Availability
        if ($row['availability']) {
            echo '<p class="availability">Availability: In Stock</p>';
        } else {
            echo '<p class="availability">Availability: Out of Stock</p>';
        }
        
        // Link to buy now
        echo '<a href="/vendor/product.php?id=' . $row['id'] . '">Buy Now</a>';
        
        echo '</div>';
    }
} else {
    echo "No computers found.";
}
    $conn->close();
    ?>

   
</body>
</html>