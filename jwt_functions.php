<?php
define('SECRET_KEY', 'a-very-secret-key'); // Titkos kulcs a JWT aláírásához.

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function createJWT($payload, $secretKey, $expirySeconds) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload['exp'] = time() + $expirySeconds;
    $payloadJson = json_encode($payload);

    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payloadJson);

    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secretKey, true);
    $base64UrlSignature = base64UrlEncode($signature);

    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}

function verifyJWT($jwt, $secretKey) {
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return false;
    }

    [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $tokenParts;
    $signature = base64UrlEncode(hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secretKey, true));

    if ($base64UrlSignature !== $signature) {
        return false;
    }

    $payload = json_decode(base64UrlDecode($base64UrlPayload), true);
    //print_r($payload['exp']);
    //print '<br>';
    //print time();
    if ($payload['exp'] < time()) {
        return false; // Token lejárt
    }

    return $payload;
}

function login($username, $password) {
    if ($username === 'klausz.gabor@virgo.hu' && $password === 'Virgo-123') {
        $accessToken = createJWT(['user' => $username], SECRET_KEY, 5); // 15 perc
        $refreshToken = createJWT(['user' => $username], SECRET_KEY, 86400); // 1 nap

        $_SESSION['access_token'] = $accessToken;
        $_SESSION['refresh_token'] = $refreshToken;

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }
    return null;
}

//$tokens = login('user', 'password');
//echo json_encode($tokens);


function checkAccessToken() {
    if (isset($_SESSION['access_token'])) {
        $payload = verifyJWT($_SESSION['access_token'], SECRET_KEY);
        if ($payload) {
            return $payload;
        }
    }

    // Ha a token lejárt vagy nincs, false-t adunk vissza
    return false;
}

// Például egy védett oldal betöltésekor ellenőrizzük a token-t
/*$userData = checkAccessToken();
if ($userData) {
    echo "Hello, " . $userData['user'];
} else {
    echo "Hozzáférés megtagadva!";
}*/


function refreshAccessToken() {
    if (isset($_SESSION['refresh_token'])) {
        $payload = verifyJWT($_SESSION['refresh_token'], SECRET_KEY);
        if ($payload) {
            // Új access token-t generálunk és elmentjük a session-be
            $newAccessToken = createJWT(['user' => $payload['user']], SECRET_KEY, 900); // 15 perc
            $_SESSION['access_token'] = $newAccessToken;

            return [
                'access_token' => $newAccessToken,
                'refresh_token' => $_SESSION['refresh_token'] // A refresh token marad a régi
            ];
        }
    }

    return null;
}

/*
// Ha szükséges, frissítjük az access token-t
$newTokens = refreshAccessToken();
if ($newTokens) {
    echo json_encode($newTokens);
} else {
    echo "Nem sikerült a token frissítése.";
}
*/