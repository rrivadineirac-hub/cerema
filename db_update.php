<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec("ALTER TABLE mensualidad ADD COLUMN numero_recibo VARCHAR(50) NULL AFTER numero_accion");
    echo "Success: Added numero_recibo to mensualidad.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
