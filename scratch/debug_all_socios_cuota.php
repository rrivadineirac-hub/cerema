<?php
// scratch/debug_all_socios_cuota.php

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';
require_once 'c:/xampp/htdocs/cerema/Model/mcuota_inicial.php';

$database = new Database();
$db = $database->getConnection();
$socioModel = new SocioModel($db);
$cuotaModel = new CuotaInicialModel($db);

$stmt = $socioModel->getAll();
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== DIAGNÓSTICO COMPLETO DE SOCIOS Y PAGOS ===\n";
echo "Total socios: " . count($socios) . "\n\n";

$conPagos = 0;
$conCuotaAsignada = 0;

foreach ($socios as $s) {
    $resumen = $cuotaModel->getResumenSocio($s['id_socio']);
    $pagadoEnTabla = (float)$s['cuota_inicial_pagado'];
    $cuotaPerfil = (float)$s['cuota_inicial'];
    
    if ($pagadoEnTabla > 0 || $resumen['total_pagado'] > 0) {
        $conPagos++;
        echo "SOCIO ID {$s['id_socio']} ({$s['ap_paterno']} {$s['nombre']}):\n";
        echo "  - Cuota en Perfil (por acción): Bs. {$cuotaPerfil}\n";
        echo "  - Acciones: {$s['acciones']}\n";
        echo "  - Meta Total: Bs. {$resumen['meta_total']}\n";
        echo "  - Total Pagado (Model): Bs. {$resumen['total_pagado']}\n";
        echo "  - Total Pagado (Subquery): Bs. {$pagadoEnTabla}\n";
        echo "  - Saldo Pendiente: Bs. {$resumen['saldo_pendiente']}\n";
        echo "  - Estado Cuota: " . ($resumen['saldo_pendiente'] <= 0 ? "TOTALMENTE PAGADO ✔" : "PAGO PARCIAL ⏳") . "\n\n";
    }
    
    if ($cuotaPerfil > 0) {
        $conCuotaAsignada++;
    }
}

echo "Socios con al menos 1 pago registrado: {$conPagos}\n";
echo "Socios con cuota_inicial > 0 en perfil: {$conCuotaAsignada}\n";
