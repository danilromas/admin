<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Component Arrivals</title>
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
        .form-container {
            width: 80%;
            max-width: 600px;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
            color: #333;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .form-container input, .form-container select {
            margin-bottom: 20px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>New Component Arrival</h2>
        <form action="add_component_arrival.php" method="post">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Материнская плата">Материнская плата</option>
                <option value="Процессор">Процессор</option>
                <option value="Оперативная память">Оперативная память</option>
                <option value="Видеокарта">Видеокарта</option>
                <option value="Блок питания">Блок питания</option>
                <option value="SSD диск">SSD диск</option>
                <option value="HDD диск">HDD диск</option>
                <option value="Корпус">Корпус</option>
                <option value="Куллер (процессор)">Куллер (процессор)</option>
                <option value="Куллер (доп)">Куллер (доп)</option>
            </select>

            <label for="component_id">Component:</label>
            <select name="component_id" id="component_id" required>
                <option value="">Select a component</option>
            </select>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="price">Price per Unit:</label>
            <input type="number" step="0.01" id="price" name="price" required>

            <button type="submit">Add Arrival</button>
        </form>
    </div>

    <script>
        document.getElementById('category').addEventListener('change', function() {
            var category = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_components1.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var components = JSON.parse(xhr.responseText);
                        var componentSelect = document.getElementById('component_id');
                        componentSelect.innerHTML = '<option value="">Select a component</option>';
                        components.forEach(function(component) {
                            var option = document.createElement('option');
                            option.value = component.id;
                            option.textContent = component.name;
                            componentSelect.appendChild(option);
                        });
                    } catch (e) {
                        console.error('Failed to parse JSON response', e);
                    }
                } else {
                    console.error('Failed to load components', xhr.status, xhr.statusText);
                }
            };
            xhr.send('category=' + encodeURIComponent(category));
        });
    </script>
</body>
</html>
