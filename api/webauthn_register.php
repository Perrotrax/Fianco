<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $response['message'] = 'JSON inválido.';
    echo json_encode($response);
    exit;
}

$correo = isset($data['correo']) ? trim($data['correo']) : '';
$credentialId_b64 = isset($data['credentialId']) ? $data['credentialId'] : '';
$publicKeyJwk = isset($data['publicKeyJwk']) ? $data['publicKeyJwk'] : null;

if (!$correo || !$credentialId_b64) {
    $response['message'] = 'Faltan parámetros.';
    echo json_encode($response);
    exit;
}

// Buscar usuario
$sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) { $response['message'] = 'Error DB'; echo json_encode($response); exit; }
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { $response['message'] = 'Usuario no encontrado.'; echo json_encode($response); exit; }
$user = $res->fetch_assoc();
$userId = $user['id_usuario'];
$stmt->close();

// Decode credentialId (base64url or base64)
// We'll store the credentialId (base64url string)
$credB64 = $credentialId_b64;
$publicKeyJson = null;
if ($publicKeyJwk) {
    $publicKeyJson = json_encode($publicKeyJwk);
}

$sql = "INSERT INTO webauthn_credentials (id_usuario, credential_id, public_key, sign_count, transports, aaguid) VALUES (?, ?, ?, 0, NULL, NULL)";
$stmt = $conn->prepare($sql);
if (!$stmt) { $response['message'] = 'Error al preparar inserción.'; echo json_encode($response); exit; }
$stmt->bind_param('iss', $userId, $credB64, $publicKeyJson);
if ($stmt->execute()) {
    // habilitar webauthn en usuarios
    $u = $conn->prepare("UPDATE usuarios SET webauthn_enabled = 1 WHERE id_usuario = ?");
    if ($u) { $u->bind_param('i', $userId); $u->execute(); $u->close(); }
    $response['success'] = true;
    $response['message'] = 'Credencial registrada.';
$stmt->close();
} else {
    $response['message'] = 'Error al guardar credencial.';
}
echo json_encode($response);

?>
