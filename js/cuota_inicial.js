// js/cuota_inicial.js

function openCuotaModal() {
    const btnOpen = document.getElementById('btnOpenCuotaModal');
    if (btnOpen && btnOpen.disabled) {
        alert('Este asociado ya ha completado el pago total de su Cuota Inicial.');
        return;
    }

    const form = document.getElementById('cuotaForm');
    if (form) form.reset();

    const idCuotaElem = document.getElementById('id_cuota');
    if (idCuotaElem) idCuotaElem.value = '';

    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) modalTitle.innerText = 'Registrar Pago de Cuota Inicial';

    const btnGuardar = document.getElementById('btnGuardarCuota');
    if (btnGuardar) {
        btnGuardar.innerText = 'Guardar Pago';
        btnGuardar.disabled = false;
    }

    // Set default local datetime (YYYY-MM-DDTHH:MM)
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const formattedNow = `${year}-${month}-${day}T${hours}:${minutes}`;

    const fechaElem = document.getElementById('fecha_pago');
    if (fechaElem) fechaElem.value = formattedNow;

    const conceptoElem = document.getElementById('concepto');
    if (conceptoElem) conceptoElem.value = 'Pago de Cuota Inicial';

    // Auto-fill default amount with pending balance and set max attribute
    const defaultSaldo = (typeof SALDO_PENDIENTE_SOCIO !== 'undefined') ? parseFloat(SALDO_PENDIENTE_SOCIO) : 0;
    const montoInput = document.getElementById('monto');
    if (montoInput) {
        montoInput.value = defaultSaldo > 0 ? defaultSaldo.toFixed(2) : '0.00';
        montoInput.max = defaultSaldo > 0 ? defaultSaldo.toFixed(2) : '';
    }
    const montoMaxSpan = document.getElementById('montoMaxSpan');
    if (montoMaxSpan) {
        montoMaxSpan.innerText = defaultSaldo.toFixed(2);
    }

    const msgElem = document.getElementById('reciboCheckMsg');
    if (msgElem) {
        msgElem.style.display = 'none';
        msgElem.innerText = '';
    }

    const modal = document.getElementById('cuotaModal');
    if (modal) modal.style.display = 'block';
}

function closeCuotaModal() {
    const modal = document.getElementById('cuotaModal');
    if (modal) modal.style.display = 'none';
}

function saveCuota() {
    const btnGuardar = document.getElementById('btnGuardarCuota');
    if (btnGuardar && btnGuardar.disabled) {
        return;
    }

    const form = document.getElementById('cuotaForm');
    if (!form) return;

    const montoInput = document.getElementById('monto');
    if (montoInput) {
        const valMonto = parseFloat(montoInput.value) || 0;
        const maxAttr = montoInput.getAttribute('max');
        if (maxAttr && maxAttr !== '') {
            const maxMonto = parseFloat(maxAttr);
            if (valMonto > maxMonto + 0.001) {
                montoInput.setCustomValidity('El monto acreditado (Bs. ' + valMonto.toFixed(2) + ') no puede ser mayor al saldo disponible (Bs. ' + maxMonto.toFixed(2) + ').');
                montoInput.reportValidity();
                montoInput.addEventListener('input', function() {
                    this.setCustomValidity('');
                }, { once: true });
                return;
            }
        }
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    fetch('../Controller/cuota_inicial.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeCuotaModal();
            showToast(data.message || 'Pago de cuota inicial guardado exitosamente.');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            if (data.message && (data.message.includes('recibo') || data.message.includes('utilizado'))) {
                const inputRecibo = document.getElementById('numero_recibo');
                if (inputRecibo) {
                    inputRecibo.setCustomValidity(data.message);
                    inputRecibo.reportValidity();
                    inputRecibo.addEventListener('input', function() {
                        this.setCustomValidity('');
                    }, { once: true });
                    return;
                }
            }
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar el pago de cuota inicial.');
    });
}

