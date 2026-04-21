#!/bin/bash

# run_tests.sh - Скрипт запуска тестов для порта 3000

echo "======================================"
echo "Запуск Selenium тестов (Firefox)"
echo "Целевой URL: http://localhost:3000/login.php"
echo "======================================"

# Проверка установки Firefox
if ! command -v firefox &> /dev/null; then
    echo "Firefox не установлен!"
    echo "Установите Firefox: sudo apt install firefox"
    exit 1
fi

# Проверка доступности сервера
echo ""
echo "Проверка доступности сервера..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:3000/login.php | grep -q "200\|302"; then
    echo "✓ Сервер доступен"
else
    echo "❌ Сервер недоступен!"
    echo "Убедитесь, что сервер запущен на порту 3000"
    echo "Попробуйте: php -S localhost:3000"
    exit 1
fi

# Установка зависимостей
echo ""
echo "Установка Python зависимостей..."
pip install -r requirements.txt --quiet

# Запуск диагностики
echo ""
echo "Запуск диагностики страницы..."
python check_page.py

# Запуск тестов
echo ""
echo "Запуск тестов..."
python test_login_selenium.py

echo ""
echo "======================================"
echo "Готово!"
echo "Отчеты сохранены в папке reports/"
echo "Скриншот: login_page_screenshot.png"
echo "HTML страница: login_page_debug.html"
echo "======================================"