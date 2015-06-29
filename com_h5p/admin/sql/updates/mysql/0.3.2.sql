CREATE TABLE `#__h5p_status` (
	`h5p_id` varchar(42) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL,
	`started` int(11) NOT NULL,
	`finished` int(11) NOT NULL DEFAULT 0,
	`score` int(3),
	`max_score` int(4),
	PRIMARY KEY (`h5p_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;