<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error'=>'Method not allowed']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$correo = isset($data['correo']) ? $data['correo'] : '';
if (!$correo) { echo json_encode(['error'=>'Missing correo']); exit; }

// find user cred
$stmt = $conn->prepare('SELECT wc.credential_id FROM webauthn_credentials wc JOIN usuarios u ON wc.id_usuario = u.id_usuario WHERE u.correo = ? LIMIT 1');
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(['error'=>'No credentials']); exit; }
$row = $res->fetch_assoc();
$stmt->close();

$challenge = base64_encode(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

$allowed = [];
$cred = $row['credential_id'];
// credential_id may be stored as base64url string
$allowed[] = ['type'=>'public-key','id' => rtrim(strtr($cred, '+/', '-_'), '=')];

echo json_encode(['challenge' => rtrim(strtr($challenge, '+/', '-_'), '='), 'allowedCredentials' => $allowed]);
