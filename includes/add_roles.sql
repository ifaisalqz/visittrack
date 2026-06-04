ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin','supervisor') NOT NULL DEFAULT 'admin';

UPDATE users SET role = 'admin' WHERE role = 'admin' OR role IS NULL;

INSERT INTO users (username, password, role)
SELECT 'supervisor',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'supervisor'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'supervisor');
