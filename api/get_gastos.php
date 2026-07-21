<?php
require_once __DIR__ . '/api_common.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    api_json(['success' => false, 'message' => 'No autorizado.']);
}

require_once __DIR__ . '/conexion.php';

$userId = $_SESSION['id_usuario'];

$sql = "SELECT g.id_gasto, g.descripcion, g.monto, g.categoria, g.estado, g.metodo_pago, g.xml_invoice, g.fecha, 
               g.id_proyecto, p.nombre as proyecto_nombre, g.id_viaje, v.destino as viaje_destino,
               (g.foto_recibo IS NOT NULL) AS tiene_foto 
        FROM gastos g 
        LEFT JOIN proyectos p ON g.id_proyecto = p.id_proyecto 
        LEFT JOIN viajes v ON g.id_viaje = v.id_viaje 
        WHERE g.id_usuario = ? 
        ORDER BY g.fecha DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $gastos = [];
    $total = 0.0;
    while ($row = $result->fetch_assoc()) {
        $gastos[] = [
            'id_gasto' => intval($row['id_gasto']),
            'descripcion' => $row['descripcion'],
            'monto' => floatval($row['monto']),
            'categoria' => $row['categoria'],
            'estado' => $row['estado'],
            'metodo_pago' => $row['metodo_pago'],
            'xml_invoice' => $row['xml_invoice'],
            'fecha' => $row['fecha'],
            'id_proyecto' => $row['id_proyecto'] ? intval($row['id_proyecto']) : null,
            'proyecto_nombre' => $row['proyecto_nombre'] ?? 'General',
            'id_viaje' => $row['id_viaje'] ? intval($row['id_viaje']) : null,
            'viaje_destino' => $row['viaje_destino'] ?? 'General',
            'tiene_foto' => (bool)$row['tiene_foto']
        ];
        
        // Sumamos a total gastado solo los gastos que no han sido rechazados
        if ($row['estado'] !== 'Rechazado') {
            $total += (float)$row['monto'];
        }
    }
    
    api_json([
        'success' => true,
        'gastos' => $gastos,
        'total_gastado' => $total
    ]);
    
    $stmt->close();
} else {
    api_json(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
