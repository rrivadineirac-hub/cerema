<?php
// scratch/test_cuota_completed.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';

$database = new Database();
$db = $database->getConnection();
$cuotaModel = new CuotaInicialModel($db);

// Crear socio temporal o test
$testRecibo = 'REC-FULL-' . rand(1000, 9999);

// Crear pago que salde la meta
$cuotaModel->create([
    'id_socio' => 1,
    'numero_accion' => 1,
    'numero_recibo' => $testRecibo,
    'concepto' => 'Pago total cuota inicial',
    'monto' => 1000.00,
    'fecha_pago' => date('Y-m-d H:i:s'),
    'observaciones' => 'Test pago completo'
]);

$lastId = $db->lastInsertId();

// Asignar cuota_inicial en perfil de socio 1 a 500 para asegurar meta_total = 500
$db->exec("UPDATE asociados SET cuota_inicial = 500.00 WHERE id_socio = 1");

$_GET['id_socio'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$htmlOutput = ob_get_clean();

echo "--- Verificación de botón deshabilitado ---\n";
if (strpos($htmlOutput, 'id="btnOpenCuotaModal" onclick="openCuotaModal()" disabled') !== false) {
    echo "✔ El botón 'Registrar Pago Cuota Inicial' se deshabilita correctamente al completarse la cuota!\n";
} else {
    echo "❌ Error: El botón no se deshabilitó.\n";
}

if (strpos($htmlOutput, 'Cuota Inicial Completada:') !== false) {
    echo "✔ El aviso verde 'Cuota Inicial Completada' aparece en pantalla!\n";
}

// Limpiar registro de prueba
$cuotaModel->delete($lastId);
