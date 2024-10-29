CREATE TABLE user_answers.sql (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    attempt_id INT NOT NULL,
    answers JSON NOT NULL,
    date_answered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id)
);