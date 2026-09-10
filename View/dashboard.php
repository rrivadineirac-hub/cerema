<?php 
// dashboard.php
include 'header.php'; 

require_once '../Config/database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div style='padding: 30px; margin: 20px; background: #fff; border-left: 5px solid #FF7A00; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);'>";
    echo "<h2 style='color: #FF7A00; margin-top:0;'>⚠️ Error de Base de Datos</h2>";
    echo "<p>No se pudo establecer la conexión con la base de datos en este equipo.</p>";
    if (!empty($database->error)) {
        echo "<p style='font-family: monospace; background: #FFF5F5; color: #C53030; padding: 10px; border-radius: 4px;'><b>Detalle:</b> " . htmlspecialchars($database->error) . "</p>";
    }
    echo "</div>";
    include 'footer.php';
    exit();
}

// 1. Total Miembros
$query_socios = "SELECT COUNT(*) as total FROM asociados";
$stmt = $db->prepare($query_socios);
$stmt->execute();
$total_miembros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 2. Fondo Actual y Desgloses
$query_aportes = "SELECT 
    (SELECT COALESCE(SUM(monto), 0) FROM mensualidad WHERE estado = 'Pagado') as total_mensualidades,
    (SELECT COALESCE(SUM(monto), 0) FROM aporte_extraordinario) as total_extraordinarios,
    (SELECT COALESCE(SUM(monto), 0) FROM aportes_especiales) as total_especiales,
    (SELECT COALESCE(SUM(monto), 0) FROM otros_ingresos WHERE estado = 'Cobrado') as total_otros_ingresos";
$stmt = $db->prepare($query_aportes);
$stmt->execute();
$row_aportes = $stmt->fetch(PDO::FETCH_ASSOC);
$total_mensualidades = $row_aportes['total_mensualidades'];
$total_extraordinarios = $row_aportes['total_extraordinarios'];
$total_especiales = $row_aportes['total_especiales'];
$total_otros_ingresos = $row_aportes['total_otros_ingresos'];
$fondo_actual = $total_mensualidades + $total_extraordinarios + $total_especiales + $total_otros_ingresos;

// 3. Cuotas Pendientes (cuotas no pagadas desde enero hasta el mes actual de la gestión)
$anio_actual = (int)date('Y');
$mes_actual_num = (int)date('n'); // 1=Enero, ..., 9=Septiembre, etc.

$meses_nombres_all = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Obtener lista de socios activos
$query_socios_acc = "SELECT id_socio, COALESCE(acciones, 1) as num_acciones FROM asociados WHERE estado = 1";
$stmt_sa = $db->prepare($query_socios_acc);
$stmt_sa->execute();
$socios_list = $stmt_sa->fetchAll(PDO::FETCH_ASSOC);

