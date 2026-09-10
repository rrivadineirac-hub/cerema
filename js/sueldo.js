// js/sueldo.js

function openSueldoModal() {
    document.getElementById('sueldoForm').reset();
    document.getElementById('id_sueldo').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Pago de Sueldo';
    
    // Set default date
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('monto').value = '0.00';

    // Set current month by default
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const currentMonth = meses[now.getMonth()];
    const selectMes = document.getElementById('mes');
    if (selectMes) {
        selectMes.value = currentMonth;
    }

    document.getElementById('btn-save-sueldo').innerText = 'Pagar Sueldo';

    document.getElementById('sueldoModal').style.display = 'block';
}

function closeSueldoModal() {
    document.getElementById('sueldoModal').style.display = 'none';
}

function saveSueldo() {
    const form = document.getElementById('sueldoForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const monto = document.getElementById('monto').value;
    if (parseFloat(monto) <= 0) {
        alert("El monto del pago debe ser mayor a 0.");
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'save');

    fetch('../Controller/sueldo.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeSueldoModal();
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

function editSueldo(id) {
    fetch(`../Controller/sueldo.controller.php?action=get&id_sueldo=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_sueldo').value = data.id_sueldo;
        document.getElementById('empleado').value = data.empleado;
        document.getElementById('cargo').value = data.cargo;
        if (document.getElementById('mes')) {
            document.getElementById('mes').value = data.mes || '';
        }
        document.getElementById('monto').value = data.monto;
        document.getElementById('fecha_pago').value = data.fecha_pago;
        document.getElementById('comprobante').value = data.comprobante;
        
        document.getElementById('modalTitle').innerText = 'Editar Pago de Sueldo';
        document.getElementById('btn-save-sueldo').innerText = 'Actualizar';
        document.getElementById('sueldoModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos.');
    });
}

let currentDeleteId = null;

function deleteSueldo(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDeleteSueldo() {
    if (!currentDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_sueldo', currentDeleteId);

    fetch('../Controller/sueldo.controller.php', {
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
