<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['id_usuario'];
$data = json_decode(file_get_contents('php://input'), true);

$monto = isset($data['monto']) ? floatval($data['monto']) : 0.00;
$motivo = isset($data['motivo']) ? trim($data['motivo']) : '';
$id_viaje = isset($data['id_viaje']) && $data['id_viaje'] !== '' ? intval($data['id_viaje']) : null;

if ($monto <= 0 || !$motivo) {
    echo json_encode(['success' => false, 'message' => 'El monto debe ser positivo y el motivo es obligatorio']);
    exit;
}

$estado = 'Pendiente'; // Siempre se crea como Pendiente para el flujo de Aprobación

$stmt = $conn->prepare("INSERT INTO anticipos (id_usuario, id_viaje, monto, motivo, estado) VALUES (?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iidds", $userId, $id_viaje, $monto, $motivo, $estado);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Solicitud de anticipo registrada con éxito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar anticipo: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
