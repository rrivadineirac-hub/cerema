<?php
// scratch/test_cuota_inicial_crud.php

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
$socios = $socioModel->getActivos()->fetchAll(PDO::FETCH_ASSOC);
echo "Socios activos en BD: " . count($socios) . "\n";

if (!empty($socios)) {
    $s = $socios[0];
    echo "Socio de prueba: ID {$s['id_socio']} - {$s['nombre']} {$s['ap_paterno']} (Acciones: {$s['acciones']}, Cuota/Acción: {$s['cuota_inicial']})\n";
    
    $cuotaModel = new CuotaInicialModel($db);
    $resumen = $cuotaModel->getResumenSocio($s['id_socio']);
    echo "Resumen Socio: Meta Total: {$resumen['meta_total']}, Total Pagado: {$resumen['total_pagado']}, Saldo Pendiente: {$resumen['saldo_pendiente']}\n";
    
    // Probar insertar pago
    $testRecibo = 'REC-TEST-' . rand(1000, 9999);
    $resCreate = $cuotaModel->create([
        'id_socio' => $s['id_socio'],
        'numero_accion' => 1,
        'numero_recibo' => $testRecibo,
        'concepto' => 'Pago inicial de prueba CLI',
        'monto' => 100.50,
        'fecha_pago' => date('Y-m-d H:i:s'),
        'observaciones' => 'Prueba automatizada'
    ]);
    $idCreated = $db->lastInsertId();
    echo "Pago creado exitosamente: " . ($resCreate ? "OK (ID: {$idCreated})" : "ERROR") . "\n";
    
    // Probar obtener pago
    $pago = $cuotaModel->getById($idCreated);
    echo "Pago recuperado: Recibo {$pago['numero_recibo']} | Monto: {$pago['monto']}\n";
    
    // Probar actualización
    $resUpdate = $cuotaModel->update([
        'id_cuota' => $idCreated,
        'id_socio' => $s['id_socio'],
        'numero_accion' => 1,
        'numero_recibo' => $testRecibo,
        'concepto' => 'Pago inicial actualizado CLI',
        'monto' => 150.00,
        'fecha_pago' => date('Y-m-d H:i:s'),
        'observaciones' => 'Actualizado'
    ]);
    echo "Resultado actualización: " . ($resUpdate ? "OK" : "ERROR") . "\n";
    
    $pagoMod = $cuotaModel->getById($idCreated);
    echo "Monto tras actualización: {$pagoMod['monto']}\n";
    
    // Probar eliminación
    $resDel = $cuotaModel->delete($idCreated);
    echo "Resultado eliminación: " . ($resDel ? "OK" : "ERROR") . "\n";
}