if (empty($socios_list)) {
    $query_socios_acc = "SELECT id_socio, COALESCE(acciones, 1) as num_acciones FROM asociados";
    $stmt_sa = $db->prepare($query_socios_acc);
    $stmt_sa->execute();
    $socios_list = $stmt_sa->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener todas las mensualidades pagadas de la gestión actual
$query_pagadas_map = "SELECT id_socio, COALESCE(numero_accion, 1) as num_acc, mes 
                      FROM mensualidad 
                      WHERE anio = ? AND estado = 'Pagado'";
$stmt_pm = $db->prepare($query_pagadas_map);
$stmt_pm->execute([$anio_actual]);
$pagadas_rows = $stmt_pm->fetchAll(PDO::FETCH_ASSOC);

// Mapa de cuotas pagadas: [id_socio][num_acc][mes] = true
$paid_map = [];
foreach ($pagadas_rows as $pr) {
    $s_id = $pr['id_socio'];
    $acc_num = (int)$pr['num_acc'];
    $m_name = $pr['mes'];
    $paid_map[$s_id][$acc_num][$m_name] = true;
}

// Contar cuotas pendientes desde Enero (mes 1) hasta el mes actual
$cuotas_pendientes = 0;
foreach ($socios_list as $socio) {
    $s_id = $socio['id_socio'];
    $max_acc = max(1, (int)$socio['num_acciones']);
    
    for ($acc = 1; $acc <= $max_acc; $acc++) {
        for ($m = 1; $m <= $mes_actual_num; $m++) {
            $mes_nombre = $meses_nombres_all[$m];
            if (!isset($paid_map[$s_id][$acc][$mes_nombre])) {
                $cuotas_pendientes++;
            }
        }
    }
}

// 4. Eventos Próximos
$query_count_eventos = "SELECT COUNT(*) as total FROM eventos WHERE fecha_evento >= CURDATE() AND estado = 'Programado'";
$stmt = $db->prepare($query_count_eventos);
$stmt->execute();
$eventos_proximos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query_eventos = "SELECT * FROM eventos WHERE fecha_evento >= CURDATE() AND estado = 'Programado' ORDER BY fecha_evento ASC LIMIT 3";
$stmt = $db->prepare($query_eventos);
$stmt->execute();
$eventos_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Actividad Reciente (Últimos 3 eventos realizados o cancelados)
$query_recientes = "SELECT * FROM eventos WHERE estado IN ('Realizado', 'Cancelado') ORDER BY fecha_evento DESC, id_evento DESC LIMIT 3";
$stmt = $db->prepare($query_recientes);
$stmt->execute();
$eventos_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <h1 class="page-title">Bienvenido a CEREMA</h1>
    <p class="page-subtitle">Resumen general de la fraternidad</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(255, 122, 0, 0.1); color: #FF7A00;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-details">
            <h3>Total Miembros</h3>
            <p class="stat-value"><?php echo $total_miembros; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="stat-details">
            <h3>Fondo Actual</h3>
            <p class="stat-value">Bs. <?php echo number_format($fondo_actual, 2); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-details">
            <h3>Eventos Próximos</h3>
            <p class="stat-value"><?php echo $eventos_proximos; ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="stat-details">
            <h3>Cuotas Pendientes</h3>
            <p class="stat-value"><?php echo $cuotas_pendientes; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div class="stat-details">
            <h3>Aportes Mensuales</h3>
            <p class="stat-value">Bs. <?php echo number_format($total_mensualidades, 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(241, 196, 15, 0.1); color: #f1c40f;">
            <i class="fa-solid fa-star"></i>
        </div>
        <div class="stat-details">
            <h3>Aportes Extraordinarios</h3>
            <p class="stat-value">Bs. <?php echo number_format($total_extraordinarios, 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(230, 126, 34, 0.1); color: #e67e22;">
            <i class="fa-solid fa-gift"></i>
        </div>
        <div class="stat-details">
            <h3>Aportes Especiales</h3>
            <p class="stat-value">Bs. <?php echo number_format($total_especiales, 2); ?></p>
        </div>
    </div>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <div class="widget-header">
            <h2>Próximos Eventos</h2>
            <button class="btn-sm">Ver todos</button>
        </div>
        <div class="widget-content">
            <ul class="event-list">
                <?php if (count($eventos_lista) > 0): ?>
                    <?php foreach ($eventos_lista as $ev): 
                        $meses = ['Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr','May'=>'May','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic'];
                        $dia = date('d', strtotime($ev['fecha_evento']));
                        $mes = $meses[date('M', strtotime($ev['fecha_evento']))];
                        $hora = $ev['hora_evento'] ? date('H:i', strtotime($ev['hora_evento'])) : '--:--';
                        $lugar = $ev['lugar'] ? htmlspecialchars($ev['lugar']) : 'Lugar no especificado';
                    ?>
                    <li class="event-item">
                        <div class="event-date">
                            <span class="day"><?php echo $dia; ?></span>
                            <span class="month"><?php echo $mes; ?></span>
                        </div>
                        <div class="event-info">
                            <h4><?php echo htmlspecialchars($ev['tipo_evento']); ?></h4>
                            <p><i class="fa-regular fa-clock"></i> <?php echo $hora; ?> - <?php echo $lugar; ?></p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="event-item">
                        <div class="event-info">
                            <p>No hay eventos próximos programados.</p>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h2>Actividad Reciente</h2>
        </div>
        <div class="widget-content">
            <ul class="activity-list">
                <?php if (count($eventos_recientes) > 0): ?>
                    <?php foreach ($eventos_recientes as $ev_reciente): 
                        // Calcular tiempo transcurrido (simplificado)
                        $fecha_reg = new DateTime($ev_reciente['fecha_registro']);
                        $ahora = new DateTime();
                        $diferencia = $ahora->diff($fecha_reg);
                        
                        if ($diferencia->days == 0) {
                            if ($diferencia->h == 0) {
                                $tiempo_hace = "Hace " . $diferencia->i . " minutos";
                            } else {
                                $tiempo_hace = "Hace " . $diferencia->h . " horas";
                            }
                        } elseif ($diferencia->days == 1) {
                            $tiempo_hace = "Ayer";
                        } else {
                            $tiempo_hace = "Hace " . $diferencia->days . " días";
                        }
                    ?>
                    <?php
                        $icon_class = ($ev_reciente['estado'] == 'Cancelado') ? 'event-cancelado' : 'event-realizado';
                    ?>
                    <li class="activity-item">
                        <div class="activity-icon <?php echo $icon_class; ?>"><i class="fa-solid fa-calendar"></i></div>
                        <div class="activity-info">
                            <?php if ($ev_reciente['estado'] == 'Cancelado'): ?>
                                <p>Se canceló el evento <strong><?php echo htmlspecialchars($ev_reciente['tipo_evento']); ?></strong></p>
                            <?php else: ?>
                                <p>Se realizó el evento <strong><?php echo htmlspecialchars($ev_reciente['tipo_evento']); ?></strong></p>
                            <?php endif; ?>
                            <span class="time"><?php echo $tiempo_hace; ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <p>No hay actividad reciente registrada.</p>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
