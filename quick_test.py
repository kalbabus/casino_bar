#!/usr/bin/env python3
# quick_test.py - Быстрая проверка работы сайта

import requests
from selenium import webdriver
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.options import Options
from webdriver_manager.firefox import GeckoDriverManager
import time

def test_connection():
    """Тест HTTP соединения"""
    print("\n=== ТЕСТ HTTP СОЕДИНЕНИЯ ===")
    try:
        response = requests.get("http://localhost:3000/login.php", timeout=5)
        print(f"Статус: {response.status_code}")
        if response.status_code == 200:
            print("✓ Сервер отвечает")
            return True
    except Exception as e:
        print(f"❌ Ошибка соединения: {e}")
        return False

def test_selenium():
    """Тест Selenium"""
    print("\n=== ТЕСТ SELENIUM ===")
    options = Options()
    options.add_argument('--headless')
    
    try:
        driver = webdriver.Firefox(
            service=Service(GeckoDriverManager().install()),
            options=options
        )
        driver.get("http://localhost:3000/login.php")
        time.sleep(2)
        
        print(f"Заголовок: {driver.title}")
        print(f"URL: {driver.current_url}")
        
        # Проверяем наличие элементов
        elements = driver.find_elements(By.NAME, "username")
        if elements:
            print("✓ Форма входа найдена")
        else:
            print("✗ Форма входа не найдена")
        
        driver.quit()
        return True
    except Exception as e:
        print(f"❌ Ошибка Selenium: {e}")
        return False

if __name__ == "__main__":
    print("=" * 50)
    print("БЫСТРАЯ ПРОВЕРКА САЙТА")
    print("URL: http://localhost:3000/login.php")
    print("=" * 50)
    
    if test_connection():
        test_selenium()