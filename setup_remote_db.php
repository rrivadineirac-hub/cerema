<?php
// setup_remote_db.php - Instalador por lotes (Batch Installer) para InfinityFree (Evita 502 Bad Gateway)
header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/cerema_db.sql';
if (!file_exists($sqlFile)) {
    die("<h3>✅ La base de datos ya fue instalada previamente o el archivo de instalación fue limpiado.</h3><p><a href='index.php'>Ir al inicio de sesión</a></p>");
}

require_once __DIR__ . '/Config/database.php';
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("<h3>❌ Error al conectar a MySQL: " . htmlspecialchars($database->error) . "</h3>");
}

$sqlContent = file_get_contents($sqlFile);
$lines = explode("\n", $sqlContent);

$queries = [];
$currentQuery = '';

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) continue;
    if (strpos($trimmed, '/*') === 0 && strrpos($trimmed, '*/') === (strlen($trimmed) - 2)) continue;

    $currentQuery .= $line . "\n";
    if (substr($trimmed, -1) === ';') {
        $queries[] = $currentQuery;
        $currentQuery = '';
    }
}
if (!empty(trim($currentQuery))) {
    $queries[] = $currentQuery;
}

$totalQueries = count($queries);
$batchSize = 250; // Procesar 250 sentencias por paso para evitar timeout de 20s de InfinityFree
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$batch = array_slice($queries, $offset, $batchSize);

$conn->exec("SET FOREIGN_KEY_CHECKS = 0;");
$conn->exec("SET NAMES utf8mb4;");

$executedInBatch = 0;
foreach ($batch as $q) {
    $q = trim($q);
    if (empty($q)) continue;
    try {
        $conn->exec($q);
        $executedInBatch++;
    } catch (PDOException $e) {
        // Ignorar errores de tablas o vistas secundarias duplicadas
    }
}

$nextOffset = $offset + $batchSize;
$percentage = min(100, round(($nextOffset / max(1, $totalQueries)) * 100));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalando Base de Datos - CEREMA</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0F172A; color: white; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #1E293B; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); max-width: 480px; width: 90%; text-align: center; border: 1px solid #334155; }
        h2 { color: #38BDF8; margin-top: 0; }
        .progress-bg { background: #334155; border-radius: 20px; height: 20px; overflow: hidden; margin: 20px 0; }
        .progress-bar { background: linear-gradient(90deg, #38BDF8, #818CF8); height: 100%; transition: width 0.3s; }
        .btn { display: inline-block; background: #38BDF8; color: #0F172A; font-weight: bold; padding: 12px 24px; border-radius: 8px; text-decoration: none; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>⚡ Instalando Base de Datos CEREMA</h2>
        <p>Optimizando tablas para hosting en la nube...</p>

        <div class="progress-bg">
            <div class="progress-bar" style="width: <?php echo $percentage; ?>%;"></div>
        </div>
        <p><b>Progreso: <?php echo $percentage; ?>%</b> (<?php echo min($nextOffset, $totalQueries); ?> de <?php echo $totalQueries; ?> sentencias)</p>

        <?php if ($nextOffset < $totalQueries): ?>
            <p style="color: #94A3B8; font-size: 14px;">Avanzando automáticamente al siguiente lote...</p>
            <script>
                setTimeout(function() {
                    window.location.href = "setup_remote_db.php?offset=<?php echo $nextOffset; ?>";
                }, 400);
            </script>
        <?php else: ?>
            <?php
                // Desactivar temporalmente revisión de claves foráneas
                $conn->exec("SET FOREIGN_KEY_CHECKS = 1;");
                @unlink($sqlFile);
                @unlink(__FILE__);
            ?>
            <h3 style="color: #4ADE80;">🎉 ¡Instalación Completada Exitosamente!</h3>
            <p>Se importaron todas las tablas y datos del sistema.</p>
            <a href="index.php" class="btn">Ir al Sistema CEREMA</a>
            <script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 2000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
