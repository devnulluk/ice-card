<?php
require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$lat = $_GET['lat'];
$lon = $_GET['lon'];

//setup Ntfy server
use Ntfy\Server;
use Ntfy\Message;
use Ntfy\Client;

$server = new Server($_ENV['ntfy_server_url']);
//send notification to ntfy server

$url = "https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lon;

$action = new Ntfy\Action\View();
$action->label('View Location');
$action->url($url);


$message = new Message();
$message->topic($_ENV['ntfy_topic']);
$message->title("Location Shared");
$message->body('Scanner location shared: ' . $lat . ', ' . $lon);
$message->clickAction($url);
$message->priority(Message::PRIORITY_MAX);
$message->action($action);

$client = new Client($server);
$client->send($message);
echo "<a href=\"" . $url . "\">" . $url . "</a>"  ;
?>