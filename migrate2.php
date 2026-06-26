<?php
/**
 * Migration: Add new features columns
 * - foto_recibo to gastos table
 * - fecha_inicio, fecha_fin to proyectos table
 * - proveedores table
 */
require_once __DIR__ . '/api/conexion.php';

$ok = true;
$msgs = [];

// 1. Add foto_recibo to gastos
$res = $conn->query("SHOW COLUMNS FROM gastos LIKE 'foto_recibo'");
if ($res->num_rows === 0) {
    if ($conn->query("ALTER TABLE gastos ADD COLUMN foto_recibo MEDIUMBLOB NULL AFTER metodo_pago")) {
        $msgs[] = "✅ Columna foto_recibo agregada a gastos";
    } else {
        $msgs[] = "❌ Error agregando foto_recibo: " . $conn->error; $ok = false;
    }
} else {
    $msgs[] = "ℹ️ Columna foto_recibo ya existe en gastos";
}

// 2. Add fecha_inicio, fecha_fin to proyectos
$res = $conn->query("SHOW COLUMNS FROM proyectos LIKE 'fecha_inicio'");
if ($res->num_rows === 0) {
    if ($conn->query("ALTER TABLE proyectos ADD COLUMN fecha_inicio DATE NULL, ADD COLUMN fecha_fin DATE NULL")) {
        $msgs[] = "✅ Columnas fecha_inicio y fecha_fin agregadas a proyectos";
    } else {
        $msgs[] = "❌ Error en proyectos: " . $conn->error; $ok = false;
    }
} else {
    $msgs[] = "ℹ️ Columnas de fechas ya existen en proyectos";
}

// 3. Create proveedores table
$res = $conn->query("SHOW TABLES LIKE 'proveedores'");
if ($res->num_rows === 0) {
    $sql = "CREATE TABLE proveedores (
        id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        rfc VARCHAR(20) NULL,
        categoria VARCHAR(100) NULL,
        contacto VARCHAR(200) NULL,
        notas TEXT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_prov_usuario (id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if ($conn->query($sql)) {
        $msgs[] = "✅ Tabla proveedores creada";
    } else {
        $msgs[] = "❌ Error creando proveedores: " . $conn->error; $ok = false;
    }
} else {
    $msgs[] = "ℹ️ Tabla proveedores ya existe";
}

echo "<h2 style='font-family:monospace;'>Migración " . ($ok ? "✅ Exitosa" : "⚠️ Con errores") . "</h2>";
echo "<ul style='font-family:monospace;'>";
foreach ($msgs as $m) echo "<li>$m</li>";
echo "</ul>";
echo "<p style='font-family:monospace;'><a href='dashboard.php'>→ Ir al Dashboard</a></p>";
?>
