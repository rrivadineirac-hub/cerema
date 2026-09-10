<?php
// scratch/test_monto_default.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';

$database = new Database();
$db = $database->getConnection();
$cuotaModel = new CuotaInicialModel($db);

// Configurar cuota_inicial de socio 1 a 500 Bs y limpiar pagos previos
$db->exec("UPDATE asociados SET cuota_inicial = 500.00, acciones = 1 WHERE id_socio = 1");
$db->exec("DELETE FROM cuota_inicial_pagos WHERE id_socio = 1");

$_GET['id_socio'] = 1;
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$html = ob_get_clean();

echo "--- Test 1: Valor por defecto y atributo MAX en HTML ---\n";
if (strpos($html, 'value="500.00"') !== false) {
    echo "✔ El campo 'Monto Acreditado' aparece prellenado con 500.00 por defecto!\n";
} else {
    echo "❌ Error: valor por defecto no encontrado en HTML.\n";
}

if (strpos($html, 'max="500.00"') !== false) {
    echo "✔ Atributo max='500.00' configurado en el input!\n";
}

if (strpos($html, 'SALDO_PENDIENTE_SOCIO = 500') !== false) {
    echo "✔ Variable JavaScript SALDO_PENDIENTE_SOCIO inyectada correctamente!\n";
}

echo "\n--- Test 2: Intento de guardar monto superior (600 Bs vs 500 Bs) ---\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'save';
$_POST['id_socio'] = 1;
$_POST['numero_accion'] = 1;
$_POST['numero_recibo'] = 'TEST-OVER-999';
$_POST['concepto'] = 'Pago Excedente';
$_POST['monto'] = 600.00;
$_POST['fecha_pago'] = date('Y-m-d H:i:s');

ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$jsonResp = ob_get_clean();

echo "Respuesta del controlador: " . $jsonResp . "\n";
$resp = json_decode($jsonResp, true);
if ($resp && $resp['status'] === 'error' && strpos($resp['message'], 'no puede ser mayor al saldo') !== false) {
    echo "✔ El controlador rechazó correctamente el monto excedente!\n";
} else {
    echo "❌ Error: No se bloqueó el monto excedente.\n";
}
