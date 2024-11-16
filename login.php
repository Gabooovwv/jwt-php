<?php
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 1);
ini_set("session.gc_maxlifetime", 5);
session_start();
require 'jwt_functions.php'; // Ide jön a createJWT és verifyJWT

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'];
$password = $input['password'];

$tokens = login($username, $password); // Előzőleg megírt login függvény

if ($tokens) {
    echo json_encode($tokens);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Helytelen bejelentkezési adatok.']);
}
