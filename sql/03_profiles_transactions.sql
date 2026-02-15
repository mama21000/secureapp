-- PROFILE TABLE
-- Keep user auth info in users; profile holds editable personal details + bio + avatar metadata.
CREATE TABLE IF NOT EXISTS profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  full_name VARCHAR(120) NULL,
  phone VARCHAR(30) NULL,
  bio MEDIUMTEXT NULL,                 -- long content
  avatar_path VARCHAR(255) NULL,       -- relative path like "avatars/abc123.webp" (NOT user supplied)
  avatar_mime VARCHAR(60) NULL,        -- e.g., image/png
  avatar_size INT UNSIGNED NULL,       -- bytes
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profiles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

-- TRANSACTIONS TABLE
-- Each transfer inserts 1 row, showing sender, receiver, amount, comment.
CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  amount INT UNSIGNED NOT NULL,        -- store as smallest unit (e.g., Rupees as int). Must be > 0.
  comment VARCHAR(255) NULL,           -- optional; shown to receiver
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_tx_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE RESTRICT,

  CONSTRAINT fk_tx_receiver
    FOREIGN KEY (receiver_id) REFERENCES users(id)
    ON DELETE RESTRICT,

  -- Prevent self-transfer at DB level
  CONSTRAINT chk_not_self CHECK (sender_id <> receiver_id),

  -- Ensure amount is positive
  CONSTRAINT chk_amount_positive CHECK (amount > 0),

  INDEX idx_sender_time (sender_id, created_at),
  INDEX idx_receiver_time (receiver_id, created_at),
  INDEX idx_sender_receiver_time (sender_id, receiver_id, created_at)
);

-- Optional: ensure every user has a profiles row (create on register or via trigger)
-- Recommended: create profile row in PHP immediately after user insert.

