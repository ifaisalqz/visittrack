-- 1. أضف عمود visit_date
ALTER TABLE visitors ADD COLUMN visit_date DATE NULL AFTER departure_time;

-- 2. الزيارات الموجودة — خذ التاريخ من created_at
UPDATE visitors SET visit_date = DATE(created_at) WHERE visit_date IS NULL;

-- 3. اجعله مطلوباً
ALTER TABLE visitors MODIFY COLUMN visit_date DATE NOT NULL;

-- 4. auto-expire: أي طلب معلق أو مقبول وتاريخه مضى
UPDATE visitors
SET status = 'expired'
WHERE status IN ('pending','approved')
  AND visit_date < CURDATE();
