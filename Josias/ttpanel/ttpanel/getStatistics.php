<?php

include __DIR__.'/functions.php';
include __DIR__.'/include/db.php';

// Function to convert timezones
function convertToTrackerTimezone($date, $timezone) {
    $date_in_tiktok = new DateTime($date, new DateTimeZone($timezone));
    $date_in_tracker = $date_in_tiktok->setTimezone(new DateTimeZone('America/Sao_Paulo'));
    return $date_in_tracker->format('Y-m-d');
}

// Variables for TikTok API
$sessionid_ss_ads = 'b329d8a108f312adcace9fff1a06805b';
$csrfToken = 'qNSFV7KYpg4jTs4YoWCAZVe4qWdBsNg4';
$idAccount = '7099461686511370242';

// Get the timezone for the current token
$token = $db->query("SELECT * FROM `tokens` WHERE id_ad_account = '$idAccount' LIMIT 1")->fetch();
$timezone_tiktok = $token['timezone'];

date_default_timezone_set($timezone_tiktok); // Set the default timezone for TikTok Ads

// Get the current date in TikTok timezone
$today_in_tiktok = date("Y-m-d");

// Get statistics using TikTok timezone
$getStatisticsAll = getStatisticsAll($idAccount, $csrfToken, $sessionid_ss_ads, $today_in_tiktok);

// Convert date to Tracker timezone before displaying
$today_in_tracker = convertToTrackerTimezone($today_in_tiktok, $timezone_tiktok);

echo "<pre>";
var_dump($getStatisticsAll['data']);
echo "</pre>";
?>
