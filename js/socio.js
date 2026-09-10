// js/socio.js

const modal = document.getElementById('socioModal');
const form = document.getElementById('socioForm');
const modalTitle = document.getElementById('modalTitle');

function openModal() {
    form.reset();
    document.getElementById('id_socio').value = '';
    modalTitle.textContent = 'Nuevo Socio';
    
    // Set today as default date for new socio
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_ingreso').value = today;
    document.getElementById('acciones').value = '1';
    if (document.getElementById('cuota_inicial')) {
        document.getElementById('cuota_inicial').value = '0.00';
    }
    
    // Hide estado and set to Activo for new socio
    document.getElementById('estado').value = 'Activo';
    document.getElementById('estado').closest('.form-group').style.display = 'none';
    
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

function editSocio(id) {
    fetch(`socio.controller.php?action=get_socio&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data) {
                document.getElementById('id_socio').value = data.id_socio;
                document.getElementById('ci').value = data.ci;
                document.getElementById('nombre').value = data.nombre;
                document.getElementById('ap_paterno').value = data.ap_paterno;
                document.getElementById('ap_materno').value = data.ap_materno;
                document.getElementById('telefono').value = data.telefono;
                document.getElementById('correo').value = data.correo;
                document.getElementById('fecha_ingreso').value = data.fecha_ingreso;
                const estadoVal = (data.estado === 'Moroso') ? 'Pasivo' : (data.estado || 'Activo');
                document.getElementById('estado').value = estadoVal;
                document.getElementById('estado').closest('.form-group').style.display = 'block';
                document.getElementById('acciones').value = data.acciones || 1;
                if (document.getElementById('cuota_inicial')) {
                    document.getElementById('cuota_inicial').value = data.cuota_inicial || 0;
                }
                
                modalTitle.textContent = 'Editar Socio';
                modal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching socio details:', error);
            alert('No se pudo obtener la información del socio.');
        });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerText = message;
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 400); // esperar a que termine la transición
    }, 5000);
}

function saveSocio() {
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const isEditing = document.getElementById('id_socio').value !== '';

    fetch('socio.controller.php', {
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
            if(isEditing) {
                showToast('Socio editado correctamente');
            } else {
                showToast('Socio registrado correctamente');
            }
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving socio:', error);
        alert('Ocurrió un error al guardar. Verifica la consola.');
    });
}

let currentDeleteId = null;

function deleteSocio(id, nombre) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = nombre;
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
            formData.append('id_socio', currentDeleteId);

            fetch('socio.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    closeDeleteModal();
                    showToast('Asociado eliminado exitosamente');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert('No se pudo eliminar el socio. Es posible que tenga registros financieros vinculados.');
                }
            })
            .catch(error => {
                console.error('Error deleting socio:', error);
                alert('Ocurrió un error al eliminar. Verifica la consola.');
            });
        });
    }
});
