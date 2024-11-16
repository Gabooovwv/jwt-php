<?php
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 1);
ini_set("session.gc_maxlifetime", 5);
session_start();
require 'jwt_functions.php';

$input = json_decode(file_get_contents("php://input"), true);
$refreshToken = $input['refresh_token'];

if (verifyJWT($refreshToken, SECRET_KEY)) {
    $payload = verifyJWT($refreshToken, SECRET_KEY);
    $newAccessToken = createJWT(['user' => $payload['user']], SECRET_KEY, 5); // 15 perc
    $newRefreshToken = createJWT(['user' => $payload['user']], SECRET_KEY, 86400); // 1 nap

    $_SESSION['access_token'] = $newAccessToken;
    $_SESSION['refresh_token'] = $newRefreshToken;
    echo json_encode([
        'access_token' => $newAccessToken,
        'refresh_token' => $newRefreshToken
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Érvénytelen refresh token.']);
}
