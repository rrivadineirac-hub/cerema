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
    const rangeModal = document.getElementById('aporteRangeModal');
    if (event.target == modal) {
        closeAporteModal();
    }
    if (event.target == rangeModal) {
        closeAporteRangeModal();
    }
}

// ==========================================
// FUNCIONES MULTIAÑO DE APORTES EXTRAORDINARIOS
// ==========================================

function openAporteRangeModal() {
    const modal = document.getElementById('aporteRangeModal');
    if (modal) {
        modal.style.display = 'block';
        loadAllPaidMonthsAporteMultiYear();
    }
}

function closeAporteRangeModal() {
    const modal = document.getElementById('aporteRangeModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function loadAllPaidMonthsAporteMultiYear() {
    const socioSelect = document.getElementById('range_aporte_id_socio');
    const accionSelect = document.getElementById('range_aporte_numero_accion');
    const motivoSelect = document.getElementById('range_aporte_motivo');

    if (!socioSelect || !motivoSelect) return;

    const idSocio = socioSelect.value;
    const numAccion = accionSelect ? accionSelect.value : '1';
    const motivo = motivoSelect.value;

    // Resetear todos los checkboxes primero
    document.querySelectorAll('.aymonth-cb').forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
        delete cb.dataset.originallyPaid;
        const year = cb.dataset.year;
        const month = cb.value;
        const label = document.getElementById(`aymlabel_${year}_${month}`);
        if (label) {
            label.style.backgroundColor = '#ffffff';
            label.style.borderColor = '#cbd5e1';
            label.style.color = '#1e293b';
            const tag = label.querySelector('.paid-tag');
            if (tag) tag.remove();
        }
    });

    document.querySelectorAll('.ayear-enable-cb').forEach(ycb => ycb.checked = false);

    if (!idSocio || idSocio === 'all' || !motivo) {
        updateAporteMultiYearTotals();
        return;
    }

    fetch(`../Controller/aporte.controller.php?action=get_all_paid_months_aporte&id_socio=${idSocio}&numero_accion=${numAccion}&motivo=${encodeURIComponent(motivo)}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' && data.paid) {
            const paid = data.paid;
            for (let year in paid) {
                const monthsArr = paid[year];
                if (Array.isArray(monthsArr)) {
                    monthsArr.forEach(mName => {
                        const cb = document.querySelector(`.aymonth-cb-${year}[value="${mName}"]`);
                        if (cb) {
                            cb.checked = true;
                            cb.dataset.originallyPaid = "1";
                            const label = document.getElementById(`aymlabel_${year}_${mName}`);
                            if (label) {
                                label.style.backgroundColor = '#dcfce7';
                                label.style.borderColor = '#86efac';
                                label.style.color = '#15803d';
                                if (!label.querySelector('.paid-tag')) {
                                    const tag = document.createElement('span');
                                    tag.className = 'paid-tag';
                                    tag.style.fontSize = '10px';
                                    tag.style.fontWeight = '700';
                                    tag.style.color = '#15803d';
                                    tag.style.marginLeft = 'auto';
                                    tag.innerText = '✓';
                                    label.appendChild(tag);
                                }
                            }
                        }
                    });

                    // Si hay meses pagados en este año, activar checkbox de año
                    const ycb = document.querySelector(`.ayear-enable-cb[data-year="${year}"]`);
                    if (ycb && monthsArr.length > 0) {
                        ycb.checked = true;
                    }
                }
            }
        }
        updateAporteMultiYearTotals();
    })
    .catch(err => {
        console.error('Error al cargar meses pagados de aporte:', err);
        updateAporteMultiYearTotals();
    });
}

function toggleAporteYearCard(year) {
    const ycb = document.querySelector(`.ayear-enable-cb[data-year="${year}"]`);
    const isChecked = ycb ? ycb.checked : false;
    setAporteYearMonths(year, isChecked ? 'all' : 'clear');
}

function setAporteYearMonths(year, mode) {
    const cbs = document.querySelectorAll(`.aymonth-cb-${year}`);
    cbs.forEach(cb => {
        cb.checked = (mode === 'all');
    });
    const ycb = document.querySelector(`.ayear-enable-cb[data-year="${year}"]`);
    if (ycb) ycb.checked = (mode === 'all');
    updateAporteMultiYearTotals();
}

function applyGlobalAporteMonto() {
    const input = document.getElementById('globalAporteMontoInput');
    if (!input) return;
    const val = parseFloat(input.value) || 0.00;
    document.querySelectorAll('.ayear-monto-input').forEach(inp => {
        inp.value = val.toFixed(2);
    });
    updateAporteMultiYearTotals();
}

function selectAllAporteMonthsGlobal(enable) {
    document.querySelectorAll('.aymonth-cb').forEach(cb => {
        cb.checked = enable;
    });
    document.querySelectorAll('.ayear-enable-cb').forEach(ycb => {
        ycb.checked = enable;
    });
    updateAporteMultiYearTotals();
}

function updateAporteMultiYearTotals() {
    let totalMonthsCount = 0;
    let totalBsSum = 0;

    for (let y = 2018; y <= 2029; y++) {
        const cbs = document.querySelectorAll(`.aymonth-cb-${y}`);
        const montoInput = document.getElementById(`aymonto_${y}`);
        const montoVal = parseFloat(montoInput ? montoInput.value : 0) || 0;
        
        let countYear = 0;
        cbs.forEach(cb => {
            const year = cb.dataset.year;
            const month = cb.value;
            const label = document.getElementById(`aymlabel_${year}_${month}`);
            const wasOriginallyPaid = (cb.dataset.originallyPaid === "1");

            if (cb.checked) {
                countYear++;
                if (label) {
                    label.style.backgroundColor = wasOriginallyPaid ? '#dcfce7' : '#e0f2fe';
                    label.style.borderColor = wasOriginallyPaid ? '#86efac' : '#7dd3fc';
                    label.style.color = wasOriginallyPaid ? '#15803d' : '#0369a1';
                }
            } else {
                if (label) {
                    if (wasOriginallyPaid) {
                        label.style.backgroundColor = '#fee2e2';
                        label.style.borderColor = '#fca5a5';
                        label.style.color = '#b91c1c';
                    } else {
                        label.style.backgroundColor = '#ffffff';
                        label.style.borderColor = '#cbd5e1';
                        label.style.color = '#1e293b';
                    }
                }
            }
        });

        const subtotalBs = countYear * montoVal;
        const subElem = document.getElementById(`aysubtotal_${y}`);
        if (subElem) {
            subElem.innerText = `Subtotal ${countYear} meses = Bs. ${subtotalBs.toFixed(2)}`;
            subElem.style.color = countYear > 0 ? '#0284c7' : '#64748b';
        }

        const ycb = document.querySelector(`.ayear-enable-cb[data-year="${y}"]`);
        if (ycb) {
            ycb.checked = (countYear > 0);
        }

        totalMonthsCount += countYear;
        totalBsSum += subtotalBs;
    }

    const summaryElem = document.getElementById('totalAporteMultiYearSummary');
    if (summaryElem) {
        summaryElem.innerHTML = `Total Aportes: <span style="color: #0284c7;">${totalMonthsCount} meses</span> | <span style="color: #27AE60; font-size: 16px;">Bs. ${totalBsSum.toFixed(2)}</span>`;
    }
}

function saveAporteMultiYear() {
    const socioSelect = document.getElementById('range_aporte_id_socio');
    const numAccionSelect = document.getElementById('range_aporte_numero_accion');
    const motivoSelect = document.getElementById('range_aporte_motivo');
    const numReciboInput = document.getElementById('range_aporte_numero_recibo');
    const btnSave = document.getElementById('btnSaveAporteMultiYear');

    if (!socioSelect || !socioSelect.value) {
        alert('Debe seleccionar un socio.');
        return;
    }

    const idSocio = socioSelect.value;
    const numAccion = numAccionSelect ? numAccionSelect.value : '1';
    const motivo = motivoSelect ? motivoSelect.value : '';
    const numRecibo = numReciboInput ? numReciboInput.value.trim() : '';

    if (!motivo) {
        alert('Debe seleccionar un Motivo para el Aporte Extraordinario.');
        return;
    }

    const yearsData = [];
    for (let y = 2018; y <= 2029; y++) {
        const cbs = document.querySelectorAll(`.aymonth-cb-${y}`);
        const montoInput = document.getElementById(`aymonto_${y}`);
        const montoVal = parseFloat(montoInput ? montoInput.value : 0) || 0;

        const checkedMonths = [];
        const monthsToDelete = [];

        cbs.forEach(cb => {
            const wasOriginallyPaid = (cb.dataset.originallyPaid === "1");
            if (cb.checked) {
                checkedMonths.push(cb.value);
            } else if (wasOriginallyPaid) {
                monthsToDelete.push(cb.value);
            }
        });

        if (checkedMonths.length > 0 || monthsToDelete.length > 0) {
            yearsData.push({
                anio: y,
                monto: montoVal,
                meses: checkedMonths,
                meses_eliminar: monthsToDelete
            });
        }
    }

    if (yearsData.length === 0) {
        alert('No has seleccionado ningún mes para guardar ni eliminar.');
        return;
    }

    const originalText = btnSave ? btnSave.innerHTML : 'Guardar Aportes';
    if (btnSave) {
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
    }

    const formData = new FormData();
    formData.append('action', 'save_multi_year_aporte');
    formData.append('id_socio', idSocio);
    formData.append('numero_accion', numAccion);
    formData.append('motivo', motivo);
    formData.append('numero_recibo', numRecibo);
    formData.append('years_json', JSON.stringify(yearsData));

    fetch('../Controller/aporte.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }

        if (data.status === 'success') {
            closeAporteRangeModal();
            showToast('✅ ' + data.message);
            setTimeout(() => { location.reload(); }, 1800);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
        }
        console.error('Error al guardar aportes multiaño:', err);
        alert('Ocurrió un error al conectar con el servidor.');
    });
}

