<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include __DIR__.'/functions.php';
include __DIR__.'/include/db.php';

$tokens = $db->query("SELECT * FROM `tokens` ORDER BY id DESC");

foreach ($tokens as $token) {
    $sessionid_ss_ads = $token['sessionid_ss_ads'];
    $csrfToken = $token['csrf'];
    $idAccount = $token['id_ad_account'];
    $timezone = $token['timezone']; // Get the timezone from the token

    // Set the timezone for the current token
    date_default_timezone_set($timezone);

    $today = date("Y-m-d"); // Today's date for requesting spend

    $getBalance = getBalance($idAccount, $csrfToken, $sessionid_ss_ads);
    $getStatus = getStatus($idAccount, $csrfToken, $sessionid_ss_ads); // Check if the account is banned
    $getStatisticsAll = getStatisticsAll($idAccount, $csrfToken, $sessionid_ss_ads, $today); //

    if($getBalance['data']['account_balance'] > 0){
        // Update the balance in the database
        $db->execute("UPDATE `tokens` SET `balance`='".$getBalance['data']['account_balance']."' WHERE id_ad_account='$idAccount'");
    }

    if($getStatus['data']['reject_reason'] == '-' || $getStatus['data']['reject_reason'] == ''){
        $db->execute("UPDATE `tokens` SET `status`='1' WHERE id_ad_account='$idAccount'");
    } else {
        $db->execute("UPDATE `tokens` SET `status`='2' WHERE id_ad_account='$idAccount'");
        // echo $getStatus['data']['reject_reason']; // Reason for account ban
    }

    if($getStatisticsAll['data']['statistics']['campaign_name'] === '-'){
        if($getStatisticsAll['data']['statistics']['show_cnt'] != '-'){
            $db->execute("UPDATE `tokens` SET `impressions`='".$getStatisticsAll['data']['statistics']['show_cnt']."', `clicks`='".$getStatisticsAll['data']['statistics']['click_cnt']."', `cost`='".$getStatisticsAll['data']['statistics']['stat_cost']."', `ctr`='".$getStatisticsAll['data']['statistics']['ctr']."', `cpc`='".$getStatisticsAll['data']['statistics']['cpc']."', `cpa`='".$getStatisticsAll['data']['statistics']['time_attr_convert_cnt']."', `cpa_cost`='".$getStatisticsAll['data']['statistics']['time_attr_conversion_cost']."' WHERE id_ad_account='$idAccount'");
        }
    }
}

echo "Data updated!";
?>
