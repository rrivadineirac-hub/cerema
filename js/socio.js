// js/socio.js

const modal = document.getElementById('socioModal');
const form = document.getElementById('socioForm');
const modalTitle = document.getElementById('modalTitle');
const modalErrorDiv = document.getElementById('socioModalError');
const modalErrorText = document.getElementById('socioModalErrorText');

function showModalError(msg) {
    if (modalErrorDiv && modalErrorText) {
        modalErrorText.textContent = msg;
        modalErrorDiv.style.display = 'flex';
    }
}

function hideModalError() {
    if (modalErrorDiv) {
        modalErrorDiv.style.display = 'none';
    }
}

function openModal() {
    form.reset();
    hideModalError();
    document.getElementById('id_socio').value = '';
    if (document.getElementById('complemento')) {
        document.getElementById('complemento').value = '';
    }
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
    hideModalError();
    modal.style.display = 'none';
}

// Close modal if user clicks outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

function editSocio(id) {
    hideModalError();
    fetch(`socio.controller.php?action=get_socio&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data) {
                document.getElementById('id_socio').value = data.id_socio;
                document.getElementById('ci').value = data.ci;
                if (document.getElementById('complemento')) {
                    document.getElementById('complemento').value = data.complemento || '';
                }
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
            showToast('No se pudo obtener la información del socio.', 'error');
        });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification' + (type === 'error' ? ' toast-error' : '');
    
    const icon = type === 'error' 
        ? '<i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i>' 
        : '<i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>';
        
    toast.innerHTML = icon + message;
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
    hideModalError();
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Respuesta del servidor no válida (código ' + response.status + ')');
        }
        return response.json();
    })
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
            }, 1500);
        } else {
            const errorMsg = data.message || 'Error al guardar los datos del asociado.';
            showModalError(errorMsg);
            showToast(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving socio:', error);
        const errStr = error.message || 'Ocurrió un error inesperado al guardar.';
        showModalError(errStr);
        showToast(errStr, 'error');
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
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    closeDeleteModal();
                    showToast('Asociado eliminado exitosamente');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    const msg = data.message || 'No se pudo eliminar el socio. Es posible que tenga registros financieros vinculados.';
                    showToast(msg, 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting socio:', error);
                showToast('Ocurrió un error al intentar eliminar el socio.', 'error');
            });
        });
    }
});
