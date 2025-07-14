<?php
ini_set('display_errors', 0); 
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("CDN-Cache-Control: no-store");

require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

//setup mySQL connection
$conn = new mysqli($_ENV['mysql_servername'], $_ENV['mysql_username'], $_ENV['mysql_password'], $_ENV['mysql_dbname']); 
    if ($conn->connect_error) { 
          die("Connection failed: " . $conn->connect_error); 
    }
    
echo "Server Up";
    ?>