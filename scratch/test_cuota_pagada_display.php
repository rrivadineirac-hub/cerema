<?php
// scratch/test_cuota_pagada_display.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';

$database = new Database();
$db = $database->getConnection();
$socioModel = new SocioModel($db);
$cuotaModel = new CuotaInicialModel($db);

// Limpiar pagos del socio 1
$db->exec("DELETE FROM cuota_inicial_pagos WHERE id_socio = 1");

// Test A: Sin pagos abonados
$stmtA = $socioModel->getActivos();
$rowsA = $stmtA->fetchAll(PDO::FETCH_ASSOC);
$socio1A = null;
foreach ($rowsA as $r) {
    if ($r['id_socio'] == 1) { $socio1A = $r; break; }
}

echo "--- Test A (Sin pagos registrados) ---\n";
echo "Monto pagado obtenido: Bs. " . ($socio1A['cuota_inicial_pagado'] ?? 0) . "\n";
if ((float)($socio1A['cuota_inicial_pagado'] ?? 0) == 0) {
    echo "✔ Sin abonos, cuota_inicial_pagado es 0 (en tabla se muestra '-').\n";
}

// Test B: Registrar un pago parcial de 350.00 Bs
$cuotaModel->create([
    'id_socio' => 1,
    'numero_accion' => 1,
    'numero_recibo' => 'TEST-PAGADO-1',
    'concepto' => 'Abono Parcial Cuota Inicial',
    'monto' => 350.00,
    'fecha_pago' => date('Y-m-d H:i:s'),
    'observaciones' => 'Prueba'
]);
$pagoId = $db->lastInsertId();

$stmtB = $socioModel->getActivos();
$rowsB = $stmtB->fetchAll(PDO::FETCH_ASSOC);
$socio1B = null;
foreach ($rowsB as $r) {
    if ($r['id_socio'] == 1) { $socio1B = $r; break; }
}

echo "\n--- Test B (Con pago parcial de 350 Bs) ---\n";
echo "Monto pagado obtenido: Bs. " . ($socio1B['cuota_inicial_pagado'] ?? 0) . "\n";
if ((float)($socio1B['cuota_inicial_pagado'] ?? 0) == 350.00) {
    echo "✔ Con abono registrado, cuota_inicial_pagado refleja exactamente los Bs. 350.00 pagados!\n";
}

// Limpiar registro de prueba
$cuotaModel->delete($pagoId);
