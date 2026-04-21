<?php
// footer.php - ЕДИНЫЙ ПОДВАЛ ДЛЯ ВСЕХ СТРАНИЦ
?>
        <!-- Подвал -->
        <footer class="footer">
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
        </footer>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Основной скрипт -->
    <script src="script.js?v=<?php echo time(); ?>"></script>
    
    <script>
        $(document).ready(function() {
            // Анимация появления карточек
            $('.card').hide().fadeIn(500);
            
            // Подтверждение удаления
            $('.btn-delete, .delete-btn').on('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этот элемент?')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Подсветка активной страницы
            var currentPage = window.location.pathname.split('/').pop();
            $('.nav-link').each(function() {
                var linkPage = $(this).attr('href');
                if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.php')) {
                    $(this).css('background', 'rgba(212, 175, 55, 0.2)');
                    $(this).css('color', 'var(--accent)');
                }
            });
        });
    </script>
</body>
</html>