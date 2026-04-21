import pytest
import time
import re
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from webdriver_manager.firefox import GeckoDriverManager

class TestCasinoLogin:
    """Тесты для страницы входа с использованием Firefox"""
    
    @pytest.fixture(autouse=True)
    def setup(self):
        """Настройка Firefox перед каждым тестом"""
        options = Options()
        options.add_argument('--width=1920')
        options.add_argument('--height=1080')
        options.set_preference("dom.webdriver.enabled", False)
        options.set_preference("useAutomationExtension", False)
        
        self.driver = webdriver.Firefox(
            service=Service(GeckoDriverManager().install()),
            options=options
        )
        self.wait = WebDriverWait(self.driver, 15)
        self.base_url = "http://localhost:3000"
        yield
        self.driver.quit()
    
    def go_to_login_page(self):
        """Переход на страницу логина"""
        self.driver.get(f"{self.base_url}/login.php")
        time.sleep(2)
    
    def solve_captcha(self):
        """Автоматическое решение капчи"""
        try:
            captcha_element = self.driver.find_element(By.CLASS_NAME, "captcha-question")
            captcha_text = captcha_element.text
            numbers = re.findall(r'\d+', captcha_text)
            if len(numbers) >= 2:
                answer = int(numbers[0]) + int(numbers[1])
                captcha_input = self.driver.find_element(By.NAME, "captcha")
                captcha_input.clear()
                captcha_input.send_keys(str(answer))
                return True
        except Exception as e:
            print(f"Ошибка при решении капчи: {e}")
            input("Решите капчу вручную и нажмите Enter...")
            return True
        return False
    
    def test_01_page_accessible(self):
        """Тест 1: Проверка доступности страницы входа"""
        self.go_to_login_page()
        assert "login.php" in self.driver.current_url or "index.php" in self.driver.current_url
        print("✓ Страница входа доступна")
    
    def test_02_login_form_exists(self):
        """Тест 2: Проверка наличия формы входа"""
        self.go_to_login_page()
        username_input = self.driver.find_element(By.NAME, "username")
        password_input = self.driver.find_element(By.NAME, "password")
        assert username_input.is_displayed()
        assert password_input.is_displayed()
        print("✓ Все поля формы присутствуют")
    
    def test_03_successful_login_admin(self):
        """Тест 3: Успешный вход администратора"""
        self.go_to_login_page()
        
        username_input = self.driver.find_element(By.NAME, "username")
        username_input.clear()
        username_input.send_keys("admin")
        
        password_input = self.driver.find_element(By.NAME, "password")
        password_input.clear()
        password_input.send_keys("admin123")
        
        self.solve_captcha()
        
        submit_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(3)
        
        assert "index.php" in self.driver.current_url
        print("✓ Успешный вход администратора")
    
    def test_04_successful_login_employee(self):
        """Тест 4: Успешный вход сотрудника"""
        self.go_to_login_page()
        
        username_input = self.driver.find_element(By.NAME, "username")
        username_input.clear()
        username_input.send_keys("employee")
        
        password_input = self.driver.find_element(By.NAME, "password")
        password_input.clear()
        password_input.send_keys("emp123")
        
        self.solve_captcha()
        
        submit_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(3)
        
        assert "index.php" in self.driver.current_url
        print("✓ Успешный вход сотрудника")
    
    def test_05_failed_login_wrong_password(self):
        """Тест 5: Неудачный вход с неверным паролем"""
        self.go_to_login_page()
        
        username_input = self.driver.find_element(By.NAME, "username")
        username_input.clear()
        username_input.send_keys("admin")
        
        password_input = self.driver.find_element(By.NAME, "password")
        password_input.clear()
        password_input.send_keys("wrong_password")
        
        self.solve_captcha()
        
        submit_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in self.driver.current_url
        print("✓ Неверный пароль - доступ запрещен")
    
    def test_06_demo_guest_access(self):
        """Тест 6: Вход как демо-гость"""
        self.go_to_login_page()
        
        demo_button = self.driver.find_element(By.XPATH, "//a[contains(text(), 'Демо')]")
        demo_button.click()
        
        time.sleep(2)
        
        assert "index.php" in self.driver.current_url
        print("✓ Демо-гость успешно вошел")
    
    def test_07_logout(self):
        """Тест 7: Проверка кнопки выхода"""
        # Входим как администратор
        self.go_to_login_page()
        
        username_input = self.driver.find_element(By.NAME, "username")
        username_input.clear()
        username_input.send_keys("admin")
        
        password_input = self.driver.find_element(By.NAME, "password")
        password_input.clear()
        password_input.send_keys("admin123")
        
        self.solve_captcha()
        
        submit_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(3)
        assert "index.php" in self.driver.current_url
        print("✓ Выполнен вход")
        
        # Ищем кнопку "Выйти" (теперь она точно есть в header.php)
        logout_button = self.driver.find_element(By.CSS_SELECTOR, ".logout-btn, a[href='logout.php']")
        assert logout_button.is_displayed()
        print("✓ Кнопка 'Выйти' найдена")
        
        # Нажимаем кнопку выхода
        logout_button.click()
        time.sleep(2)
        
        # Проверяем, что перебросило на страницу входа
        assert "login.php" in self.driver.current_url
        print("✓ Выход из системы работает")
    
    def test_08_logout_button_visible_for_authenticated(self):
        """Тест 8: Кнопка выхода видна только для авторизованных"""
        # Проверяем что на странице входа нет кнопки выхода
        self.go_to_login_page()
        logout_buttons = self.driver.find_elements(By.CSS_SELECTOR, ".logout-btn, a[href='logout.php']")
        assert len(logout_buttons) == 0
        print("✓ На странице входа нет кнопки выхода")
        
        # Входим
        username_input = self.driver.find_element(By.NAME, "username")
        username_input.clear()
        username_input.send_keys("admin")
        
        password_input = self.driver.find_element(By.NAME, "password")
        password_input.clear()
        password_input.send_keys("admin123")
        
        self.solve_captcha()
        
        submit_button = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(3)
        
        # После входа кнопка выхода должна быть
        logout_button = self.driver.find_element(By.CSS_SELECTOR, ".logout-btn, a[href='logout.php']")
        assert logout_button.is_displayed()
        print("✓ После входа кнопка выхода отображается")


if __name__ == "__main__":
    print("Запуск тестов с Firefox...")
    print("Целевой URL: http://localhost:3000/login.php")
    print("=" * 50)
    
    pytest.main([
        __file__,
        "-v",
        "-s",
        "--tb=short"
    ])