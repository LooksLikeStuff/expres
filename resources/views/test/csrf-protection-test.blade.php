<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Тест CSRF защиты</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        #console-log {
            background: #000;
            color: #00ff00;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Тест системы CSRF защиты</h1>
    
    <div class="test-section">
        <h2>Информация о токене</h2>
        <p><strong>Текущий токен:</strong> <span id="current-token"></span></p>
        <p><strong>Последнее обновление:</strong> <span id="last-refresh">Еще не обновлялся</span></p>
        <button class="button" onclick="updateTokenInfo()">Обновить информацию</button>
    </div>

    <div class="test-section">
        <h2>Тестирование функций</h2>
        <button class="button" onclick="testTokenRefresh()">Обновить токен вручную</button>
        <button class="button" onclick="testEnsureFresh()">Проверить свежесть токена</button>
        <button class="button" onclick="testFormSubmit()">Тест отправки формы</button>
        <button class="button" onclick="testAjaxRequest()">Тест AJAX запроса</button>
        <div id="test-results"></div>
    </div>

    <div class="test-section">
        <h2>Тестовая форма</h2>
        <form id="test-form" method="POST" action="/test-csrf">
            @csrf
            <input type="text" name="test_field" placeholder="Тестовое поле" required>
            <button type="submit" class="button">Отправить форму</button>
        </form>
    </div>

    <div class="test-section">
        <h2>Консоль (логи системы)</h2>
        <button class="button" onclick="clearConsole()">Очистить</button>
        <div id="console-log"></div>
    </div>

    <!-- Подключаем CSRF защиту -->
    <script src="{{ asset('/js/csrf-protection.js') }}"></script>
    
    <script>
        // Перехватываем console.log для отображения в интерфейсе
        const originalConsoleLog = console.log;
        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;
        
        function addToConsole(message, type = 'log') {
            const consoleDiv = document.getElementById('console-log');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff0000' : type === 'warn' ? '#ffff00' : '#00ff00';
            consoleDiv.innerHTML += `<span style="color: ${color}">[${timestamp}] ${message}</span>\n`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }
        
        console.log = function(...args) {
            originalConsoleLog.apply(console, args);
            addToConsole(args.join(' '), 'log');
        };
        
        console.error = function(...args) {
            originalConsoleError.apply(console, args);
            addToConsole(args.join(' '), 'error');
        };
        
        console.warn = function(...args) {
            originalConsoleWarn.apply(console, args);
            addToConsole(args.join(' '), 'warn');
        };

        // Функции для тестирования
        function updateTokenInfo() {
            const token = window.CSRFProtection ? window.CSRFProtection.getCurrentToken() : 'Система не загружена';
            document.getElementById('current-token').textContent = token || 'Не найден';
            
            if (window.CSRFProtection && window.CSRFProtection.lastRefresh) {
                document.getElementById('last-refresh').textContent = new Date(window.CSRFProtection.lastRefresh).toLocaleTimeString();
            }
        }

        function showResult(message, type = 'info') {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = `<div class="status ${type}">${message}</div>`;
        }

        async function testTokenRefresh() {
            try {
                showResult('Обновляем токен...', 'info');
                const newToken = await window.CSRFProtection.refreshToken();
                showResult(`Токен успешно обновлен: ${newToken.substring(0, 20)}...`, 'success');
                updateTokenInfo();
            } catch (error) {
                showResult(`Ошибка обновления токена: ${error.message}`, 'error');
            }
        }

        async function testEnsureFresh() {
            try {
                showResult('Проверяем свежесть токена...', 'info');
                const result = await window.CSRFProtection.ensureFreshToken();
                showResult(`Результат проверки: ${result ? 'Токен свежий' : 'Токен требует обновления'}`, result ? 'success' : 'error');
                updateTokenInfo();
            } catch (error) {
                showResult(`Ошибка проверки токена: ${error.message}`, 'error');
            }
        }

        function testFormSubmit() {
            showResult('Отправляем тестовую форму...', 'info');
            const form = document.getElementById('test-form');
            const testInput = form.querySelector('input[name="test_field"]');
            testInput.value = 'Тест ' + new Date().toLocaleTimeString();
            
            // Симулируем отправку формы
            const event = new Event('submit', { bubbles: true, cancelable: true });
            form.dispatchEvent(event);
        }

        async function testAjaxRequest() {
            try {
                showResult('Отправляем AJAX запрос...', 'info');
                
                const response = await fetch('/refresh-csrf', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.CSRFProtection.getCurrentToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    showResult(`AJAX запрос успешен. Получен токен: ${data.token.substring(0, 20)}...`, 'success');
                } else {
                    showResult(`Ошибка AJAX запроса: ${response.status} ${response.statusText}`, 'error');
                }
            } catch (error) {
                showResult(`Ошибка AJAX запроса: ${error.message}`, 'error');
            }
        }

        function clearConsole() {
            document.getElementById('console-log').innerHTML = '';
        }

        // Настройка callback'ов для демонстрации
        document.addEventListener('DOMContentLoaded', function() {
            if (window.CSRFProtection) {
                window.CSRFProtection.callbacks.onTokenRefresh = function(newToken) {
                    console.log('Callback: Токен обновлен!');
                    updateTokenInfo();
                };

                window.CSRFProtection.callbacks.onCriticalError = function(message) {
                    console.error('Callback: Критическая ошибка - ' + message);
                    showResult('Критическая ошибка: ' + message, 'error');
                };
            }
            
            // Обновляем информацию при загрузке
            setTimeout(updateTokenInfo, 1000);
            
            console.log('Тестовая страница CSRF защиты загружена');
        });

        // Обработка отправки формы
        document.getElementById('test-form').addEventListener('submit', function(e) {
            e.preventDefault();
            showResult('Форма была перехвачена системой CSRF защиты', 'info');
            console.log('Форма отправлена с токеном: ' + window.CSRFProtection.getCurrentToken().substring(0, 20) + '...');
        });
    </script>
</body>
</html>
