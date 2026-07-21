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

$id      = isset($data['id']) ? intval($data['id']) : 0;
$desc    = isset($data['descripcion']) ? trim($data['descripcion']) : '';
$monto   = isset($data['monto']) ? floatval($data['monto']) : 0;
$cat     = isset($data['categoria']) ? trim($data['categoria']) : '';
$estado  = isset($data['estado']) ? trim($data['estado']) : 'Pendiente';
$metodo  = isset($data['metodo_pago']) ? trim($data['metodo_pago']) : 'Efectivo';
$id_proyecto = isset($data['id_proyecto']) && $data['id_proyecto'] ? intval($data['id_proyecto']) : null;
$id_viaje    = isset($data['id_viaje']) && $data['id_viaje'] ? intval($data['id_viaje']) : null;

if (!$id || !$desc || $monto <= 0) {
    api_json(['success' => false, 'message' => 'Datos incompletos']);
}

// Verificar que el gasto pertenece al usuario
$stmt = $conn->prepare("SELECT id_gasto FROM gastos WHERE id_gasto = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id, $userId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    api_json(['success' => false, 'message' => 'Gasto no encontrado o sin permiso']);
}
$stmt->close();

$stmt = $conn->prepare("UPDATE gastos SET descripcion=?, monto=?, categoria=?, estado=?, metodo_pago=?, id_proyecto=?, id_viaje=? WHERE id_gasto=? AND id_usuario=?");
$stmt->bind_param("sdsssiiii", $desc, $monto, $cat, $estado, $metodo, $id_proyecto, $id_viaje, $id, $userId);
$ok = $stmt->execute();
$stmt->close();

api_json(['success' => $ok, 'message' => $ok ? 'Gasto actualizado' : 'Error al actualizar']);
?>
