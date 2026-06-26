<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/conexion.php';

// Intentar agregar la columna si no existe (para asegurar compatibilidad)
try {
    $conn->query("ALTER TABLE usuarios ADD COLUMN presupuesto DECIMAL(10, 2) DEFAULT 0.00");
} catch (Exception $e) {
    // La columna probablemente ya existe
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['presupuesto'])) {
    $presupuesto = floatval($input['presupuesto']);
    $userId = $_SESSION['id_usuario'];

    if ($presupuesto < 0) {
        echo json_encode(['success' => false, 'message' => 'El presupuesto no puede ser negativo.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE usuarios SET presupuesto = ? WHERE id_usuario = ?");
    if ($stmt) {
        $stmt->bind_param("di", $presupuesto, $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Presupuesto actualizado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el presupuesto.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
