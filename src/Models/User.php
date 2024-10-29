<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class User extends BaseModel
{
    public $complete_name, $email, $password;
    
    public function save($data) {
        $sql = "INSERT INTO users 
                SET
                    complete_name=:complete_name,
                    email=:email,
                    `password`=:password_hash";        
        $statement = $this->db->prepare($sql);
        $password_hash = $this->hashPassword($data['password']);
        $statement->execute([
            'complete_name' => $data['complete_name'],
            'email' => $data['email'],
            'password_hash' => $password_hash
        ]);
    
        return $statement->rowCount();
    }

    protected function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyAccess($email, $password)
    {
        $sql = "SELECT password_hash FROM users WHERE email = :email";
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'email' => $email
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return false;
        }

        return password_verify($password, $result['password_hash']);
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function toArray() {
        return [
            'complete_name' => $this->complete_name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }    

}