<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec('ALTER TABLE asociados ADD acciones INT DEFAULT 1');
    $db->exec('ALTER TABLE mensualidad ADD numero_accion INT DEFAULT 1');
    $db->exec('ALTER TABLE aporte_extraordinario ADD numero_accion INT DEFAULT 1');
    $db->exec('ALTER TABLE aportes_especiales ADD numero_accion INT DEFAULT 1');
    echo 'DB updated successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
