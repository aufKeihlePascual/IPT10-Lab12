CREATE TABLE exam_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    exam_items INT NOT NULL,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);