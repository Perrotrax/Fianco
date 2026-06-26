<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

// Returns publicKey options for navigator.credentials.create
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error'=>'Method not allowed']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$correo = isset($data['correo']) ? $data['correo'] : '';
if (!$correo) { echo json_encode(['error'=>'Missing correo']); exit; }

// find user id
$stmt = $conn->prepare('SELECT id_usuario, nombre FROM usuarios WHERE correo = ?');
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(['error'=>'Usuario no encontrado']); exit; }
$user = $res->fetch_assoc();
$stmt->close();

// create challenge
$challenge = base64_encode(random_bytes(32));
$_SESSION['webauthn_challenge'] = $challenge;

// user id as bytes (use string of id)
$userId = $user['id_usuario'];
$userIdB64 = rtrim(strtr(base64_encode((string)$userId), '+/', '-_'), '=');

$publicKey = [
    'challenge' => rtrim(strtr($challenge, '+/', '-_'), '='),
    'rp' => ['name' => 'Gestor Gastos'],
    'user' => [ 'id' => $userIdB64, 'name' => $correo, 'displayName' => $user['nombre'] ],
    'pubKeyCredParams' => [ ['type'=>'public-key','alg'=>-7] ],
    'timeout' => 60000,
    'attestation' => 'none'
];

echo json_encode(['publicKey' => $publicKey]);
