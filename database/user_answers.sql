CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    attempt_id INT NOT NULL,
    answers JSON NOT NULL,
    date_answered DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id)
);