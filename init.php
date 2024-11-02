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