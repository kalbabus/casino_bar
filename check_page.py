# check_page.py - Проверка структуры страницы с Firefox
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.options import Options
from webdriver_manager.firefox import GeckoDriverManager
import time

def check_page():
    """Диагностика страницы входа"""
    options = Options()
    # Раскомментируйте для headless режима
    # options.add_argument('--headless')
    
    driver = webdriver.Firefox(
        service=Service(GeckoDriverManager().install()),
        options=options
    )

    try:
        # ИСПРАВЛЕНО: добавлен порт 3000
        url = "http://localhost:3000/login.php"
        print(f"Открываю страницу: {url}")
        driver.get(url)
        time.sleep(3)
        
        print("=" * 60)
        print("АНАЛИЗ СТРАНИЦЫ ВХОДА (Firefox)")
        print(f"Целевой URL: {url}")
        print("=" * 60)
        
        print(f"\n1. Заголовок страницы: {driver.title}")
        print(f"2. Текущий URL: {driver.current_url}")
        
        # Ищем все формы
        forms = driver.find_elements(By.TAG_NAME, "form")
        print(f"\n3. Найдено форм: {len(forms)}")
        for i, form in enumerate(forms):
            print(f"   Форма {i+1}: action='{form.get_attribute('action')}', method='{form.get_attribute('method')}'")
        
        # Ищем все input поля
        inputs = driver.find_elements(By.TAG_NAME, "input")
        print(f"\n4. Найдено input полей: {len(inputs)}")
        for inp in inputs:
            input_type = inp.get_attribute("type")
            input_name = inp.get_attribute("name")
            input_id = inp.get_attribute("id")
            print(f"   - type='{input_type}', name='{input_name}', id='{input_id}'")
        
        # Ищем кнопки
        buttons = driver.find_elements(By.TAG_NAME, "button")
        print(f"\n5. Найдено кнопок: {len(buttons)}")
        for btn in buttons:
            btn_text = btn.text
            btn_type = btn.get_attribute("type")
            btn_class = btn.get_attribute("class")
            print(f"   - text='{btn_text}', type='{btn_type}', class='{btn_class}'")
        
        # Ищем все ссылки
        links = driver.find_elements(By.TAG_NAME, "a")
        print(f"\n6. Найдено ссылок: {len(links)}")
        for link in links[:15]:  # Показываем первые 15
            link_text = link.text.strip()
            link_href = link.get_attribute("href")
            if link_text:
                print(f"   - {link_text} -> {link_href}")
        
        # Проверяем наличие капчи
        captcha = driver.find_elements(By.CLASS_NAME, "captcha-question")
        if captcha:
            print(f"\n7. Капча найдена: {captcha[0].text}")
        else:
            # Ищем капчу по другим селекторам
            captcha = driver.find_elements(By.CSS_SELECTOR, ".captcha, .captcha-question, [class*='captcha']")
            if captcha:
                print(f"\n7. Капча найдена (альтернативный селектор): {captcha[0].text}")
            else:
                print("\n7. Капча не найдена")
        
        # Проверяем наличие ошибок на странице
        errors = driver.find_elements(By.CLASS_NAME, "alert-error")
        if errors:
            print(f"\n8. Найдены ошибки на странице: {errors[0].text}")
        
        # Проверяем наличие вкладок
        tabs = driver.find_elements(By.CLASS_NAME, "tab")
        if tabs:
            print(f"\n9. Найдено вкладок: {len(tabs)}")
            for tab in tabs:
                print(f"   - {tab.text}")
        
        print("\n" + "=" * 60)
        print("СОХРАНЯЮ HTML СТРАНИЦЫ ДЛЯ АНАЛИЗА...")
        
        with open("login_page_debug.html", "w", encoding="utf-8") as f:
            f.write(driver.page_source)
        
        print("✓ Страница сохранена в login_page_debug.html")
        
        # Делаем скриншот
        driver.save_screenshot("login_page_screenshot.png")
        print("✓ Скриншот сохранен в login_page_screenshot.png")
        
        print("\n" + "=" * 60)
        print("ДИАГНОСТИКА ЗАВЕРШЕНА")
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
    finally:
        driver.quit()
        print("\nБраузер закрыт")

def test_login_manually():
    """Ручной тест входа (с ожиданием)"""
    options = Options()
    driver = webdriver.Firefox(
        service=Service(GeckoDriverManager().install()),
        options=options
    )
    
    try:
        # ИСПРАВЛЕНО: добавлен порт 3000
        driver.get("http://localhost:3000/login.php")
        print("\n=== РУЧНОЙ ТЕСТ ВХОДА ===")
        print("URL: http://localhost:3000/login.php")
        print("Пожалуйста, выполните вход вручную...")
        print("У вас есть 30 секунд")
        
        # Ждем 30 секунд
        time.sleep(30)
        
        current_url = driver.current_url
        print(f"Текущий URL: {current_url}")
        
        if "index.php" in current_url:
            print("✓ Успешный вход!")
        else:
            print("✗ Вход не выполнен")
            
    finally:
        driver.quit()

def test_connection():
    """Тест соединения с сервером"""
    import requests
    try:
        response = requests.get("http://localhost:3000/login.php", timeout=5)
        print(f"\n=== ТЕСТ СОЕДИНЕНИЯ ===")
        print(f"Статус ответа: {response.status_code}")
        if response.status_code == 200:
            print("✓ Сервер доступен")
        else:
            print(f"✗ Сервер вернул код: {response.status_code}")
    except requests.exceptions.ConnectionError:
        print("\n❌ НЕВОЗМОЖНО ПОДКЛЮЧИТЬСЯ К СЕРВЕРУ!")
        print("Проверьте:")
        print("1. Запущен ли сервер на порту 3000?")
        print("2. Правильный ли адрес? http://localhost:3000/login.php")
        print("3. Нет ли блокировки firewall?")
    except Exception as e:
        print(f"\n❌ Ошибка соединения: {e}")

if __name__ == "__main__":
    print("=" * 60)
    print("ДИАГНОСТИКА САЙТА ЭЛИТНОЕ КАЗИНО")
    print("=" * 60)
    
    # Сначала проверяем соединение
    test_connection()
    
    print("\nВыберите действие:")
    print("1. Полная диагностика страницы")
    print("2. Ручной тест входа (30 сек)")
    
    choice = input("\nВаш выбор (1/2): ")
    
    if choice == "1":
        check_page()
    elif choice == "2":
        test_login_manually()
    else:
        print("Неверный выбор, запускаю диагностику")
        check_page()