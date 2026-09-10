<?php
include 'Config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec("DROP TABLE IF EXISTS `otros_ingresos`");
    echo "Tabla eliminada.\n";
} catch (Exception $e) {
    echo "Error dropping table: " . $e->getMessage() . "\n";
}

$files = [
    'Model/motro_ingreso.php',
    'Controller/otros_ingresos.controller.php',
    'View/votros_ingresos.php',
    'js/otros_ingresos.js',
    'css/otros_ingresos.css',
    'scratch/create_otros_ingresos.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        unlink($f);
        echo "Eliminado $f\n";
    }
}
?>
