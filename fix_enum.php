<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
$db->exec("ALTER TABLE mensualidad MODIFY COLUMN estado enum('Pagado','Pendiente','Anulado') DEFAULT 'Pagado'");
$db->exec("UPDATE mensualidad SET estado = 'Anulado' WHERE estado = '' OR estado IS NULL");
echo "Table altered and states fixed.\n";
