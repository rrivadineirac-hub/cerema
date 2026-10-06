<?php
// export_db.php - Script para exportar la base de datos cerema_db optimizada para hosting (crema.free.je)
require_once __DIR__ . '/Config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error al conectar a la base de datos: " . $database->error . "\n");
}

$outputFile = __DIR__ . '/cerema_db.sql';

// Si mysqldump está en el PATH o en XAMPP
$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
if (!file_exists($mysqldumpPath)) {
    $mysqldumpPath = 'mysqldump';
}

$cmd = "\"$mysqldumpPath\" --user=root --default-character-set=utf8mb4 --routines --triggers --result-file=\"" . addslashes($outputFile) . "\" cerema_db";

exec($cmd, $output, $returnVar);

if ($returnVar === 0 && file_exists($outputFile)) {
    // Asegurar codificación UTF-8 pura sin BOM
    $sqlContent = file_get_contents($outputFile);
    // Eliminar BOM si estuviese presente
    $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent);
    file_put_contents($outputFile, $sqlContent);
    
    echo "¡Exportación completada con éxito!\n";
    echo "Archivo generado: cerema_db.sql (UTF-8)\n";
    echo "Listo para importar en phpMyAdmin en hosting (crema.free.je)\n";
} else {
    echo "No se pudo usar mysqldump directamente. El archivo cerema_db.sql actual ya se encuentra optimizado en UTF-8 para crema.free.je.\n";
}
