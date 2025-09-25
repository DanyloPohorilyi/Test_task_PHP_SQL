CREATE DATABASE IF NOT EXISTS analytics_opt;
USE analytics_opt;
DROP TABLE IF EXISTS pv_events;
DROP TABLE IF EXISTS pages;

-- Нормалізація URL (текст + хеш)
DROP TABLE IF EXISTS pages;
CREATE TABLE pages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
    url_hash CHAR(32) NOT NULL,
    url_text VARCHAR(1024) NOT NULL,
    created_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_pages_urlhash (url_hash),
    INDEX idx_pages_urlprefix (url_text(255))
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS device_lookup;
CREATE TABLE device_lookup (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  name VARCHAR(32) NOT NULL,
  UNIQUE KEY uk_device_name (name)
) ENGINE=InnoDB;

INSERT IGNORE INTO device_lookup (id, name) VALUES
(1,'desktop'), (2,'mobile'), (3,'tablet'), (4,'other');

DROP TABLE IF EXISTS pv_events;
CREATE TABLE pv_events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  page_id INT UNSIGNED NOT NULL,
  country CHAR(2) NOT NULL,
  device_id TINYINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  duration_ms INT UNSIGNED NULL,
  PRIMARY KEY (id),
  INDEX idx_country_created_page (country, created_at, page_id),
  INDEX idx_page_created (page_id, created_at),
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_created (created_at),
  CONSTRAINT fk_pv_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE RESTRICT
) ENGINE=InnoDB
  ROW_FORMAT=DYNAMIC;

INSERT INTO pages (url_hash, url_text, created_at) VALUES
(MD5('/home'), '/home', NOW()),
(MD5('/about'), '/about', NOW()),
(MD5('/contact'), '/contact', NOW()),
(MD5('/product/42'), '/product/42', NOW()),
(MD5('/product/7'), '/product/7', NOW()),
(MD5('/category/electronics'), '/category/electronics', NOW()),
(MD5('/category/books'), '/category/books', NOW()),
(MD5('/search?q=shoes'), '/search?q=shoes', NOW()),
(MD5('/cart'), '/cart', NOW()),
(MD5('/checkout'), '/checkout', NOW());

INSERT INTO pv_events (user_id, page_id, country, device_id, created_at, duration_ms) VALUES
(1, 1, 'UA', 1, '2025-09-15 10:11:12', 350),
(2, 4, 'UA', 2, '2025-09-15 11:01:02', 900),
(7, 4, 'UA', 2, '2025-09-15 11:01:02', 900),
(1, 2, 'PL', 2, '2025-09-16 08:21:00', 120),
(3, 5, 'UA', 1, '2025-09-16 09:01:00', 220),
(4, 4, 'DE', 3, '2025-09-17 12:00:00', 460),
(5, 6, 'UA', 1, '2025-09-17 14:30:00', 300),
(6, 7, 'FR', 2, '2025-09-18 09:45:00', 180),
(7, 8, 'UA', 2, '2025-09-18 11:10:00', 600),
(8, 9, 'PL', 3, '2025-09-19 13:50:00', 240),
(9, 10, 'UA', 1, '2025-09-20 15:20:00', 500);

SELECT 
    pv.id AS event_id,
    pv.user_id,
    p.url_text,
    p.url_hash,
    pv.country,
    d.name AS device_name,
    pv.created_at AS event_time,
    pv.duration_ms
FROM pv_events pv
JOIN pages p ON pv.page_id = p.id
LEFT JOIN device_lookup d ON pv.device_id = d.id
ORDER BY pv.created_at DESC;

