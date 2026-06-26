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
$id_proyecto = null;
$id_viaje = null;
$metodo_pago = 'Efectivo';
$estado = 'Pendiente'; // Todos los gastos nuevos entran como 'Pendiente' para el flujo de Aprobación

if (stripos($contentType, 'application/json') !== false) {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $descripcion = isset($decoded['descripcion']) ? trim($decoded['descripcion']) : '';
        $monto = isset($decoded['monto']) ? (float)$decoded['monto'] : 0.0;
        $categoria = isset($decoded['categoria']) ? trim($decoded['categoria']) : '';
        $id_proyecto = (isset($decoded['id_proyecto']) && $decoded['id_proyecto'] !== '') ? (int)$decoded['id_proyecto'] : null;
        $id_viaje = (isset($decoded['id_viaje']) && $decoded['id_viaje'] !== '') ? (int)$decoded['id_viaje'] : null;
        $metodo_pago = isset($decoded['metodo_pago']) ? trim($decoded['metodo_pago']) : 'Efectivo';
        $estado = isset($decoded['estado']) ? trim($decoded['estado']) : 'Pendiente';
    }
} else {
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
    $id_proyecto = (isset($_POST['id_proyecto']) && $_POST['id_proyecto'] !== '') ? (int)$_POST['id_proyecto'] : null;
    $id_viaje = (isset($_POST['id_viaje']) && $_POST['id_viaje'] !== '') ? (int)$_POST['id_viaje'] : null;
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : 'Efectivo';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Pendiente';
}

if (empty($descripcion) || $monto <= 0 || empty($categoria)) {
    echo json_encode(['success' => false, 'message' => 'Datos de gasto inválidos o incompletos.']);
    exit;
}

$userId = $_SESSION['id_usuario'];

$conn->begin_transaction();

try {
    // Si el pago es con Wallet, verificar saldo y descontar
    if ($metodo_pago === 'Wallet') {
        $stmt = $conn->prepare("SELECT wallet_balance FROM usuarios WHERE id_usuario = ? FOR UPDATE");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if (!$user) {
            throw new Exception("Usuario no encontrado.");
        }

        $balance = floatval($user['wallet_balance']);
        if ($balance < $monto) {
            throw new Exception("Saldo insuficiente en tu Billetera (Wallet).");
        }

        $newBalance = $balance - $monto;

        // Actualizar balance
        $stmt = $conn->prepare("UPDATE usuarios SET wallet_balance = ? WHERE id_usuario = ?");
        $stmt->bind_param("di", $newBalance, $userId);
        $stmt->execute();
        $stmt->close();

        // Registrar transacción de retiro de billetera
        $descTx = "Pago de gasto: " . $descripcion;
        $tipoTx = "pago_gasto";
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (id_usuario, tipo, monto, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $userId, $tipoTx, $monto, $descTx);
        $stmt->execute();
        $stmt->close();
    }

    // Insertar gasto
    $sql = "INSERT INTO gastos (id_usuario, id_proyecto, id_viaje, descripcion, monto, categoria, estado, metodo_pago) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error al preparar base de datos: " . $conn->error);
    }
    
    $stmt->bind_param("iiisdsss", $userId, $id_proyecto, $id_viaje, $descripcion, $monto, $categoria, $estado, $metodo_pago);
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar el gasto: " . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Gasto agregado correctamente.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
