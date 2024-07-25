<?php

include __DIR__.'/functions.php';
include __DIR__.'/kt_load.php';
include __DIR__.'/include/db.php';

// Function to convert and adjust timezones correctly
function convertToTrackerTimezone($date, $timezone) {
    $date_in_tiktok = new DateTime($date . ' 00:00:00', new DateTimeZone($timezone));
    $date_in_tracker = $date_in_tiktok->setTimezone(new DateTimeZone('America/Sao_Paulo'));
    return $date_in_tracker->format('Y-m-d');
}

echo 'If you see the names of your campaigns below, then the script is working correctly';

$tokens = $db->query("SELECT * FROM `tokens` ORDER BY id DESC");

foreach ($tokens as $token) {
    $sessionid_ss_ads = $token['sessionid_ss_ads'];
    $csrfToken = $token['csrf'];
    $idAccount = $token['id_ad_account'];
    $timezone_tiktok = $token['timezone']; // Get the timezone from the token

    // Set the timezone for the current token
    date_default_timezone_set($timezone_tiktok);

    // Get the current date and time in TikTok timezone
    $now_in_tiktok = new DateTime("now", new DateTimeZone($timezone_tiktok));
    $today_in_tiktok = $now_in_tiktok->format("Y-m-d");

    // Print debug information
    echo "TikTok Timezone: $timezone_tiktok<br>";
    echo "Today's date in TikTok Timezone: $today_in_tiktok<br>";

    // Get statistics using TikTok timezone
    $getStatisticsAll = getStatisticsAll($idAccount, $csrfToken, $sessionid_ss_ads, $today_in_tiktok);

    $costs = []; // Array to hold aggregated costs

    foreach ($getStatisticsAll['data']['table'] as $taskValue) {
        $re = '/(?<=\\{).*(?=})/m';
        $id_campaign_keitaro = $taskValue['campaign_name'];
        preg_match_all($re, $id_campaign_keitaro, $matches, PREG_SET_ORDER, 0);

        // Only display campaigns where there is a macro {id}
        if (isset($matches[0][0])) {
            $id_campaign_keitaro = $matches[0][0];
            $cost = $taskValue['stat_data']['stat_cost'];

            if (isset($costs[$id_campaign_keitaro])) {
                $costs[$id_campaign_keitaro] += $cost;
            } else {
                $costs[$id_campaign_keitaro] = $cost;
            }

            echo "<br>Campaign: (" . $id_campaign_keitaro . ") " . $taskValue['campaign_name'] . " Cost: " . $cost . '<br>';
        }
    }

    // Convert date to Tracker timezone before sending to Keitaro
    $today_in_tracker = convertToTrackerTimezone($today_in_tiktok, $timezone_tiktok);

    // Print debug information
    echo "Today's date in Tracker Timezone (America/Sao_Paulo): $today_in_tracker<br>";

    // If the converted date is different from the intended TikTok date, adjust the date used in the payload
    if ($today_in_tracker != $today_in_tiktok) {
        // Adjust the date to ensure it matches the intended TikTok date
        $today_in_tracker = (new DateTime($today_in_tiktok, new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
        echo "Adjusted Tracker Date: $today_in_tracker<br>";
    }

    // Send aggregated costs to Keitaro using Tracker date
    foreach ($costs as $id_campaign_keitaro => $total_cost) {
        echo sendCostKeitaro($total_cost, $id_campaign_keitaro, $today_in_tracker); // Use Tracker date for Keitaro
    }
}
?>
