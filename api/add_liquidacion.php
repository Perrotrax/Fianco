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

$id_viaje = isset($data['id_viaje']) ? intval($data['id_viaje']) : 0;
$nombre = isset($data['nombre']) ? trim($data['nombre']) : '';

if ($id_viaje <= 0 || !$nombre) {
    echo json_encode(['success' => false, 'message' => 'El viaje y el nombre de la liquidación son obligatorios']);
    exit;
}

// 1) Obtener suma de gastos aprobados para el viaje
$monto_total = 0.00;
$stmt = $conn->prepare("SELECT SUM(monto) as total FROM gastos WHERE id_viaje = ? AND id_usuario = ? AND estado = 'Aprobado'");
if ($stmt) {
    $stmt->bind_param("ii", $id_viaje, $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monto_total = floatval($res['total'] ?? 0.00);
    $stmt->close();
}

// 2) Obtener suma de anticipos aprobados para el viaje
$monto_anticipos = 0.00;
$stmt = $conn->prepare("SELECT SUM(monto) as total FROM anticipos WHERE id_viaje = ? AND id_usuario = ? AND estado = 'Aprobado'");
if ($stmt) {
    $stmt->bind_param("ii", $id_viaje, $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monto_anticipos = floatval($res['total'] ?? 0.00);
    $stmt->close();
}

// 3) Calcular resultado
$resultado = $monto_total - $monto_anticipos;

// 4) Insertar liquidación
$stmt = $conn->prepare("INSERT INTO liquidaciones (id_usuario, id_viaje, nombre, monto_total, monto_anticipos, resultado, estado) VALUES (?, ?, ?, ?, ?, ?, 'Procesado')");
if ($stmt) {
    $stmt->bind_param("iisddd", $userId, $id_viaje, $nombre, $monto_total, $monto_anticipos, $resultado);
    if ($stmt->execute()) {
        // Opcional: Marcar el viaje como Terminado
        $update = $conn->prepare("UPDATE viajes SET estado = 'Terminado' WHERE id_viaje = ? AND id_usuario = ?");
        if ($update) {
            $update->bind_param("ii", $id_viaje, $userId);
            $update->execute();
            $update->close();
        }
        echo json_encode(['success' => true, 'message' => 'Liquidación procesada con éxito', 'resultado' => $resultado]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar liquidación: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
