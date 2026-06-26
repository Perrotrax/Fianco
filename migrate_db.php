<?php
require_once __DIR__ . '/api/conexion.php';

echo "=== Iniciando Migraciones de Base de Datos ===\n";

$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    die("Error: No se encontró la carpeta 'migrations'.\n");
}

$files = glob($migrationsDir . '/*.sql');
sort($files); // Ejecutar en orden cronológico/alfabético

foreach ($files as $file) {
    echo "Procesando archivo: " . basename($file) . "...\n";
    $sql = file_get_contents($file);
    
    // Ejecutar multi-consulta
    if ($conn->multi_query($sql)) {
        do {
            // Limpiar resultados para poder ejecutar la siguiente consulta
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "✅ Migración ejecutada con éxito.\n";
    } else {
        echo "❌ Error en migración: " . $conn->error . "\n";
    }
}

// Inicializar categorías por defecto para usuarios existentes
echo "Inicializando categorías por defecto para usuarios...\n";
$res = $conn->query("SELECT id_usuario FROM usuarios");
if ($res) {
    $default_cats = ['Comida', 'Transporte', 'Entretenimiento', 'Servicios', 'Hogar', 'Otros'];
    while ($user = $res->fetch_assoc()) {
        $uid = $user['id_usuario'];
        foreach ($default_cats as $cat) {
            $stmt = $conn->prepare("INSERT IGNORE INTO categorias_custom (id_usuario, nombre, limite_mensual) VALUES (?, ?, 0.00)");
            if ($stmt) {
                $stmt->bind_param("is", $uid, $cat);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    echo "✅ Categorías por defecto inicializadas.\n";
}

echo "=== Migraciones Finalizadas con Éxito ===\n";
?>
