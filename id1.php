<?php
require __DIR__ . '/vendor/autoload.php';
//load .env file
use Dotenv\Dotenv;$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

//setup Ntfy server
use Ntfy\Server;
use Ntfy\Message;
use Ntfy\Client;

$server = new Server($_ENV['ntfy_server_url']);

//setup mySQL connection
$conn = new mysqli($_ENV['mysql_servername'], $_ENV['mysql_username'], $_ENV['mysql_password'], $_ENV['mysql_dbname']); 
    if ($conn->connect_error) { 
          die("Connection failed: " . $conn->connect_error); 
    }

//lookup the card number
$card_number = $_GET['id'] ?? '';
if (empty($card_number)) {
    die("Card number is required.");
}

$sql = "SELECT * FROM users WHERE id = " . $card_number . ";";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
     while($row = $result->fetch_assoc()) {
        $card_name = $row['full_name'];
    }
} else {
    die("No user found with the provided card number.");
}

//send notification to ntfy server

$message = new Message();
$message->topic($_ENV['ntfy_topic']);
$message->title($card_name . " Card Scanned");
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
        <h1><?php echo $card_name; ?> - ICE</h1>
        <p>In case of emergancy please contact:</p>

<?php
$sql = "select full_name, tel from users
join ice_contacts on ice_contacts.ice_user = users.id
where ice_contacts.card_user = " . $card_number . " order by ice_contacts.priority;";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
     while($row = $result->fetch_assoc()) {
        echo "<p>" . $row['full_name'] . " <a href=\"tel:" . $row['tel'] . "\">" . $row['tel'] . "</a></p>";
    }
} else {
    die("No ICE users found for the provided card number.");
}
//close the database connection
$conn->close(); 
?>

    </body>
</html>