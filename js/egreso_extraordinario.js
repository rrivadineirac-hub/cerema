// js/egreso_extraordinario.js

function openEgresoExtraordinarioModal() {
    document.getElementById('egresoForm').reset();
    document.getElementById('id_egreso_ext').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Gasto Extraordinario';
    document.getElementById('group_estado').style.display = 'none';

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('monto').value = '';
    document.getElementById('detalle').value = '';

    const motivoElem = document.getElementById('motivo');
    if (motivoElem && motivoElem.tagName === 'SELECT') {
        motivoElem.selectedIndex = 0;
    } else if (motivoElem) {
        motivoElem.value = '';
    }

    document.getElementById('egresoModal').style.display = 'block';
}

function closeEgresoExtraordinarioModal() {
    document.getElementById('egresoModal').style.display = 'none';
}

function saveEgresoExtraordinario() {
    const form = document.getElementById('egresoForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    if (!formData.has('action') || !formData.get('action')) {
        formData.append('action', 'save');
    }

    fetch('../Controller/egreso_extraordinario.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            closeEgresoExtraordinarioModal();
            if (typeof showToast === 'function') {
                showToast(data.message);
            } else {
                alert(data.message);
            }
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            alert('Error: ' + (data.message || 'No se pudo guardar el gasto.'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ocurrió un error al guardar el gasto extraordinario.');
    });
}

function editEgresoExtraordinario(id) {
    fetch(`../Controller/egreso_extraordinario.controller.php?action=get&id_egreso_ext=${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }

        document.getElementById('id_egreso_ext').value = data.id_egreso_ext;
        document.getElementById('modalTitle').innerText = 'Editar Gasto Extraordinario';
        
        const motivoElem = document.getElementById('motivo');
        if (motivoElem) {
            motivoElem.value = data.motivo || '';
        }
        document.getElementById('detalle').value = data.detalle || '';
        document.getElementById('monto').value = parseFloat(data.monto).toFixed(2);
        
        if (data.fecha_pago) {
            document.getElementById('fecha_pago').value = data.fecha_pago.split(' ')[0];
        }
        document.getElementById('comprobante').value = data.comprobante || '';
        document.getElementById('estado').value = data.estado || 'Pagado';
        document.getElementById('group_estado').style.display = 'block';

        document.getElementById('egresoModal').style.display = 'block';
    })
    .catch(err => {
        console.error(err);
        alert('Error al obtener los datos del registro.');
    });
}

let deleteIdExt = null;

function deleteEgresoExtraordinario(id, desc) {
    deleteIdExt = id;
    document.getElementById('deleteDesc').innerText = desc;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    deleteIdExt = null;
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function() {
            if (!deleteIdExt) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_egreso_ext', deleteIdExt);

            fetch('../Controller/egreso_extraordinario.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    closeDeleteModal();
                    showToast(data.message);
                    setTimeout(() => { location.reload(); }, 1200);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Ocurrió un error al eliminar el registro.');
            });
        });
    }
});

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    const modalEgreso = document.getElementById('egresoModal');
    const modalDelete = document.getElementById('deleteModal');
    if (event.target == modalEgreso) closeEgresoExtraordinarioModal();
    if (event.target == modalDelete) closeDeleteModal();
};
