<?php
// Включить отображение всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
require 'config.php';  // Подключение файла конфигурации


// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка обновления компонента
if (isset($_POST['submit'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $quantity = intval($_POST['quantity']);
    
    // Обработка загрузки фото
    $photo = $_FILES['photo'];
    $photoPath = null;

    if ($photo['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $photoPath = $uploadDir . basename($photo['name']);
        if (!move_uploaded_file($photo['tmp_name'], $photoPath)) {
            echo "Error uploading file.";
            exit();
        }
    } elseif ($photo['error'] !== UPLOAD_ERR_NO_FILE) {
        echo "Error uploading file.";
        exit();
    }

    // Подготовка SQL-запроса
    if ($photoPath) {
        $update_sql = "UPDATE components SET name = ?, price = ?, category = ?, quantity = ?, photo = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sdsisi", $name, $price, $category, $quantity, $photoPath, $id);
    } else {
        $update_sql = "UPDATE components SET name = ?, price = ?, category = ?, quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sdsii", $name, $price, $category, $quantity, $id);
    }

    if ($update_stmt->execute()) {
        echo "Component updated successfully.";
    } else {
        echo "Error updating component: " . $update_stmt->error;
    }

    $update_stmt->close();
    header("Location: components.php");
    exit();
}

// Получение данных компонента для редактирования
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM components WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $component = $result->fetch_assoc();
} else {
    die("Invalid request.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Component</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], input[type="file"] {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .current-photo {
            margin-bottom: 15px;
        }
        .current-photo img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Component</h1>
        <form method="POST" action="edit_component.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($component['id']); ?>">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($component['name']); ?>" required>
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($component['price']); ?>" required>
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($component['category']); ?>" required>
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($component['quantity']); ?>" required>
            <div class="current-photo">
                <label>Current Photo:</label>
                <?php if (!empty($component['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($component['photo']); ?>" alt="Current Photo">
                <?php else: ?>
                    <p>No photo available</p>
                <?php endif; ?>
            </div>
            <label for="photo">Change Photo</label>
            <input type="file" id="photo" name="photo" accept="image/*">
            <button type="submit" name="submit">Update Component</button>
        </form>
    </div>
</body>
</html>
