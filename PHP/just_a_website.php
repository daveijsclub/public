<?php
// Get user IP
function getUserIP() {
    // Helper to validate IP is public
    function isValidPublicIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    if (!empty($_SERVER['HTTP_CLIENT_IP']) && isValidPublicIP($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (isValidPublicIP($ip)) {
                return $ip;
            }
        }
    }
    if (!empty($_SERVER['REMOTE_ADDR']) && isValidPublicIP($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'UNKNOWN';
}

// Get IP
$ip = getUserIP();

// Geolocation lookup using ip-api.com
// Purpose: Attempts to determine the user's country and approximate coordinates based on their public IP address.
// Limitations: Accuracy is not guaranteed, may be affected by VPNs/proxies, and the external API may have rate limits or downtime.
$geo = null;
$ch = curl_init("http://ip-api.com/json/" . $ip);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // 2 seconds to connect
curl_setopt($ch, CURLOPT_TIMEOUT, 4); // 4 seconds total timeout
$response = curl_exec($ch);
if ($response !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
    $geo = json_decode($response);
}
curl_close($ch);

$country = $geo && $geo->status === "success" ? $geo->country : "Unknown";
$lat = $geo && $geo->status === "success" ? $geo->lat : 0;
$lon = $geo && $geo->status === "success" ? $geo->lon : 0;

// Get whois info (simple, limited to Linux servers with whois installed)
function getWhois($ip) {
    // Accept both IPv4 and IPv6 addresses
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return "Invalid IP";
    $out = shell_exec("whois " . escapeshellarg($ip) . " 2>&1");
    return $out ? htmlspecialchars($out) : "Whois lookup failed or not available.";
}
$whois = getWhois($ip);

// Current date (ISO 8601)
/**
 * Get whois information for an IP address.
 * Note: Only works on Linux servers with the 'whois' command installed.
 * @param string $ip
 * @return string
 */
function getWhois($ip) {
    // Accept both IPv4 and IPv6 addresses
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return "Invalid IP";
    $out = @shell_exec("whois " . escapeshellarg($ip) . " 2>&1");
    return $out ? htmlspecialchars($out) : "Whois lookup failed or not available.";
}
$whois = getWhois($ip);
?>
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
        <div>
            <label>Your Location:</label>
            <?php if ($lat == 0 || $lon == 0): ?>
                <div style="color: #888; margin-top: 10px;">Location unknown or unavailable.</div>
            <?php else: ?>
                <div id="map"></div>
            <?php endif; ?>
        </div>
            <div id="map"></div>
    <!-- Leaflet JS -->
        <div>
            <label>Whois Info:</label>
            <div class="whois"><?php echo $whois; ?></div>
        </div>
        <div>
            <label>Your Location:</label>
            <?php if ($lat == 0 || $lon == 0): ?>
                <div style="color: #888; margin-top: 10px;">Location unknown or unavailable.</div>
            <?php else: ?>
                <div id="map"></div>
            <?php endif; ?>
        </div>
    </script>
    <?php endif; ?>
</body>
</html>
</body>
</html>
</body>
</html>
