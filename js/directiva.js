// js/directiva.js

const modal = document.getElementById('directivaModal');
const form = document.getElementById('directivaForm');
const modalTitle = document.getElementById('modalTitle');

function openModal() {
    form.reset();
    document.getElementById('id_directiva').value = '';
    modalTitle.textContent = 'Asignar Nuevo Rol';
    
    // Set today as default date for new assignment
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_inicio').value = today;
    document.getElementById('gestion').value = new Date().getFullYear();
    
    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
}

// Close modal if user clicks outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

function editDirectiva(id) {
    fetch(`directiva.controller.php?action=get_registro&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data) {
                document.getElementById('id_directiva').value = data.id_directiva;
                document.getElementById('id_cargo').value = data.id_cargo;
                document.getElementById('id_socio').value = data.id_socio;
                document.getElementById('gestion').value = data.gestion;
                document.getElementById('fecha_inicio').value = data.fecha_inicio;
                document.getElementById('fecha_fin').value = data.fecha_fin ? data.fecha_fin : '';
                
                modalTitle.textContent = 'Editar Asignación de Rol';
                modal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching directiva details:', error);
            alert('No se pudo obtener la información de la asignación.');
        });
}

function saveDirectiva() {
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    fetch('directiva.controller.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            closeModal();
            const isEditing = document.getElementById('id_directiva').value !== '';
            if(isEditing) {
                showToast('Asignación editada correctamente');
            } else {
                showToast('Asignación registrada correctamente');
            }
            setTimeout(() => { location.reload(); }, 2000); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving directiva:', error);
        alert('Ocurrió un error al guardar. Verifica la consola.');
    });
}

let currentDeleteId = null;

function deleteDirectiva(id, info) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = info;
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
            formData.append('id_directiva', currentDeleteId);

            fetch('directiva.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    closeDeleteModal();
                    showToast('Asignación eliminada exitosamente');
                    setTimeout(() => { location.reload(); }, 2000);
                } else {
                    alert('No se pudo eliminar la asignación.');
                }
            })
            .catch(error => {
                console.error('Error deleting directiva:', error);
                alert('Ocurrió un error al eliminar. Verifica la consola.');
            });
        });
    }
});
