<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Test assignment</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    </head>
    <body class="antialiased">
    <div class="container mt-5">
        <h1 class="text-center mb-4">API Documentation</h1>
        <div id="api-docs" class="bg-light border p-4 rounded">
            <!-- Содержимое swagger.json будет отображаться здесь -->
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('swagger.json') // Убедитесь, что путь к swagger.json верный
                .then(response => response.json())
                .then(data => {
                    const docsElement = document.getElementById('api-docs');
                    docsElement.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => console.error('Ошибка при загрузке API документации:', error));
        });
    </script>
    </body>


</html>
