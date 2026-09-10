<?php
// scratch/verify_controller.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

// Test 1: Opening without socio selected (Selection modal view)
unset($_GET['id_socio']);
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$output1 = ob_get_clean();

echo "--- Test 1 (Sin socio en URL) ---\n";
echo "Longitud HTML: " . strlen($output1) . " bytes\n";
if (strpos($output1, 'Seleccionar Asociado') !== false) {
    echo "✔ Modal de selección 'Seleccionar Asociado' presente!\n";
}
if (strpos($output1, 'btn-orange') !== false) {
    echo "✔ Botón naranja 'Continuar' con gradiente presente!\n";
}
if (strpos($output1, '-- Selecciona un asociado --') !== false) {
    echo "✔ Desplegable '-- Selecciona un asociado --' presente!\n";
}

// Test 2: Opening with socio selected (CRUD View)
$_GET['id_socio'] = 1;
ob_start();
require 'c:/xampp/htdocs/cerema/Controller/cuota_inicial.controller.php';
$output2 = ob_get_clean();

echo "\n--- Test 2 (Con socio id=1) ---\n";
echo "Longitud HTML: " . strlen($output2) . " bytes\n";
if (strpos($output2, 'Registrar Pago Cuota Inicial') !== false) {
    echo "✔ Botón 'Registrar Pago Cuota Inicial' presente!\n";
}
if (strpos($output2, 'Otro Asociado') !== false) {
    echo "✔ Enlace 'Otro Asociado' presente!\n";
}
if (strpos($output2, 'report-stats-grid') !== false) {
    echo "✔ Tarjetas KPI de resumen presentes!\n";
}
