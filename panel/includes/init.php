<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../../api/conexion.php';

$userId = (int) $_SESSION['id_usuario'];

$stmt = $conn->prepare('SELECT id_usuario, nombre, correo, foto_perfil, biometrico, fecha_registro FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$currentUser) {
    header('Location: ../logout.php');
    exit;
}

function panelAvatarColor(int $id): string
{
    $colors = ['#6c63ff', '#f472b6', '#14b8a6', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444'];
    return $colors[$id % count($colors)];
}

function panelFormatDate(?string $date): string
{
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('j M Y', $ts) : '—';
}

function panelGetInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials ?: 'U';
}

function panelFetchStats(mysqli $conn): array
{
    $stats = [
        'totalUsuarios' => 0,
        'totalGastos' => 0,
        'montoTotal' => 0.0,
        'biometricosActivos' => 0,
    ];

    $result = $conn->query('SELECT COUNT(*) AS total FROM usuarios');
    if ($row = $result->fetch_assoc()) {
        $stats['totalUsuarios'] = (int) $row['total'];
    }

    $result = $conn->query('SELECT COUNT(*) AS total, COALESCE(SUM(monto), 0) AS monto FROM gastos');
    if ($row = $result->fetch_assoc()) {
        $stats['totalGastos'] = (int) $row['total'];
        $stats['montoTotal'] = (float) $row['monto'];
    }

    $result = $conn->query('SELECT COUNT(*) AS total FROM usuarios WHERE biometrico = 1');
    if ($row = $result->fetch_assoc()) {
        $stats['biometricosActivos'] = (int) $row['total'];
    }

    return $stats;
}

function panelFetchUsers(mysqli $conn): array
{
    $users = [];
    $result = $conn->query(
        'SELECT id_usuario, nombre, correo, biometrico, fecha_registro
         FROM usuarios
         ORDER BY fecha_registro DESC'
    );

    while ($row = $result->fetch_assoc()) {
        $id = (int) $row['id_usuario'];
        $users[] = [
            'id' => (string) $id,
            'name' => $row['nombre'],
            'email' => $row['correo'],
            'biometric' => (bool) $row['biometrico'],
            'joined' => panelFormatDate($row['fecha_registro']),
            'avatarColor' => panelAvatarColor($id),
        ];
    }

    return $users;
}

function panelFetchGastos(mysqli $conn): array
{
    $gastos = [];
    $sql = 'SELECT g.id_gasto, g.id_usuario, g.descripcion, g.monto, g.categoria, g.fecha, u.nombre AS usuario
            FROM gastos g
            INNER JOIN usuarios u ON u.id_usuario = g.id_usuario
            ORDER BY g.fecha DESC';

    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $gastos[] = [
            'id' => (string) $row['id_gasto'],
            'userId' => (string) $row['id_usuario'],
            'userName' => $row['usuario'],
            'description' => $row['descripcion'],
            'amount' => (float) $row['monto'],
            'category' => $row['categoria'],
            'date' => $row['fecha'],
            'dateLabel' => panelFormatDate($row['fecha']),
        ];
    }

    return $gastos;
}

function panelFetchChartData(mysqli $conn): array
{
    $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $monthlyAmounts = array_fill(0, 12, 0.0);
    $monthlyCounts = array_fill(0, 12, 0);
    $registrations = array_fill(0, 12, 0);

    $result = $conn->query(
        "SELECT MONTH(fecha) AS mes, COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
         FROM gastos
         WHERE YEAR(fecha) = YEAR(CURRENT_DATE())
         GROUP BY MONTH(fecha)"
    );
    while ($row = $result->fetch_assoc()) {
        $index = (int) $row['mes'] - 1;
        if ($index >= 0 && $index < 12) {
            $monthlyAmounts[$index] = (float) $row['total'];
            $monthlyCounts[$index] = (int) $row['cantidad'];
        }
    }

    $result = $conn->query(
        "SELECT MONTH(fecha_registro) AS mes, COUNT(*) AS cantidad
         FROM usuarios
         WHERE YEAR(fecha_registro) = YEAR(CURRENT_DATE())
         GROUP BY MONTH(fecha_registro)"
    );
    while ($row = $result->fetch_assoc()) {
        $index = (int) $row['mes'] - 1;
        if ($index >= 0 && $index < 12) {
            $registrations[$index] = (int) $row['cantidad'];
        }
    }

    $categories = [];
    $result = $conn->query(
        'SELECT categoria, COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
         FROM gastos
         GROUP BY categoria
         ORDER BY total DESC'
    );
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'name' => $row['categoria'],
            'count' => (int) $row['cantidad'],
            'total' => (float) $row['total'],
        ];
    }

    return [
        'months' => $months,
        'monthlyAmounts' => $monthlyAmounts,
        'monthlyCounts' => $monthlyCounts,
        'registrations' => $registrations,
        'categories' => $categories,
    ];
}

$panelStats = panelFetchStats($conn);
$panelUsers = panelFetchUsers($conn);
$panelGastos = panelFetchGastos($conn);
$panelCharts = panelFetchChartData($conn);

$panelData = [
    'stats' => $panelStats,
    'users' => $panelUsers,
    'gastos' => $panelGastos,
    'charts' => $panelCharts,
    'currentUser' => [
        'id' => (string) $currentUser['id_usuario'],
        'name' => $currentUser['nombre'],
        'email' => $currentUser['correo'],
        'biometric' => (bool) $currentUser['biometrico'],
        'joined' => panelFormatDate($currentUser['fecha_registro']),
        'avatarColor' => panelAvatarColor((int) $currentUser['id_usuario']),
        'initials' => panelGetInitials($currentUser['nombre']),
    ],
];
