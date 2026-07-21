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
$codigo = isset($data['codigo']) ? trim($data['codigo']) : '';
$presupuesto = isset($data['presupuesto']) ? floatval($data['presupuesto']) : 0.00;

if (!$nombre || !$codigo) {
    api_json(['success' => false, 'message' => 'Faltan campos obligatorios (nombre, código)']);
}

$stmt = $conn->prepare("INSERT INTO proyectos (id_usuario, nombre, codigo, presupuesto, gastado) VALUES (?, ?, ?, ?, 0.00)");
if ($stmt) {
    $stmt->bind_param("issd", $userId, $nombre, $codigo, $presupuesto);
    if ($stmt->execute()) {
        api_json(['success' => true, 'message' => 'Proyecto creado con éxito']);
    } else {
        api_json(['success' => false, 'message' => 'Error al guardar el proyecto: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos']);
}
?>
