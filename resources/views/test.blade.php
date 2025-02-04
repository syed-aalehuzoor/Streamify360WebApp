<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
<?php

$cloudflareEmail = env('CLOUDFLARE_EMAIL');
$cloudflareApiKey = env('CLOUDFLARE_API_KEY');
$accountId = env('CLOUDFLARE_ACCOUNT_ID');

$domain = 'karachifindsone.com';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones?name=$domain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-Auth-Email: $cloudflareEmail",
    "X-Auth-Key: $cloudflareApiKey",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['success']) {
    foreach ($result['result'] as $zone) {
        echo "Zone ID: " . $zone['id'] . PHP_EOL;
        echo "Name: " . $zone['name'] . PHP_EOL;
        echo "Status: " . $zone['status'] . PHP_EOL;
        // Add other fields as needed
    }
} else {
    // Ensure that the errors are printed correctly
    if (isset($result['errors']) && is_array($result['errors'])) {
        foreach ($result['errors'] as $error) {
            echo "Error: " . $error['message'] . "<br>";
        }
    } else {
        echo "Unknown error occurred.";
    }
}
?>

</body>
</html>
