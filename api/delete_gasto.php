<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado.']);
}

require_once __DIR__ . '/conexion.php';

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$id_gasto = 0;

if (stripos($contentType, 'application/json') !== false) {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $id_gasto = isset($decoded['id_gasto']) ? (int)$decoded['id_gasto'] : 0;
    }
} else {
    $id_gasto = isset($_POST['id_gasto']) ? (int)$_POST['id_gasto'] : 0;
}

if ($id_gasto <= 0) {
    api_json(['success' => false, 'message' => 'ID de gasto inválido.']);
}

$userId = $_SESSION['id_usuario'];

$sql = "DELETE FROM gastos WHERE id_gasto = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ii", $id_gasto, $userId);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            api_json(['success' => true, 'message' => 'Gasto eliminado correctamente.']);
        } else {
            api_json(['success' => false, 'message' => 'Gasto no encontrado o no autorizado.']);
        }
    } else {
        api_json(['success' => false, 'message' => 'Error al ejecutar la eliminación.']);
    }
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
