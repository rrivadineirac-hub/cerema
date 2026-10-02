// js/otro_gasto.js

function openOtroGastoModal() {
    document.getElementById('otroGastoForm').reset();
    document.getElementById('id_otro_gasto').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Gasto';
    
    // Set default date and blank unit of measure
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    if (document.getElementById('nombre_gasto')) document.getElementById('nombre_gasto').value = '';
    if (document.getElementById('detalle')) document.getElementById('detalle').value = '';
    document.getElementById('unidad_medida').value = '';
    document.getElementById('cantidad').value = '1.00';
    document.getElementById('precio').value = '0.00';
    document.getElementById('monto').value = '0.00';
    document.getElementById('btn-save-otro-gasto').innerText = 'Guardar';

    document.getElementById('otroGastoModal').style.display = 'block';
}

function calcularTotalOtroGasto() {
    const qtyInput = document.getElementById('cantidad');
    const priceInput = document.getElementById('precio');
    const totalInput = document.getElementById('monto');
    
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    
    if (qty > 0 && price > 0) {
        totalInput.value = (qty * price).toFixed(2);
    }
}

function closeOtroGastoModal() {
    document.getElementById('otroGastoModal').style.display = 'none';
}

function saveOtroGasto() {
    const form = document.getElementById('otroGastoForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const monto = document.getElementById('monto').value;
    if (parseFloat(monto) <= 0) {
        alert("El monto del gasto debe ser mayor a 0.");
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'save');

    fetch('../Controller/otro_gasto.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeOtroGastoModal();
            showToast(data.message);
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el registro.');
    });
}

function editOtroGasto(id) {
    fetch(`../Controller/otro_gasto.controller.php?action=get&id_otro_gasto=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_otro_gasto').value = data.id_otro_gasto;
        if (document.getElementById('nombre_gasto')) {
            document.getElementById('nombre_gasto').value = data.nombre_gasto || data.detalle || '';
        }
        document.getElementById('detalle').value = data.detalle || '';
        document.getElementById('unidad_medida').value = data.unidad_medida || '';
        document.getElementById('cantidad').value = data.cantidad || '1.00';
        document.getElementById('precio').value = data.precio || data.monto;
        document.getElementById('monto').value = data.monto;
        document.getElementById('fecha_pago').value = data.fecha_pago;
        document.getElementById('comprobante').value = data.comprobante;
        
        document.getElementById('modalTitle').innerText = 'Editar Gasto';
        document.getElementById('btn-save-otro-gasto').innerText = 'Actualizar';
        document.getElementById('otroGastoModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos.');
    });
}

let currentDeleteId = null;

function deleteOtroGasto(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDeleteOtroGasto() {
    if (!currentDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_otro_gasto', currentDeleteId);

    fetch('../Controller/otro_gasto.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('deleteModal').style.display = 'none';
            showToast(data.message);
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
