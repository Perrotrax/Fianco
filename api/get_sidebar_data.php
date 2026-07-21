<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
}

$userId = $_SESSION['id_usuario'];

$response = [
    'success' => true,
    'wallet_balance' => 0.00,
    'proyectos' => [],
    'viajes' => [],
    'anticipos' => [],
    'facturas' => [],
    'liquidaciones' => [],
    'wallet_transactions' => [],
    'categorias_custom' => [],
    'aprobaciones_pendientes' => []
];

// 1) User Wallet Balance
$stmt = $conn->prepare("SELECT wallet_balance FROM usuarios WHERE id_usuario = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $response['wallet_balance'] = floatval($row['wallet_balance']);
    }
    $stmt->close();
}

// 2) Proyectos
$res = $conn->query("SELECT * FROM proyectos WHERE id_usuario = $userId ORDER BY id_proyecto DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['proyectos'][] = [
            'id_proyecto' => intval($row['id_proyecto']),
            'nombre' => $row['nombre'],
            'codigo' => $row['codigo'],
            'presupuesto' => floatval($row['presupuesto']),
            'gastado' => floatval($row['gastado'])
        ];
    }
}

// 3) Viajes
$res = $conn->query("SELECT * FROM viajes WHERE id_usuario = $userId ORDER BY id_viaje DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['viajes'][] = [
            'id_viaje' => intval($row['id_viaje']),
            'destino' => $row['destino'],
            'fecha_inicio' => $row['fecha_inicio'],
            'fecha_fin' => $row['fecha_fin'],
            'presupuesto' => floatval($row['presupuesto']),
            'estado' => $row['estado']
        ];
    }
}

// 4) Anticipos (Con JOIN a viajes para mostrar el destino)
$res = $conn->query("SELECT a.*, v.destino FROM anticipos a LEFT JOIN viajes v ON a.id_viaje = v.id_viaje WHERE a.id_usuario = $userId ORDER BY a.id_anticipo DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['anticipos'][] = [
            'id_anticipo' => intval($row['id_anticipo']),
            'id_viaje' => $row['id_viaje'] ? intval($row['id_viaje']) : null,
            'destino' => $row['destino'] ?? 'General',
            'monto' => floatval($row['monto']),
            'motivo' => $row['motivo'],
            'estado' => $row['estado'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
}

// 5) Facturas (Con JOIN a gastos)
$res = $conn->query("SELECT f.*, g.descripcion as gasto_descripcion FROM facturas f LEFT JOIN gastos g ON f.id_gasto = g.id_gasto WHERE f.id_usuario = $userId ORDER BY f.id_factura DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['facturas'][] = [
            'id_factura' => intval($row['id_factura']),
            'id_gasto' => $row['id_gasto'] ? intval($row['id_gasto']) : null,
            'gasto_descripcion' => $row['gasto_descripcion'] ?? 'Sin vincular',
            'folio' => $row['folio'],
            'emisor' => $row['emisor'],
            'receptor' => $row['receptor'],
            'monto' => floatval($row['monto']),
            'iva' => floatval($row['iva']),
            'fecha_emision' => $row['fecha_emision'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
}

// 6) Liquidaciones
$res = $conn->query("SELECT l.*, v.destino FROM liquidaciones l LEFT JOIN viajes v ON l.id_viaje = v.id_viaje WHERE l.id_usuario = $userId ORDER BY l.id_liquidacion DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['liquidaciones'][] = [
            'id_liquidacion' => intval($row['id_liquidacion']),
            'id_viaje' => $row['id_viaje'] ? intval($row['id_viaje']) : null,
            'destino' => $row['destino'] ?? 'Desconocido',
            'nombre' => $row['nombre'],
            'monto_total' => floatval($row['monto_total']),
            'monto_anticipos' => floatval($row['monto_anticipos']),
            'resultado' => floatval($row['resultado']),
            'estado' => $row['estado'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
}

// 7) Wallet Transactions
$res = $conn->query("SELECT * FROM wallet_transactions WHERE id_usuario = $userId ORDER BY id_transaccion DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['wallet_transactions'][] = [
            'id_transaccion' => intval($row['id_transaccion']),
            'tipo' => $row['tipo'],
            'monto' => floatval($row['monto']),
            'descripcion' => $row['descripcion'],
            'fecha' => $row['fecha']
        ];
    }
}

// 8) Categorías Personalizadas
$res = $conn->query("SELECT * FROM categorias_custom WHERE id_usuario = $userId ORDER BY nombre ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['categorias_custom'][] = [
            'id_categoria' => intval($row['id_categoria']),
            'nombre' => $row['nombre'],
            'limite_mensual' => floatval($row['limite_mensual'])
        ];
    }
}

// 9) Aprobaciones Pendientes (Gastos y Anticipos que están 'Pendiente')
$res = $conn->query("SELECT id_gasto as id, descripcion as titulo, monto, 'gasto' as tipo, fecha as fecha FROM gastos WHERE id_usuario = $userId AND estado = 'Pendiente'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['aprobaciones_pendientes'][] = [
            'id' => intval($row['id']),
            'titulo' => $row['titulo'],
            'monto' => floatval($row['monto']),
            'tipo' => $row['tipo'],
            'fecha' => $row['fecha']
        ];
    }
}
$res = $conn->query("SELECT id_anticipo as id, motivo as titulo, monto, 'anticipo' as tipo, fecha_creacion as fecha FROM anticipos WHERE id_usuario = $userId AND estado = 'Pendiente'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $response['aprobaciones_pendientes'][] = [
            'id' => intval($row['id']),
            'titulo' => $row['titulo'],
            'monto' => floatval($row['monto']),
            'tipo' => $row['tipo'],
            'fecha' => $row['fecha']
        ];
    }
}

// Ordenar aprobaciones pendientes por fecha descendente
usort($response['aprobaciones_pendientes'], function($a, $b) {
    return strcmp($b['fecha'], $a['fecha']);
});

api_json($response);
?>
