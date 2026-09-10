// js/servicio.js

function openServicioModal() {
    document.getElementById('servicioForm').reset();
    document.getElementById('id_servicio').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Pago de Servicio';
    
    // Set default date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('monto').value = '0.00';
    
    // Set default month
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const currentMonthIndex = new Date().getMonth();
    document.getElementById('mes_pago').value = meses[currentMonthIndex];

    document.getElementById('btn-save-servicio').innerText = 'Pagar';

    document.getElementById('servicioModal').style.display = 'block';
}

function closeServicioModal() {
    document.getElementById('servicioModal').style.display = 'none';
}

function saveServicio() {
    const form = document.getElementById('servicioForm');
    
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

    fetch('../Controller/servicio.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeServicioModal();
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

function editServicio(id) {
    fetch(`../Controller/servicio.controller.php?action=get&id_servicio=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_servicio').value = data.id_servicio;
        document.getElementById('tipo_servicio').value = data.tipo_servicio;
        document.getElementById('mes_pago').value = data.mes_pago || '';
        document.getElementById('monto').value = data.monto;
        document.getElementById('fecha_pago').value = data.fecha_pago;
        document.getElementById('comprobante').value = data.comprobante;
        
        document.getElementById('modalTitle').innerText = 'Editar Pago de Servicio';
        document.getElementById('btn-save-servicio').innerText = 'Actualizar';
        document.getElementById('servicioModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos.');
    });
}

let currentDeleteId = null;

function deleteServicio(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDeleteServicio() {
    if (!currentDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_servicio', currentDeleteId);

    fetch('../Controller/servicio.controller.php', {
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
