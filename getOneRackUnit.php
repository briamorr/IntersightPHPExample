<?php
$apikey = 'xxxx/yyyyy/zzzzzz';
$method = 'GET';
$hostName = 'intersight.com';
$url = '/api/v1/compute/RackUnits?$top=1&$select=Serial,Name';

$apiTime = 'date: ' . gmdate(DATE_RFC1123);
$apiTime = 'date: Fri, 09 Sep 2022 05:03:10 +0000';

$payload = '';
$payloadDigest = openssl_digest ($payload , "sha256", true);
$payloadDigest = 'digest: SHA-256=' . base64_encode($payloadDigest);

$apiSignature = '(request-target): ' . strtolower($method) . ' ' . strtolower($url);
$apiSignature .= PHP_EOL . $apiTime;
$apiSignature .= PHP_EOL . $payloadDigest;
$apiSignature .= PHP_EOL . 'host: ' . $hostName;

$pkeyid = openssl_pkey_get_private("file:///mnt/yourprivatekey/SecretKey.txt");

openssl_sign($apiSignature, $signature, $pkeyid, "sha256WithRSAEncryption");
openssl_free_key($pkeyid);

$apiSignature = base64_encode($signature);

// Create a stream
$opts = array(
  'http'=>array(
    'protocol_version'=>"1.1",
     'ignore_errors'=>true,
    'method'=>$method,
    'header'=>["Accept: application/json",
        "Host: " . $hostName,
        $apiTime,
        $payloadDigest,
       "Authorization: Signature keyId=\"" . $apikey . "\",algorithm=\"rsa-sha256\",headers=\"(request-target) date digest host\",signature=\"" . $apiSignature . "\""]
  )
);

$context = stream_context_create($opts);

$url = 'https://' . $hostName . $url;

$output = file_get_contents($url, false, $context);
echo $output;

?>
