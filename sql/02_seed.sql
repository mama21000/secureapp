-- Optional seed admin/test account (password: Password@123)
INSERT INTO users (username, email, password_hash, balance)
VALUES (
  'testuser',
  'testuser@example.com',
  '$2y$10$bMrUd7PZQ7uLDUWhhjc1ietUgg/P7sV.wU1f252uKYJ65buUfuqKq',
  100
)
ON DUPLICATE KEY UPDATE username=username;

