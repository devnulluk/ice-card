<?php
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("CDN-Cache-Control: no-store");

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
$card_number = $_GET['id'];
if (empty($card_number)) {
    die("Card number is required.");
}
// get flag to set ntfy off
if (isset($_GET['ntfy'])) {
  $ntfy = $_GET['ntfy'];}
else {
    $ntfy = "true"; // default to true if not set
}

$sql = "SELECT * FROM users WHERE id = " . $card_number . ";";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
     while($row = $result->fetch_assoc()) {
        $card_name = $row['full_name'];
        $pronouns = $row['pronouns'];
    }
} else {
    die("No user found with the provided card number.");
}

// If ntfy is set to true, send a notification
if ($ntfy !== "false") {
  //send notification to ntfy server
  $url = $_ENV['web_root'] . "id1.php?id=" . $card_number . "&ntfy=false";
  $message = new Message();
  $message->topic($_ENV['ntfy_topic']);
  $message->title($card_name . " Card Scanned");
  $message->body('An ICE card has been scanned successfully.');
  $message->priority(Message::PRIORITY_MAX);
  $action = new Ntfy\Action\View();
  $action->label('View Card');
  $action->url($url);
  $message->action($action);
  $client = new Client($server);
  $client->send($message);
}
?>
<html>
    <head>
        <title><?php echo $card_name; ?> - ICE</title>
        <link rel="stylesheet" href="stylesheet.css" type="text/css" charset="utf-8" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <h1><?php echo $card_name . " (" . $pronouns . ")"; ?></h1>
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
    echo("No ICE users found for the provided card number.");
}

$sql = "select medication from medication where user = " . $card_number . " order by medication;";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  echo "<h2>Medication</h2>";
     while($row = $result->fetch_assoc()) {
        echo "<p>" . $row['medication'] . "</p>";
    }
} else {
    
}
?>


<!-- Modal requesting location-->
<div id="myModal" class="modal">

  <div class="modal-content">
    <span class="close">&times;</span>
    <h1>Please tap OK to share your location with emergency contact</h1>
    <button class="button okButton">OK</button><button class="button cancelButton">Cancel</button>
  </div>
</div>

<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var closeX = document.getElementsByClassName("close")[0];
var closeCancel = document.getElementsByClassName("button cancelButton")[0];

// Get the button that confirms sharing location
var okButton = document.getElementsByClassName("button okButton")[0];

// When the user clicks on <span> (x), close the modal
closeX.onclick = function() {
  modal.style.display = "none";
}

closeCancel.onclick = function() {
  modal.style.display = "none";
}

okButton.onclick = function() {
  modal.style.display = "none";
  // Here you can add the code to share the location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var lat = position.coords.latitude;
      var lon = position.coords.longitude;
      // You can send this data to your server or use it as needed
      console.log("Latitude: " + lat + ", Longitude: " + lon);
       if (window.XMLHttpRequest){
            xmlhttp = new XMLHttpRequest();
        }
        else{
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        var PageToSendTo = "location.php?";
        var UrlToSend = PageToSendTo + "id=" + <?php echo $card_number; ?> + "&lat=" + lat + "&lon=" + lon;
        xmlhttp.open("GET", UrlToSend, false);
        xmlhttp.send();
    }, function(error) {
      console.error("Error obtaining location: " + error.message);
    });
  } else {
    alert("Geolocation is not supported by this browser.");
  }
}   

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

document.addEventListener('DOMContentLoaded', function() {
   modal.style.display = "block";
}, false);
</script>
    </body>
</html>
<?php
// Close the database connection
$conn->close(); 
?>