<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/conexion.php';

$userId = $_SESSION['id_usuario'];

$sql = "SELECT id_gasto, descripcion, monto, categoria, fecha FROM gastos WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $gastos = [];
    $total = 0.0;
    while ($row = $result->fetch_assoc()) {
        $gastos[] = $row;
        $total += (float)$row['monto'];
    }
    
    echo json_encode([
        'success' => true,
        'gastos' => $gastos,
        'total_gastado' => $total
    ]);
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
