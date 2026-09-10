<?php
// scratch/test_overpayment_post.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'save';
$_REQUEST['action'] = 'save';
$_POST['id_socio'] = 1;
$_POST['numero_accion'] = 1;
$_POST['numero_recibo'] = 'TEST-OVER-999';
$_POST['concepto'] = 'Pago Excedente';
$_POST['monto'] = 600.00;
$_POST['fecha_pago'] = date('Y-m-d H:i:s');

ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$resp = ob_get_clean();

echo "Respuesta JSON: " . $resp . "\n";
$data = json_decode($resp, true);
if ($data && $data['status'] === 'error') {
    echo "✔ Éxito: Servidor rechazó el monto excedente de 600 Bs (Saldo disponible: 500 Bs).\n";
    echo "Mensaje de error devuelto: {$data['message']}\n";
} else {
    echo "❌ Error: El servidor no rechazó el monto.\n";
}
