<?php

$url = "http://localhost/api/v1/delivery-man/accept-order";
$orderId = 100174; // Change this to a pending order ID in your DB
$token1 = "USER_TOKEN_1"; // Change these to valid DM tokens
$token2 = "USER_TOKEN_2";

$data1 = json_encode(['order_id' => $orderId]);
$data2 = json_encode(['order_id' => $orderId]);

$mh = curl_multi_init();

$ch1 = curl_init($url);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $data1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token1
]);

$ch2 = curl_init($url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $data2);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token2
]);

curl_multi_add_handle($mh, $ch1);
curl_multi_add_handle($mh, $ch2);

$active = null;
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

$response1 = curl_multi_getcontent($ch1);
$response2 = curl_multi_getcontent($ch2);
$httpcode1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
$httpcode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);

echo "Response 1 (HTTP $httpcode1):\n$response1\n\n";
echo "Response 2 (HTTP $httpcode2):\n$response2\n\n";

?>
