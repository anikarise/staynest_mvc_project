USE staynest_db;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS account_status ENUM('active','pending','rejected') NOT NULL DEFAULT 'active' AFTER status;

UPDATE users
SET account_status = 'active'
WHERE account_status IS NULL OR account_status = '';

CREATE INDEX IF NOT EXISTS idx_users_account_status ON users(account_status);
