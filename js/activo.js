function openActivoModal() {
    document.getElementById('activoForm').reset();
    document.getElementById('id_activo').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Nuevo Activo';
    
    // Configurar fecha por defecto a hoy
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_adquisicion').value = today;
    document.getElementById('valor_estimado').value = '';
    document.getElementById('estado_operativo').value = 'Bueno';

    // Limpiar advertencias
    let warningSpan = document.getElementById('nombreWarning');
    if (warningSpan) warningSpan.style.display = 'none';
    const btnGuardar = document.getElementById('btnGuardarActivo');
    if (btnGuardar) btnGuardar.disabled = false;

    document.getElementById('activoModal').style.display = 'block';
}

function closeActivoModal() {
    document.getElementById('activoModal').style.display = 'none';
}

function checkNombreActivo() {
    const inputNombre = document.getElementById('nombre_activo');
    const idActivo = document.getElementById('id_activo') ? document.getElementById('id_activo').value : '';
    const btnGuardar = document.getElementById('btnGuardarActivo');
    
    if (!inputNombre) return;

    const nombre = inputNombre.value.trim();

    let warningSpan = document.getElementById('nombreWarning');
    if (!warningSpan) {
        warningSpan = document.createElement('div');
        warningSpan.id = 'nombreWarning';
        warningSpan.style.color = '#e74c3c';
        warningSpan.style.fontSize = '0.88em';
        warningSpan.style.marginTop = '5px';
        warningSpan.style.fontWeight = '500';
        inputNombre.parentNode.appendChild(warningSpan);
    }

    if (nombre === '') {
        warningSpan.style.display = 'none';
        if (btnGuardar) btnGuardar.disabled = false;
        return;
    }

    const formData = new FormData();
    formData.append('action', 'check_name');
    formData.append('nombre_activo', nombre);
    formData.append('id_activo', idActivo);

    fetch('../Controller/activo.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'exists') {
            warningSpan.innerText = data.message || '⚠️ Ya existe un activo registrado con este nombre.';
            warningSpan.style.display = 'block';
            if (btnGuardar) btnGuardar.disabled = true;
        } else {
            warningSpan.style.display = 'none';
            if (btnGuardar) btnGuardar.disabled = false;
        }
    })
    .catch(error => console.error('Error al verificar nombre del activo:', error));
}

function saveActivo() {
    const form = document.getElementById('activoForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const btnGuardar = document.getElementById('btnGuardarActivo');
    if (btnGuardar && btnGuardar.disabled) {
        return;
    }

    const formData = new FormData(form);

    fetch('../Controller/activo.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeActivoModal();
            showToast(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el activo.');
    });
}

function editActivo(id) {
    fetch(`../Controller/activo.controller.php?action=get&id_activo=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_activo').value = data.id_activo;
        document.getElementById('nombre_activo').value = data.nombre_activo;
        document.getElementById('tipo_activo').value = data.tipo_activo;
        document.getElementById('valor_estimado').value = data.valor_estimado || '0.00';
        
        if (data.fecha_adquisicion) {
            const dateStr = data.fecha_adquisicion.split(' ')[0];
            document.getElementById('fecha_adquisicion').value = dateStr;
        } else {
            document.getElementById('fecha_adquisicion').value = '';
        }

        document.getElementById('estado_operativo').value = data.estado_operativo || 'Bueno';
        document.getElementById('observaciones').value = data.observaciones || '';
        
        let warningSpan = document.getElementById('nombreWarning');
        if (warningSpan) warningSpan.style.display = 'none';
        const btnGuardar = document.getElementById('btnGuardarActivo');
        if (btnGuardar) btnGuardar.disabled = false;

        document.getElementById('modalTitle').innerText = 'Editar Activo';
        document.getElementById('activoModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos del activo.');
    });
}

let currentDeleteId = null;

function deleteActivo(id, nombre) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = nombre;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    currentDeleteId = null;
    document.getElementById('deleteModal').style.display = 'none';
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
    }, 4000);
}

// Filtrado de la tabla de activos
function filterActivos() {
    const searchInput = document.getElementById('searchActivo');
    const filterTipo = document.getElementById('filterTipo');
    const filterEstado = document.getElementById('filterEstado');

    const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const tipoVal = filterTipo ? filterTipo.value : '';
    const estadoVal = filterEstado ? filterEstado.value : '';

    const tableRows = document.querySelectorAll('#activosTable tbody tr');

    tableRows.forEach(row => {
        // Ignorar fila de "no hay datos"
        if (row.cells.length === 1) return;

        const nombre = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
        const tipo = row.cells[2] ? row.cells[2].getAttribute('data-tipo') || row.cells[2].textContent.trim() : '';
        const estado = row.cells[5] ? row.cells[5].getAttribute('data-estado') || row.cells[5].textContent.trim() : '';

        const matchesSearch = searchText === '' || nombre.includes(searchText);
        const matchesTipo = tipoVal === '' || tipo === tipoVal;
        const matchesEstado = estadoVal === '' || estado === estadoVal;

        if (matchesSearch && matchesTipo && matchesEstado) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function() {
            if (!currentDeleteId) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_activo', currentDeleteId);

            fetch('../Controller/activo.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeDeleteModal();
                    showToast('Activo eliminado correctamente');
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

    const searchInput = document.getElementById('searchActivo');
    const filterTipo = document.getElementById('filterTipo');
    const filterEstado = document.getElementById('filterEstado');

    if (searchInput) searchInput.addEventListener('input', filterActivos);
    if (filterTipo) filterTipo.addEventListener('change', filterActivos);
    if (filterEstado) filterEstado.addEventListener('change', filterActivos);

    const nombreInput = document.getElementById('nombre_activo');
    if (nombreInput) {
        nombreInput.addEventListener('input', checkNombreActivo);
        nombreInput.addEventListener('change', checkNombreActivo);
    }
});

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('activoModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target == modal) {
        closeActivoModal();
    }
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
}
