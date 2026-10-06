// js/usuario.js - Lógica cliente independiente para el CRUD de Usuarios

let userToDeleteId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Escuchador para verificación dinámica de nombre de usuario
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('blur', checkUsernameAvailability);
    }
});

// Abrir modal para Crear Nuevo Usuario
function openUserModal() {
    const modal = document.getElementById('usuarioModal');
    const form = document.getElementById('usuarioForm');
    
    if (!modal || !form) return;

    form.reset();
    document.getElementById('id_usuario').value = "0";
    document.getElementById('modalUserTitle').innerHTML = '<i class="fa-solid fa-user-plus"></i> Crear Nuevo Usuario';
    
    // Configurar campos de contraseña para modo creación
    document.getElementById('password').required = true;
    document.getElementById('passwordRequiredStar').style.display = 'inline';
    document.getElementById('passwordHint').innerText = 'Requerido para cuentas nuevas (mínimo 4 caracteres).';
    
    // Limpiar mensaje de verificación de usuario
    const usernameMsg = document.getElementById('usernameMsg');
    if (usernameMsg) {
        usernameMsg.innerText = '';
        usernameMsg.style.color = '';
    }

    // Cargar socios disponibles actualizados
    loadAvailableSocios(0);

    modal.style.display = 'block';
    setTimeout(() => document.getElementById('username').focus(), 100);
}

// Cerrar modal de Usuario
function closeUserModal() {
    const modal = document.getElementById('usuarioModal');
    if (modal) modal.style.display = 'none';
}

// Cargar socios disponibles en el combo box mediante AJAX
function loadAvailableSocios(currentUserId, selectedSocioId = null) {
    fetch(`../Controller/usuario.controller.php?action=get_socios&id_usuario=${currentUserId}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const socioSelect = document.getElementById('id_socio');
                socioSelect.innerHTML = '<option value="">-- Sin socio vinculado (Usuario administrativo global) --</option>';
                
                res.data.forEach(soc => {
                    const option = document.createElement('option');
                    option.value = soc.id_socio;
                    const nombreCompleto = `${soc.ap_paterno} ${soc.ap_materno}, ${soc.nombre}`;
                    option.textContent = `${nombreCompleto} (CI: ${soc.ci || '-'})`;
                    
                    if (selectedSocioId && parseInt(soc.id_socio) === parseInt(selectedSocioId)) {
                        option.selected = true;
                    }
                    socioSelect.appendChild(option);
                });
            }
        })
        .catch(err => console.error('Error al cargar socios:', err));
}

// Editar usuario existente
function editUser(id_usuario) {
    if (!id_usuario) return;

    fetch(`../Controller/usuario.controller.php?action=get&id_usuario=${id_usuario}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                const user = res.data;
                const form = document.getElementById('usuarioForm');
                if (!form) return;

                form.reset();

                document.getElementById('id_usuario').value = user.id_usuario;
                document.getElementById('username').value = user.username;
                document.getElementById('rol_sistema').value = user.rol_sistema;
                document.getElementById('activo').value = user.activo;
                
                // Configurar contraseña para modo edición (opcional)
                const passwordInput = document.getElementById('password');
                passwordInput.value = '';
                passwordInput.required = false;
                document.getElementById('passwordRequiredStar').style.display = 'none';
                document.getElementById('passwordHint').innerText = 'Dejar en blanco para mantener la contraseña actual.';

                document.getElementById('modalUserTitle').innerHTML = '<i class="fa-solid fa-user-pen"></i> Editar Usuario: <b>' + escapeHtml(user.username) + '</b>';

                // Limpiar aviso de verificación de usuario
                const usernameMsg = document.getElementById('usernameMsg');
                if (usernameMsg) usernameMsg.innerText = '';

                // Cargar combo de socios incluyendo el actualmente vinculado si existe
                loadAvailableSocios(user.id_usuario, user.id_socio);

                document.getElementById('usuarioModal').style.display = 'block';
            } else {
                showToast(res.message || 'No se pudo cargar la información del usuario.');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error de conexión al obtener los datos del usuario.');
        });
}

// Alternar visibilidad de la contraseña
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    
    if (!passwordInput || !toggleBtn) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}

