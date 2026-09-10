// js/evento.js

function openEventoModal() {
    document.getElementById('eventoForm').reset();
    document.getElementById('id_evento').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Evento';
    
    // Set default date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_evento').value = today;
    document.getElementById('estado').value = 'Programado';
    document.getElementById('estado').disabled = true;
    
    document.getElementById('otro_evento_container').style.display = 'none';
    document.getElementById('otro_tipo_evento').required = false;

    document.getElementById('eventoModal').style.display = 'block';
}

function closeEventoModal() {
    document.getElementById('eventoModal').style.display = 'none';
}

function toggleOtroEvento() {
    const tipoSelect = document.getElementById('tipo_evento');
    const container = document.getElementById('otro_evento_container');
    const input = document.getElementById('otro_tipo_evento');
    
    if (tipoSelect.value === 'Otros Eventos') {
        container.style.display = 'block';
        input.required = true;
    } else {
        container.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

function saveEvento() {
    const form = document.getElementById('eventoForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'save');
    if (!formData.has('estado')) {
        formData.append('estado', document.getElementById('estado').value);
    }

    fetch('../Controller/evento.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeEventoModal();
            showToast(data.message);
            setTimeout(() => { location.reload(); }, 2000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el evento.');
    });
}

function editEvento(id) {
    fetch(`../Controller/evento.controller.php?action=get&id_evento=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_evento').value = data.id_evento;
        
        const tipoSelect = document.getElementById('tipo_evento');
        let optionFound = false;
        for (let i = 0; i < tipoSelect.options.length; i++) {
            if (tipoSelect.options[i].value && data.tipo_evento && tipoSelect.options[i].value.toLowerCase() === data.tipo_evento.toLowerCase()) {
                tipoSelect.value = tipoSelect.options[i].value;
                optionFound = true;
                break;
            }
        }
        
        if (optionFound) {
            document.getElementById('otro_tipo_evento').value = '';
        } else {
            tipoSelect.value = 'Otros Eventos';
            document.getElementById('otro_tipo_evento').value = data.tipo_evento;
        }
        toggleOtroEvento();
        
        document.getElementById('fecha_evento').value = data.fecha_evento;
        
        // El input type="time" necesita formato HH:mm (a veces viene con segundos)
        if(data.hora_evento) {
            document.getElementById('hora_evento').value = data.hora_evento.substring(0, 5);
        } else {
            document.getElementById('hora_evento').value = '';
        }
        
        document.getElementById('lugar').value = data.lugar;
        document.getElementById('descripcion').value = data.descripcion;
        document.getElementById('estado').value = data.estado;
        document.getElementById('estado').disabled = false;
        
        document.getElementById('modalTitle').innerText = 'Editar Evento';
        document.getElementById('eventoModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos del evento.');
    });
}

let currentDeleteId = null;

function deleteEvento(id, titulo) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = titulo;
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
            formData.append('id_evento', currentDeleteId);

            fetch('../Controller/evento.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeDeleteModal();
                    showToast(data.message);
                    setTimeout(() => { location.reload(); }, 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al intentar eliminar el evento.');
            });
        });
    }
});

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('eventoModal');
    if (event.target == modal) {
        closeEventoModal();
    }
}
