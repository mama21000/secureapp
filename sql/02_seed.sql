-- Optional seed admin/test account (password: Password@123)
INSERT INTO users (username, email, password_hash, balance)
VALUES (
  'testuser',
  'testuser@example.com',
  '$2y$10$wHcO0nG2j4dO7r6Q1u1yA.2y8x6oJxE7O8m1xKq1kC3b5bVY8l0eC',
  100
)
ON DUPLICATE KEY UPDATE username=username;

