<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$pageTitle = "Gestión de Eventos";
ob_start();
?>
<link rel="stylesheet" href="../css/evento.css">

<div class="content-header">
    <h2>Eventos Institucionales CEREMA</h2>
    <?php if (!is_socio()): ?>
    <button class="btn-primary" onclick="openEventoModal()">+ Registrar Evento</button>
    <?php endif; ?>
</div>

<div class="events-cards-grid">
    <?php 
    $eventos_array = [];
    if (isset($eventos)) {
        if (is_array($eventos)) {
            $eventos_array = $eventos;
        } elseif ($eventos instanceof Traversable) {
            $eventos_array = iterator_to_array($eventos);
        }
    }
    ?>
    <?php if(!empty($eventos_array)): ?>
        <?php foreach($eventos_array as $evento): 
            $fecha = date('d/m/Y', strtotime($evento['fecha_evento']));
            $hora = !empty($evento['hora_evento']) ? date('H:i', strtotime($evento['hora_evento'])) : '--:--';
            $lugar = !empty($evento['lugar']) ? mb_strtoupper($evento['lugar'], 'UTF-8') : 'NO ESPECIFICADO';
            
            $nombreEvento = !empty($evento['titulo']) ? $evento['titulo'] : $evento['tipo_evento'];
            
            $estadoClass = '';
            switch($evento['estado']) {
                case 'Programado': $estadoClass = 'estado-programado'; break;
                case 'Realizado': $estadoClass = 'estado-realizado'; break;
                case 'Cancelado': $estadoClass = 'estado-cancelado'; break;
            }
        ?>
        <div class="event-card">
            <div class="event-card-header">
                <div class="event-card-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="event-card-details">
                    <span class="event-card-subtitle"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span>
                    <h3 class="event-card-title"><?php echo htmlspecialchars($nombreEvento); ?></h3>
                    <p class="event-card-org">Organizados por CEREMA</p>
                </div>
            </div>
            
            <?php if (!empty($evento['descripcion'])): ?>
            <div class="event-card-desc">
                <?php echo htmlspecialchars($evento['descripcion']); ?>
            </div>
            <?php endif; ?>
            
            <div class="event-card-info-row">
                <div class="event-info-item">
                    <i class="fa-regular fa-clock"></i>
                    <span><?php echo $fecha . ' ' . $hora; ?></span>
                </div>
                <div class="event-info-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span><?php echo htmlspecialchars($lugar); ?></span>
                </div>
            </div>

            <div class="event-card-footer">
                <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($evento['estado']); ?></span>
                <?php if (!is_socio()): ?>
                <div class="actions">
                    <button class="btn-icon edit-btn" onclick="editEvento(<?php echo $evento['id_evento']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-icon delete-btn" onclick="deleteEvento(<?php echo $evento['id_evento']; ?>, '<?php echo addslashes($nombreEvento); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-events-card">
            <div class="event-card-icon" style="margin: 0 auto 15px auto;">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h3>No hay eventos registrados</h3>
            <p>No se encontraron eventos programados en esta gestión o no hay una gestión activa.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Crear/Editar Evento -->
<div id="eventoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Evento</h3>
            <span class="close" onclick="closeEventoModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="eventoForm" onsubmit="event.preventDefault(); saveEvento();">
                <input type="hidden" id="id_evento" name="id_evento">
                

                <div class="form-row">
                    <div class="form-group half">
                        <label for="tipo_evento">Tipo de Evento</label>
                        <select id="tipo_evento" name="tipo_evento" required onchange="toggleOtroEvento()">
                            <option value="" disabled selected>Elegir Evento</option>
                            <option value="Fiesta de Aniversario">Fiesta de Aniversario</option>
                            <option value="Reunion Mensual">Reunión Mensual</option>
                            <option value="Reunion Extraordinaria">Reunión Extraordinaria</option>
                            <option value="Otros Eventos">Otros Eventos</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" disabled required>
                            <option value="Programado" selected>Programado</option>
                            <option value="Realizado">Realizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="otro_evento_container" style="display:none; margin-top:-10px; margin-bottom:15px;">
                    <label for="otro_tipo_evento">Especificar Nuevo Evento</label>
                    <input type="text" id="otro_tipo_evento" name="otro_tipo_evento" placeholder="Ej: KERMESSE" onkeyup="this.value = this.value.toUpperCase();">
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="fecha_evento">Fecha</label>
                        <input type="date" id="fecha_evento" name="fecha_evento" min="2018-01-01" required>
                    </div>
                    <div class="form-group half">
                        <label for="hora_evento">Hora (Opcional)</label>
                        <input type="time" id="hora_evento" name="hora_evento">
                    </div>
                </div>

                <div class="form-group">
                    <label for="lugar">Lugar (Opcional)</label>
                    <input type="text" id="lugar" name="lugar" placeholder="Ej: SALÓN DE EVENTOS" onkeyup="this.value = this.value.toUpperCase();">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional)</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Detalles adicionales del evento..." onkeyup="this.value = this.value.toUpperCase();"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeEventoModal()">Cancelar</button>
            <button class="btn-primary" onclick="saveEvento()">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Confirmación Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar el evento <strong id="deleteDesc"></strong>?</p>
            <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
            <button class="btn-danger" id="btnConfirmDelete">Eliminar</button>
        </div>
    </div>
</div>

<script src="../js/evento.js"></script>

<?php
$content = ob_get_clean();
require 'header.php';
echo $content;
require 'footer.php';
?>
