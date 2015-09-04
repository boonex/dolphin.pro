DROP TABLE moxman_cache;

CREATE TABLE moxman_cache (
	mc_id INT(10) NOT NULL AUTO_INCREMENT,
	mc_path VARCHAR(745) NOT NULL,
	mc_name VARCHAR(255) NOT NULL,
	mc_extension VARCHAR(255) NOT NULL,
	mc_attrs CHAR(4) NOT NULL,
	mc_size INT(11),
	mc_last_modified DATETIME NOT NULL,
	mc_cached_time DATETIME NOT NULL,
	PRIMARY KEY (mc_id),
	INDEX mc_path_mc_name (mc_path(247), mc_name(85)),
	INDEX mc_path_mc_size (mc_path(247), mc_size),
	INDEX mc_path (mc_path(333)),
	INDEX mc_name (mc_name(85))
) COLLATE=utf8_general_ci ENGINE=MyISAM;
