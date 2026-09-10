<?php
// scratch/test_get_cuota.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';

$database = new Database();
$db = $database->getConnection();
$cuotaModel = new CuotaInicialModel($db);

// Insert test record
$testId = $cuotaModel->create([
    'id_socio' => 1,
    'numero_accion' => 1,
    'numero_recibo' => 'TEST-EDIT-123',
    'concepto' => 'Pago de Prueba para Editar',
    'monto' => 250.00,
    'fecha_pago' => date('Y-m-d H:i:s'),
    'observaciones' => 'Prueba Edit'
]);
$idCuota = $db->lastInsertId();

// Test 1: action=get_cuota
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'get_cuota';
$_GET['id_cuota'] = $idCuota;
$_REQUEST['action'] = 'get_cuota';

ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$json1 = ob_get_clean();

echo "--- Test 1 (action=get_cuota) ---\n";
echo "Raw JSON: " . $json1 . "\n";
$data1 = json_decode($json1, true);
if ($data1 && isset($data1['id_cuota'])) {
    echo "✔ JSON parse exitoso! Recibo: {$data1['numero_recibo']} Monto: {$data1['monto']}\n";
} else {
    echo "❌ Error al parsear JSON\n";
}

// Limpiar registro de prueba
$cuotaModel->delete($idCuota);
