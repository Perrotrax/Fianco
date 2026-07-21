<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado']);
}
$userId = $_SESSION['id_usuario'];

$response = ['success' => true];

// 1. KPIs del mes actual
$mesActual = date('Y-m');
$mesAnterior = date('Y-m', strtotime('-1 month'));

// Gasto total mes actual (solo aprobados)
$stmt = $conn->prepare("SELECT COALESCE(SUM(monto),0) as total FROM gastos WHERE id_usuario=? AND estado='Aprobado' AND DATE_FORMAT(fecha,'%Y-%m')=?");
if (!$stmt) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $conn->error]);
}
$stmt->bind_param("is", $userId, $mesActual);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $stmt->error]);
}
$row = $res->fetch_assoc();
$response['gasto_mes_actual'] = floatval($row['total'] ?? 0);
$stmt->close();

// Gasto total mes anterior
$stmt = $conn->prepare("SELECT COALESCE(SUM(monto),0) as total FROM gastos WHERE id_usuario=? AND estado='Aprobado' AND DATE_FORMAT(fecha,'%Y-%m')=?");
if (!$stmt) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $conn->error]);
}
$stmt->bind_param("is", $userId, $mesAnterior);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $stmt->error]);
}
$row = $res->fetch_assoc();
$response['gasto_mes_anterior'] = floatval($row['total'] ?? 0);
$stmt->close();

// Presupuesto mensual
$stmt = $conn->prepare("SELECT presupuesto FROM usuarios WHERE id_usuario=?");
if (!$stmt) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $conn->error]);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res) {
    api_json(['success' => false, 'message' => 'Error en la consulta de estadísticas', 'detail' => $stmt->error]);
}
$row = $res->fetch_assoc();
$response['presupuesto'] = floatval($row['presupuesto'] ?? 0);
$stmt->close();

// Días transcurridos y días del mes
$hoy = new DateTime();
$diasTranscurridos = intval($hoy->format('j'));
$diasDelMes = intval($hoy->format('t'));
$diasRestantes = $diasDelMes - $diasTranscurridos;
$response['dias_transcurridos'] = $diasTranscurridos;
$response['dias_restantes'] = $diasRestantes;
$response['dias_del_mes'] = $diasDelMes;

// Gasto diario promedio
$response['gasto_diario_promedio'] = $diasTranscurridos > 0
    ? round($response['gasto_mes_actual'] / $diasTranscurridos, 2)
    : 0;

// Proyección fin de mes
$response['proyeccion_fin_mes'] = round($response['gasto_diario_promedio'] * $diasDelMes, 2);

// % presupuesto consumido
$response['pct_presupuesto'] = $response['presupuesto'] > 0
    ? round(($response['gasto_mes_actual'] / $response['presupuesto']) * 100, 1)
    : 0;

// 2. Top 5 gastos más altos del mes
$stmt = $conn->prepare("SELECT descripcion, monto, categoria, fecha FROM gastos WHERE id_usuario=? AND DATE_FORMAT(fecha,'%Y-%m')=? ORDER BY monto DESC LIMIT 5");
$stmt->bind_param("is", $userId, $mesActual);
$stmt->execute();
$res = $stmt->get_result();
$top5 = [];
while ($row = $res->fetch_assoc()) {
    $top5[] = ['descripcion' => $row['descripcion'], 'monto' => floatval($row['monto']), 'categoria' => $row['categoria'], 'fecha' => $row['fecha']];
}
$response['top5_gastos'] = $top5;
$stmt->close();

// 3. Gasto por categoría mes actual vs anterior
$stmt = $conn->prepare("SELECT categoria, COALESCE(SUM(monto),0) as total FROM gastos WHERE id_usuario=? AND DATE_FORMAT(fecha,'%Y-%m')=? GROUP BY categoria");
$stmt->bind_param("is", $userId, $mesActual);
$stmt->execute();
$res = $stmt->get_result();
$catActual = [];
while ($row = $res->fetch_assoc()) $catActual[$row['categoria']] = floatval($row['total']);
$response['categorias_mes_actual'] = $catActual;
$stmt->close();

$stmt = $conn->prepare("SELECT categoria, COALESCE(SUM(monto),0) as total FROM gastos WHERE id_usuario=? AND DATE_FORMAT(fecha,'%Y-%m')=? GROUP BY categoria");
$stmt->bind_param("is", $userId, $mesAnterior);
$stmt->execute();
$res = $stmt->get_result();
$catAnterior = [];
while ($row = $res->fetch_assoc()) $catAnterior[$row['categoria']] = floatval($row['total']);
$response['categorias_mes_anterior'] = $catAnterior;
$stmt->close();

// 4. Conteo de gastos por estado
$stmt = $conn->prepare("SELECT estado, COUNT(*) as cnt FROM gastos WHERE id_usuario=? AND DATE_FORMAT(fecha,'%Y-%m')=? GROUP BY estado");
$stmt->bind_param("is", $userId, $mesActual);
$stmt->execute();
$res = $stmt->get_result();
$estados = ['Pendiente' => 0, 'Aprobado' => 0, 'Rechazado' => 0];
while ($row = $res->fetch_assoc()) $estados[$row['estado']] = intval($row['cnt']);
$response['conteo_estados'] = $estados;
$stmt->close();

api_json($response);
?>
