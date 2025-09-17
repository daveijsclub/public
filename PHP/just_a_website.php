<?php
// Get user IP
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$ip = getUserIP();

// Get geolocation info
$geo = @json_decode(file_get_contents("http://ip-api.com/json/" . $ip));
$country = $geo && $geo->status === "success" ? $geo->country : "Unknown";
$lat = $geo && $geo->status === "success" ? $geo->lat : 0;
$lon = $geo && $geo->status === "success" ? $geo->lon : 0;

// Get whois info (simple, limited to Linux servers with whois installed)
function getWhois($ip) {
    if (!preg_match('/^[0-9\.]+$/', $ip)) return "Invalid IP";
    $out = @shell_exec("whois " . escapeshellarg($ip) . " 2>&1");
    return $out ? htmlspecialchars($out) : "Whois lookup failed or not available.";
}
$whois = getWhois($ip);

// Current date
$date = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Info Page</title>
    <meta charset="utf-8">
    <style>
        body {
            background: #e6f2ff;
            color: #000;
            font-family: 'Arial Narrow', Arial, sans-serif;
            margin: 0; padding: 0;
        }
        h1 {
            font-family: Arial, sans-serif;
            font-weight: bold;
            margin-top: 30px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #bbb;
            padding: 30px;
        }
        .info {
            margin-bottom: 20px;
            font-size: 1.1em;
        }
        .whois {
            background: #f4f8fb;
            border: 1px solid #b3d1ea;
            padding: 10px;
            font-size: 0.95em;
            overflow-x: auto;
            max-height: 300px;
        }
        #map {
            height: 300px;
            margin-top: 20px;
            border: 1px solid #b3d1ea;
            border-radius: 6px;
        }
        label {
            font-weight: bold;
        }
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <h1>User Information</h1>
    <div class="container">
        <div class="info">
            <div><label>Date:</label> <?php echo $date; ?></div>
            <div><label>Your IP:</label> <?php echo htmlspecialchars($ip); ?></div>
            <div><label>Country:</label> <?php echo htmlspecialchars($country); ?></div>
        </div>
        <div>
            <label>Whois Info:</label>
            <div class="whois"><?php echo nl2br($whois); ?></div>
        </div>
        <div>
            <label>Your Location:</label>
            <div id="map"></div>
        </div>
    </div>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var lat = <?php echo json_encode($lat); ?>;
        var lon = <?php echo json_encode($lon); ?>;
        var map = L.map('map').setView([lat, lon], lat && lon ? 8 : 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        if (lat && lon) {
            L.marker([lat, lon]).addTo(map)
                .bindPopup("Approximate location").openPopup();
        }
    </script>
</body>
</html>
