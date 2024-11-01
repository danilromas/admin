document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('report-form');
    const tableBody = document.getElementById('report-table-body');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;

        fetch(`get_report.php?start_date=${startDate}&end_date=${endDate}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                tableBody.innerHTML = '';

                if (data.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="6">Нет данных за выбранный период</td>';
                    tableBody.appendChild(row);
                } else {
                    data.forEach(item => {
                        const row = document.createElement('tr');

                        row.innerHTML = `
                            <td>${item.date}</td>
                            <td>${item.city}</td>
                            <td>${item.total_price}</td>
                            <td>${item.manager_fee}</td> <!-- Плата менеджеру -->
                            <td>${item.assistant_fee}</td> <!-- Плата заместителю -->
                            <td>${item.assembler_fee}</td> <!-- Плата сборщику -->
                        `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(error => console.error('Ошибка получения данных:', error));
    });
});
