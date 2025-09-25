-- A1. Топ‑5 сторінок за унікальними користувачами у діапазоні дат і країні.
-- БУЛО
SELECT 
    pv.url, 
    COUNT(DISTINCT pv.user_id) AS unique_users
FROM analytics_legacy.pv
WHERE STR_TO_DATE(pv.created_at, '%Y-%m-%d %H:%i:%s') 
          BETWEEN '2025-09-14 00:00:00' AND '2025-09-16 23:59:59'
  AND pv.country = 'UA'
GROUP BY pv.url
ORDER BY unique_users DESC
LIMIT 5;

-- СТАЛО
SELECT p.url_text AS url,
       t.unique_users
FROM (
  SELECT page_id, COUNT(*) AS unique_users
  FROM (
    SELECT page_id, user_id
    FROM analytics_opt.pv_events
    WHERE country = 'UA'
      AND created_at BETWEEN '2025-09-15 00:00:00' AND '2025-09-18 23:59:59'
    GROUP BY page_id, user_id
  ) su
  GROUP BY page_id
  ORDER BY unique_users DESC
  LIMIT 5
) t
JOIN analytics_opt.pages p ON p.id = t.page_id;
SELECT * FROM analytics_opt.pv_events;

-- A2. Batch insert 100–1000 рядків за раз з порадами щодо швидкості 
-- (транзакції, INSERT ... VALUES (...) , (...), LOAD DATA - якщо 
-- доречно). 