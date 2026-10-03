<?php
require 'Config/database.php';
require 'Model/msocio.php';

$db = (new Database())->getConnection();
$socioModel = new SocioModel($db);
$stmt = $socioModel->getAll();

$index = 1;
while ($s = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $max_acc = max(1, intval($s['acciones'] ?? 1));
    $name = trim($s['ap_paterno'] . ' ' . $s['ap_materno'] . ' ' . $s['nombre']);
    for ($acc = 1; $acc <= $max_acc; $acc++) {
        $displayName = $name . ($max_acc > 1 ? " N°$acc" : "");
        echo sprintf("%3d | ID:%3d | Acc:%d | %s\n", $index++, $s['id_socio'], $acc, $displayName);
    }
}
