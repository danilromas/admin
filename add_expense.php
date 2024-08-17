<?php
require 'auth.php';
check_login(); // Проверяет, авторизован ли пользователь

$is_admin = check_role(['admin']); // Проверяет, имеет ли пользователь роль администратора

if (!$is_admin) {
    header("Location: index.php"); // Перенаправление, если роль не соответствует
    exit();
}
?>


<?php
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); // Установите кодировку

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = $_POST['store_id'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $sql = "INSERT INTO expenses (store_id, category, amount, date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("isds", $store_id, $category, $amount, $date);
    if ($stmt->execute() === false) {
        die("Error executing statement: " . $stmt->error);
    }

    echo "<p class='success-message'>Expense added successfully!</p>";
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Expense</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            font-size: 24px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: bold;
        }

        select, input[type="number"], input[type="date"], button {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        select:focus, input[type="number"]:focus, input[type="date"]:focus {
            border-color: #009688;
            outline: none;
        }

        button {
            background-color: #009688;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #00796b;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #388e3c;
            padding: 10px;
            border: 1px solid #c8e6c9;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Mobile Styles */
        @media (max-width: 480px) {
            .form-container {
                padding: 20px;
            }

            .form-container h2 {
                font-size: 20px;
            }

            label {
                margin-bottom: 8px;
            }

            select, input[type="number"], input[type="date"], button {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Expense</h2>
    <form action="add_expense.php" method="post">
        <label for="store_id">Store:</label>
        <select name="store_id" id="store_id">
            <option value="1">Techpower</option> <!-- Используйте правильные ID магазинов -->
            <option value="2">HQ</option>
            <option value="3">Artem</option>
            <option value="4">Another Store</option>
            <!-- Добавьте остальные магазины с правильными ID -->
        </select>

        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="Реклама Авито">Реклама Авито</option>
            <option value="Траты на персонал">Траты на персонал</option>
            <option value="Брак">Брак</option>
            <option value="Прочее">Прочее</option>
            <option value="Офис">Офис</option>
            <option value="Реклама ВК">Реклама ВК</option>
            <option value="Реклама сайт">Реклама сайт</option>
        </select>

        <label for="amount">Amount:</label>
        <input type="number" step="0.01" id="amount" name="amount" required>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <button type="submit">Add Expense</button>
    </form>
</div>

</body>
</html>
