-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Апр 27 2026 г., 09:51
-- Версия сервера: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- Версия PHP: 8.1.2-1ubuntu2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `project_Bolshakov`
--

-- --------------------------------------------------------

--
-- Структура таблицы `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор сотрудника',
  `first_name` varchar(50) NOT NULL COMMENT 'Имя сотрудника',
  `last_name` varchar(50) NOT NULL COMMENT 'Фамилия сотрудника',
  `position` varchar(50) NOT NULL COMMENT 'Должность',
  `login` varchar(50) DEFAULT NULL COMMENT 'Логин для входа в систему',
  `password_hash` varchar(255) DEFAULT NULL COMMENT 'Хэш пароля',
  `access_level` int(11) DEFAULT 1 COMMENT 'Уровень доступа',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Статус активности'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `position`, `login`, `password_hash`, `access_level`, `is_active`) VALUES
(1, 'Анна', 'Иванова', 'менеджер', 'anna', 'ef92b778', 3, 1),
(2, 'Михаил', 'Петров', 'бармен', 'misha', 'c6ba91b9', 2, 1),
(3, 'Елена', 'Сидорова', 'крупье', 'elena', '5efc2b017', 2, 1),
(4, 'Сергей', 'Кузнецов', 'кассир', 'sergey', '11f52f7857', 2, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор гостя',
  `first_name` varchar(50) NOT NULL COMMENT 'Имя гостя',
  `last_name` varchar(50) NOT NULL COMMENT 'Фамилия гостя',
  `birth_date` date DEFAULT NULL,
  `document_number` varchar(50) DEFAULT NULL COMMENT 'Номер документа',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Контактный телефон\r\n',
  `visits_count` int(11) DEFAULT 0 COMMENT 'Количество посещений заведения',
  `total_spent` decimal(10,2) DEFAULT 0.00 COMMENT 'Общая сумма потраченных средств',
  `registration_date` datetime DEFAULT current_timestamp() COMMENT 'Дата первой регистрации в системе'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `guests`
--

INSERT INTO `guests` (`id`, `first_name`, `last_name`, `birth_date`, `document_number`, `phone`, `visits_count`, `total_spent`, `registration_date`) VALUES
(1, 'Иван', 'Смирнов', '1990-05-15', '1234567890', '+79161234567', 1, '1000.00', '2026-02-02 13:40:51'),
(2, 'Мария', 'Кузнецова', '1985-08-20', '0987654321', '+79169876543', 0, '0.00', '2026-02-02 13:40:51'),
(3, 'Алексей', 'Попов', '1992-11-30', '1122334455', '+79167778899', 0, '0.00', '2026-02-02 13:40:51'),
(4, 'Ольга', 'Васильева', '1988-03-22', '5566778899', '+79165554433', 12, '200.00', '2026-02-02 13:40:51'),
(5, 'Адольф-Тот-Самый', 'Приклз', '1889-03-23', '148814881488', '+79228671488', 85, '560000.00', '2026-02-02 14:40:12'),
(8, 'Алексей', 'Савинко', '2007-02-01', '1223478', '79234679372', 2, '5100.00', '2026-04-20 10:58:28'),
(9, 'Илья', 'Носков', '2001-04-01', '67312761', '79000579332', 3, '7590.00', '2026-04-20 11:40:11'),
(10, 'Администратор', 'Системы', NULL, NULL, 'admin', 1, '550.00', '2026-04-20 11:59:39');

-- --------------------------------------------------------

--
-- Структура таблицы `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор товара',
  `name` varchar(100) NOT NULL COMMENT 'Наименование товара',
  `category` varchar(50) NOT NULL COMMENT 'Категория',
  `price` decimal(10,2) NOT NULL COMMENT 'Розничная цена для гостей',
  `stock_quantity` int(11) NOT NULL COMMENT 'Количество товара в наличии на складе',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT 'Себестоимость товара',
  `is_available` tinyint(1) DEFAULT 1 COMMENT 'Доступность товара для заказа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `category`, `price`, `stock_quantity`, `cost_price`, `is_available`) VALUES
