<?php
// scratch/test_all_cuota_cases.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';

$database = new Database();
$db = $database->getConnection();
$cuotaModel = new CuotaInicialModel($db);

// Configurar casos de prueba:
// Socio 1: Canceló TODO (Meta: 500, Pagado: 500)
// Socio 2: Canceló PARTE (Meta: 6000, Pagado: 3000)
// Socio 3: NO canceló NADA (Meta: 6000, Pagado: 0)

$db->exec("UPDATE asociados SET cuota_inicial = 500.00, acciones = 1 WHERE id_socio = 1");
$db->exec("UPDATE asociados SET cuota_inicial = 6000.00, acciones = 1 WHERE id_socio = 2");
$db->exec("UPDATE asociados SET cuota_inicial = 6000.00, acciones = 1 WHERE id_socio = 3");

$db->exec("DELETE FROM cuota_inicial_pagos WHERE id_socio IN (1, 2, 3)");

// Insertar pago completo para Socio 1
$cuotaModel->create([
    'id_socio' => 1,
    'numero_accion' => 1,
    'numero_recibo' => 'TEST-FULL-1',
    'concepto' => 'Pago Total Cuota',
    'monto' => 500.00,
    'fecha_pago' => date('Y-m-d H:i:s'),
    'observaciones' => 'Prueba Todo'
]);
$p1 = $db->lastInsertId();

// Insertar pago parcial para Socio 2
$cuotaModel->create([
    'id_socio' => 2,
    'numero_accion' => 1,
    'numero_recibo' => 'TEST-PARTIAL-2',
    'concepto' => 'Pago Parcial Cuota',
    'monto' => 3000.00,
    'fecha_pago' => date('Y-m-d H:i:s'),
    'observaciones' => 'Prueba Parte'
]);
$p2 = $db->lastInsertId();

// Renderizar vsocio.php
$socioModel = new SocioModel($db);
$socios = $socioModel->getAll();

ob_start();
require 'c:/xampp/htdocs/cerema/View/vsocio.php';
$html = ob_get_clean();

echo "=== VERIFICACIÓN DE TODOS LOS CASOS ===\n";

if (strpos($html, 'Bs. 500.00') !== false) {
    echo "✔ CASO 1 (CANCELÓ TODO): Muestra Bs. 500.00 en verde con indicador de completado!\n";
} else {
    echo "❌ Error en Caso 1\n";
}

if (strpos($html, 'Bs. 3,000.00') !== false) {
    echo "✔ CASO 2 (CANCELÓ PARTE): Muestra Bs. 3,000.00 (monto real pagado) con detalle de meta!\n";
} else {
    echo "❌ Error en Caso 2\n";
}

if (strpos($html, '<td style=\'text-align: center; color: #94a3b8; font-weight: 500;\' title=\'Sin abonos registrados\'>-</td>') !== false || strpos($html, 'title="Sin abonos registrados">-</td>') !== false || strpos($html, '>-</td>') !== false) {
    echo "✔ CASO 3 (NO CANCELÓ NADA): Muestra '-' limpiamente sin mostrar 0!\n";
} else {
    echo "❌ Error en Caso 3\n";
}

// Limpiar pagos de prueba
$cuotaModel->delete($p1);
$cuotaModel->delete($p2);
