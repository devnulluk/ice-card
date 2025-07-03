# ice-card
Backend for a In Case of Emergency (ICE) RFID card

Project to display ICE details and to share location when an RFID card or QR code is scanned.

mod_rewrite needs to be enabled on Apache for the short url functionality to work which keeps the QR code as simple as possible and easier to scan.

WEB_ROOT/[id] redirects to WEB_ROOT/id1.php?id=[ID]