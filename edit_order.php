<?php
require 'config.php';  // Подключение файла конфигурации


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $delivery = $_POST['delivery'];
    $additional = $_POST['additional'];
    $additional_price = $_POST['additional_price'];
    $total_price = $_POST['total_price'];

    $sql = "UPDATE orders SET date = ?, name = ?, city = ?, delivery = ?, additional = ?, additional_price = ?, total_price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdsi", $date, $name, $city, $delivery, $additional, $additional_price, $total_price, $id);

    if ($stmt->execute()) {
        echo "Order updated successfully.";
        header("Location: orders.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 1em;
            border: 1px solid #ccc;
            border-radius: 1em;
        }
        label {
            margin-top: 1em;
            display: block;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 0.7em;
            margin-top: 0.5em;
        }
        textarea {
            width: 100%;
            padding: 0.7em;
            margin-top: 0.5em;
        }
        select {
            width: 100%;
            padding: 0.5em;
            margin-top: 0.5em;
        }
        input[type="submit"] {
            margin-top: 1em;
            padding: 0.7em;
            border: none;
            border-radius: 0.5em;
            background: #007BFF;
            color: white;
            font-size: 1em;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const additionalPriceInput = document.getElementById("additional_price");
            const totalPriceInput = document.getElementById("total_price");

            function updateTotalPrice() {
                const basePrice = parseFloat(document.getElementById("base_price").value);
                const additionalPrice = parseFloat(additionalPriceInput.value) || 0;
                totalPriceInput.value = (basePrice + additionalPrice).toFixed(2);
            }

            additionalPriceInput.addEventListener("input", updateTotalPrice);
        });
    </script>
</head>
<body>
    <h2>Edit Order</h2>
    <form action="edit_order.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
        <input type="hidden" id="base_price" value="<?php echo $order['total_price'] - $order['additional_price']; ?>">

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($order['date']); ?>" required>
        
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($order['name']); ?>" required>
        
        <label for="city">City:</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($order['city']); ?>" required>
        
        <label for="delivery">Delivery Method:</label>
        <select id="delivery" name="delivery" required>
            <option value="До города" <?php echo $order['delivery'] == 'До города' ? 'selected' : ''; ?>>До города</option>
            <option value="Самовывоз" <?php echo $order['delivery'] == 'Самовывоз' ? 'selected' : ''; ?>>Самовывоз</option>
        </select>
        
        <label for="additional">Additional Components:</label>
        <textarea id="additional" name="additional"><?php echo htmlspecialchars($order['additional']); ?></textarea>
        
        <label for="additional_price">Additional Price:</label>
        <input type="number" id="additional_price" name="additional_price" step="0.01" value="<?php echo htmlspecialchars($order['additional_price']); ?>">

        <label for="total_price">Total Price:</label>
        <input type="number" id="total_price" name="total_price" step="0.01" value="<?php echo htmlspecialchars($order['total_price']); ?>" readonly>

        <input type="submit" value="Update Order">
    </form>
</body>
</html>
