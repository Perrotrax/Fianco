<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');

try {
    $challenge = random_bytes(32);
    $challenge_b64 = base64_encode($challenge);
    $_SESSION['webauthn_challenge'] = $challenge_b64;
    api_json(['challenge' => $challenge_b64]);
} catch (Exception $e) {
    http_response_code(500);
    api_json(['error' => 'No se pudo generar el challenge.']);
}
