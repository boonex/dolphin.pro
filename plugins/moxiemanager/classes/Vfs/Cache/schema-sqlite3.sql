CREATE TABLE moxman_cache (
	mc_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	mc_path VARCHAR(1024) NOT NULL,
	mc_name VARCHAR(255) NOT NULL,
	mc_extension VARCHAR(255) NOT NULL,
	mc_attrs VARCHAR(4) NOT NULL,
	mc_size INTEGER,
	mc_last_modified TIMESTAMP NOT NULL,
	mc_cached_time TIMESTAMP NOT NULL
);

CREATE INDEX idx_mc_path ON moxman_cache(mc_path);
CREATE INDEX idx_mc_path_mc_name ON moxman_cache(mc_path, mc_name);
CREATE INDEX idx_mc_path_mc_size ON moxman_cache(mc_path, mc_size);
