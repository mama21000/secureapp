-- Optional seed testuser account (password: xf01@1234Hgppx)
INSERT INTO users (username, email, password_hash, balance)
VALUES (
  'testuser',
  'testuser@example.com',
  '$2y$10$ONMP0AQRBq6kAUDnBiE.EeQ6JA5k3dt/w4cDpZIxEA8.jLhjCGZ/.',
  100
)
ON DUPLICATE KEY UPDATE username=username;

