DROP TABLE IF EXISTS `#__h5p`;
DROP TABLE IF EXISTS `#__h5p_status`;
DROP TABLE IF EXISTS `#__h5p_content`;
DROP TABLE IF EXISTS `#__h5p_libraries`;
DROP TABLE IF EXISTS `#__h5p_library_dependencies`;
DROP TABLE IF EXISTS `#__h5p_library_subdependencies`;
DROP TABLE IF EXISTS `#__h5p_library_languages`;

CREATE TABLE `#__h5p` (
	`h5p_id` varchar(42) NOT NULL DEFAULT '',
	`title` VARCHAR(100) NOT NULL DEFAULT '',
	`json_content` longtext NOT NULL,
	`embed_type` varchar(127) NOT NULL DEFAULT '',
	`main_library_id` int(10) unsigned NOT NULL,
	`content_type` varchar(127) DEFAULT NULL,
	`author` varchar(127) DEFAULT NULL,
	`license` varchar(7) DEFAULT NULL,
	`meta_keywords` text,
	`meta_description` text,
	PRIMARY KEY (`h5p_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_content` (
	`h5p_id` varchar(42) NOT NULL,
	`content_id` INT(10) NOT NULL,
	 PRIMARY KEY  (`h5p_id`, `content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_libraries` (
	`library_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`machine_name` varchar(255) NOT NULL DEFAULT '',
	`title` varchar(255) NOT NULL DEFAULT '',
	`major_version` int(10) unsigned NOT NULL,
	`minor_version` int(10) unsigned NOT NULL,
	`patch_version` int(10) unsigned NOT NULL,
	`runnable` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`fullscreen` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`embed_types` varchar(255) NOT NULL DEFAULT '',
	`preloaded_js` text,
	`preloaded_css` text,
	`drop_library_css` text,
	`semantics` text NOT NULL,
	PRIMARY KEY (`library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_library_dependencies` (
	`h5p_id` varchar(42) NOT NULL,
	`library_id` int(10) unsigned NOT NULL,
	`preloaded` tinyint(1) unsigned NOT NULL DEFAULT '1',
	`drop_css` tinyint(1) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`h5p_id`,`library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_library_subdependencies` (
	`library_id` int(10) unsigned NOT NULL,
	`required_library_id` int(10) unsigned NOT NULL,
	`dependency_type` varchar(255) NOT NULL,
	PRIMARY KEY (`library_id`,`required_library_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_library_languages` (
	`library_id` int(10) unsigned NOT NULL,
	`language_code` varchar(10) NOT NULL,
	`language_json` text NOT NULL,
	PRIMARY KEY (`library_id`,`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__h5p_status` (
	`h5p_id` varchar(42) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL,
	`started` int(11) NOT NULL,
	`finished` int(11) NOT NULL DEFAULT 0,
	`score` int(3),
	`max_score` int(4),
	PRIMARY KEY (`h5p_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;