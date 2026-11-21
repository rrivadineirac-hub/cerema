// js/ingreso_venta_accion.js

function openVentaAccionModal() {
    const form = document.getElementById('ventaAccionForm');
    if (form) form.reset();

    document.getElementById('id_venta_accion').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Venta de Acción';
    document.getElementById('btnGuardarVentaAccion').innerText = 'Guardar Venta';

    // Fecha actual por defecto
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_venta').value = today;

    // Valores por defecto
    document.getElementById('cantidad_acciones').value = 1;
    document.getElementById('precio_unitario').value = '0.00';
    document.getElementById('monto_total').value = '0.00';
    document.getElementById('metodo_pago').value = 'Efectivo';
    document.getElementById('estado').value = 'Cobrado';

    const groupEstado = document.getElementById('group_estado');
    if (groupEstado) groupEstado.style.display = 'none';

    document.getElementById('ventaAccionModal').style.display = 'block';

    const modalBody = document.querySelector('#ventaAccionModal .modal-body');
    if (modalBody) modalBody.scrollTop = 0;
}

function closeVentaAccionModal() {
    document.getElementById('ventaAccionModal').style.display = 'none';
}

function calculateVentaTotal() {
    const cantidad = parseFloat(document.getElementById('cantidad_acciones').value) || 0;
    const precio = parseFloat(document.getElementById('precio_unitario').value) || 0;
    const total = cantidad * precio;
    
    // Si el usuario modifica cantidad o precio, recalculamos monto total
    document.getElementById('monto_total').value = total.toFixed(2);
}

function onSocioSelectChange() {
    const select = document.getElementById('id_socio');
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value) {
        const nombreCompleto = selectedOption.getAttribute('data-nombre') || '';
        const compradorInput = document.getElementById('comprador');
        if (compradorInput && (!compradorInput.value || compradorInput.value.trim() === '')) {
            compradorInput.value = nombreCompleto;
        }
    }
}

function saveVentaAccion() {
    const form = document.getElementById('ventaAccionForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const comprador = document.getElementById('comprador').value.trim();
    if (!comprador) {
        alert("Por favor ingrese el nombre del comprador o seleccione un asociado.");
        return;
    }

    const cantidad = parseInt(document.getElementById('cantidad_acciones').value, 10);
    if (isNaN(cantidad) || cantidad <= 0) {
        alert("La cantidad de acciones debe ser al menos 1.");
        return;
    }

    const montoTotal = parseFloat(document.getElementById('monto_total').value);
    if (isNaN(montoTotal) || montoTotal <= 0) {
        alert("El monto total de la venta debe ser mayor a 0.");
        return;
    }

    const btn = document.getElementById('btnGuardarVentaAccion');
    btn.disabled = true;
    btn.innerText = 'Guardando...';

    const formData = new FormData(form);
    formData.append('action', 'save');

    fetch('../Controller/ingreso_venta_accion.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = 'Guardar Venta';
        if (data.status === 'success') {
            closeVentaAccionModal();
            if (typeof showToast === 'function') {
                showToast(data.message);
            } else {
                alert(data.message);
            }
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = 'Guardar Venta';
        console.error('Error:', err);
        alert('Ocurrió un error al procesar la venta de acción.');
    });
}

function editVentaAccion(id) {
    fetch(`../Controller/ingreso_venta_accion.controller.php?action=get&id_venta_accion=${id}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }

        document.getElementById('id_venta_accion').value = data.id_venta_accion;
        document.getElementById('id_socio').value = data.id_socio || '';
        document.getElementById('comprador').value = data.comprador || '';
        document.getElementById('cantidad_acciones').value = data.cantidad_acciones || 1;
        document.getElementById('numero_accion').value = data.numero_accion || '';
        document.getElementById('precio_unitario').value = data.precio_unitario || '0.00';
        document.getElementById('monto_total').value = data.monto_total || '0.00';
        document.getElementById('fecha_venta').value = data.fecha_venta || '';
        document.getElementById('numero_recibo').value = data.numero_recibo || '';
        document.getElementById('metodo_pago').value = data.metodo_pago || 'Efectivo';
        document.getElementById('observaciones').value = data.observaciones || '';
        document.getElementById('estado').value = data.estado || 'Cobrado';

        const groupEstado = document.getElementById('group_estado');
        if (groupEstado) groupEstado.style.display = 'block';

        document.getElementById('modalTitle').innerText = 'Editar Venta de Acción';
        document.getElementById('btnGuardarVentaAccion').innerText = 'Actualizar Venta';
        document.getElementById('ventaAccionModal').style.display = 'block';

        const modalBody = document.querySelector('#ventaAccionModal .modal-body');
        if (modalBody) modalBody.scrollTop = 0;
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Ocurrió un error al cargar los datos.');
    });
}

let currentDeleteId = null;

function deleteVentaAccion(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDeleteVentaAccion() {
    if (!currentDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_venta_accion', currentDeleteId);

    fetch('../Controller/ingreso_venta_accion.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('deleteModal').style.display = 'none';
        if (data.status === 'success') {
            if (typeof showToast === 'function') {
                showToast(data.message);
            } else {
                alert(data.message);
            }
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Ocurrió un error al eliminar el registro.');
    });
}

function filterVentaAccionesTable() {
    const input = document.getElementById('searchVentaAccion').value.toUpperCase();
    const table = document.getElementById('tableVentasAcciones');
    if (!table) return;
    const trs = table.getElementsByTagName('tr');

    for (let i = 1; i < trs.length; i++) {
        const tr = trs[i];
        if (tr.getElementsByTagName('td').length === 0) continue;
        const text = tr.innerText.toUpperCase();
        if (text.indexOf(input) > -1) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    }
}

// Cerrar modal al hacer click en el backdrop
window.addEventListener('click', function(event) {
    const modal = document.getElementById('ventaAccionModal');
    const delModal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeVentaAccionModal();
    }
    if (event.target === delModal) {
        delModal.style.display = 'none';
    }
});
