<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { api_json(['error'=>'Method not allowed']); }
$data = json_decode(file_get_contents('php://input'), true);
$correo = isset($data['correo']) ? $data['correo'] : '';
if (!$correo) { api_json(['error'=>'Missing correo']); }

// find user cred only if biometrics/WebAuthn are still enabled
$stmt = $conn->prepare('SELECT wc.credential_id FROM webauthn_credentials wc JOIN usuarios u ON wc.id_usuario = u.id_usuario WHERE u.correo = ? AND u.webauthn_enabled = 1 AND u.biometrico = 1 LIMIT 1');
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    api_json(['error' => 'No biometric credentials found for this user.', 'noCredentials' => true]);
}
$row = $res->fetch_assoc();
$stmt->close();

$challenge = base64_encode(random_bytes(32));
$challenge_b64url = rtrim(strtr($challenge, '+/', '-_'), '=');
$_SESSION['webauthn_challenge'] = $challenge_b64url;

$allowed = [];
$cred = $row['credential_id'];
// credential_id may be stored as base64url string
$allowed[] = ['type'=>'public-key','id' => rtrim(strtr($cred, '+/', '-_'), '=')];

api_json(['challenge' => $challenge_b64url, 'allowedCredentials' => $allowed]);