// Verificar disponibilidad del nombre de usuario
function checkUsernameAvailability() {
    const usernameInput = document.getElementById('username');
    const idUsuarioInput = document.getElementById('id_usuario');
    const usernameMsg = document.getElementById('usernameMsg');
    
    if (!usernameInput || !usernameMsg) return;

    const username = usernameInput.value.trim();
    const id_usuario = idUsuarioInput ? idUsuarioInput.value : 0;

    if (username.length < 3) {
        usernameMsg.innerText = '';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'check_username');
    formData.append('username', username);
    formData.append('id_usuario', id_usuario);

    fetch('../Controller/usuario.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'exists') {
            usernameMsg.innerText = data.message;
            usernameMsg.style.color = '#E53E3E';
        } else {
            usernameMsg.innerText = '✓ Nombre de usuario disponible';
            usernameMsg.style.color = '#38A169';
        }
    })
    .catch(() => {});
}

// Guardar Usuario (Crear o Editar via AJAX)
function saveUser() {
    const form = document.getElementById('usuarioForm');
    if (!form) return;

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const id_usuario = parseInt(document.getElementById('id_usuario').value) || 0;

    if (!username) {
        showToast('El nombre de usuario es obligatorio.');
        document.getElementById('username').focus();
        return;
    }

    if (username.length < 3) {
        showToast('El nombre de usuario debe tener al menos 3 caracteres.');
        document.getElementById('username').focus();
        return;
    }

    if (id_usuario === 0 && !password) {
        showToast('La contraseña es obligatoria para nuevos usuarios.');
        document.getElementById('password').focus();
        return;
    }

    if (password && password.length < 4) {
        showToast('La contraseña debe tener al menos 4 caracteres.');
        document.getElementById('password').focus();
        return;
    }

    const formData = new FormData(form);

    fetch('../Controller/usuario.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            closeUserModal();
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Ocurrió un error al procesar el usuario.');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión con el servidor.');
    });
}

// Cambiar estado activo / inactivo de un usuario rápidamente
function toggleUserStatus(id_usuario, nuevo_estado, username) {
    const accionTexto = nuevo_estado === 1 ? 'activar' : 'desactivar';
    if (!confirm(`¿Está seguro de que desea ${accionTexto} la cuenta del usuario "${username}"?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id_usuario', id_usuario);
    formData.append('activo', nuevo_estado);

    fetch('../Controller/usuario.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message);
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'No se pudo cambiar el estado.');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error al intentar cambiar el estado del usuario.');
    });
}

// Abrir modal de confirmación de eliminación
function deleteUser(id_usuario, username) {
    userToDeleteId = id_usuario;
    document.getElementById('deleteUsernameText').innerText = username;
    
    const deleteModal = document.getElementById('deleteUserModal');
    const confirmBtn = document.getElementById('btnConfirmDeleteUser');
    
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            executeUserDelete();
        };
    }

    deleteModal.style.display = 'block';
}

// Cerrar modal de eliminación
function closeDeleteUserModal() {
    const deleteModal = document.getElementById('deleteUserModal');
    if (deleteModal) deleteModal.style.display = 'none';
    userToDeleteId = null;
}

// Ejecutar eliminación via AJAX
function executeUserDelete() {
    if (!userToDeleteId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_usuario', userToDeleteId);

    fetch('../Controller/usuario.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        closeDeleteUserModal();
        if (data.status === 'success') {
            showToast(data.message);
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'No se pudo eliminar el usuario.');
        }
    })
    .catch(err => {
        console.error(err);
        closeDeleteUserModal();
        showToast('Error de conexión al eliminar el usuario.');
    });
}

// Filtrar tabla de usuarios en tiempo real
function filterUsersTable() {
    const searchVal = document.getElementById('searchUser').value.toLowerCase().trim();
    const roleVal = document.getElementById('filterRole').value;
    const statusVal = document.getElementById('filterStatus').value;
    
    const table = document.getElementById('usersTable');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        if (!row.hasAttribute('data-username')) return; // omitir fila de "sin registros"

        const username = row.getAttribute('data-username') || '';
        const socio = row.getAttribute('data-socio') || '';
        const rol = row.getAttribute('data-rol') || '';
        const estado = row.getAttribute('data-estado') || '';

        const matchesSearch = !searchVal || username.includes(searchVal) || socio.includes(searchVal);
        const matchesRole = !roleVal || rol === roleVal;
        const matchesStatus = !statusVal || estado === statusVal;

        if (matchesSearch && matchesRole && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Función de escape HTML para seguridad en concatenación
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
