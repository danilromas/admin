




<?php
require 'auth.php';
check_login();
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
                        <span class="material-icons-sharp">dashboard</span>
                        <h3>Add Components</h3>
                    </a>
                    <a href="components.php">
                        <span class="material-icons-sharp">person_outline</span>
                        <h3>Components</h3>
                    </a>
                    <a href="add_computer1.php">
                        <span class="material-icons-sharp">person_outline</span>
                        <h3>Add computer</h3>
                    </a>
                    <a href="computers.php">
                        <span class="material-icons-sharp">receipt_long</span>
                        <h3>Computers</h3>
                    </a>
                    <a href="orders.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Orders</h3>
                    </a>
                    <a href="report.html">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Report</h3>
                    </a>
                    <a href="purchased_orders.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Выполненные заказы</h3>
                    </a>
                    <a href="rejected_orders.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Отказы</h3>
                    </a>
                    <a href="component_arrivals.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Добавление прихода</h3>
                    </a>
                    <a href="view_component_arrivals.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Просмотр прихода</h3>
                    </a>
                    <a href="analytics.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Аналитика</h3>
                    </a>
                    <a href="add_expense.php">
                        <span class="material-icons-sharp">mail_outline</span>
                        <h3>Добавление расходов</h3>
                    </a>
                    <a href="#">
                        <span class="material-icons-sharp">logout</span>
                        <h3>Logout</h3>
                    </a>
                </div>
            </aside>
            <!-- End of Sidebar Section -->

            <!-- Main Content -->
            <main>
                <h1>Analytics</h1>
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
                    document.addEventListener('DOMContentLoaded', function() {
                        fetch('get_analytics.php')
                            .then(response => response.json())
                            .then(data => {
                                // Update total sales
                                document.getElementById('total-sales').innerText = `${data.totalSales}`;
                                document.getElementById('total-revenue').innerText = `${data.totalRevenue} руб`;
                                document.getElementById('total-markup').innerText = `${data.totalMarkup} руб`;
                                
                                // Update progress circles (dummy example with fixed percentages)
                                // These percentage values can be dynamically calculated if needed
                                document.getElementById('sales-circle').style.strokeDasharray = `${data.totalSales / 1000 * 2.28}, 228`;
                                document.getElementById('revenue-circle').style.strokeDasharray = `${data.totalRevenue / 1000 * 2.28}, 228`;
                                document.getElementById('markup-circle').style.strokeDasharray = `${data.totalMarkup / 1000 * 2.28}, 228`;
                                
                                // Dummy percentage calculations
                                document.getElementById('sales-percentage').innerText = `${(data.totalSales / 1000 * 100).toFixed(2)}%`;
                                document.getElementById('revenue-percentage').innerText = `${(data.totalRevenue / 1000 * 100).toFixed(2)}%`;
                                document.getElementById('markup-percentage').innerText = `${(data.totalMarkup / 1000 * 100).toFixed(2)}%`;
                            })
                            .catch(error => console.error('Error fetching analytics data:', error));
                    });
                </script>
            
                <!-- End of Analyses -->

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
                <div class="charts">
                    <h2>Sales Charts</h2>
                    <canvas id="salesChart"></canvas>
                    <canvas id="profitChart"></canvas>
                </div>
                <!-- End of Sales Charts Section -->

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
