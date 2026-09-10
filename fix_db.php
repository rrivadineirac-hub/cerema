<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
$db->exec("UPDATE mensualidad SET estado = 'Anulado' WHERE estado = '' OR estado IS NULL");
echo "DB Fixed.\n";
