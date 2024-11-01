<?php
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка формы для добавления клиента
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_info = $conn->real_escape_string($_POST['client_info']);
    $sql = "INSERT INTO clients (info) VALUES ('$client_info')";

    if ($conn->query($sql) === TRUE) {
        echo "Информация о клиенте успешно добавлена.";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}

// Получение информации о клиентах
$sql = "SELECT * FROM clients";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Клиенты</title>
</head>
<body>
<h2>Информация о клиентах</h2>
<form action="clients.php" method="post">
    <label for="client_info">Введите информацию о клиенте:</label><br>
    <textarea id="client_info" name="client_info" rows="4" cols="50" required></textarea><br><br>
    <input type="submit" value="Добавить клиента">
</form>

<h3>Список клиентов:</h3>
<ul>
    <?php
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['info']) . "</li>";
    }
    ?>
</ul>
</body>
</html>
