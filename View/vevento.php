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
    <h2>Eventos de la Gestión Actual</h2>
    <button class="btn-primary" onclick="openEventoModal()">+ Registrar Evento</button>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Tipo de Evento</th>
                <th>Fecha y Hora</th>
                <th>Lugar</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($eventos) && (is_array($eventos) || $eventos instanceof Traversable)): ?>
                <?php foreach($eventos as $evento): ?>
                    <tr>
                        <td><span class="badge-status badge-tipo"><?php echo htmlspecialchars($evento['tipo_evento']); ?></span></td>
                        <td>
                            <?php 
                                $fecha = date('d/m/Y', strtotime($evento['fecha_evento']));
                                $hora = $evento['hora_evento'] ? date('H:i', strtotime($evento['hora_evento'])) : '--:--';
                                echo $fecha . ' ' . $hora;
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars(mb_strtoupper($evento['lugar'] ?: 'NO ESPECIFICADO', 'UTF-8')); ?></td>
                        <td>
                            <?php 
                                $estadoClass = '';
                                switch($evento['estado']) {
                                    case 'Programado': $estadoClass = 'estado-programado'; break;
                                    case 'Realizado': $estadoClass = 'estado-realizado'; break;
                                    case 'Cancelado': $estadoClass = 'estado-cancelado'; break;
                                }
                            ?>
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($evento['estado']); ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn-icon edit-btn" onclick="editEvento(<?php echo $evento['id_evento']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteEvento(<?php echo $evento['id_evento']; ?>, '<?php echo addslashes($evento['tipo_evento']); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No hay eventos registrados en esta gestión o no hay gestión activa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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
