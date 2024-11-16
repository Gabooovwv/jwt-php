<?php
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 1);
ini_set("session.gc_maxlifetime", 5);
session_start();
require 'jwt_functions.php';

$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $accessToken = str_replace('Bearer ', '', $headers['Authorization']);
    $payload = verifyJWT($accessToken, SECRET_KEY);
    if ($payload) {
        echo json_encode(['message' => 'Sikeres hozzáférés!', 'user' => $payload['user']]);
        exit;
    }
}

http_response_code(401);
echo json_encode(['error' => 'Hozzáférés megtagadva vagy token lejárt.']);
