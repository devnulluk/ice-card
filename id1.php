<?php
require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Ntfy\Server;
use Ntfy\Message;
use Ntfy\Client;

$server = new Server($_ENV['ntfy_server_url']);

$message = new Message();
$message->topic($_ENV['ntfy_topic']);
$message->title($_ENV['card_name'] . " Card Scanned");
$message->body('An ICE card has been scanned successfully.');
$message->priority(Message::PRIORITY_MAX);

$client = new Client($server);
$client->send($message);

?>
<html>
<head>
    <title><?php echo $_ENV['card_name']; ?> - ICE</title>
    <link rel="stylesheet" href="stylesheet.css" type="text/css" charset="utf-8" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
    <h1><?php echo $_ENV['card_name']; ?> - ICE</h1>
    <p>In case of emergancy please contact:</p>
    <p><?php echo $_ENV['ice_name']; ?> <a href="tel:<?php echo $_ENV['ice_tel']; ?>"><?php echo $_ENV['ice_tel']; ?></a></p>
</body>
</html>