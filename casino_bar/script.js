// script.js - ОСНОВНЫЕ СКРИПТЫ
console.log('Элитное Казино - Система управления загружена');

$(document).ready(function() {
    // Фильтрация таблиц
    $('.filter-select, .filter-input').on('change keyup', function() {
        filterTable();
    });
    
    function filterTable() {
        var category = $('#filterCategory').val();
        var availability = $('#filterAvailability').val();
        var search = $('#searchInput').val().toLowerCase();
        
        $('#menuTable tbody tr').each(function() {
            var show = true;
            if (category && $(this).data('category') != category) show = false;
            if (availability && $(this).data('available') != availability) show = false;
            if (search && $(this).data('name').indexOf(search) === -1) show = false;
            $(this).toggle(show);
        });
    }
    
    // Подтверждение действий
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Вы уверены?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Автоматическое скрытие сообщений
    setTimeout(function() {
        $('.alert').fadeOut(500);
    }, 5000);
});