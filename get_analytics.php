<?php
require 'config.php';  // Подключение файла конфигурации


$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Fetch total sales
$stmt = $pdo->query("SELECT SUM(total_price) as total_sales FROM orders WHERE status != 'отказ'");
$totalSales = $stmt->fetchColumn();

// Fetch total site visits (for demonstration purposes, we use a static value)
$totalVisits = 24981; 

// Fetch total searches (for demonstration purposes, we use a static value)
$totalSearches = 14147;

// Fetch sales percentage change (dummy value for demonstration)
$salesPercentageChange = 81;

// Fetch visits percentage change (dummy value for demonstration)
$visitsPercentageChange = -48;

// Fetch searches percentage change (dummy value for demonstration)
$searchesPercentageChange = 21;

echo json_encode([
    'totalSales' => $totalSales,
    'totalVisits' => $totalVisits,
    'totalSearches' => $totalSearches,
    'salesPercentageChange' => $salesPercentageChange,
    'visitsPercentageChange' => $visitsPercentageChange,
    'searchesPercentageChange' => $searchesPercentageChange,
]);
?>
