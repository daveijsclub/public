<?php
// Haal het IP-adres van de gebruiker op
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$ip = getUserIP();

// Haal geolocatie-informatie op via ip-api.com
$geo = @json_decode(file_get_contents("http://ip-api.com/json/" . $ip));
$country = $geo && $geo->status === "success" ? $geo->country : "Unknown";
$lat = $geo && $geo->status === "success" ? $geo->lat : 0;
$lon = $geo && $geo->status === "success" ? $geo->lon : 0;

// Haal whois-informatie op via Talos Intelligence
function getWhois($ip) {
    // Controleer of het IP-adres geldig is
    if (!preg_match('/^[0-9\.]+$/', $ip)) return "Invalid IP";
    $url = "https://talosintelligence.com/reputation_center/lookup?search=" . urlencode($ip);
    $context = stream_context_create([
        'http' => [
            'user_agent' => 'Mozilla/5.0 (compatible; PHP script)',
            'timeout' => 5
        ]
    ]);
    $html = @file_get_contents($url, false, $context);
    if (!$html) return "Talos lookup failed or not available.";

    // Probeer het reputatieblok uit de HTML te halen (kan breken als Talos de layout wijzigt)
    if (preg_match('/<div[^>]*class="field reputation-score"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        $summary = strip_tags($matches[1]);
        return htmlspecialchars(trim($summary));
    }
    // Fallback: toon een link naar Talos
    return 'See <a href="' . htmlspecialchars($url) . '" target="_blank">Talos Intelligence Lookup</a> for details.';
}
$whois = getWhois($ip);

// Haal nieuwsberichten op van de ijsclub (laatste 4 via RSS)
function getLatestNews($rssUrl, $max = 4) {
    $items = [];
    $rss = @simplexml_load_file($rssUrl);
    if ($rss && isset($rss->channel->item)) {
        foreach ($rss->channel->item as $i => $item) {
            if ($i >= $max) break;
            $items[] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'date' => date('Y-m-d', strtotime((string)$item->pubDate)),
                'desc' => strip_tags((string)$item->description)
            ];
        }
    }
    return $items;
}
$news = getLatestNews('https://www.nu.nl/rss');

// Huidige datum/tijd
$date = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Info Page</title>
    <meta charset="utf-8">
    <style>
        /* Stijl voor de pagina */
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
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info-table td {
            border: none;
            padding: 6px 8px;
            font-size: 1.1em;
        }
        table.info-table label {
            font-weight: bold;
        }
        .news-section {
            margin-top: 30px;
        }
        .news-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .news-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .news-list li {
            margin-bottom: 18px;
            background: #f4f8fb;
            border-radius: 6px;
            padding: 12px 14px;
            border: 1px solid #b3d1ea;
        }
        .news-list .news-date {
            font-size: 0.95em;
            color: #555;
            margin-bottom: 4px;
            display: block;
        }
    </style>
    <!-- Leaflet CSS voor de kaart -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <h1>Gebruikersinformatie</h1>
    <div class="container">
        <table class="info-table">
            <tr>
                <td><label>Datum:</label></td>
                <td><?php echo $date; ?></td>
                <td rowspan="3" style="text-align:center; vertical-align:middle;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/85/Sciurus_carolinensis_Squirrel.jpg/220px-Sciurus_carolinensis_Squirrel.jpg" 
                         alt="Squirrel" style="max-width:120px; border-radius:8px; box-shadow:0 2px 8px #bbb;">
                    <div style="font-size:0.95em; color:#555; margin-top:6px;">Eekhoorn</div>
                </td>
            </tr>
            <tr>
                <td><label>Jouw IP:</label></td>
                <td><?php echo htmlspecialchars($ip); ?></td>
            </tr>
            <tr>
                <td><label>Land:</label></td>
                <td><?php echo htmlspecialchars($country); ?></td>
            </tr>
        </table>
        <div>
            <label>Whois Info:</label>
            <div class="whois"><?php echo nl2br($whois); ?></div>
        </div>
        <div>
            <label>Jouw Locatie:</label>
            <div id="map"></div>
        </div>
        <div class="news-section">
            <div class="news-title">Nieuws (nu.nl)</div>
            <ul class="news-list">
                <?php if ($news): foreach ($news as $item): ?>
                    <li>
                        <span class="news-date"><?php echo htmlspecialchars($item['date']); ?></span>
                        <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                        <div><?php echo htmlspecialchars($item['desc']); ?></div>
                    </li>
                <?php endforeach; else: ?>
                    <li>Geen nieuws gevonden.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <!-- Leaflet JS voor de interactieve kaart -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Zet de kaart op basis van latitude/longitude
        var lat = <?php echo json_encode($lat); ?>;
        var lon = <?php echo json_encode($lon); ?>;
        var map = L.map('map').setView([lat, lon], lat && lon ? 8 : 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        if (lat && lon) {
            L.marker([lat, lon]).addTo(map)
                .bindPopup("Geschatte locatie").openPopup();
        }
    </script>
</body>
</html>
