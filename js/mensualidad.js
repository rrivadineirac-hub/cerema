// js/mensualidad.js

function openMensualidadModal() {
    document.getElementById('mensualidadForm').reset();
    document.getElementById('id_mensualidad').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Pago de Mensualidad';
    
    // Configurar visibilidad de secciones (Modo Lote / Nuevo)
    const checklistSection = document.getElementById('checklistSection');
    const singleMonthRow = document.getElementById('singleMonthRow');
    if (checklistSection) checklistSection.style.display = 'block';
    if (singleMonthRow) singleMonthRow.style.display = 'none';

    // Restaurar valor por defecto y botón "Pagar" para nuevos registros
    const estadoSelect = document.getElementById('estado');
    if (estadoSelect) {
        estadoSelect.value = 'Pagado';
    }
    
    const btnGuardar = document.getElementById('btnGuardarMensualidad');
    if (btnGuardar) {
        btnGuardar.innerText = 'Pagar';
        btnGuardar.disabled = false;
    }

    // Fechas y valores por defecto
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('anio').value = new Date().getFullYear();
    
    const defaultMonto = (typeof MONTO_MENSUALIDAD_SUGERIDO !== 'undefined') ? MONTO_MENSUALIDAD_SUGERIDO : '50.00';
    document.getElementById('monto_unitario').value = defaultMonto;

    document.getElementById('mensualidadModal').style.display = 'block';
    
    // Cargar meses pagados mediante AJAX para el socio/acción/año actual
    loadPaidMonths();
}

function closeMensualidadModal() {
    document.getElementById('mensualidadModal').style.display = 'none';
}

function loadPaidMonths() {
    const isEditing = document.getElementById('id_mensualidad').value !== '';
    if (isEditing) return; // En modo edición no aplica la cuadrícula de lote

    const id_socio = document.getElementById('id_socio').value;
    const numero_accion = document.getElementById('numero_accion') ? document.getElementById('numero_accion').value : 1;
    const anio = document.getElementById('anio').value;

    const anioNum = parseInt(anio, 10);
    const maxAnioVal = new Date().getFullYear() + 3;
    if (!id_socio || !anio || isNaN(anioNum) || anioNum < 2018 || anioNum > maxAnioVal) return;

    fetch(`../Controller/mensualidad.controller.php?action=get_paid_months&id_socio=${id_socio}&numero_accion=${numero_accion}&anio=${anio}`)
    .then(r => r.json())
    .then(data => {
        const paidMonths = (data.status === 'success' && Array.isArray(data.paid_months)) ? data.paid_months : [];
        const checkboxes = document.querySelectorAll('.month-checkbox');

        checkboxes.forEach(cb => {
            const val = cb.value;
            const card = document.getElementById('card_' + val);
            const tag = document.getElementById('tag_' + val);
            const isPaid = paidMonths.includes(val);

            if (isPaid) {
                cb.checked = true;
                cb.disabled = true;
                if (card) {
                    card.classList.add('paid');
                    card.classList.remove('selected');
                }
                if (tag) tag.innerText = '(Pagado)';
            } else {
                cb.checked = false;
                cb.disabled = false;
                if (card) {
                    card.classList.remove('paid');
                    card.classList.remove('selected');
                }
                if (tag) tag.innerText = '';
            }
        });

        updateCalculatedTotal();
    })
    .catch(error => {
        console.error('Error al cargar meses pagados:', error);
    });
}

function updateCalculatedTotal() {
    const isEditing = document.getElementById('id_mensualidad').value !== '';
    const unitVal = parseFloat(document.getElementById('monto_unitario').value) || 0;
    const montoInput = document.getElementById('monto');

    if (isEditing) {
        montoInput.value = unitVal.toFixed(2);
        return;
    }

    const checkboxes = document.querySelectorAll('.month-checkbox');
    let selectedUnpaidCount = 0;

    checkboxes.forEach(cb => {
        const val = cb.value;
        const card = document.getElementById('card_' + val);

        if (!cb.disabled) {
            if (cb.checked) {
                selectedUnpaidCount++;
                if (card) card.classList.add('selected');
            } else {
                if (card) card.classList.remove('selected');
            }
        }
    });

    const total = selectedUnpaidCount * unitVal;
    montoInput.value = total.toFixed(2);
}

function selectUnpaidMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = true;
        }
    });
    updateCalculatedTotal();
}

function uncheckAllMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = false;
        }
    });
    updateCalculatedTotal();
}

function saveMensualidad() {
    const btnGuardar = document.getElementById('btnGuardarMensualidad');
    if (btnGuardar && btnGuardar.disabled) {
        return;
    }
    
    const form = document.getElementById('mensualidadForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const isEditing = document.getElementById('id_mensualidad').value !== '';

    if (!isEditing) {
        const selectedUnpaid = Array.from(document.querySelectorAll('.month-checkbox')).filter(cb => cb.checked && !cb.disabled);
        if (selectedUnpaid.length === 0) {
            alert('Por favor seleccione al menos un mes a pagar.');
            return;
        }
    }
    
    const formData = new FormData(form);
    
    if (!isEditing) {
        let estadoActual = document.getElementById('estado').value;
        if (estadoActual === 'Pendiente') {
            estadoActual = 'Pagado';
        }
        formData.append('estado', estadoActual);
    }

    fetch('../Controller/mensualidad.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeMensualidadModal();
            showToast('Mensualidad registrada exitosamente');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar la mensualidad.');
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerText = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 400);
    }, 3000);
}

function editMensualidad(id) {
    fetch(`../Controller/mensualidad.controller.php?action=get&id_mensualidad=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_mensualidad').value = data.id_mensualidad;
        if(document.getElementById('numero_accion')) {
            document.getElementById('numero_accion').value = data.numero_accion || 1;
        }
        if(document.getElementById('numero_recibo')) {
            document.getElementById('numero_recibo').value = data.numero_recibo || '';
        }

        // En modo edición individual, ocultar checklist y mostrar select de mes individual
        const checklistSection = document.getElementById('checklistSection');
        const singleMonthRow = document.getElementById('singleMonthRow');
        if (checklistSection) checklistSection.style.display = 'none';
        if (singleMonthRow) singleMonthRow.style.display = 'block';

        if(document.getElementById('mes')) {
            document.getElementById('mes').value = data.mes;
        }
        document.getElementById('anio').value = data.anio;
        document.getElementById('monto_unitario').value = data.monto;
        document.getElementById('monto').value = data.monto;
        
        if(data.fecha_pago) {
            const dateStr = data.fecha_pago.split(' ')[0];
            document.getElementById('fecha_pago').value = dateStr;
        }

        if (document.getElementById('estado')) {
            document.getElementById('estado').value = data.estado || 'Pagado';
        }
        document.getElementById('btnGuardarMensualidad').innerText = 'Actualizar';
        document.getElementById('btnGuardarMensualidad').disabled = false;
        
        let warningSpan = document.getElementById('statusWarning');
        if(warningSpan) {
            warningSpan.style.display = 'none';
        }
        
        document.getElementById('modalTitle').innerText = 'Editar Pago de Mensualidad';
        document.getElementById('mensualidadModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos de la mensualidad.');
    });
}

let currentDeleteId = null;

function deleteMensualidad(id, descripcion) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = descripcion;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    currentDeleteId = null;
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if(btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function() {
            if(!currentDeleteId) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_mensualidad', currentDeleteId);

            fetch('../Controller/mensualidad.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    closeDeleteModal();
                    showToast('Mensualidad eliminada exitosamente');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error al eliminar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al eliminar el registro.');
            });
        });
    }

    const anioInput = document.getElementById('anio');
    const accionSelect = document.getElementById('numero_accion');
    if (anioInput) {
        anioInput.addEventListener('change', loadPaidMonths);
        anioInput.addEventListener('keyup', loadPaidMonths);
    }
    if (accionSelect) {
        accionSelect.addEventListener('change', loadPaidMonths);
    }
});

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('mensualidadModal');
    if (event.target == modal) {
        closeMensualidadModal();
    }
}

