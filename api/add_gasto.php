<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/conexion.php';

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$descripcion = '';
$monto = 0.0;
$categoria = '';

if (stripos($contentType, 'application/json') !== false) {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $descripcion = isset($decoded['descripcion']) ? trim($decoded['descripcion']) : '';
        $monto = isset($decoded['monto']) ? (float)$decoded['monto'] : 0.0;
        $categoria = isset($decoded['categoria']) ? trim($decoded['categoria']) : '';
    }
} else {
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
}

if (empty($descripcion) || $monto <= 0 || empty($categoria)) {
    echo json_encode(['success' => false, 'message' => 'Datos de gasto inválidos o incompletos.']);
    exit;
}

$userId = $_SESSION['id_usuario'];

$sql = "INSERT INTO gastos (id_usuario, descripcion, monto, categoria) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("isds", $userId, $descripcion, $monto, $categoria);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Gasto agregado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el gasto.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
