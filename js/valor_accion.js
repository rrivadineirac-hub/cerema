// js/valor_accion.js
const VA_URL = '../Controller/valor_accion.controller.php';
let currentDeleteId = null;

function openValorModal(numAccion) {
    const btn = document.getElementById('btnOpenValorModal');
    const form = document.getElementById('valorForm');
    if (!form) return;
    form.reset();
    document.getElementById('id_valor').value = '';
    document.getElementById('modalTitle').innerText = 'Asignar Valor de Acción';
    document.getElementById('btnGuardarValor').innerText = 'Guardar';
    document.getElementById('fecha_valor').value = new Date().toISOString().slice(0, 10);

    const sel = document.getElementById('numero_accion');
    let firstFree = null;
    Array.from(sel.options).forEach(o => {
        const used = o.dataset.used === '1';
        o.disabled = used;
        if (!used && firstFree === null) firstFree = o.value;
    });
    if (numAccion) {
        sel.value = String(numAccion);
    } else if (firstFree !== null) {
        sel.value = firstFree;
    } else if (btn && btn.disabled) {
        alert('Todas las acciones ya tienen un valor asignado.');
        return;
    }
    document.getElementById('valorModal').style.display = 'block';
}

function closeValorModal() {
    document.getElementById('valorModal').style.display = 'none';
}

function saveValor() {
    const form = document.getElementById('valorForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const btn = document.getElementById('btnGuardarValor');
    btn.disabled = true;

    // Habilitar opciones para que se envíe el valor seleccionado
    Array.from(document.getElementById('numero_accion').options).forEach(o => o.disabled = false);

    fetch(VA_URL, { method: 'POST', body: new FormData(form) })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                closeValorModal();
                showToast(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error(err);
            alert('Ocurrió un error al guardar el valor de la acción.');
        });
}

function editValor(id) {
    fetch(`${VA_URL}?action=get&id_valor=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.status === 'error') { alert(data.message); return; }
            const sel = document.getElementById('numero_accion');
            Array.from(sel.options).forEach(o => {
                o.disabled = o.dataset.used === '1' && o.value !== String(data.numero_accion);
            });
            document.getElementById('id_valor').value = data.id_valor;
            sel.value = data.numero_accion;
            document.getElementById('valor').value = data.valor;
            document.getElementById('fecha_valor').value = (data.fecha_valor || '').slice(0, 10);
            document.getElementById('observaciones').value = data.observaciones || '';
            document.getElementById('modalTitle').innerText = 'Editar Valor de Acción';
            document.getElementById('btnGuardarValor').innerText = 'Actualizar';
            document.getElementById('valorModal').style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error al obtener los datos.');
        });
}

function deleteValor(id, desc) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = desc || `ID #${id}`;
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
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    const btnDel = document.getElementById('btnConfirmDelete');
    if (btnDel) {
        btnDel.addEventListener('click', () => {
            if (!currentDeleteId) return;
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id_valor', currentDeleteId);
            fetch(VA_URL, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        closeDeleteModal();
                        showToast(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Ocurrió un error al eliminar.'));
        });
    }
});

window.addEventListener('click', e => {
    if (e.target === document.getElementById('valorModal')) closeValorModal();
    if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
});
