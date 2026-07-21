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

$action = isset($data['action']) ? $data['action'] : '';
$monto = isset($data['monto']) ? floatval($data['monto']) : 0.00;

if ($monto <= 0) {
    api_json(['success' => false, 'message' => 'El monto debe ser mayor a cero']);
}

$conn->begin_transaction();

try {
    // Obtener saldo actual
    $stmt = $conn->prepare("SELECT wallet_balance FROM usuarios WHERE id_usuario = ? FOR UPDATE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception("Usuario no encontrado");
    }

    $currentBalance = floatval($user['wallet_balance']);

    if ($action === 'deposit') {
        $newBalance = $currentBalance + $monto;
        $desc = "Recarga de billetera";
        $tipo = "deposito";
    } elseif ($action === 'withdraw') {
        if ($currentBalance < $monto) {
            throw new Exception("Saldo insuficiente en tu billetera");
        }
        $newBalance = $currentBalance - $monto;
        $desc = "Retiro de efectivo";
        $tipo = "retiro";
    } else {
        throw new Exception("Acción no válida");
    }

    // Actualizar balance
    $stmt = $conn->prepare("UPDATE usuarios SET wallet_balance = ? WHERE id_usuario = ?");
    $stmt->bind_param("di", $newBalance, $userId);
    $stmt->execute();
    $stmt->close();

    // Registrar transacción
    $stmt = $conn->prepare("INSERT INTO wallet_transactions (id_usuario, tipo, monto, descripcion) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $userId, $tipo, $monto, $desc);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    api_json(['success' => true, 'balance' => $newBalance, 'message' => 'Operación realizada con éxito']);
} catch (Exception $e) {
    $conn->rollback();
    api_json(['success' => false, 'message' => $e->getMessage()]);
}
?>
