<?php
// Set the cooldown period in seconds (e.g., 3600 seconds for 1 hour)
$cooldown_period = 3600; 

// Get parameters from the URL
$subid = isset($_GET['subid']) ? $_GET['subid'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

if (!$subid) {
    // Log error or handle the absence of subid
    error_log("Required parameters are missing");
    file_put_contents('pixel_log.txt', date('Y-m-d H:i:s') . " - Error: Required parameter subid is missing\n", FILE_APPEND);
    exit;
}

// Check if the lead has been triggered recently
if (isset($_COOKIE['lead_triggered'])) {
    $last_triggered = $_COOKIE['lead_triggered'];
    $current_time = time();

    // If the cooldown period hasn't passed, do not trigger the lead again
    if (($current_time - $last_triggered) < $cooldown_period) {
        file_put_contents('pixel_log.txt', date('Y-m-d H:i:s') . " - Info: Lead event not triggered due to cooldown period.\n", FILE_APPEND);
        exit;
    }
}

// If status is provided, handle the status postback
if ($status) {
    // The URL where the POST request will be sent
    $url = "https://roitrack.site/f914036/postback?subid=" . urlencode($subid) . "&status=" . urlencode($status);

    // Use cURL to send the POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Log the response or handle it as needed
    if ($response === false) {
        file_put_contents('pixel_log.txt', date('Y-m-d H:i:s') . " - Error: cURL error: " . $curl_error . "\n", FILE_APPEND);
    } else {
        file_put_contents('pixel_log.txt', date('Y-m-d H:i:s') . " - Success: Response: " . $response . "\n", FILE_APPEND);
        
        // Set a cookie to indicate the lead was triggered
        setcookie('lead_triggered', time(), time() + $cooldown_period, "/");
    }
}
?>
