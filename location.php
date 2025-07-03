<?php
require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$card_number = $_GET['id'];
$lat = $_GET['lat'];
$lon = $_GET['lon'];

//setup Ntfy server
use Ntfy\Server;
use Ntfy\Message;
use Ntfy\Client;

//setup mySQL connection
$conn = new mysqli($_ENV['mysql_servername'], $_ENV['mysql_username'], $_ENV['mysql_password'], $_ENV['mysql_dbname']); 
    if ($conn->connect_error) { 
          die("Connection failed: " . $conn->connect_error); 
    }

//lookup the card number
$card_number = $_GET['id'];
if (empty($card_number)) {
    
}

$sql = "SELECT full_name FROM users WHERE id = " . $card_number . ";";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
     while($row = $result->fetch_assoc()) {
        $card_name = $row['full_name'];
    }
} else {
    
}


$server = new Server($_ENV['ntfy_server_url']);
//send notification to ntfy server

$url = "https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lon;

$action = new Ntfy\Action\View();
$action->label('View Location');
$action->url($url);


$message = new Message();
$message->topic($_ENV['ntfy_topic']);
$message->title($card_name . " Location Shared");
$message->body('Scanner location shared: ' . $lat . ', ' . $lon);
$message->clickAction($url);
$message->priority(Message::PRIORITY_MAX);
$message->action($action);

$client = new Client($server);
$client->send($message);
echo "<a href=\"" . $url . "\">" . $url . "</a>"  ;
?>