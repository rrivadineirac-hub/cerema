// js/aporte.js

function openAporteModal() {
    document.getElementById('aporteForm').reset();
    document.getElementById('id_aporte').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Aporte Extraordinario';
    
    // Set default dates if needed
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_aporte').value = today;
    document.getElementById('monto').value = '0.00';
    document.getElementById('numero_recibo').value = '';
    document.getElementById('payment_info_box').style.display = 'none';

    // Filtrar opciones del select: ocultar los aportes que el socio ya completó en su totalidad
    const motivoSelect = document.getElementById('motivo');
    const btnGuardar = document.getElementById('btnGuardarAporte');

    if (motivoSelect && motivoSelect.tagName === 'SELECT') {
        let hasAvailableOptions = false;
        let firstAvailableValue = '';
        Array.from(motivoSelect.options).forEach(option => {
            if (option.value === '') return;
            if (option.getAttribute('data-completado') === '1') {
                option.style.display = 'none';
                option.disabled = true;
            } else {
                option.style.display = '';
                option.disabled = false;
                if (!firstAvailableValue) firstAvailableValue = option.value;
                hasAvailableOptions = true;
            }
        });

        let warningNoOpt = document.getElementById('noCategoryWarning');
        if (!hasAvailableOptions) {
            motivoSelect.value = '';
            if (!warningNoOpt) {
                warningNoOpt = document.createElement('div');
                warningNoOpt.id = 'noCategoryWarning';
                warningNoOpt.style.color = '#27ae60';
                warningNoOpt.style.fontSize = '13.5px';
                warningNoOpt.style.marginTop = '8px';
                warningNoOpt.style.fontWeight = '500';
                warningNoOpt.innerHTML = '✔ Todos los aportes extraordinarios de la gestión actual han sido pagados en su totalidad por este socio.';
                motivoSelect.parentNode.appendChild(warningNoOpt);
            } else {
                warningNoOpt.style.display = 'block';
            }
            motivoSelect.disabled = true;
            if (btnGuardar) btnGuardar.disabled = true;
        } else {
            if (warningNoOpt) warningNoOpt.style.display = 'none';
            motivoSelect.disabled = false;
            if (btnGuardar) btnGuardar.disabled = false;
            if (firstAvailableValue) {
                motivoSelect.value = firstAvailableValue;
                updatePaymentInfo(firstAvailableValue, true);
            }
        }
    }

    document.getElementById('aporteModal').style.display = 'block';
}

function closeAporteModal() {
    document.getElementById('aporteModal').style.display = 'none';
}

function saveAporte() {
    const btnGuardar = document.getElementById('btnGuardarAporte');
    if (btnGuardar && btnGuardar.disabled) {
        return;
    }

    const form = document.getElementById('aporteForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);

    fetch('../Controller/aporte.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
            if (data.status === 'success') {
                closeAporteModal();
                showToast(data.message);
                setTimeout(() => { location.reload(); }, 2000);
            } else {
                if (data.message.includes("número de recibo") || data.message.includes("ya fue utilizado")) {
                    const inputRecibo = document.getElementById('numero_recibo');
                    if (inputRecibo) {
                        inputRecibo.setCustomValidity(data.message);
                        inputRecibo.reportValidity();
                        inputRecibo.addEventListener('input', function() {
                            this.setCustomValidity('');
                        }, {once: true});
                        return;
                    }
                }
                alert('Error: ' + data.message);
            }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el aporte.');
    });
}

function editAporte(id) {
    fetch(`../Controller/aporte.controller.php?action=get&id_aporte=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_aporte').value = data.id_aporte;
        const btnGuardar = document.getElementById('btnGuardarAporte');
        if (btnGuardar) btnGuardar.disabled = false;
        
        const motivoSelect = document.getElementById('motivo');
        if (motivoSelect && motivoSelect.tagName === 'SELECT') {
            motivoSelect.disabled = false;
            let warningNoOpt = document.getElementById('noCategoryWarning');
            if (warningNoOpt) warningNoOpt.style.display = 'none';

            Array.from(motivoSelect.options).forEach(option => {
                if (option.value === '' || option.value === data.motivo || option.getAttribute('data-completado') !== '1') {
                    option.style.display = '';
                    option.disabled = false;
                } else {
                    option.style.display = 'none';
                    option.disabled = true;
                }
            });
            motivoSelect.value = data.motivo;
        }

        document.getElementById('monto').value = data.monto;
        if(document.getElementById('numero_recibo')) {
            document.getElementById('numero_recibo').value = data.numero_recibo || '';
        }
        
        // Formatear la fecha al formato YYYY-MM-DD para el input date
        if(data.fecha_aporte) {
            const dateStr = data.fecha_aporte.split(' ')[0]; // si viene con hora
            document.getElementById('fecha_aporte').value = dateStr;
        }
        
        document.getElementById('modalTitle').innerText = 'Editar Aporte Extraordinario';
        
        // Cargar información de pago, pero no sobreescribir el monto actual
        updatePaymentInfo(data.motivo, false);
        
        document.getElementById('aporteModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos del aporte.');
    });
}

let currentDeleteId = null;

function deleteAporte(id, descripcion) {
    currentDeleteId = id;
    document.getElementById('deleteDesc').innerText = descripcion;
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
            formData.append('id_aporte', currentDeleteId);

            fetch('../Controller/aporte.controller.php', {
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
                alert('Ocurrió un error al intentar eliminar el aporte.');
            });
        });
    }

    const motivoSelect = document.getElementById('motivo');
    if (motivoSelect) {
        motivoSelect.addEventListener('change', function() {
            updatePaymentInfo(this.value, true);
        });
    }
});

function updatePaymentInfo(motivo, autoFillMonto = true) {
    const idSocio = document.getElementById('id_socio').value;
    const idAporte = document.getElementById('id_aporte') ? document.getElementById('id_aporte').value : '';
    const infoBox = document.getElementById('payment_info_box');
    
    if (!motivo) {
        if(infoBox) infoBox.style.display = 'none';
        document.getElementById('monto').removeAttribute('max');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'get_payment_info');
    formData.append('id_socio', idSocio);
    formData.append('motivo', motivo);
    if (idAporte) {
        formData.append('id_aporte', idAporte);
    }

    fetch('../Controller/aporte.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && infoBox) {
            document.getElementById('info_total').innerText = 'Bs ' + parseFloat(data.total).toFixed(2);
            document.getElementById('info_pagado').innerText = 'Bs ' + parseFloat(data.pagado).toFixed(2);
            document.getElementById('info_saldo').innerText = 'Bs ' + parseFloat(data.saldo).toFixed(2);
            infoBox.style.display = 'block';
            
            if (autoFillMonto) {
                document.getElementById('monto').value = parseFloat(data.saldo).toFixed(2);
            }
            // Add native HTML validation constraint for max amount
            document.getElementById('monto').max = parseFloat(data.saldo).toFixed(2);
        }
    })
    .catch(error => console.error('Error fetching payment info:', error));
}

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('aporteModal');
    if (event.target == modal) {
        closeAporteModal();
    }
}

