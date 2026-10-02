<?php
// Model/mreporte.php

class ReporteModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureOtrosGastosColumnsExist();
    }

    private function ensureOtrosGastosColumnsExist() {
        try { $this->conn->exec("ALTER TABLE otros_gastos ADD COLUMN unidad_medida VARCHAR(50) DEFAULT 'Glb'"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE otros_gastos ADD COLUMN cantidad DECIMAL(10,2) DEFAULT 1.00"); } catch (Exception $e) {}
        try { $this->conn->exec("ALTER TABLE otros_gastos ADD COLUMN precio DECIMAL(10,2) DEFAULT NULL"); } catch (Exception $e) {}
    }

    // Obtener lista de asociados activos
    public function getSociosActivos() {
        $query = "SELECT 
                    s.id_socio, 
                    s.ci, 
                    s.complemento,
                    s.ap_paterno, 
                    s.ap_materno, 
                    s.nombre, 
                    COALESCE(s.acciones, 1) as acciones, 
                    COALESCE(s.cuota_inicial, 0.00) as cuota_inicial,
                    COALESCE((SELECT SUM(c.monto) FROM cuota_inicial_pagos c WHERE c.id_socio = s.id_socio AND (c.estado = 'Pagado' OR c.estado IS NULL OR c.estado = '')), 0.00) as cuota_inicial_pagado,
                    s.telefono, 
                    s.correo, 
                    s.fecha_ingreso, 
                    s.estado 
                  FROM asociados s
                  WHERE s.estado = 'Activo' OR s.estado = '1'
                  ORDER BY s.ap_paterno ASC, s.ap_materno ASC, s.nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener estadísticas de asociados activos
    public function getStatsSociosActivos() {
        $query = "SELECT 
                    COUNT(*) as total_socios,
                    COUNT(*) as total_activos,
                    COALESCE(SUM(COALESCE(acciones, 1)), 0) as total_acciones
                  FROM asociados 
                  WHERE estado = 'Activo' OR estado = '1'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener lista de asociados inactivos
    public function getSociosInactivos() {
        $query = "SELECT 
                    id_socio, 
                    ci, 
                    complemento,
                    ap_paterno, 
                    ap_materno, 
                    nombre, 
                    COALESCE(acciones, 1) as acciones, 
                    COALESCE(cuota_inicial, 0.00) as cuota_inicial,
                    telefono, 
                    correo, 
                    fecha_ingreso, 
                    estado 
                  FROM asociados 
                  WHERE estado = 'Inactivo' OR estado = '0'
                  ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener estadísticas de asociados inactivos
    public function getStatsSociosInactivos() {
        $query = "SELECT 
                    COUNT(*) as total_socios,
                    COUNT(*) as total_inactivos,
                    COALESCE(SUM(COALESCE(acciones, 1)), 0) as total_acciones
                  FROM asociados 
                  WHERE estado = 'Inactivo' OR estado = '0'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener lista de asociados pasivos
    public function getSociosPasivos() {
        $query = "SELECT 
                    id_socio, 
                    ci, 
                    complemento,
                    ap_paterno, 
                    ap_materno, 
                    nombre, 
                    COALESCE(acciones, 1) as acciones, 
                    COALESCE(cuota_inicial, 0.00) as cuota_inicial,
                    telefono, 
                    correo, 
                    fecha_ingreso, 
                    estado 
                  FROM asociados 
                  WHERE estado = 'Pasivo' OR estado = 'Moroso'
                  ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener estadísticas de asociados pasivos
    public function getStatsSociosPasivos() {
        $query = "SELECT 
                    COUNT(*) as total_socios,
                    COUNT(*) as total_pasivos,
                    COALESCE(SUM(COALESCE(acciones, 1)), 0) as total_acciones
                  FROM asociados 
                  WHERE estado = 'Pasivo' OR estado = 'Moroso'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener lista de todos los asociados
    public function getTodosSocios() {
        $query = "SELECT 
                    id_socio, 
                    ci, 
                    complemento,
                    ap_paterno, 
                    ap_materno, 
                    nombre, 
                    COALESCE(acciones, 1) as acciones, 
                    COALESCE(cuota_inicial, 0.00) as cuota_inicial,
                    telefono, 
                    correo, 
                    fecha_ingreso, 
                    estado 
                  FROM asociados 
                  ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener estadísticas de todos los asociados
    public function getStatsTodosSocios() {
        $query = "SELECT 
                    COUNT(*) as total_socios,
                    COALESCE(SUM(COALESCE(acciones, 1)), 0) as total_acciones
                  FROM asociados";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REPORTES DE PAGOS: MENSUALIDADES
    public function getReporteMensualidades() {
        $query = "SELECT 
                    m.id_mensualidad,
                    m.id_socio,
                    m.numero_accion,
                    m.numero_recibo,
                    m.mes,
                    m.anio,
                    m.monto,
                    m.fecha_pago,
                    m.estado,
                    s.ci,
                    s.complemento,
                    s.ap_paterno,
                    s.ap_materno,
                    s.nombre
                  FROM mensualidad m
                  JOIN asociados s ON m.id_socio = s.id_socio
                  ORDER BY m.fecha_pago DESC, m.id_mensualidad DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteMensualidades() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_recaudado
                  FROM mensualidad";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REPORTES DE PAGOS: EXTRAORDINARIOS
    public function getReporteExtraordinario() {
        $query = "SELECT 
                    a.id_aporte,
                    a.id_socio,
                    a.numero_accion,
                    a.motivo,
                    a.monto,
                    a.fecha_aporte as fecha_pago,
                    a.numero_recibo,
                    s.ci,
                    s.complemento,
                    s.ap_paterno,
                    s.ap_materno,
                    s.nombre
                  FROM aporte_extraordinario a
                  JOIN asociados s ON a.id_socio = s.id_socio
                  ORDER BY a.fecha_aporte DESC, a.id_aporte DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteExtraordinario() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_recaudado
                  FROM aporte_extraordinario";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REPORTES DE PAGOS: ESPECIALES
    public function getReporteEspeciales() {
        $query = "SELECT 
                    e.id_aporte_esp as id_aporte,
                    e.id_socio,
                    e.numero_accion,
                    e.motivo,
                    e.monto,
                    e.fecha_aporte as fecha_pago,
                    e.numero_recibo,
                    s.ci,
                    s.complemento,
                    s.ap_paterno,
                    s.ap_materno,
                    s.nombre
                  FROM aportes_especiales e
                  JOIN asociados s ON e.id_socio = s.id_socio
                  ORDER BY e.fecha_aporte DESC, e.id_aporte_esp DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEspeciales() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_recaudado
                  FROM aportes_especiales";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REPORTES PLANILLA MATRIZ ANUAL DE MENSUALIDADES
    public function getMatrizAnualMensualidades($anio = null) {
        if (!$anio) {
            $anio = date('Y');
        }

        // 1. Obtener todos los asociados
        $querySocios = "SELECT id_socio, ci, complemento, ap_paterno, ap_materno, nombre, COALESCE(acciones, 1) as total_acciones, estado 
                        FROM asociados 
                        ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        $stmtSocios = $this->conn->prepare($querySocios);
        $stmtSocios->execute();
        $socios = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);

        // 2. Obtener todos los pagos de mensualidades para la gestión
        $queryPagos = "SELECT id_socio, numero_accion, mes, monto, numero_recibo, fecha_pago, estado 
                       FROM mensualidad 
                       WHERE anio = :anio";
        $stmtPagos = $this->conn->prepare($queryPagos);
        $stmtPagos->bindParam(':anio', $anio);
        $stmtPagos->execute();
        $pagosRaw = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        // Mapear pagos por id_socio -> numero_accion -> mes
        $pagosMap = [];
        foreach ($pagosRaw as $p) {
            $idSocio = $p['id_socio'];
            $numAccion = intval($p['numero_accion'] ?? 1);
            $mes = trim($p['mes']);
            $pagosMap[$idSocio][$numAccion][$mes] = $p;
        }

        $matriz = [];
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        foreach ($socios as $socio) {
            $idSocio = $socio['id_socio'];
            $totalAcciones = intval($socio['total_acciones']);

            // Crear una fila por cada acción que posee el socio
            for ($acc = 1; $acc <= $totalAcciones; $acc++) {
                $row = [
                    'id_socio' => $idSocio,
                    'ci' => $socio['ci'] . (!empty($socio['complemento']) ? '-' . $socio['complemento'] : ''),
                    'nombre_completo' => trim($socio['ap_paterno'] . ' ' . $socio['ap_materno'] . ', ' . $socio['nombre']),
                    'estado_socio' => $socio['estado'],
                    'numero_accion' => $acc,
                    'total_acciones_socio' => $totalAcciones,
                    'meses' => [],
                    'total_pagado_anio' => 0,
                    'meses_pagados_cnt' => 0
                ];

                foreach ($meses as $m) {
                    $pagoInfo = $pagosMap[$idSocio][$acc][$m] ?? null;
                    if ($pagoInfo) {
                        $row['meses'][$m] = [
                            'pagado' => true,
                            'monto' => floatval($pagoInfo['monto']),
                            'recibo' => $pagoInfo['numero_recibo'],
                            'fecha' => $pagoInfo['fecha_pago']
                        ];
                        $row['total_pagado_anio'] += floatval($pagoInfo['monto']);
                        $row['meses_pagados_cnt']++;
                    } else {
                        $row['meses'][$m] = [
                            'pagado' => false,
                            'monto' => 0,
                            'recibo' => '',
                            'fecha' => ''
                        ];
                    }
                }

                $matriz[] = $row;
            }
        }

        return $matriz;
    }

    // REPORTES PLANILLA MATRIZ ANUAL DE APORTES EXTRAORDINARIOS
    public function getMatrizAnualExtraordinarios($anio = null) {
        if (!$anio) {
            $anio = date('Y');
        }

        $queryCat = "SELECT c.monto_sugerido 
                     FROM categorias_ingreso c 
                     JOIN aporte_extraordinario a ON a.motivo COLLATE utf8mb4_general_ci = c.nombre COLLATE utf8mb4_general_ci 
                     WHERE YEAR(a.fecha_aporte) = :anio 
                     ORDER BY a.id_aporte DESC LIMIT 1";
        $stmtCat = $this->conn->prepare($queryCat);
        $stmtCat->bindParam(':anio', $anio);
        $stmtCat->execute();
        $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
        $montoMetaG = $catRow ? (float)$catRow['monto_sugerido'] : 0.00;

        if ($montoMetaG <= 0) {
            $queryCatFallback = "SELECT monto_sugerido FROM categorias_ingreso WHERE monto_sugerido > 50.00 ORDER BY id_cat_ingreso DESC LIMIT 1";
            $stmtFb = $this->conn->prepare($queryCatFallback);
            $stmtFb->execute();
            $fbRow = $stmtFb->fetch(PDO::FETCH_ASSOC);
            if ($fbRow) {
                $montoMetaG = (float)$fbRow['monto_sugerido'];
            }
        }

        $querySocios = "SELECT id_socio, ci, complemento, ap_paterno, ap_materno, nombre, COALESCE(acciones, 1) as total_acciones, estado 
                        FROM asociados 
                        ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        $stmtSocios = $this->conn->prepare($querySocios);
        $stmtSocios->execute();
        $socios = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);

        $queryPagos = "SELECT id_socio, numero_accion, motivo, monto, numero_recibo, fecha_aporte as fecha_pago,
                              MONTH(fecha_aporte) as mes_num
                       FROM aporte_extraordinario 
                       WHERE YEAR(fecha_aporte) = :anio";
        $stmtPagos = $this->conn->prepare($queryPagos);
        $stmtPagos->bindParam(':anio', $anio);
        $stmtPagos->execute();
        $pagosRaw = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        $mesesMapNum = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $pagosMap = [];
        foreach ($pagosRaw as $p) {
            $idSocio = $p['id_socio'];
            $numAccion = intval($p['numero_accion'] ?? 1);
            $mesNombre = $mesesMapNum[intval($p['mes_num'])] ?? '';
            if ($mesNombre) {
                $pagosMap[$idSocio][$numAccion][$mesNombre][] = $p;
            }
        }

        $matriz = [];
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        foreach ($socios as $socio) {
            $idSocio = $socio['id_socio'];
            $totalAcciones = intval($socio['total_acciones']);

            for ($acc = 1; $acc <= $totalAcciones; $acc++) {
                $row = [
                    'id_socio' => $idSocio,
                    'ci' => $socio['ci'] . (!empty($socio['complemento']) ? '-' . $socio['complemento'] : ''),
                    'nombre_completo' => trim($socio['ap_paterno'] . ' ' . $socio['ap_materno'] . ', ' . $socio['nombre']),
                    'estado_socio' => $socio['estado'],
                    'numero_accion' => $acc,
                    'total_acciones_socio' => $totalAcciones,
                    'meses' => [],
                    'total_pagado_anio' => 0,
                    'meses_pagados_cnt' => 0,
                    'meses_pagados_arr' => [],
                    'monto_meta' => $montoMetaG,
                    'falta_por_pagar' => 0,
                    'estado_pago' => 'PENDIENTE'
                ];

                $fechasArr = [];
                foreach ($meses as $m) {
                    $listaP = $pagosMap[$idSocio][$acc][$m] ?? [];
                    if (!empty($listaP)) {
                        $montoM = 0;
                        $recibos = [];
                        foreach ($listaP as $lp) {
                            $montoM += floatval($lp['monto']);
                            if (!empty($lp['numero_recibo'])) $recibos[] = $lp['numero_recibo'];
                            if (!empty($lp['fecha_pago'])) {
                                $fFormatted = date('d/m/Y', strtotime($lp['fecha_pago']));
                                $mFormatted = number_format(floatval($lp['monto']), 2);
                                $motivoStr = !empty($lp['motivo']) ? trim($lp['motivo']) : '';
                                $fechasArr[] = $fFormatted . ($motivoStr !== '' ? ' - ' . $motivoStr : '') . ' (Bs. ' . $mFormatted . ')';
                            }
                        }

                        $row['meses'][$m] = [
                            'pagado' => true,
                            'monto' => $montoM,
                            'recibo' => implode(', ', $recibos),
                            'fecha' => $listaP[0]['fecha_pago']
                        ];
                        $row['total_pagado_anio'] += $montoM;
                        $row['meses_pagados_cnt']++;
                        $row['meses_pagados_arr'][] = $m;
                    } else {
                        $row['meses'][$m] = [
                            'pagado' => false,
                            'monto' => 0,
                            'recibo' => '',
                            'fecha' => ''
                        ];
                    }
                }

                $row['monto_meta'] = $montoMetaG;

                if ($montoMetaG > 0) {
                    $row['falta_por_pagar'] = max(0, $montoMetaG - $row['total_pagado_anio']);
                    if ($row['total_pagado_anio'] >= $montoMetaG) {
                        $row['estado_pago'] = 'COMPLETO';
                    } elseif ($row['total_pagado_anio'] > 0) {
                        $row['estado_pago'] = 'PARCIAL';
                    } else {
                        $row['estado_pago'] = 'PENDIENTE';
                    }
                } else {
                    $row['falta_por_pagar'] = 0;
                    $row['estado_pago'] = ($row['total_pagado_anio'] > 0) ? 'COMPLETO' : 'PENDIENTE';
                }

                $row['meses_pagados_str'] = !empty($row['meses_pagados_arr']) ? implode(', ', $row['meses_pagados_arr']) : 'Ninguno';
                $row['fechas_pagos_arr'] = array_values(array_unique($fechasArr));
                $row['fechas_pagos_str'] = !empty($fechasArr) ? implode(', ', array_unique($fechasArr)) : '';

                $matriz[] = $row;
            }
        }

        return $matriz;
    }

    // REPORTES PLANILLA MATRIZ ANUAL DE APORTES ESPECIALES
    public function getMatrizAnualEspeciales($anio = null) {
        if (!$anio) {
            $anio = date('Y');
        }

        $queryCat = "SELECT c.monto_sugerido 
                     FROM categorias_ingreso c 
                     JOIN aportes_especiales e ON e.motivo COLLATE utf8mb4_general_ci = c.nombre COLLATE utf8mb4_general_ci 
                     WHERE YEAR(e.fecha_aporte) = :anio 
                     ORDER BY e.id_aporte_esp DESC LIMIT 1";
        $stmtCat = $this->conn->prepare($queryCat);
        $stmtCat->bindParam(':anio', $anio);
        $stmtCat->execute();
        $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
        $montoMetaG = $catRow ? (float)$catRow['monto_sugerido'] : 0.00;

        if ($montoMetaG <= 0) {
            $queryCatFallback = "SELECT monto_sugerido FROM categorias_ingreso WHERE monto_sugerido > 50.00 ORDER BY id_cat_ingreso DESC LIMIT 1";
            $stmtFb = $this->conn->prepare($queryCatFallback);
            $stmtFb->execute();
            $fbRow = $stmtFb->fetch(PDO::FETCH_ASSOC);
            if ($fbRow) {
                $montoMetaG = (float)$fbRow['monto_sugerido'];
            }
        }

        $querySocios = "SELECT id_socio, ci, complemento, ap_paterno, ap_materno, nombre, COALESCE(acciones, 1) as total_acciones, estado 
                        FROM asociados 
                        ORDER BY ap_paterno ASC, ap_materno ASC, nombre ASC";
        $stmtSocios = $this->conn->prepare($querySocios);
        $stmtSocios->execute();
        $socios = $stmtSocios->fetchAll(PDO::FETCH_ASSOC);

        $queryPagos = "SELECT id_socio, numero_accion, motivo, monto, numero_recibo, fecha_aporte as fecha_pago,
                              MONTH(fecha_aporte) as mes_num
                       FROM aportes_especiales 
                       WHERE YEAR(fecha_aporte) = :anio";
        $stmtPagos = $this->conn->prepare($queryPagos);
        $stmtPagos->bindParam(':anio', $anio);
        $stmtPagos->execute();
        $pagosRaw = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        $mesesMapNum = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $pagosMap = [];
        foreach ($pagosRaw as $p) {
            $idSocio = $p['id_socio'];
            $numAccion = intval($p['numero_accion'] ?? 1);
            $mesNombre = $mesesMapNum[intval($p['mes_num'])] ?? '';
            if ($mesNombre) {
                $pagosMap[$idSocio][$numAccion][$mesNombre][] = $p;
            }
        }

        $matriz = [];
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        foreach ($socios as $socio) {
            $idSocio = $socio['id_socio'];
            $totalAcciones = intval($socio['total_acciones']);

            for ($acc = 1; $acc <= $totalAcciones; $acc++) {
                $row = [
                    'id_socio' => $idSocio,
                    'ci' => $socio['ci'] . (!empty($socio['complemento']) ? '-' . $socio['complemento'] : ''),
                    'nombre_completo' => trim($socio['ap_paterno'] . ' ' . $socio['ap_materno'] . ', ' . $socio['nombre']),
                    'estado_socio' => $socio['estado'],
                    'numero_accion' => $acc,
                    'total_acciones_socio' => $totalAcciones,
                    'meses' => [],
                    'total_pagado_anio' => 0,
                    'meses_pagados_cnt' => 0,
                    'meses_pagados_arr' => [],
                    'monto_meta' => $montoMetaG,
                    'falta_por_pagar' => 0,
                    'estado_pago' => 'PENDIENTE'
                ];

                $fechasArr = [];
                foreach ($meses as $m) {
                    $listaP = $pagosMap[$idSocio][$acc][$m] ?? [];
                    if (!empty($listaP)) {
                        $montoM = 0;
                        $recibos = [];
                        foreach ($listaP as $lp) {
                            $montoM += floatval($lp['monto']);
                            if (!empty($lp['numero_recibo'])) $recibos[] = $lp['numero_recibo'];
                            if (!empty($lp['fecha_pago'])) {
                                $fFormatted = date('d/m/Y', strtotime($lp['fecha_pago']));
                                $mFormatted = number_format(floatval($lp['monto']), 2);
                                $motivoStr = !empty($lp['motivo']) ? trim($lp['motivo']) : '';
                                $fechasArr[] = $fFormatted . ($motivoStr !== '' ? ' - ' . $motivoStr : '') . ' (Bs. ' . $mFormatted . ')';
                            }
                        }

                        $row['meses'][$m] = [
                            'pagado' => true,
                            'monto' => $montoM,
                            'recibo' => implode(', ', $recibos),
                            'fecha' => $listaP[0]['fecha_pago']
                        ];
                        $row['total_pagado_anio'] += $montoM;
                        $row['meses_pagados_cnt']++;
                        $row['meses_pagados_arr'][] = $m;
                    } else {
                        $row['meses'][$m] = [
                            'pagado' => false,
                            'monto' => 0,
                            'recibo' => '',
                            'fecha' => ''
                        ];
                    }
                }

                $row['monto_meta'] = $montoMetaG;

                if ($montoMetaG > 0) {
                    $row['falta_por_pagar'] = max(0, $montoMetaG - $row['total_pagado_anio']);
                    if ($row['total_pagado_anio'] >= $montoMetaG) {
                        $row['estado_pago'] = 'COMPLETO';
                    } elseif ($row['total_pagado_anio'] > 0) {
                        $row['estado_pago'] = 'PARCIAL';
                    } else {
                        $row['estado_pago'] = 'PENDIENTE';
                    }
                } else {
                    $row['falta_por_pagar'] = 0;
                    $row['estado_pago'] = ($row['total_pagado_anio'] > 0) ? 'COMPLETO' : 'PENDIENTE';
                }

                $row['meses_pagados_str'] = !empty($row['meses_pagados_arr']) ? implode(', ', $row['meses_pagados_arr']) : 'Ninguno';
                $row['fechas_pagos_arr'] = array_values(array_unique($fechasArr));
                $row['fechas_pagos_str'] = !empty($fechasArr) ? implode(', ', array_unique($fechasArr)) : '';

                $matriz[] = $row;
            }
        }

        return $matriz;
    }

    // REPORTES DE ACTIVOS Y PATRIMONIO
    public function getReporteActivos() {
        $query = "SELECT 
                    id_activo,
                    nombre_activo,
                    tipo_activo,
                    valor_estimado,
                    fecha_adquisicion,
                    estado_operativo,
                    observaciones
                  FROM activos
                  ORDER BY nombre_activo ASC, id_activo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteActivos() {
        $query = "SELECT 
                    COUNT(*) as total_activos,
                    COALESCE(SUM(valor_estimado), 0) as valor_total,
                    SUM(CASE WHEN estado_operativo IN ('Excelente', 'Bueno') THEN 1 ELSE 0 END) as operativos,
                    SUM(CASE WHEN estado_operativo IN ('Regular', 'En Mantenimiento', 'Fuera de Servicio') THEN 1 ELSE 0 END) as observados
                  FROM activos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REPORTES DE OTROS INGRESOS
    public function getReporteOtrosIngresos() {
        $query = "SELECT 
                    o.id_otro_ingreso,
                    o.id_gestion,
                    o.detalle,
                    o.monto,
                    o.fecha_pago,
                    o.comprobante,
                    o.estado,
                    g.gestion
                  FROM otros_ingresos o
                  LEFT JOIN gestiones g ON o.id_gestion = g.id_gestion
                  ORDER BY o.fecha_pago DESC, o.id_otro_ingreso DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteOtrosIngresos() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_recaudado
                  FROM otros_ingresos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------
    // REPORTES DE EGRESOS
    // -------------------------------------------------------------

    // 1. SUELDOS
    public function getReporteEgresosSueldo() {
        $query = "SELECT 
                    s.id_sueldo,
                    s.id_gestion,
                    s.cargo,
                    s.empleado,
                    s.mes,
                    s.monto,
                    s.fecha_pago,
                    s.comprobante,
                    s.estado,
                    g.gestion
                  FROM sueldos s
                  LEFT JOIN gestiones g ON s.id_gestion = g.id_gestion
                  ORDER BY s.fecha_pago DESC, s.id_sueldo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEgresosSueldo() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_egresado
                  FROM sueldos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. SERVICIOS BÁSICOS
    public function getReporteEgresosServicios() {
        $query = "SELECT 
                    sb.id_servicio,
                    sb.id_gestion,
                    sb.tipo_servicio,
                    sb.mes_pago,
                    sb.monto,
                    sb.fecha_pago,
                    sb.comprobante,
                    sb.estado,
                    g.gestion
                  FROM servicios_basicos sb
                  LEFT JOIN gestiones g ON sb.id_gestion = g.id_gestion
                  ORDER BY sb.fecha_pago DESC, sb.id_servicio DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEgresosServicios() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_egresado
                  FROM servicios_basicos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. EXTRAORDINARIOS
    public function getReporteEgresosExtraordinarios() {
        $query = "SELECT 
                    ee.id_egreso_ext,
                    ee.id_gestion,
                    ee.motivo,
                    ee.detalle,
                    ee.monto,
                    ee.fecha_pago,
                    ee.comprobante,
                    ee.estado,
                    g.gestion
                  FROM egresos_extraordinarios ee
                  LEFT JOIN gestiones g ON ee.id_gestion = g.id_gestion
                  ORDER BY ee.fecha_pago DESC, ee.id_egreso_ext DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEgresosExtraordinarios() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_egresado
                  FROM egresos_extraordinarios";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. ESPECIALES
    public function getReporteEgresosEspeciales() {
        $query = "SELECT 
                    es.id_egreso_esp,
                    es.id_gestion,
                    es.motivo,
                    es.detalle,
                    es.monto,
                    es.fecha_pago,
                    es.comprobante,
                    es.estado,
                    g.gestion
                  FROM egresos_especiales es
                  LEFT JOIN gestiones g ON es.id_gestion = g.id_gestion
                  ORDER BY es.fecha_pago DESC, es.id_egreso_esp DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEgresosEspeciales() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_egresado
                  FROM egresos_especiales";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 5. OTROS GASTOS
    public function getConceptosOtrosGastos() {
        $query = "SELECT DISTINCT COALESCE(NULLIF(nombre_gasto, ''), detalle) AS concepto FROM otros_gastos WHERE (nombre_gasto IS NOT NULL AND nombre_gasto != '') OR (detalle IS NOT NULL AND detalle != '') ORDER BY concepto ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getReporteEgresosOtrosGastos() {
        $query = "SELECT 
                    og.id_otro_gasto,
                    og.id_gestion,
                    COALESCE(NULLIF(og.nombre_gasto, ''), og.detalle) AS nombre_gasto,
                    og.detalle,
                    COALESCE(og.unidad_medida, '') AS unidad_medida,
                    COALESCE(og.cantidad, 1.00) AS cantidad,
                    COALESCE(og.precio, og.monto) AS precio,
                    og.monto,
                    og.fecha_pago,
                    og.comprobante,
                    og.estado,
                    g.gestion
                  FROM otros_gastos og
                  LEFT JOIN gestiones g ON og.id_gestion = g.id_gestion
                  ORDER BY og.fecha_pago DESC, og.id_otro_gasto DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStatsReporteEgresosOtrosGastos() {
        $query = "SELECT 
                    COUNT(*) as total_registros,
                    COALESCE(SUM(monto), 0) as total_egresado
                  FROM otros_gastos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
