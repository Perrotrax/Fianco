<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
}

$userId = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);

$nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
$limite_mensual = isset($data['limite_mensual']) ? floatval($data['limite_mensual']) : 0.00;

if (!$nombre || $limite_mensual < 0) {
    api_json(['success' => false, 'message' => 'Parámetros inválidos']);
}

$stmt = $conn->prepare("INSERT INTO categorias_custom (id_usuario, nombre, limite_mensual) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE limite_mensual = ?");
if ($stmt) {
    $stmt->bind_param("isdd", $userId, $nombre, $limite_mensual, $limite_mensual);
    if ($stmt->execute()) {
        api_json(['success' => true, 'message' => 'Límite actualizado con éxito']);
    } else {
        api_json(['success' => false, 'message' => 'Error al guardar límite: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos']);
}
?>
