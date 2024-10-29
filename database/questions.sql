CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_number INT NOT NULL,
    question TEXT NOT NULL,
    choices JSON NOT NULL,
    correct_answer CHAR(1) NOT NULL
);