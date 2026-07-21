<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

// Returns publicKey options for navigator.credentials.create
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { api_json(['error'=>'Method not allowed']); }
$data = json_decode(file_get_contents('php://input'), true);
$correo = isset($data['correo']) ? $data['correo'] : '';
if (!$correo) { api_json(['error'=>'Missing correo']); }

// find user id
$stmt = $conn->prepare('SELECT id_usuario, nombre FROM usuarios WHERE correo = ?');
if (!$stmt) {
    api_json(['error' => 'Error de base de datos.', 'detail' => $conn->error]);
}
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
    api_json(['error' => 'Error al obtener datos del usuario.', 'detail' => $stmt->error]);
}
if ($res->num_rows === 0) {
    api_json(['error' => 'Usuario no encontrado']);
}
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
    'pubKeyCredParams' => [
        ['type'=>'public-key','alg'=>-7],
        ['type'=>'public-key','alg'=>-257]
    ],
    'timeout' => 60000,
    'attestation' => 'none'
];

api_json(['publicKey' => $publicKey]);
