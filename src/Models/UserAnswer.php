<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class UserAnswer extends BaseModel
{
    protected $user_id;
    protected $answers;

    public function save($user_id, $answers, $attempt_id)
    {
        $this->user_id = $user_id;
        $this->answers = $answers;

        var_dump([
            'user_id' => $user_id,
            'answers' => $answers,
            'attempt_id' => $attempt_id
        ]);

        $sql = "INSERT INTO user_answers
            SET
                user_id=:user_id,
                answers=:answers,
                attempt_id=:attempt_id"
        ;    

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'user_id' => $user_id,
            'answers' => $answers,
            'attempt_id' => $attempt_id
        ]);
    
        return $statement->rowCount();
    }

    public function saveAttempt($user_id, $exam_items, $score)
    {
        $sql = "INSERT INTO exam_attempts (user_id, exam_items, score) VALUES (:user_id, :exam_items, :score)";
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'exam_items' => $exam_items,
            'score' => $score
        ]);

        return $this->db->lastInsertId();
    }

    public function getLatestAttemptByUserId($user_id)
    {
        $sql = "SELECT id AS attempt_id FROM exam_attempts WHERE user_id = :user_id ORDER BY attempt_date DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    public function exportData($attempt_id) {
        $sql = "SELECT 
            ua.id AS answer_id,  
            ua.attempt_id,
            ua.answers,
            ua.date_answered,
            ea.attempt_date AS attempt_date,  
            u.complete_name AS examinee_name,
            u.email AS examinee_email,
            ea.exam_items,
            ea.score AS exam_score  
        FROM 
            users_answers AS ua
        JOIN 
            users AS u ON ua.user_id = u.id
        JOIN 
            exam_attempts AS ea ON ua.attempt_id = ea.id  
        WHERE 
            ea.id = :attempt_id  
        ORDER BY 
            ua.date_answered DESC";

        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':attempt_id', $attempt_id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAllExamAttempts()
    {
        $sql = "SELECT u.complete_name, u.email, ea.attempt_date, 
                       ea.exam_items, ea.score, ea.id as attempt_id
                FROM exam_attempts ea
                INNER JOIN users u ON ea.user_id = u.id";
                
        $statement = $this->db->prepare($sql);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

}