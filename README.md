# ice-card
Backend for a In Case of Emergency (ICE) RFID card

Project to display ICE details and to share location when an RFID card or QR code is scanned.

mod_rewrite needs to be enabled on Apache for the short url functionality to work which keeps the QR code as simple as possible and easier to scan.

WEB_ROOT/[id] redirects to WEB_ROOT/id1.php?id=[ID]

My setup involves CloudFlare tunnels to reach the server, this also allows the setup of a worker to provide a fallback page if the server is unreachable or down.

Under workers create the following worker, under the domain settings create a working route with route being your domain followed by /* (example.com/*) and worker being the worker you set up. 

(Change WEB_ROOT to the URL of your server)

````
addEventListener("fetch", event => {
  event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
  const backendOrigin = "WEB_ROOT" // Your origin behind the Tunnel
  const url = new URL(request.url)

  // Build new URL with path and search parameters forwarded
  const targetUrl = backendOrigin + url.pathname + url.search

  try {
    const response = await fetch(targetUrl, {
      method: request.method,
      headers: request.headers,
      body: request.body,
      redirect: "follow"
    })

    // Return backend response if it's okay
    if (response.ok) return response

    // Fallback if backend gives a 5xx or error
    throw new Error(`Backend returned status ${response.status}`)
  } catch (err) {
    return new Response(`
      <html>
        <head><title>Site Unavailable</title></head>
        <body style="font-family: sans-serif;">
          <h1>We're experiencing technical issues</h1>
          <p>Sorry! Please contact us at <a href="mailto:support@devnull.co.uk">support@devnull.co.uk</a>.</p>
        </body>
      </html>
    `, {
      status: 503,
      headers: {
        "Content-Type": "text/html"
      }
    })
  }
}

````