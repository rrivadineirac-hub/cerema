<?php
// scratch/check_db_cuota.php

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

echo "=== ESTRUCTURA Y REGISTROS DE cuota_inicial_pagos ===\n";
$stmtP = $db->query("SELECT * FROM cuota_inicial_pagos");
$pagos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
echo "Total filas en cuota_inicial_pagos: " . count($pagos) . "\n";
foreach ($pagos as $p) {
    print_r($p);
}

echo "\n=== PRIMEROS 15 ASOCIADOS CON CÁLCULO DE CUOTA INICIAL PAGADO ===\n";
$socioModel = new SocioModel($db);
$stmtS = $socioModel->getAll();
$socios = $stmtS->fetchAll(PDO::FETCH_ASSOC);

foreach (array_slice($socios, 0, 15) as $s) {
    echo "ID: {$s['id_socio']} | CI: {$s['ci']} | Nombre: {$s['ap_paterno']} {$s['nombre']} | Acciones: {$s['acciones']} | Cuota Asignada: {$s['cuota_inicial']} | Cuota Pagada: {$s['cuota_inicial_pagado']}\n";
}
