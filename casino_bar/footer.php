<?php
// footer.php
?>
    
        <!-- Подвал -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-logo">
                        <h3><i class="fas fa-gem"></i> Элитное Казино</h3>
                        <p>Система управления премиальным заведением</p>
                    </div>
                    <div class="footer-info">
                        <p><i class="fas fa-map-marker-alt"></i> г. Москва, ул. Премиальная, 1</p>
                        <p><i class="fas fa-phone"></i> +7 (495) 123-45-67</p>
                        <p><i class="fas fa-envelope"></i> info@elite-casino.ru</p>
                    </div>
                    <div class="footer-copy">
                        <p>&copy; <?php echo date('Y'); ?> Элитное Казино. Все права защищены.</p>
                        <p>Разработано для демонстрации системы управления</p>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- закрытие container -->
    
    <!-- Скрипты -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    
    <script>
        // Базовые скрипты для взаимодействия
        $(document).ready(function() {
            // Подтверждение удаления
            $('.btn-delete').on('click', function() {
                return confirm('Вы уверены, что хотите удалить этот элемент?');
            });
            
            // Анимация появления элементов
            $('.card').hide().fadeIn(500);
            
            // Подсветка активной страницы в навигации
            var currentPage = window.location.pathname.split('/').pop();
            $('.nav-link').each(function() {
                var linkPage = $(this).attr('href');
                if (linkPage === currentPage) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>