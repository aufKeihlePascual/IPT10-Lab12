<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class UserAnswer extends BaseModel
{
    protected $user_id;
    protected $answers;

    public function save($user_id, $answers)
    {
        $this->user_id = $user_id;
        $this->answers = $answers;

        $sql = "INSERT INTO user_answers
                SET
                    user_id=:user_id,
                    answers=:answers";        
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'answers' => $answers
        ]);
    
        return $statement->rowCount();
    }

    public function saveAttempt($user_id, $exam_items, $score)
    {
        $sql = "INSERT INTO exam_attempts
                SET
                    user_id=:user_id,
                    exam_items=:exam_items,
                    score=:score";   
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'user_id' => $user_id,
            'exam_items' => $exam_items,
            'score' => $score
        ]);
    }

}