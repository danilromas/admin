<?php
require 'config.php';

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы
    $phone = $conn->real_escape_string($_POST['phone']);
    $name_avito = $conn->real_escape_string($_POST['name_avito']);
    $link_avito = $conn->real_escape_string($_POST['link_avito']);
    $check_god_eye = $_FILES['check_god_eye']['name']; // Имя файла
    $check_get_contact = $_FILES['check_get_contact']['name'];

    // Логика для загрузки файлов
    move_uploaded_file($_FILES['check_god_eye']['tmp_name'], "uploads/" . $check_god_eye);
    move_uploaded_file($_FILES['check_get_contact']['tmp_name'], "uploads/" . $check_get_contact);

    // Вставка данных в базу данных
    $sql = "INSERT INTO security_info (phone, name_avito, link_avito, check_god_eye, check_get_contact)
            VALUES ('$phone', '$name_avito', '$link_avito', '$check_god_eye', '$check_get_contact')";

    if ($conn->query($sql) === TRUE) {
        echo "Информация успешно добавлена.";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Информация по службе безопасности</title>
</head>
<body>
<h2>Внесите информацию по службе безопасности</h2>
<form action="security.php" method="post" enctype="multipart/form-data">
    <label for="phone">Номер телефона/телеграм:</label><br>
    <input type="text" id="phone" name="phone" required><br><br>

    <label for="name_avito">Имя авито:</label><br>
    <input type="text" id="name_avito" name="name_avito" required><br><br>

    <label for="link_avito">Ссылка на авито:</label><br>
    <input type="url" id="link_avito" name="link_avito" required><br><br>

    <label for="check_god_eye">Проверка глазом бога (скрин):</label><br>
    <input type="file" id="check_god_eye" name="check_god_eye" accept="image/*" required><br><br>

    <label for="check_get_contact">Проверка GetContact (скрин):</label><br>
    <input type="file" id="check_get_contact" name="check_get_contact" accept="image/*" required><br><br>

    <input type="submit" value="Отправить">
</form>
</body>
</html>
