-- ПОГАНА СХЕМА (стартова) 
CREATE DATABASE IF NOT EXISTS analytics_legacy; 
USE analytics_legacy; 

DROP TABLE IF EXISTS pv;  
CREATE TABLE pv ( 
    id INT NOT NULL AUTO_INCREMENT, 
    user_id INT NULL, 
    url VARCHAR(1024) NULL, 
    country VARCHAR(3) NULL, 
    device VARCHAR(16) NULL, 
    created_at VARCHAR(32) NULL,    
    duration_ms INT NULL,           
    PRIMARY KEY (id) 
) ENGINE=InnoDB; 

-- вставка даних
INSERT INTO pv (user_id, url, country, device, created_at, duration_ms) VALUES 
(1,'/product/42','UA','desktop','2025-09-15 10:11:12',350), 
(2,'/product/42','UA','mobile','2025-09-15 11:01:02',900), 
(1,'/home','PL','mobile','2025-09-16 08:21:00',120), 
(3,'/product/7','UA','desktop','2025-09-16 09:01:00',220);
