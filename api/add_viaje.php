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

$destino = isset($data['destino']) ? trim($data['destino']) : '';
$fecha_inicio = isset($data['fecha_inicio']) ? trim($data['fecha_inicio']) : null;
$fecha_fin = isset($data['fecha_fin']) ? trim($data['fecha_fin']) : null;
$presupuesto = isset($data['presupuesto']) ? floatval($data['presupuesto']) : 0.00;
$estado = isset($data['estado']) ? trim($data['estado']) : 'Planificado';

if (!$destino) {
    api_json(['success' => false, 'message' => 'El destino es obligatorio']);
}

$stmt = $conn->prepare("INSERT INTO viajes (id_usuario, destino, fecha_inicio, fecha_fin, presupuesto, estado) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("isssds", $userId, $destino, $fecha_inicio, $fecha_fin, $presupuesto, $estado);
    if ($stmt->execute()) {
        api_json(['success' => true, 'message' => 'Viaje creado con éxito']);
    } else {
        api_json(['success' => false, 'message' => 'Error al guardar el viaje: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos']);
}
?>
