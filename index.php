<?php
require 'auth.php';
check_login();
$is_admin = check_role(['admin', 'manager', 'assembler']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Responsive Dashboard Design</title>
</head>

<body>

    <div id="main-content">
        <div class="container">
            <!-- Sidebar Section -->
            <aside>
                <div class="toggle">
                    <div class="logo">
                        <img src="images/logo.png">
                        <h2>Tech<span class="danger">Power</span></h2>
                    </div>
                    <div class="close" id="close-btn">
                        <span class="material-icons-sharp">close</span>
                    </div>
                </div>
                <div class="sidebar">
                    <a href="add_component.html">
                        <span class="material-icons-sharp">add_circle_outline</span>
                        <h3>Добавить компонент</h3>
                    </a>
                    <a href="components.php">
                        <span class="material-icons-sharp">memory</span>
                        <h3>Компоненты</h3>
                    </a>
                    <a href="add_computer1.php">
                        <span class="material-icons-sharp">add_to_photos</span>
                        <h3>Добавить компьютер</h3>
                    </a>
                    <a href="computers.php">
                        <span class="material-icons-sharp">desktop_mac</span>
                        <h3>Компьютеры</h3>
                    </a>
                    <a href="orders.php">
                        <span class="material-icons-sharp">shopping_cart</span>
                        <h3>Заказы</h3>
                    </a>
                    <a href="report.html">
                        <span class="material-icons-sharp">bar_chart</span>
                        <h3>Отчет</h3>
                    </a>
                    <a href="purchased_orders.php">
                        <span class="material-icons-sharp">done_outline</span>
                        <h3>Выполненные заказы</h3>
                    </a>
                    <a href="rejected_orders.php">
                        <span class="material-icons-sharp">cancel</span>
                        <h3>Отмененные заказы</h3>
                    </a>
                    <a href="component_arrivals.php">
                        <span class="material-icons-sharp">local_shipping</span>
                        <h3>Приход компонентов</h3>
                    </a>
                    <a href="view_component_arrivals.php">
                        <span class="material-icons-sharp">visibility</span>
                        <h3>Просмотр прихода</h3>
                    </a>
                    <a href="analytics.php">
                        <span class="material-icons-sharp">insights</span>
                        <h3>Аналитика</h3>
                    </a>
                    <a href="add_expense.php">
                        <span class="material-icons-sharp">account_balance_wallet</span>
                        <h3>Добавить расходы</h3>
                    </a>
                    <a href="changes.php">
                        <span class="material-icons-sharp">history</span>
                        <h3>История</h3>
                    </a>
                    <a href="logout.php">
                        <span class="material-icons-sharp">logout</span>
                        <h3>Выйти</h3>
                    </a>
                </div>
            </aside>
            <!-- End of Sidebar Section -->

            <!-- Main Content -->
            <main>
                <h1>Analytics</h1>
                <!-- Форма для выбора даты и установки целей -->
    <form id="analytics-form">
        <div>
            <label for="start-date">Start Date:</label>
            <input type="date" id="start-date" name="start_date" required>
        </div>
        <div>
            <label for="end-date">End Date:</label>
            <input type="date" id="end-date" name="end_date" required>
        </div>
        <div>
            <label for="sales-goal">Sales Goal:</label>
            <input type="number" id="sales-goal" name="sales_goal" placeholder="45" required>
        </div>
        <div>
            <label for="revenue-goal">Revenue Goal:</label>
            <input type="number" id="revenue-goal" name="revenue_goal" placeholder="2000000" required>
        </div>
        <div>
            <label for="markup-goal">Markup Goal:</label>
            <input type="number" id="markup-goal" name="markup_goal" placeholder="300000" required>
        </div>
        <button type="submit">Apply</button>
    </form>
                <?php if ($is_admin == check_role(['admin'])): ?>
                                <!-- Analyses -->
                                <div class="analyse">
                    <div class="sales">
                        <div class="status">
                            <div class="info">
                                <h3>Total Sales</h3>
                                <h1 id="total-sales">0</h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36" id="sales-circle"></circle>
                                </svg>
                                <div class="percentage">
                                    <p id="sales-percentage">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="visits">
                        <div class="status">
                            <div class="info">
                                <h3>Total Revenue</h3>
                                <h1 id="total-revenue">$0</h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36" id="revenue-circle"></circle>
                                </svg>
                                <div class="percentage">
                                    <p id="revenue-percentage">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="searches">
                        <div class="status">
                            <div class="info">
                                <h3>Total Markup</h3>
                                <h1 id="total-markup">$0</h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36" id="markup-circle"></circle>
                                </svg>
                                <div class="percentage">
                                    <p id="markup-percentage">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
        document.getElementById('analytics-form').addEventListener('submit', function (e) {
            e.preventDefault();

            // Собираем данные из формы
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();

            // Делаем запрос к get_analytics.php
            fetch(`get_analytics.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    // Обновляем данные аналитики
                    document.getElementById('total-sales').innerText = `${data.totalSales}`;
                    document.getElementById('total-revenue').innerText = `${data.totalRevenue} руб`;
                    document.getElementById('total-markup').innerText = `${data.totalMarkup} руб`;

                    // Обновляем круги прогресса
                    const salesGoal = parseFloat(formData.get('sales_goal')) || 1;
                    const revenueGoal = parseFloat(formData.get('revenue_goal')) || 1;
                    const markupGoal = parseFloat(formData.get('markup_goal')) || 1;

                    document.getElementById('sales-circle').style.strokeDasharray = `${data.totalSales / salesGoal * 228}, 228`;
                    document.getElementById('revenue-circle').style.strokeDasharray = `${data.totalRevenue / revenueGoal * 228}, 228`;
                    document.getElementById('markup-circle').style.strokeDasharray = `${data.totalMarkup / markupGoal * 228}, 228`;

                    document.getElementById('sales-percentage').innerText = `${(data.totalSales / salesGoal * 100).toFixed(2)}%`;
                    document.getElementById('revenue-percentage').innerText = `${(data.totalRevenue / revenueGoal * 100).toFixed(2)}%`;
                    document.getElementById('markup-percentage').innerText = `${(data.totalMarkup / markupGoal * 100).toFixed(2)}%`;
                })
                .catch(error => console.error('Error fetching analytics data:', error));
        });
    </script>
                <!-- End of Analyses -->
                <?php endif; ?>

                <!-- Recent Orders Table -->
                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Number</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="orders-table-body">
                            <!-- Orders will be populated here by JavaScript -->
                        </tbody>
                    </table>
                    <a href="#">Show All</a>
                </div>
                <!-- End of Recent Orders -->

                <!-- Sales Charts Section -->
                <?php if ($is_admin == check_role(['admin'])): ?>
                    <div class="charts">
                    <h2>Sales Charts</h2>
                    <canvas id="salesChart"></canvas>
                    <canvas id="profitChart"></canvas>
                </div>
                <!-- End of Sales Charts Section -->
                <?php endif; ?>

                

            </main>
            <!-- End of Main Content -->

            <!-- Right Section -->
            <div class="right-section">
                <div class="nav">
                    <button id="menu-btn">
                        <span class="material-icons-sharp">menu</span>
                    </button>
                    <div class="dark-mode">
                        <span class="material-icons-sharp active">light_mode</span>
                        <span class="material-icons-sharp">dark_mode</span>
                    </div>
                    <div class="profile">
                        <div class="info">
                            <p>Hey, <b>Reza</b></p>
                            <small class="text-muted">Admin</small>
                        </div>
                        <div class="profile-photo">
                            <img src="images/profile-1.jpg">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="orders.js"></script>
    <script src="index.js"></script>
</body>
</html>
