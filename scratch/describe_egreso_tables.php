<?php
require_once __DIR__ . '/../Config/database.php';
$db = (new Database())->getConnection();

$tables = ['sueldos', 'servicios_basicos', 'egreso_extraordinario', 'egresos_especiales', 'otros_gastos'];

foreach ($tables as $t) {
    echo "=== TABLE: {$t} ===\n";
    try {
        $stmt = $db->query("DESCRIBE {$t}");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo " - {$c['Field']} ({$c['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Error describing {$t}: " . $e->getMessage() . "\n";
    }
}
