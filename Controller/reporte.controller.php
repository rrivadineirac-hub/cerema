<?php
// Controller/reporte.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('reportes');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mreporte.php';

$database = new Database();
$db = $database->getConnection();

$reporteModel = new ReporteModel($db);

// Obtener lista completa de socios para los desplegables de filtro
$todos_socios_stmt = $reporteModel->getTodosSocios();
$todos_socios = $todos_socios_stmt ? $todos_socios_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$type = isset($_GET['type']) ? $_GET['type'] : 'socios_activos';

if ($type == 'planilla_anual_mensualidades' || $type == 'planilla_anual_extraordinarios' || $type == 'planilla_anual_especiales') {
    $anio_sel = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
    
    if ($type == 'planilla_anual_extraordinarios') {
        $matriz = $reporteModel->getMatrizAnualExtraordinarios($anio_sel);
        $tipo_planilla_nombre = "APORTES EXTRAORDINARIOS";
    } elseif ($type == 'planilla_anual_especiales') {
        $matriz = $reporteModel->getMatrizAnualEspeciales($anio_sel);
        $tipo_planilla_nombre = "APORTES ESPECIALES";
    } else {
        $matriz = $reporteModel->getMatrizAnualMensualidades($anio_sel);
        $tipo_planilla_nombre = "MENSUALIDADES";
    }

    $titulo_reporte = "PLANILLA ANUAL DE CONTROL DE " . $tipo_planilla_nombre . " - GESTIÓN " . $anio_sel;
    include __DIR__ . '/../View/vreporte_planilla_anual.php';

} elseif ($type == 'pagos_mensualidades') {
    $pagos = $reporteModel->getReporteMensualidades();
    $stats = $reporteModel->getStatsReporteMensualidades();
    $titulo_reporte = "REPORTE OFICIAL DE PAGOS - MENSUALIDADES";
    $pago_categoria = "Mensualidades";
    include __DIR__ . '/../View/vreporte_pagos.php';

} elseif ($type == 'pagos_extraordinario') {
    $pagos = $reporteModel->getReporteExtraordinario();
    $stats = $reporteModel->getStatsReporteExtraordinario();
    $titulo_reporte = "REPORTE OFICIAL DE PAGOS - APORTES EXTRAORDINARIOS";
    $pago_categoria = "Extraordinarios";
    include __DIR__ . '/../View/vreporte_pagos.php';

} elseif ($type == 'pagos_especiales') {
    $pagos = $reporteModel->getReporteEspeciales();
    $stats = $reporteModel->getStatsReporteEspeciales();
    $titulo_reporte = "REPORTE OFICIAL DE PAGOS - APORTES ESPECIALES";
    $pago_categoria = "Especiales";
    include __DIR__ . '/../View/vreporte_pagos.php';

} elseif ($type == 'otros_ingresos') {
    $ingresos = $reporteModel->getReporteOtrosIngresos();
    $stats = $reporteModel->getStatsReporteOtrosIngresos();
    $titulo_reporte = "REPORTE OFICIAL DE OTROS INGRESOS INSTITUCIONALES";
    include __DIR__ . '/../View/vreporte_otros_ingresos.php';

} elseif ($type == 'egresos_sueldo') {
    $egresos = $reporteModel->getReporteEgresosSueldo();
    $stats = $reporteModel->getStatsReporteEgresosSueldo();
    $titulo_reporte = "REPORTE OFICIAL DE EGRESOS - SUELDOS Y SALARIOS";
    $egreso_tipo = "sueldo";
    include __DIR__ . '/../View/vreporte_egresos.php';

} elseif ($type == 'egresos_servicios') {
    $egresos = $reporteModel->getReporteEgresosServicios();
    $stats = $reporteModel->getStatsReporteEgresosServicios();
    $titulo_reporte = "REPORTE OFICIAL DE EGRESOS - SERVICIOS BÁSICOS";
    $egreso_tipo = "servicios";
    include __DIR__ . '/../View/vreporte_egresos.php';

} elseif ($type == 'egresos_extraordinario') {
    $egresos = $reporteModel->getReporteEgresosExtraordinarios();
    $stats = $reporteModel->getStatsReporteEgresosExtraordinarios();
    $titulo_reporte = "REPORTE OFICIAL DE EGRESOS - GASTOS EXTRAORDINARIOS";
    $egreso_tipo = "extraordinario";
    include __DIR__ . '/../View/vreporte_egresos.php';

} elseif ($type == 'egresos_especiales') {
    $egresos = $reporteModel->getReporteEgresosEspeciales();
    $stats = $reporteModel->getStatsReporteEgresosEspeciales();
    $titulo_reporte = "REPORTE OFICIAL DE EGRESOS - GASTOS ESPECIALES";
    $egreso_tipo = "especiales";
    include __DIR__ . '/../View/vreporte_egresos.php';

} elseif ($type == 'egresos_otros_gastos') {
    $conceptos_gastos = $reporteModel->getConceptosOtrosGastos();
    $egresos = $reporteModel->getReporteEgresosOtrosGastos();
    $stats = $reporteModel->getStatsReporteEgresosOtrosGastos();
    $titulo_reporte = "REPORTE OFICIAL DE OTROS GASTOS";
    $egreso_tipo = "otros_gastos";
    include __DIR__ . '/../View/vreporte_otros_gastos.php';

} elseif ($type == 'socios_inactivos') {
    $estado_filter = 'Inactivo';
    $socios = $reporteModel->getSociosInactivos();
    $stats = $reporteModel->getStatsSociosInactivos();
    $titulo_estado = "ASOCIADOS INACTIVOS";
    include __DIR__ . '/../View/vreporte_socios_inactivos.php';

} elseif ($type == 'socios_pasivos') {
    $estado_filter = 'Pasivo';
    $socios = $reporteModel->getSociosPasivos();
    $stats = $reporteModel->getStatsSociosPasivos();
    $titulo_estado = "ASOCIADOS PASIVOS";
    include __DIR__ . '/../View/vreporte_socios_pasivos.php';

} elseif ($type == 'activos') {
    $activos = $reporteModel->getReporteActivos();
    $stats = $reporteModel->getStatsReporteActivos();
    $titulo_reporte = "REPORTE OFICIAL DE ACTIVOS Y PATRIMONIO INSTITUCIONAL";
    include __DIR__ . '/../View/vreporte_activos.php';

} else {
    // Por defecto: Socios Activos
    $type = 'socios_activos';
    $estado_filter = 'Activo';
    $socios = $reporteModel->getSociosActivos();
    $stats = $reporteModel->getStatsSociosActivos();
    $titulo_estado = "ASOCIADOS ACTIVOS";
    include __DIR__ . '/../View/vreporte_socios_activos.php';
}
?>
