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

$id = isset($data['id']) ? intval($data['id']) : 0;
$tipo = isset($data['tipo']) ? trim($data['tipo']) : ''; // 'gasto' o 'anticipo'
$accion = isset($data['accion']) ? trim($data['accion']) : ''; // 'aprobar' o 'rechazar'

if ($id <= 0 || !in_array($tipo, ['gasto', 'anticipo']) || !in_array($accion, ['aprobar', 'rechazar'])) {
    api_json(['success' => false, 'message' => 'Parámetros inválidos']);
}

$nuevoEstado = ($accion === 'aprobar') ? 'Aprobado' : 'Rechazado';
$conn->begin_transaction();

try {
    if ($tipo === 'gasto') {
        // Obtener detalles del gasto para verificar método de pago y proyecto
        $stmt = $conn->prepare("SELECT monto, metodo_pago, id_proyecto, estado FROM gastos WHERE id_gasto = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $gasto = $res->fetch_assoc();
        $stmt->close();

        if (!$gasto) {
            throw new Exception("Gasto no encontrado");
        }

        if ($gasto['estado'] !== 'Pendiente') {
            throw new Exception("El gasto ya ha sido procesado (Estado: " . $gasto['estado'] . ")");
        }

        // Actualizar el estado del gasto
        $stmt = $conn->prepare("UPDATE gastos SET estado = ? WHERE id_gasto = ? AND id_usuario = ?");
        $stmt->bind_param("sii", $nuevoEstado, $id, $userId);
        $stmt->execute();
        $stmt->close();

        $monto = floatval($gasto['monto']);

        if ($accion === 'aprobar') {
            // Si el gasto está asignado a un proyecto, acumular en gastado
            if ($gasto['id_proyecto']) {
                $stmt = $conn->prepare("UPDATE proyectos SET gastado = gastado + ? WHERE id_proyecto = ? AND id_usuario = ?");
                $stmt->bind_param("dii", $monto, $gasto['id_proyecto'], $userId);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($accion === 'rechazar') {
            // Si fue pagado con Wallet, reembolsar el saldo
            if ($gasto['metodo_pago'] === 'Wallet') {
                // Obtener saldo actual del usuario
                $stmt = $conn->prepare("SELECT wallet_balance FROM usuarios WHERE id_usuario = ? FOR UPDATE");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                $user = $res->fetch_assoc();
                $stmt->close();

                $newBalance = floatval($user['wallet_balance']) + $monto;

                // Actualizar saldo
                $stmt = $conn->prepare("UPDATE usuarios SET wallet_balance = ? WHERE id_usuario = ?");
                $stmt->bind_param("di", $newBalance, $userId);
                $stmt->execute();
                $stmt->close();

                // Registrar transacción de devolución
                $desc = "Reembolso por rechazo de gasto #" . $id;
                $tipo_t = "deposito";
                $stmt = $conn->prepare("INSERT INTO wallet_transactions (id_usuario, tipo, monto, descripcion) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isds", $userId, $tipo_t, $monto, $desc);
                $stmt->execute();
                $stmt->close();
            }
        }

    } elseif ($tipo === 'anticipo') {
        // Obtener detalles del anticipo
        $stmt = $conn->prepare("SELECT monto, estado, id_viaje FROM anticipos WHERE id_anticipo = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $anticipo = $res->fetch_assoc();
        $stmt->close();

        if (!$anticipo) {
            throw new Exception("Anticipo no encontrado");
        }

        if ($anticipo['estado'] !== 'Pendiente') {
            throw new Exception("El anticipo ya ha sido procesado");
        }

        // Actualizar el estado del anticipo
        $stmt = $conn->prepare("UPDATE anticipos SET estado = ? WHERE id_anticipo = ? AND id_usuario = ?");
        $stmt->bind_param("sii", $nuevoEstado, $id, $userId);
        $stmt->execute();
        $stmt->close();

        // Si se aprueba, depositar automáticamente el anticipo a la Wallet del usuario
        // (Esto es súper conveniente y le da una excelente cohesión a la wallet!)
        if ($accion === 'aprobar') {
            $monto = floatval($anticipo['monto']);

            $stmt = $conn->prepare("SELECT wallet_balance FROM usuarios WHERE id_usuario = ? FOR UPDATE");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            $newBalance = floatval($user['wallet_balance']) + $monto;

            $stmt = $conn->prepare("UPDATE usuarios SET wallet_balance = ? WHERE id_usuario = ?");
            $stmt->bind_param("di", $newBalance, $userId);
            $stmt->execute();
            $stmt->close();

            // Registrar transacción de depósito de anticipo
            $desc = "Depósito de Anticipo aprobado #" . $id;
            $tipo_t = "deposito";
            $stmt = $conn->prepare("INSERT INTO wallet_transactions (id_usuario, tipo, monto, descripcion) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $userId, $tipo_t, $monto, $desc);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->commit();
    api_json(['success' => true, 'message' => "Elemento $nuevoEstado con éxito"]);
} catch (Exception $e) {
    $conn->rollback();
    api_json(['success' => false, 'message' => $e->getMessage()]);
}
?>
