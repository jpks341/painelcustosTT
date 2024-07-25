<?php

function sendCostKeitaro($cost, $id_camp_keitaro, $date) {
    $apikey = '3863de0a5ff302c615248697d8cbc175	'; // Your API key for Keitaro
    $domain = 'http://157.245.244.227'; // Keitaro domain without trailing slash
    $timezone = 'America/Sao_Paulo'; // Your timezone
    $currency = 'USD';

    // Ensure the campaign ID is an integer
    $id_camp_keitaro = (int) $id_camp_keitaro;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $domain . '/admin_api/v1/clicks/update_costs',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'campaign_ids' => [$id_camp_keitaro],
            'costs' => [[
                'start_date' => "$date 00:00",
                'end_date' => "$date 23:59",
                'cost' => $cost
            ]],
            'timezone' => $timezone,
            'currency' => $currency,
            'only_campaign_uniques' => 0
        ]),
        CURLOPT_HTTPHEADER => [
            'Connection: keep-alive',
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/json;charset=UTF-8',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Api-Key: ' . $apikey
        ],
    ));

    $response = curl_exec($curl);

    // Debugging information
    echo "Request to Keitaro: " . $domain . '/admin_api/v1/clicks/update_costs' . "<br>";
    echo "Payload: " . json_encode([
        'campaign_ids' => [$id_camp_keitaro],
        'costs' => [[
            'start_date' => "$date 00:00",
            'end_date' => "$date 23:59",
            'cost' => $cost
        ]],
        'timezone' => $timezone,
        'currency' => $currency,
        'only_campaign_uniques' => 0
    ]) . "<br>";
    echo "Response from Keitaro: " . $response . "<br>";

    curl_close($curl);

    $updateres = json_decode($response);
    if ($updateres->success) {
        echo "Loaded costs to campaign ID: " . $id_camp_keitaro . " for " . $date . "<br/>";
    } else {
        echo "Could not load costs for campaign " . $id_camp_keitaro . " <br> The campaign may not exist <br>";
        var_dump($updateres);
    }
}
?>
