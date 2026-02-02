-- phpMyAdmin SQL Dump
-- version 5.1.3-3.red80
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Фев 02 2026 г., 08:06
-- Версия сервера: 10.11.11-MariaDB
-- Версия PHP: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `casino_bar`
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
  `birth_date` date NOT NULL COMMENT 'Дата рождения',
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
(4, 'Ольга', 'Васильева', '1988-03-22', '5566778899', '+79165554433', 0, '0.00', '2026-02-02 13:40:51'),
(5, 'Адольф-Тот-Самый', 'Приклз', '1889-03-23', '148814881488', '+79228671488', 85, '56000.00', '2026-02-02 14:40:12');

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
(1, 'Виски Джемсон', 'алкоголь', '350.00', 100, '120.00', 1),
(2, 'Пиво Балтика', 'алкоголь', '200.00', 200, '60.00', 1),
(3, 'Кофе Латте', 'напитки', '150.00', 300, '30.00', 1),
(4, 'Картофель фри', 'закуски', '300.00', 150, '80.00', 1),
(5, 'Чизбургер', 'закуски', '400.00', 80, '120.00', 1),
(6, 'Минеральная вода', 'напитки', '100.00', 500, '20.00', 1),
(7, 'Шоколадная залупа', 'Убойный алкоголь', '2.00', 76, '1.00', 13);

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
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Способ оплаты: cash, card, bonus и т.д',
  `game_name` varchar(100) DEFAULT NULL COMMENT 'Статус: pending, paid, completed, cancelled',
  `bet_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Сумма ставки (только для order_type="game")',
  `win_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Сумма выигрыша (только для order_type="game")'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `guest_id`, `employee_id`, `order_type`, `order_time`, `total_amount`, `status`, `payment_method`, `game_name`, `bet_amount`, `win_amount`) VALUES
(1, 1, 2, 'bar', '2026-02-02 13:41:30', '1000.00', 'paid', 'card', NULL, '0.00', '0.00'),
(2, 2, 3, 'game', '2026-02-02 13:41:48', '500.00', 'completed', 'cash', 'Рулетка', '1000.00', '1500.00');

-- --------------------------------------------------------

--
-- Структура таблицы `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL COMMENT 'Уникальный идентификатор позиции заказа',
  `order_id` int(11) NOT NULL COMMENT 'Ссылка на родительский заказ',
  `menu_item_id` int(11) DEFAULT NULL COMMENT 'Ссылка на товар из меню (NULL для специальных позиций)',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Количество единиц товара',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Цена за единицу на момент заказа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `menu_item_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 2, '350.00'),
(2, 1, 4, 1, '300.00');

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
(1, 1, '2026-02-02 13:40:51', '2026-02-02 13:42:50', '50000.00', '55000.00', '1000.00', '500.00', '0.00', 'closed');

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
  ADD KEY `idx_guests_name` (`last_name`,`first_name`);

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
-- Индексы таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Индексы таблицы `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shifts_employee` (`employee_id`),
  ADD KEY `idx_shifts_status` (`status`),
  ADD KEY `idx_shifts_time` (`start_time`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор сотрудника', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор гостя', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор товара', AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор заказа/транзакции', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор позиции заказа', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор смены', AUTO_INCREMENT=2;

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
-- Ограничения внешнего ключа таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);

--
-- Ограничения внешнего ключа таблицы `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
