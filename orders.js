document.addEventListener("DOMContentLoaded", () => {
    fetch('get_data.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.month);
            const salesData = data.map(item => parseFloat(item.total_sales));
            const orderCountData = data.map(item => parseInt(item.order_count));

            // Sales Chart
            const salesChartCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesChartCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales',
                        data: salesData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Profit Chart
            const profitChartCtx = document.getElementById('profitChart').getContext('2d');
            const profitChart = new Chart(profitChartCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Orders Count',
                        data: orderCountData,
                        backgroundColor: 'rgba(153, 102, 255, 0.6)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    // Fetch and populate orders tabl
    fetch('get_orders.php')
        .then(response => response.json())
        .then(orders => {
            const tableBody = document.getElementById('orders-table-body');
            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.name}</td>
                    <td>${order.computer_id}</td>
                    <td>${order.total_price}</td>
                    <td>${order.status}</td>
                    <td></td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching orders:', error));
});
