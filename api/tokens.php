<?php

$payload = [
    "sub" => $user["id"],
    "name" => $user["username"],
    "exp" => time() + 300  //20
];

$access_token = $codec->encode($payload);

$refresh_token_expiry = time() + 432000;

$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => $refresh_token_expiry  // time in second, it is 5 days
]);

// access token
// $access_token = base64_encode(json_encode($payload));

echo json_encode(["access_token" => $access_token, "refresh_token" => $refresh_token]);
