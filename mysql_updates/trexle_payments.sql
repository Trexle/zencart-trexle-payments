CREATE TABLE `trexle` (
  `id` int(11) UNSIGNED NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `response_code` int(1) DEFAULT '0',
  `response_text` varchar(255) DEFAULT '',
  `authorization_type` varchar(50) DEFAULT '',
  `transaction_id` varchar(64) DEFAULT NULL,
  `sent` longtext NOT NULL,
  `received` longtext NOT NULL,
  `time` varchar(50) DEFAULT '',
  `session_id` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `trexle_order_token` (
  `trexle_order_token` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `trexle_tokens` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `card_token` varchar(64) DEFAULT NULL,
  `cardinfo` varchar(32) DEFAULT NULL,
  `unique_card` varchar(64) NOT NULL,
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `trexle`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `trexle_order_token`
  ADD PRIMARY KEY (`trexle_order_token`);

ALTER TABLE `trexle_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `customer_id` (`customer_id`);

ALTER TABLE `trexle`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `trexle_order_token`
  MODIFY `trexle_order_token` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `trexle_tokens` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

