<?php
// Подключение к базе данных
require 'config.php';  // Подключение файла конфигурации

require 'auth.php';
check_login(); // Проверяет, авторизован ли пользователь

$is_admin = check_role(['admin', 'manager', 'assembler']);

if (!$is_admin) {
    header("Location: index.php"); // Перенаправление, если роль не соответствует
    exit();
}

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка кодировки
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
    exit();
}

// Обработка удаления
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM components WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if ($delete_stmt === false) {
        die("Error preparing delete statement: " . $conn->error);
    }
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "Component deleted successfully.";
    } else {
        echo "Error deleting component: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Поиск
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Сортировка по категории
$category = '';
if (isset($_GET['category'])) {
    $category = $conn->real_escape_string($_GET['category']);
}

$sql_avg_price = "
    SELECT 
        c.id,
        c.name,
        COALESCE((
            SELECT ca.price
            FROM component_arrivals ca
            WHERE ca.component_id = c.id
            ORDER BY ca.arrival_date DESC
            LIMIT 1
        ), c.price) AS average_price
    FROM components c
    WHERE 1=1"; // Базовое условие

// Инициализация переменных
$types_avg_price = '';
$params_avg_price = [];

// Условия поиска и фильтрации
if ($search) {
    $sql_avg_price .= " AND c.name LIKE ?";
    $params_avg_price[] = '%' . $search . '%';
    $types_avg_price .= 's';
}

if ($category) {
    $sql_avg_price .= " AND c.category = ?";
    $params_avg_price[] = $category;
    $types_avg_price .= 's';
}

$sql_avg_price .= " GROUP BY c.id";

// Подготовка запроса
$stmt_avg_price = $conn->prepare($sql_avg_price);
if ($stmt_avg_price === false) {
    die("Error preparing average price statement: " . $conn->error);
}

// Привязка параметров
if (!empty($params_avg_price)) {
    $stmt_avg_price->bind_param($types_avg_price, ...$params_avg_price);
}

// Выполнение запроса
$stmt_avg_price->execute();
$result_avg_price = $stmt_avg_price->get_result();
if ($result_avg_price === false) {
    die("Error executing average price statement: " . $stmt_avg_price->error);
}


// Обновление цен в таблице components
$update_sql = "UPDATE components SET price = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt === false) {
    die("Error preparing update statement: " . $conn->error);
}

while ($row = $result_avg_price->fetch_assoc()) {
    $average_price = $row['average_price'] !== null ? $row['average_price'] : 0;

    $update_stmt->bind_param("di", $average_price, $row['id']);
    if (!$update_stmt->execute()) {
        echo "Error updating component price: " . $update_stmt->error;
    }
}
$update_stmt->close();

// Получение всех уникальных категорий
$categories_sql = "SELECT DISTINCT category FROM components";
$categories_result = $conn->query($categories_sql);
if ($categories_result === false) {
    die("Error fetching categories: " . $conn->error);
}

// Построение SQL-запроса для вывода данных с подзапросом для последней цены
$sql = "
    SELECT 
        c.*, 
        COALESCE(NULLIF(c.price, 0), (
            SELECT ca.price 
            FROM component_arrivals ca 
            WHERE ca.component_id = c.id 
            ORDER BY ca.arrival_date DESC 
            LIMIT 1
        )) AS display_price
    FROM components c
    WHERE 1=1";

// Добавляем условия фильтрации поиска и категории
$params = [];
$types = '';

if ($search) {
    $sql .= " AND c.name LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if ($category) {
    $sql .= " AND c.category = ?";
    $params[] = $category;
    $types .= 's';
}

$sql .= " ORDER BY c.name";

// Подготовка и выполнение запроса
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Components List</title>
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%; /* Изменено на 90% для мобильных устройств */
    margin: 50px auto;
    background: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #333;
}

.filters {
    margin-bottom: 20px;
    display: flex; /* Изменено на flex для лучшего выравнивания на мобильных */
    flex-direction: column; /* Вертикальное выравнивание для мобильных */
    align-items: center; /* Центрирование элементов */
}

.filters form {
    display: flex; /* Изменено на flex для более удобного отображения */
    flex-wrap: wrap; /* Позволяет оборачивать элементы на мобильных устройствах */
    justify-content: center; /* Центрирование формы */
    margin-bottom: 10px; /* Уменьшено расстояние между формами */
}

.filters input,
.filters select {
    padding: 5px;
    margin: 5px; /* Увеличены отступы для мобильных */
    width: calc(100% - 10px); /* Устанавливаем ширину для мобильных */
    max-width: 150px; /* Устанавливаем максимальную ширину */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table,
th,
td {
    border: 1px solid #ddd;
}

th,
td {
    padding: 12px;
    text-align: center;
}

th {
    background-color: #f4f4f4;
    color: #333;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

.btn {
    padding: 5px 10px;
    text-decoration: none;
    color: #fff;
    border-radius: 3px;
}

.btn-edit {
    background-color: #4CAF50;
}

.btn-delete {
    background-color: #f44336;
}

.photo {
    width: 100px;
    height: auto;
}

.back-button {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: #f4f4f4;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 0.5em 1em;
    color: #007BFF;
    text-decoration: none;
    font-weight: bold;
}

.back-button:hover {
    background-color: #e0e0e0;
}

/* Медиазапросы для мобильных устройств */
@media (max-width: 600px) {
    h1 {
        font-size: 1.5em; /* Уменьшаем размер заголовка */
    }

    .filters {
        flex-direction: column; /* Вертикальное выравнивание на мобильных */
        align-items: stretch; /* Заставляем элементы занимать всю ширину */
    }

    .filters form {
        justify-content: center; /* Центрирование формы */
    }

    .filters input,
    .filters select {
        width: 100%; /* Устанавливаем ширину на 100% */
        max-width: none; /* Убираем ограничение по максимальной ширине */
    }

    .container {
        padding: 10px; /* Уменьшаем отступы контейнера */
    }

    .photo {
        width: 80px; /* Уменьшаем размер фото */
    }
}

    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">Назад</a>
        <h1>Components List</h1>
        <div class="filters">
            <form action="components.php" method="get">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php
                    if ($categories_result->num_rows > 0) {
                        while ($row = $categories_result->fetch_assoc()) {
                            $cat = htmlspecialchars($row['category']);
                            $selected = ($category == $cat) ? 'selected' : '';
                            echo "<option value='$cat' $selected>$cat</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit">Apply</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <?php if ($is_admin == check_role(['admin'])): ?><th>Price</th> <?php endif; ?>
                    <th>Category</th>
                    <th>Photo</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
               if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Используем поле display_price, которое содержит последнюю цену, если price равно 0
                    $price = number_format($row['display_price'] ?? 0, 2); // Исправлено здесь
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    if ($is_admin == check_role(['admin'])) {
                        echo "<td>" . $price . "</td>"; // Цена
                    }
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td><img src='" . htmlspecialchars($row['photo']) . "' class='photo'></td>";
                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td>
                            <a href='edit_component.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-edit'>Edit</a>
                            <a href='components.php?delete_id=" . htmlspecialchars($row['id']) . "' class='btn btn-delete'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                    echo "<tr><td colspan='7'>No components found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
