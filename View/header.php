<?php
date_default_timezone_set('America/La_Paz');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Config/permissions.php';
// Verificación de sesión y tiempo por inactividad (10 minutos = 300 segundos)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Controller/login.controller.php");
    exit();
}

$timeout_duration = 600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // La sesión caducó por inactividad
    session_unset();
    session_destroy();
    header("Location: ../Controller/login.controller.php?msg=timeout");
    exit();
}

// Actualizar el tiempo de última actividad
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEREMA - Dashboard</title>
    <!-- Favicon -->
    <link rel="icon" href="../imagenes/institucinal/logo.png" type="image/png">
    <!-- Local Fonts & Icons -->
    <link rel="stylesheet" href="../css/outfit.css">
    <link rel="stylesheet" href="../css/font-awesome/css/all.min.css">
    <!-- Custom CSS (con cache-busting automático para móviles) -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
    <script>
        function toggleMobileSidebarDirect(e) {
            if (e) {
                if (e.cancelable) e.preventDefault();
                e.stopPropagation();
            }
            var sb = document.getElementById('sidebar');
            var ov = document.getElementById('sidebar-overlay');
            if (!sb) return;
            var isOpen = sb.classList.contains('mobile-open');
            if (isOpen) {
                sb.classList.remove('mobile-open');
                if (ov) ov.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                sb.classList.add('mobile-open');
                if (ov) ov.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
    </script>
    <script>
        // Funciones Globales
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.innerText = message;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        // Transformar a mayúsculas cualquier input de tipo texto en todo el proyecto
        document.addEventListener('input', function(e) {
            if (e.target.tagName.toLowerCase() === 'input' && e.target.type === 'text') {
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;
                e.target.value = e.target.value.toUpperCase();
                e.target.setSelectionRange(start, end);
            }
        });
    </script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header-card">
                <div class="logo-box">
                    <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="sidebar-logo-img">
                </div>
                <h3 class="sidebar-title">CEREMA</h3>
                <p class="sidebar-subtitle">Centro de Residentes Mairaneños</p>
                <button class="toggle-btn" id="toggle-btn">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-heading">MENÚ PRINCIPAL</div>
                <?php 
                $current_page = basename($_SERVER['PHP_SELF']); 
                $is_socios = ($current_page == 'socio.controller.php');
                $is_mensualidad = ($current_page == 'mensualidad.controller.php');
                $is_matriz_pagos = ($current_page == 'matriz_pagos.controller.php');
                $is_aporte = ($current_page == 'aporte.controller.php');
                $is_matriz_aportes = ($current_page == 'matriz_aportes.controller.php');
                $is_aporte_especial = ($current_page == 'aporte_especial.controller.php');
                $is_cuota_inicial = ($current_page == 'cuota_inicial.controller.php');
                $is_directiva = ($current_page == 'directiva.controller.php');
                $is_usuario = ($current_page == 'usuario.controller.php');
                $is_evento = ($current_page == 'evento.controller.php');
                $is_categorias_ingreso = ($current_page == 'categorias_ingreso.controller.php');
                $is_otro_ingreso = ($current_page == 'otro_ingreso.controller.php');
                $is_sueldo = ($current_page == 'sueldo.controller.php');
                $is_servicio = ($current_page == 'servicio.controller.php');
                $is_otro_gasto = ($current_page == 'otro_gasto.controller.php');
                $is_egreso_ext = ($current_page == 'egreso_extraordinario.controller.php');
                $is_egreso_esp = ($current_page == 'egreso_especial.controller.php');
                $is_activo = ($current_page == 'activo.controller.php');
                $is_reporte = ($current_page == 'reporte.controller.php');
                $current_report_type = $_GET['type'] ?? 'socios_activos';
                $is_reporte_asociados = $is_reporte && in_array($current_report_type, ['socios_activos', 'socios_inactivos', 'socios_pasivos', 'socios']);
                $is_reporte_ingresos = $is_reporte && in_array($current_report_type, ['pagos_mensualidades', 'pagos_extraordinario', 'pagos_especiales', 'otros_ingresos']);
                $is_reporte_egresos = $is_reporte && in_array($current_report_type, ['egresos_sueldo', 'egresos_servicios', 'egresos_extraordinario', 'egresos_especiales', 'egresos_otros_gastos']);
                ?>
                <ul class="nav-list">
                    <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <a href="../View/dashboard.php" class="nav-link">
                            <i class="fa-solid fa-house"></i>
                            <span class="nav-text">Inicio</span>
                        </a>
                    </li>
                    <?php if (check_permission('socios')): ?>
                    <li class="nav-item has-submenu <?php echo ($is_socios || $is_mensualidad || $is_matriz_pagos || $is_aporte || $is_matriz_aportes || $is_aporte_especial || $is_cuota_inicial) ? 'active open' : ''; ?>">
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-users"></i>
                            <span class="nav-text">Asociados</span>
                            <i class="fa-solid fa-chevron-down submenu-icon" style="margin-left: auto; font-size: 12px; color: #00B300;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li><a href="../Controller/socio.controller.php" class="submenu-link <?php echo $is_socios ? 'active' : ''; ?>">Listas</a></li>
                            <li><a href="../Controller/mensualidad.controller.php" class="submenu-link <?php echo $is_mensualidad ? 'active' : ''; ?>">Aporte Mensual</a></li>
                            <li><a href="../Controller/matriz_pagos.controller.php" class="submenu-link <?php echo $is_matriz_pagos ? 'active' : ''; ?>">Matriz de Pagos Anual</a></li>
                            <li><a href="../Controller/aporte.controller.php" class="submenu-link <?php echo $is_aporte ? 'active' : ''; ?>">Aporte Extraordinario</a></li>
                            <li><a href="../Controller/matriz_aportes.controller.php" class="submenu-link <?php echo $is_matriz_aportes ? 'active' : ''; ?>">Matriz de Aportes Extraordinarios</a></li>
                            <li><a href="../Controller/aporte_especial.controller.php" class="submenu-link <?php echo $is_aporte_especial ? 'active' : ''; ?>">Aportes Especiales</a></li>
                            <li><a href="../Controller/cuota_inicial.controller.php" class="submenu-link <?php echo $is_cuota_inicial ? 'active' : ''; ?>">Cuota Inicial</a></li>
                        </ul>
                    </li>
                    <?php elseif (is_socio()): ?>
                    <li class="nav-item <?php echo ($is_mensualidad) ? 'active' : ''; ?>">
                        <a href="../Controller/mensualidad.controller.php" class="nav-link">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span class="nav-text">Mis Mensualidades</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($is_aporte) ? 'active' : ''; ?>">
                        <a href="../Controller/aporte.controller.php" class="nav-link">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span class="nav-text">Mis Extraordinarios</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($is_aporte_especial) ? 'active' : ''; ?>">
                        <a href="../Controller/aporte_especial.controller.php" class="nav-link">
                            <i class="fa-solid fa-star-of-life"></i>
                            <span class="nav-text">Mis Especiales</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('directiva')): ?>
                    <li class="nav-item <?php echo ($current_page == 'directiva.controller.php') ? 'active' : ''; ?>">
                        <a href="../Controller/directiva.controller.php" class="nav-link">
                            <i class="fa-solid fa-user-tie"></i>
                            <span class="nav-text">Directiva</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('usuarios')): ?>
                    <li class="nav-item <?php echo ($is_usuario) ? 'active' : ''; ?>">
                        <a href="../Controller/usuario.controller.php" class="nav-link">
                            <i class="fa-solid fa-user-gear"></i>
                            <span class="nav-text">Usuarios</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('eventos')): ?>
                    <li class="nav-item <?php echo ($is_evento) ? 'active' : ''; ?>">
                        <a href="../Controller/evento.controller.php" class="nav-link">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span class="nav-text">Eventos</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('activos')): ?>
                    <li class="nav-item <?php echo ($is_activo) ? 'active' : ''; ?>">
                        <a href="../Controller/activo.controller.php" class="nav-link">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span class="nav-text">Activos</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('categorias_ingreso')): ?>
                    <li class="nav-item has-submenu <?php echo ($is_categorias_ingreso || $is_otro_ingreso) ? 'active open' : ''; ?>">
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span class="nav-text">Ingresos</span>
                            <i class="fa-solid fa-chevron-down submenu-icon" style="margin-left: auto; font-size: 12px; color: #00B300;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li><a href="../Controller/categorias_ingreso.controller.php" class="submenu-link <?php echo $is_categorias_ingreso ? 'active' : ''; ?>">Configurar Montos</a></li>
                            <li><a href="../Controller/otro_ingreso.controller.php" class="submenu-link <?php echo $is_otro_ingreso ? 'active' : ''; ?>">Otros Ingresos</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (check_permission('sueldo')): ?>
                    <li class="nav-item has-submenu <?php echo ($is_servicio || $is_sueldo || $is_otro_gasto || $is_egreso_ext || $is_egreso_esp) ? 'active open' : ''; ?>">
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span class="nav-text">Egreso</span>
                            <i class="fa-solid fa-chevron-down submenu-icon" style="margin-left: auto; font-size: 12px; color: #00B300;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li><a href="../Controller/sueldo.controller.php" class="submenu-link <?php echo $is_sueldo ? 'active' : ''; ?>">Sueldo</a></li>
                            <li><a href="../Controller/servicio.controller.php" class="submenu-link <?php echo $is_servicio ? 'active' : ''; ?>">Servicios Básicos</a></li>
                            <li><a href="../Controller/egreso_extraordinario.controller.php" class="submenu-link <?php echo $is_egreso_ext ? 'active' : ''; ?>">Extraordinarios</a></li>
                            <li><a href="../Controller/egreso_especial.controller.php" class="submenu-link <?php echo $is_egreso_esp ? 'active' : ''; ?>">Especiales</a></li>
                            <li><a href="../Controller/otro_gasto.controller.php" class="submenu-link <?php echo $is_otro_gasto ? 'active' : ''; ?>">Otros Gastos</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if (check_permission('reportes')): ?>
                    <li class="nav-item has-submenu <?php echo ($is_reporte) ? 'active open' : ''; ?>">
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="nav-text">Reportes</span>
                            <i class="fa-solid fa-chevron-down submenu-icon" style="margin-left: auto; font-size: 12px; color: #00B300;"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="has-nested-submenu">
                                <a href="#" class="submenu-link <?php echo $is_reporte_asociados ? 'active' : ''; ?>" onclick="event.preventDefault(); event.stopPropagation(); const sub = this.nextElementSibling; const ic = this.querySelector('.sub-chevron'); const isH = sub.style.display === 'none' || sub.style.display === ''; sub.style.display = isH ? 'block' : 'none'; if(ic) ic.style.transform = isH ? 'rotate(180deg)' : 'rotate(0deg)'; return false;" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Asociados</span>
                                    <i class="fa-solid fa-chevron-down sub-chevron" style="font-size: 10px; margin-left: 6px; transition: transform 0.2s; <?php echo $is_reporte_asociados ? 'transform: rotate(180deg);' : ''; ?>"></i>
                                </a>
                                <ul class="nav-nested-submenu" style="list-style: none; padding-left: 12px; margin-top: 4px; display: <?php echo $is_reporte_asociados ? 'block' : 'none'; ?>;">
                                    <li><a href="../Controller/reporte.controller.php?type=socios_activos" class="submenu-link <?php echo ($is_reporte && ($current_report_type == 'socios_activos' || $current_report_type == 'socios')) ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Asociados Activos</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=socios_inactivos" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'socios_inactivos') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Asociados Inactivos</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=socios_pasivos" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'socios_pasivos') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Asociados Pasivos</a></li>
                                </ul>
                            </li>
                            <li class="has-nested-submenu" style="margin-top: 4px;">
                                <a href="#" class="submenu-link <?php echo $is_reporte_ingresos ? 'active' : ''; ?>" onclick="event.preventDefault(); event.stopPropagation(); const sub = this.nextElementSibling; const ic = this.querySelector('.sub-chevron'); const isH = sub.style.display === 'none' || sub.style.display === ''; sub.style.display = isH ? 'block' : 'none'; if(ic) ic.style.transform = isH ? 'rotate(180deg)' : 'rotate(0deg)'; return false;" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Ingresos</span>
                                    <i class="fa-solid fa-chevron-down sub-chevron" style="font-size: 10px; margin-left: 6px; transition: transform 0.2s; <?php echo $is_reporte_ingresos ? 'transform: rotate(180deg);' : ''; ?>"></i>
                                </a>
                                <ul class="nav-nested-submenu" style="list-style: none; padding-left: 12px; margin-top: 4px; display: <?php echo $is_reporte_ingresos ? 'block' : 'none'; ?>;">
                                    <li><a href="../Controller/reporte.controller.php?type=pagos_mensualidades" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'pagos_mensualidades') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Mensualidades</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=pagos_extraordinario" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'pagos_extraordinario') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Extraordinarios</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=pagos_especiales" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'pagos_especiales') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Especiales</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=otros_ingresos" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'otros_ingresos') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Otros Ingresos</a></li>
                                </ul>
                            </li>
                            <li class="has-nested-submenu" style="margin-top: 4px;">
                                <a href="#" class="submenu-link <?php echo $is_reporte_egresos ? 'active' : ''; ?>" onclick="event.preventDefault(); event.stopPropagation(); const sub = this.nextElementSibling; const ic = this.querySelector('.sub-chevron'); const isH = sub.style.display === 'none' || sub.style.display === ''; sub.style.display = isH ? 'block' : 'none'; if(ic) ic.style.transform = isH ? 'rotate(180deg)' : 'rotate(0deg)'; return false;" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Egresos</span>
                                    <i class="fa-solid fa-chevron-down sub-chevron" style="font-size: 10px; margin-left: 6px; transition: transform 0.2s; <?php echo $is_reporte_egresos ? 'transform: rotate(180deg);' : ''; ?>"></i>
                                </a>
                                <ul class="nav-nested-submenu" style="list-style: none; padding-left: 12px; margin-top: 4px; display: <?php echo $is_reporte_egresos ? 'block' : 'none'; ?>;">
                                    <li><a href="../Controller/reporte.controller.php?type=egresos_sueldo" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'egresos_sueldo') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Sueldo</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=egresos_servicios" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'egresos_servicios') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Servicios Básicos</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=egresos_extraordinario" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'egresos_extraordinario') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Extraordinarios</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=egresos_especiales" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'egresos_especiales') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Especiales</a></li>
                                    <li><a href="../Controller/reporte.controller.php?type=egresos_otros_gastos" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'egresos_otros_gastos') ? 'active' : ''; ?>" style="font-size: 12.5px; padding: 6px 10px;">Otros Gastos</a></li>
                                </ul>
                            </li>
                            <li style="margin-top: 4px;">
                                <a href="../Controller/reporte.controller.php?type=activos" class="submenu-link <?php echo ($is_reporte && $current_report_type == 'activos') ? 'active' : ''; ?>">Activos y Patrimonio</a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="../Controller/login.controller.php?action=logout" class="nav-link logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="nav-text">Cerrar Sesión</span>
                </a>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const sidebarNav = document.querySelector('.sidebar-nav');

                    // 1. Restaurar posición de scroll exacta de la barra de navegación lateral
                    const savedScroll = sessionStorage.getItem('sidebarNavScrollTop');
                    if (savedScroll !== null && sidebarNav) {
                        sidebarNav.scrollTop = parseInt(savedScroll, 10);
                        requestAnimationFrame(function() {
                            sidebarNav.scrollTop = parseInt(savedScroll, 10);
                        });
                    }

                    // Guardar posición de scroll al desplazarse o hacer clic en cualquier opción
                    if (sidebarNav) {
                        sidebarNav.addEventListener('scroll', function() {
                            sessionStorage.setItem('sidebarNavScrollTop', sidebarNav.scrollTop);
                        });
                        sidebarNav.querySelectorAll('a').forEach(link => {
                            link.addEventListener('click', function() {
                                sessionStorage.setItem('sidebarNavScrollTop', sidebarNav.scrollTop);
                            });
                        });
                    }

                    // 2. Manejo de submenús principales
                    const submenuToggles = document.querySelectorAll('.has-submenu > .nav-link');
                    submenuToggles.forEach(toggle => {
                        toggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            const parentLi = this.parentElement;
                            const isOpen = parentLi.classList.contains('open');
                            
                            if (isOpen) {
                                parentLi.classList.remove('open');
                            } else {
                                document.querySelectorAll('.has-submenu').forEach(li => {
                                    li.classList.remove('open');
                                });
                                parentLi.classList.add('open');
                            }

                            if (sidebarNav) {
                                sessionStorage.setItem('sidebarNavScrollTop', sidebarNav.scrollTop);
                            }
                        });
                    });
                });
            </script>
        </aside>

        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleMobileSidebarDirect(event)"></div>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <button class="mobile-toggle-btn" id="mobile-toggle-btn" onclick="toggleMobileSidebarDirect(event)" aria-label="Abrir Menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div style="flex: 1;"></div>
                
                <div class="topbar-actions">
                    <div class="user-profile">
                        <?php 
                            $currUser = $_SESSION['username'] ?? $_SESSION['nombre_usuario'] ?? '';
                            $userAvatar = (strcasecmp($currUser, 'Infoser76') === 0 || strcasecmp($_SESSION['username'] ?? '', 'Infoser76') === 0) ? '../img/circular.png' : '../img/avatar.png';
                        ?>
                        <img src="<?php echo $userAvatar; ?>" alt="Perfil">
                        <div class="user-info">
                            <span class="user-name"><?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Usuario'; ?></span>
                            <span class="user-role"><?php echo htmlspecialchars(get_user_role()); ?></span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content starts here -->
            <div class="content-wrapper">
