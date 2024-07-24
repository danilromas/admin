document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('report-form');
    const tableBody = document.getElementById('report-table-body');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;

        fetch(`get_report.php?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';

                if (data.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="5">Нет данных за выбранный период</td>';
                    tableBody.appendChild(row);
                } else {
                    data.forEach(item => {
                        const row = document.createElement('tr');
                        const managerFee = 700;
                        const assistantFee = 1000;

                        row.innerHTML = `
                            <td>${item.date}</td>
                            <td>${item.city}</td>
                            <td>${item.total_price}</td>
                            <td>${managerFee}</td>
                            <td>${assistantFee}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(error => console.error('Ошибка получения данных:', error));
    });
});
