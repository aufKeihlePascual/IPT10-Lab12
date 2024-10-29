<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Models\DatabaseConnection;

// Initialize Templating Engine
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views')
));

$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
$dotenv->load();

// Initialize Database Connection
$db_type = $_ENV['DB_CONNECTION'];
$db_host = $_ENV['DB_HOST'];
$db_port = $_ENV['DB_PORT'];
$db_name = $_ENV['DB_DATABASE'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];

$db = new DatabaseConnection(
    $db_type,
    $db_host,
    $db_port,
    $db_name,
    $db_username,
    $db_password
);
$conn = $db->connect();

function tableExists($conn, $tableName) {
    $query = "SHOW TABLES LIKE :tableName";
    $stmt = $conn->prepare($query);
    $stmt->execute(['tableName' => $tableName]);
    return $stmt->rowCount() > 0;
}

if ($conn) {
    $directory = __DIR__ . '/database';

    $sqlFiles = [
        'users.sql' => 'users',
        'exam_attempts.sql' => 'exam_attempts',
        'questions.sql' => 'questions',
        'user_answers.sql' => 'user_answers'
    ];

    foreach ($sqlFiles as $file => $tableName) {
        if (!tableExists($conn, $tableName)) {
            $filePath = "{$directory}/{$file}";

            if (file_exists($filePath)) {
                $sql = file_get_contents($filePath);
                try {
                    $conn->exec($sql);
                    echo "Executed $file successfully.\n";
                } catch (PDOException $e) {
                    echo "Error executing $file: " . $e->getMessage() . "\n";
                }
            } else {
                echo "File $file does not exist.\n";
            }
        }
    }
} else {
    echo "Database connection failed.\n";
}