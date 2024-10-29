<?php

namespace App\Models;

use App\Models;

class BaseModel
{
    protected $db;

    public function __construct()
    {
        // Global Database Connection
        global $conn;
        $this->db = $conn;        
    }

    public function fill($payload)
    {
        foreach ($payload as $key => $value)
        {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}