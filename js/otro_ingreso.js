// js/otro_ingreso.js

function openOtroIngresoModal() {
    document.getElementById('otroIngresoForm').reset();
    document.getElementById('id_otro_ingreso').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Nuevo Ingreso';
    
    // Fecha actual por defecto
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('monto').value = '0.00';
    const groupEstado = document.getElementById('group_estado');
    if (groupEstado) groupEstado.style.display = 'none';

    document.getElementById('otroIngresoModal').style.display = 'block';
}

function closeOtroIngresoModal() {
    document.getElementById('otroIngresoModal').style.display = 'none';
}

function saveOtroIngreso() {
    const form = document.getElementById('otroIngresoForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const monto = document.getElementById('monto').value;
    if (parseFloat(monto) <= 0) {
        alert("El monto del ingreso debe ser mayor a 0.");
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'save');

    fetch('../Controller/otro_ingreso.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeOtroIngresoModal();
            if (typeof showToast === 'function') {
                showToast(data.message);
            } else {
                alert(data.message);
            }
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el ingreso.');
    });
}

function editOtroIngreso(id) {
    fetch(`../Controller/otro_ingreso.controller.php?action=get&id_otro_ingreso=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_otro_ingreso').value = data.id_otro_ingreso;
        document.getElementById('detalle').value = data.detalle;
        document.getElementById('monto').value = data.monto;
        document.getElementById('fecha_pago').value = data.fecha_pago;
        document.getElementById('comprobante').value = data.comprobante;
        document.getElementById('estado').value = data.estado || 'Cobrado';
        
        const groupEstado = document.getElementById('group_estado');
        if (groupEstado) groupEstado.style.display = 'block';
        
        document.getElementById('modalTitle').innerText = 'Editar Ingreso';
        document.getElementById('btn-save-otro-ingreso').innerText = 'Actualizar Ingreso';
        document.getElementById('otroIngresoModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos.');
    });
}

let currentDeleteId = null;

function deleteOtroIngreso(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDeleteOtroIngreso() {
    if (!currentDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_otro_ingreso', currentDeleteId);

    fetch('../Controller/otro_ingreso.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('deleteModal').style.display = 'none';
            if (typeof showToast === 'function') {
                showToast(data.message);
            } else {
                alert(data.message);
            }
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al eliminar el registro.');
    });
}

function filterIngresosTable() {
    const input = document.getElementById('searchIngreso').value.toUpperCase();
    const table = document.getElementById('tableOtrosIngresos');
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
