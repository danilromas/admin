<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "computer_sales";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$computer = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM computers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $computer = $result->fetch_assoc();

    $stmt->close();
}

$conn->close();
?>


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Computer</title>
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
        .readonly {
            background-color: #e9ecef;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const additionalPriceInput = document.getElementById("additional_price");
            const totalPriceInput = document.getElementById("total_price");
            const basePriceInput = document.getElementById("base_price");

            function updateTotalPrice() {
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const additionalPrice = parseFloat(additionalPriceInput.value) || 0;
                totalPriceInput.value = (basePrice + additionalPrice).toFixed(2);
            }

            additionalPriceInput.addEventListener("input", updateTotalPrice);

            // Initial total price calculation
            updateTotalPrice();
        });
    </script>
</head>
<body>
    <h2>Sell Computer</h2>
    <form action="create_order.php" method="POST">
        <input type="hidden" name="computer_id" value="<?php echo $computer['id']; ?>">
        
        <label for="base_price">Base Price:</label>
        <input type="number" id="base_price" class="readonly" value="<?php echo $computer['final_price']; ?>" readonly>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>
        
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="city">City:</label>
        <input type="text" id="city" name="city" required>
        
        <label for="delivery">Delivery Method:</label>
        <select id="delivery" name="delivery" required>
            <option value="До города">До города</option>
            <option value="Самовывоз">Самовывоз</option>
        </select>
        
        <label for="additional">Additional Components:</label>
        <textarea id="additional" name="additional"></textarea>
        
        <label for="additional_price">Additional Price:</label>
        <input type="number" id="additional_price" name="additional_price" step="0.01">

        <label for="total_price">Total Price:</label>
        <input type="number" id="total_price" name="total_price" step="0.01" readonly>

        <input type="submit" value="Confirm Order">
    </form>
</body>
</html>