(1, 'Виски Джемсон', 'spirits', '359.00', 100, '120.00', 1),
(2, 'Пиво Балтика', 'beer', '199.00', 200, '60.00', 1),
(3, 'Кофе Латте', 'coffee', '152.00', 300, '30.00', 1),
(4, 'Картофель фри', 'appetizers', '300.00', 150, '80.00', 1),
(5, 'Чизбургер', 'burgers', '419.00', 80, '120.00', 1),
(6, 'Минеральная вода', 'water', '189.00', 500, '20.00', 1),
(7, 'Шоколадная залупа', 'spirits', '1.00', 76, '1.00', 1),
(9, 'Чизнигер', 'appetizers', '129.00', 235, NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор заказа/транзакции',
  `guest_id` int(11) DEFAULT NULL COMMENT 'Ссылка на гостя',
  `employee_id` int(11) NOT NULL COMMENT 'Сотрудник, обслуживающий заказ/игру',
  `order_type` enum('bar','game') NOT NULL COMMENT 'Тип операции: bar - бар, game - игра',
  `order_time` datetime DEFAULT current_timestamp() COMMENT 'Дата и время создания заказа',
  `total_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Итоговая сумма (прибыль для казино)',
  `status` varchar(50) DEFAULT 'pending' COMMENT 'Способ оплаты: cash, card, bonus и т.д',
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Способ оплаты: cash, card, bonus и т.д',
  `game_name` varchar(100) DEFAULT NULL COMMENT 'Статус: pending, paid, completed, cancelled',
  `bet_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Сумма ставки (только для order_type="game")',
  `win_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Сумма выигрыша (только для order_type="game")'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `guest_id`, `employee_id`, `order_type`, `order_time`, `total_amount`, `status`, `notes`, `payment_method`, `game_name`, `bet_amount`, `win_amount`) VALUES
(1, 1, 2, 'bar', '2026-02-02 13:41:30', '1000.00', 'paid', NULL, 'card', NULL, '0.00', '0.00'),
(2, 2, 3, 'game', '2026-02-02 13:41:48', '500.00', 'completed', NULL, 'cash', 'Рулетка', '1000.00', '1500.00'),
(3, 8, 1, 'bar', '2026-04-20 11:14:33', '1950.00', 'pending', NULL, NULL, NULL, '0.00', '0.00'),
(4, 8, 1, 'bar', '2026-04-20 11:20:17', '3150.00', 'pending', NULL, NULL, NULL, '0.00', '0.00'),
(5, 9, 1, 'bar', '2026-04-20 11:41:15', '1400.00', 'pending', NULL, NULL, NULL, '0.00', '0.00'),
(6, 10, 1, 'game', '2026-04-20 11:59:43', '50.00', 'completed', NULL, NULL, 'Рулетка', '50.00', '0.00'),
(7, 10, 1, 'game', '2026-04-20 11:59:50', '50.00', 'completed', NULL, NULL, 'Рулетка', '50.00', '0.00'),
(8, 10, 1, 'game', '2026-04-20 11:59:56', '50.00', 'completed', NULL, NULL, 'Рулетка', '50.00', '0.00'),
(9, 10, 1, 'game', '2026-04-20 11:59:57', '50.00', 'completed', NULL, NULL, 'Рулетка', '50.00', '0.00'),
(10, 10, 1, 'game', '2026-04-20 12:00:11', '50.00', 'completed', NULL, NULL, 'Кости', '50.00', '100.00'),
(11, 10, 1, 'game', '2026-04-20 12:00:12', '50.00', 'completed', NULL, NULL, 'Кости', '50.00', '0.00'),
(12, 10, 1, 'game', '2026-04-20 12:00:12', '50.00', 'completed', NULL, NULL, 'Кости', '50.00', '0.00'),
(13, 10, 1, 'game', '2026-04-20 12:00:13', '50.00', 'completed', NULL, NULL, 'Кости', '50.00', '0.00'),
(14, 10, 1, 'game', '2026-04-20 12:00:14', '100.00', 'completed', NULL, NULL, 'Кости', '100.00', '0.00'),
(15, 10, 1, 'game', '2026-04-20 12:00:14', '100.00', 'completed', NULL, NULL, 'Кости', '100.00', '0.00'),
(16, 10, 1, 'game', '2026-04-20 12:00:14', '100.00', 'completed', NULL, NULL, 'Кости', '100.00', '200.00'),
(17, 9, 1, 'bar', '2026-04-20 12:03:50', '4900.00', 'pending', NULL, NULL, NULL, '0.00', '0.00'),
(18, 9, 1, 'bar', '2026-04-20 13:35:00', '1290.00', 'pending', NULL, NULL, NULL, '0.00', '0.00');

-- --------------------------------------------------------

--
-- Структура таблицы `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор смены',
  `employee_id` int(11) NOT NULL COMMENT 'Сотрудник, ответственный за смену',
  `start_time` datetime NOT NULL COMMENT 'Время начала смены',
  `end_time` datetime DEFAULT NULL COMMENT 'Время окончания смены',
  `initial_cash` decimal(10,2) NOT NULL COMMENT 'Начальная сумма в кассе',
  `final_cash` decimal(10,2) DEFAULT NULL COMMENT 'Конечная сумма в кассе',
  `bar_revenue` decimal(10,2) DEFAULT 0.00 COMMENT 'Выручка бара за смену',
  `casino_revenue` decimal(10,2) DEFAULT 0.00 COMMENT 'Выручка казино за смену',
  `expenses` decimal(10,2) DEFAULT 0.00 COMMENT 'Расходы за смену (закупки, инкассация и т.д.)',
  `status` enum('active','closed') DEFAULT 'active' COMMENT 'татус смены: active - активна, closed - закрыта'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `shifts`
--

INSERT INTO `shifts` (`id`, `employee_id`, `start_time`, `end_time`, `initial_cash`, `final_cash`, `bar_revenue`, `casino_revenue`, `expenses`, `status`) VALUES
(5, 1, '2026-02-09 13:12:33', '2026-02-09 13:26:56', '50000.00', '50000.00', '0.00', '0.00', '0.00', 'closed'),
(6, 1, '2026-02-09 13:59:46', '2026-03-05 13:28:32', '50000.00', '50000.00', '0.00', '0.00', '0.00', 'closed'),
(9, 1, '2026-04-20 11:30:04', '2026-04-20 11:30:11', '10000.00', '10000.00', '0.00', '0.00', '0.00', 'closed');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','employee','guest') DEFAULT 'guest',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `login_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `first_name`, `last_name`, `phone`, `created_at`, `last_login`, `is_active`, `login_count`) VALUES
(1, 'admin', '$2y$10$rp4RU1A3EIBzjWBmhu1JYeVkQhTVNCWUngSujY7LVpzcGG0q55r/i', 'admin@casino.ru', 'admin', 'Администратор', 'Системы', NULL, '2026-02-09 06:51:16', '2026-04-21 06:06:18', 1, 9),
(2, 'employee', '$2y$10$p9q8r7s6t5u4v3w2x1y0z.A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P', 'employee@casino.ru', 'employee', 'Сотрудник', 'Тестовый', NULL, '2026-02-09 06:51:16', '2026-04-21 05:03:30', 1, 3),
(3, 'anna', 'ef92b778', NULL, 'admin', 'Анна', 'Иванова', NULL, '2026-02-09 06:51:16', NULL, 1, 0),
(4, 'misha', 'c6ba91b9', NULL, 'employee', 'Михаил', 'Петров', NULL, '2026-02-09 06:51:16', NULL, 1, 0),
(5, 'elena', '5efc2b017', NULL, 'employee', 'Елена', 'Сидорова', NULL, '2026-02-09 06:51:16', NULL, 1, 0),
(6, 'sergey', '11f52f7857', NULL, 'employee', 'Сергей', 'Кузнецов', NULL, '2026-02-09 06:51:16', NULL, 1, 0),
(10, 'mark', '$2y$10$JGY7M3431nq4kQ8dQoefFuFLlyXTaFrMCAan3EQ6WoJQRd6PX44we', 'mobab@gmai.com', 'guest', 'mark', 'mark', '79000579342', '2026-04-20 03:13:13', '2026-04-20 03:21:12', 1, 3),
(13, 'seeeeeer', '$2y$10$j5AzUjrTDzursPVgiTjfJ.BLurgq8X3WVmfh/TLpisB1zGZQtFKvG', 'sobab@gmai.com', 'guest', 'ser', 'ser', '79000579362', '2026-04-20 03:49:15', NULL, 1, 0),
(14, 'Alexs', '$2y$10$1UX1qFHYeU9zuseR/0qaM.GE5wotqPoUUpn4xwxAHvPT1NX08z90O', 'aleksej.savenko.07@bk.ru', 'guest', 'Алексей', 'Савинко', '79234679372', '2026-04-20 03:58:28', '2026-04-20 04:02:43', 1, 1),
(15, 'Nasos', '$2y$10$Ckaj8z/IBWymY.6vxZdt0ec3wCu0fG95JcxUAvEySLIPP7rYsVIta', 'aleksej.noskov.07@bk.ru', 'guest', 'Илья', 'Носков', '79000579332', '2026-04-20 04:40:11', '2026-04-20 06:34:27', 1, 5);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `idx_guests_document` (`document_number`),
  ADD KEY `idx_guests_phone` (`phone`),
  ADD KEY `idx_guests_name` (`last_name`,`first_name`),
  ADD KEY `idx_document` (`document_number`);

--
-- Индексы таблицы `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_category` (`category`),
  ADD KEY `idx_menu_price` (`price`),
  ADD KEY `idx_menu_availability` (`is_available`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_orders_time` (`order_time`),
  ADD KEY `idx_orders_guest` (`guest_id`),
  ADD KEY `idx_orders_type` (`order_type`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Индексы таблицы `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shifts_employee` (`employee_id`),
  ADD KEY `idx_shifts_status` (`status`),
  ADD KEY `idx_shifts_time` (`start_time`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор сотрудника', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор гостя', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор товара', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор заказа/транзакции', AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор смены', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Ограничения внешнего ключа таблицы `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
