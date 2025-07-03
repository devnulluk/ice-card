<?php
require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Ntfy\Server;
use Ntfy\Message;
use Ntfy\Client;

$server = new Server('$_ENV['ntfy_server_url']');

$message = new Message();
$message->topic('$_ENV['ntfy_topic']');
$message->title('Card Scanned');
$message->body('An ICE card has been scanned successfully.');
$message->priority(Message::PRIORITY_MAX);

$client = new Client($server);
$client->send($message);
?>