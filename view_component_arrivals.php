<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Component Arrivals</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .table-container {
            width: 80%;
            max-width: 1000px;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h2>Component Arrivals</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Component</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Arrival Date</th>
            </tr>
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "computer_sales";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "
                SELECT ca.id, c.name AS component_name, ca.quantity, ca.price, ca.arrival_date
                FROM component_arrivals ca
                JOIN components c ON ca.component_id = c.id
                ORDER BY ca.arrival_date DESC
            ";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['component_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['arrival_date']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No component arrivals found.</td></tr>";
            }

            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
