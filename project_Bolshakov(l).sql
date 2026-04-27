-- phpMyAdmin SQL Dump
-- version 5.1.3-3.red80
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 27 2026 г., 02:51
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
-- База данных: `project_Bolshakov`
--

-- --------------------------------------------------------

--
-- Структура таблицы `guest_sessions`
--

CREATE TABLE `guest_sessions` (
  `id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, 1, 4, 1, '300.00'),
(3, 3, 1, 5, '350.00'),
(4, 3, 2, 1, '200.00'),
(5, 4, 1, 9, '350.00'),
(6, 5, 1, 4, '350.00'),
(7, 17, 1, 14, '350.00'),
(8, 18, 9, 10, '129.00');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `guest_sessions`
--
ALTER TABLE `guest_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`);

--
-- Индексы таблицы `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_category` (`category`),
  ADD KEY `idx_menu_price` (`price`),
  ADD KEY `idx_menu_availability` (`is_available`);

--
-- Индексы таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `guest_sessions`
--
ALTER TABLE `guest_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор товара', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор позиции заказа', AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