function editCuota(id) {
    fetch(`../Controller/cuota_inicial.controller.php?action=get_cuota&id_cuota=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }

        document.getElementById('id_cuota').value = data.id_cuota;
        if (document.getElementById('numero_recibo')) {
            document.getElementById('numero_recibo').value = data.numero_recibo || '';
        }
        if (document.getElementById('numero_accion')) {
            document.getElementById('numero_accion').value = data.numero_accion || 1;
        }
        if (document.getElementById('monto')) {
            document.getElementById('monto').value = data.monto;

            // Calculate max for edit
            const baseSaldo = (typeof SALDO_PENDIENTE_SOCIO !== 'undefined') ? parseFloat(SALDO_PENDIENTE_SOCIO) : 0;
            const maxEditMonto = baseSaldo + parseFloat(data.monto || 0);
            document.getElementById('monto').max = maxEditMonto.toFixed(2);

            const montoMaxSpan = document.getElementById('montoMaxSpan');
            if (montoMaxSpan) {
                montoMaxSpan.innerText = maxEditMonto.toFixed(2);
            }
        }
        if (document.getElementById('concepto')) {
            document.getElementById('concepto').value = data.concepto || 'Pago de Cuota Inicial';
        }
        if (document.getElementById('observaciones')) {
            document.getElementById('observaciones').value = data.observaciones || '';
        }

        if (data.fecha_pago && document.getElementById('fecha_pago')) {
            // Replace space with T for datetime-local input
            const dateStr = data.fecha_pago.replace(' ', 'T').slice(0, 16);
            document.getElementById('fecha_pago').value = dateStr;
        }

        const modalTitle = document.getElementById('modalTitle');
        if (modalTitle) modalTitle.innerText = 'Editar Pago de Cuota Inicial';

        const btnGuardar = document.getElementById('btnGuardarCuota');
        if (btnGuardar) {
            btnGuardar.innerText = 'Actualizar Pago';
            btnGuardar.disabled = false;
        }

        const msgElem = document.getElementById('reciboCheckMsg');
        if (msgElem) {
            msgElem.style.display = 'none';
            msgElem.innerText = '';
        }

        document.getElementById('cuotaModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos del pago de cuota inicial.');
    });
}

let currentDeleteId = null;

function deleteCuota(id, descripcion) {
    currentDeleteId = id;
    const descElem = document.getElementById('deleteDesc');
    if (descElem) descElem.innerText = descripcion || `ID #${id}`;
    
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) deleteModal.style.display = 'block';
}

function closeDeleteModal() {
    currentDeleteId = null;
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) deleteModal.style.display = 'none';
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
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function() {
            if (!currentDeleteId) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id_cuota', currentDeleteId);

            fetch('../Controller/cuota_inicial.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeDeleteModal();
                    showToast(data.message || 'Pago eliminado correctamente.');
                    setTimeout(() => {
                        location.reload();
                    }, 1200);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al eliminar el pago de cuota inicial.');
            });
        });
    }

    // Check duplicate recibo on typing
    const inputRecibo = document.getElementById('numero_recibo');
    let checkTimeout = null;
    if (inputRecibo) {
        inputRecibo.addEventListener('input', function() {
            const numeroRecibo = this.value.trim();
            const idCuota = document.getElementById('id_cuota') ? document.getElementById('id_cuota').value : '';
            const msgElem = document.getElementById('reciboCheckMsg');

            if (checkTimeout) clearTimeout(checkTimeout);

            if (!numeroRecibo || !msgElem) {
                if (msgElem) msgElem.style.display = 'none';
                return;
            }

            checkTimeout = setTimeout(() => {
                fetch(`../Controller/cuota_inicial.controller.php?action=check_recibo&numero_recibo=${encodeURIComponent(numeroRecibo)}&id_cuota=${encodeURIComponent(idCuota)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'error' || data.exists) {
                        msgElem.style.color = '#ef4444';
                        msgElem.innerText = '⚠ Este N° de recibo ya está registrado.';
                        msgElem.style.display = 'block';
                    } else {
                        msgElem.style.color = '#10b981';
                        msgElem.innerText = '✓ N° de recibo disponible.';
                        msgElem.style.display = 'block';
                    }
                })
                .catch(() => {
                    msgElem.style.display = 'none';
                });
            }, 400);
        });
    }
});

// Close modal when clicking outside modal-content
window.onclick = function(event) {
    const modal = document.getElementById('cuotaModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeCuotaModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
};
