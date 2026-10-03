// js/mensualidad.js

function openMensualidadModal() {
    document.getElementById('mensualidadForm').reset();
    document.getElementById('id_mensualidad').value = '';
    document.getElementById('modalTitle').innerText = 'Registrar Pago de Mensualidad';
    
    // Configurar visibilidad de secciones (Modo Lote / Nuevo)
    const checklistSection = document.getElementById('checklistSection');
    const singleMonthRow = document.getElementById('singleMonthRow');
    if (checklistSection) checklistSection.style.display = 'block';
    if (singleMonthRow) singleMonthRow.style.display = 'none';

    // Restaurar valor por defecto y botón "Pagar" para nuevos registros
    const estadoSelect = document.getElementById('estado');
    if (estadoSelect) {
        estadoSelect.value = 'Pagado';
    }
    
    const btnGuardar = document.getElementById('btnGuardarMensualidad');
    if (btnGuardar) {
        btnGuardar.innerText = 'Pagar';
        btnGuardar.disabled = false;
    }

    // Fechas y valores por defecto
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_pago').value = today;
    document.getElementById('anio').value = new Date().getFullYear();
    
    const defaultMonto = (typeof MONTO_MENSUALIDAD_SUGERIDO !== 'undefined') ? MONTO_MENSUALIDAD_SUGERIDO : '50.00';
    document.getElementById('monto_unitario').value = defaultMonto;

    document.getElementById('mensualidadModal').style.display = 'block';
    
    // Cargar meses pagados mediante AJAX para el socio/acción/año actual
    loadPaidMonths();
}

function closeMensualidadModal() {
    document.getElementById('mensualidadModal').style.display = 'none';
}

function loadPaidMonths() {
    const isEditing = document.getElementById('id_mensualidad').value !== '';
    if (isEditing) return; // En modo edición no aplica la cuadrícula de lote

    const id_socio = document.getElementById('id_socio').value;
    const numero_accion = document.getElementById('numero_accion') ? document.getElementById('numero_accion').value : 1;
    const anio = document.getElementById('anio').value;

    const anioNum = parseInt(anio, 10);
    const maxAnioVal = new Date().getFullYear() + 3;
    if (!id_socio || !anio || isNaN(anioNum) || anioNum < 2018 || anioNum > maxAnioVal) return;

    fetch(`../Controller/mensualidad.controller.php?action=get_paid_months&id_socio=${id_socio}&numero_accion=${numero_accion}&anio=${anio}`)
    .then(r => r.json())
    .then(data => {
        const paidMonths = (data.status === 'success' && Array.isArray(data.paid_months)) ? data.paid_months : [];
        const checkboxes = document.querySelectorAll('.month-checkbox');

        checkboxes.forEach(cb => {
            const val = cb.value;
            const card = document.getElementById('card_' + val);
            const tag = document.getElementById('tag_' + val);
            const isPaid = paidMonths.includes(val);

            if (isPaid) {
                cb.checked = true;
                cb.disabled = true;
                if (card) {
                    card.classList.add('paid');
                    card.classList.remove('selected');
                }
                if (tag) tag.innerText = '(Pagado)';
            } else {
                cb.checked = false;
                cb.disabled = false;
                if (card) {
                    card.classList.remove('paid');
                    card.classList.remove('selected');
                }
                if (tag) tag.innerText = '';
            }
        });

        updateCalculatedTotal();
    })
    .catch(error => {
        console.error('Error al cargar meses pagados:', error);
    });
}

function updateCalculatedTotal() {
    const isEditing = document.getElementById('id_mensualidad').value !== '';
    const unitVal = parseFloat(document.getElementById('monto_unitario').value) || 0;
    const montoInput = document.getElementById('monto');

    if (isEditing) {
        montoInput.value = unitVal.toFixed(2);
        return;
    }

    const checkboxes = document.querySelectorAll('.month-checkbox');
    let selectedUnpaidCount = 0;

    checkboxes.forEach(cb => {
        const val = cb.value;
        const card = document.getElementById('card_' + val);

        if (!cb.disabled) {
            if (cb.checked) {
                selectedUnpaidCount++;
                if (card) card.classList.add('selected');
            } else {
                if (card) card.classList.remove('selected');
            }
        }
    });

    const total = selectedUnpaidCount * unitVal;
    montoInput.value = total.toFixed(2);
}

function selectUnpaidMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = true;
        }
    });
    updateCalculatedTotal();
}

function uncheckAllMonths() {
    const checkboxes = document.querySelectorAll('.month-checkbox');
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = false;
        }
    });
    updateCalculatedTotal();
}

function saveMensualidad() {
    const btnGuardar = document.getElementById('btnGuardarMensualidad');
    if (btnGuardar && btnGuardar.disabled) {
        return;
    }
    
    const form = document.getElementById('mensualidadForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const isEditing = document.getElementById('id_mensualidad').value !== '';

    if (!isEditing) {
        const selectedUnpaid = Array.from(document.querySelectorAll('.month-checkbox')).filter(cb => cb.checked && !cb.disabled);
        if (selectedUnpaid.length === 0) {
            alert('Por favor seleccione al menos un mes a pagar.');
            return;
        }
    }
    
    const formData = new FormData(form);
    
    if (!isEditing) {
        let estadoActual = document.getElementById('estado').value;
        if (estadoActual === 'Pendiente') {
            estadoActual = 'Pagado';
        }
        formData.append('estado', estadoActual);
    }

    fetch('../Controller/mensualidad.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            closeMensualidadModal();
            showToast('Mensualidad registrada exitosamente');
            setTimeout(() => {
                location.reload();
            }, 1200);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al guardar la mensualidad.');
    });
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

function editMensualidad(id) {
    fetch(`../Controller/mensualidad.controller.php?action=get&id_mensualidad=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('id_mensualidad').value = data.id_mensualidad;
        if(document.getElementById('numero_accion')) {
            document.getElementById('numero_accion').value = data.numero_accion || 1;
        }
        if(document.getElementById('numero_recibo')) {
            document.getElementById('numero_recibo').value = data.numero_recibo || '';
        }

        // En modo edición individual, ocultar checklist y mostrar select de mes individual
        const checklistSection = document.getElementById('checklistSection');
        const singleMonthRow = document.getElementById('singleMonthRow');
        if (checklistSection) checklistSection.style.display = 'none';
        if (singleMonthRow) singleMonthRow.style.display = 'block';

        if(document.getElementById('mes')) {
            document.getElementById('mes').value = data.mes;
        }
        document.getElementById('anio').value = data.anio;
        document.getElementById('monto_unitario').value = data.monto;
        document.getElementById('monto').value = data.monto;
        
        if(data.fecha_pago) {
            const dateStr = data.fecha_pago.split(' ')[0];
            document.getElementById('fecha_pago').value = dateStr;
        }

        if (document.getElementById('estado')) {
            document.getElementById('estado').value = data.estado || 'Pagado';
        }
        document.getElementById('btnGuardarMensualidad').innerText = 'Actualizar';
        document.getElementById('btnGuardarMensualidad').disabled = false;
        
        let warningSpan = document.getElementById('statusWarning');
        if(warningSpan) {
            warningSpan.style.display = 'none';
        }
        
        document.getElementById('modalTitle').innerText = 'Editar Pago de Mensualidad';
        document.getElementById('mensualidadModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al obtener los datos de la mensualidad.');
    });
}

let currentDeleteId = null;

function deleteMensualidad(id, descripcion) {
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
            formData.append('id_mensualidad', currentDeleteId);

            fetch('../Controller/mensualidad.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    closeDeleteModal();
                    showToast('Mensualidad eliminada exitosamente');
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

    const anioInput = document.getElementById('anio');
    const accionSelect = document.getElementById('numero_accion');
    if (anioInput) {
        anioInput.addEventListener('change', loadPaidMonths);
        anioInput.addEventListener('keyup', loadPaidMonths);
    }
    if (accionSelect) {
        accionSelect.addEventListener('change', loadPaidMonths);
    }

    const rangeSocioSelect = document.getElementById('range_id_socio');
    const rangeAccionSelect = document.getElementById('range_numero_accion');
    if (rangeSocioSelect) {
        rangeSocioSelect.addEventListener('change', loadAllPaidMonthsMultiYear);
    }
    if (rangeAccionSelect) {
        rangeAccionSelect.addEventListener('change', loadAllPaidMonthsMultiYear);
    }
});

// Functions for Carga Masiva Multiaño & Rango (2018 - 2029)
let activeRangeTab = 'multi';

function openRangeModal() {
    const rangeModal = document.getElementById('rangeModal');
    if (rangeModal) {
        rangeModal.style.display = 'block';
        switchRangeTab(activeRangeTab);
        loadAllPaidMonthsMultiYear();
    }
}

function closeRangeModal() {
    const rangeModal = document.getElementById('rangeModal');
    if (rangeModal) {
        rangeModal.style.display = 'none';
    }
}

function loadAllPaidMonthsMultiYear() {
    const socioSelect = document.getElementById('range_id_socio');
    const accionSelect = document.getElementById('range_numero_accion');
    if (!socioSelect || !accionSelect) return;

    const id_socio = socioSelect.value;
    const numero_accion = accionSelect.value;

    if (!id_socio || id_socio === 'all') {
        resetMultiYearPaidStyles();
        return;
    }

    fetch(`../Controller/mensualidad.controller.php?action=get_all_paid_months&id_socio=${id_socio}&numero_accion=${numero_accion}`)
    .then(r => r.json())
    .then(data => {
        const paidData = (data.status === 'success' && data.paid_data) ? data.paid_data : {};
        
        for (let y = 2018; y <= 2029; y++) {
            const paidMonthsForYear = paidData[y] || [];
            const monthCbs = document.querySelectorAll(`.ymonth-cb-${y}`);

            monthCbs.forEach(mcb => {
                const val = mcb.value;
                const lbl = document.getElementById(`ymlabel_${y}_${val}`);
                const isPaid = paidMonthsForYear.includes(val);

                mcb.disabled = false; // Permitir interactuar para marcar o desmarcar

                if (isPaid) {
                    mcb.checked = true;
                    mcb.dataset.originallyPaid = "1";
                    if (lbl) {
                        lbl.style.borderColor = '#16a34a';
                        lbl.style.background = '#dcfce7';
                        lbl.style.color = '#15803d';
                        lbl.title = 'Mes pagado en BD (Desmarca si deseas eliminarlo)';
                    }
                } else {
                    mcb.checked = false;
                    mcb.dataset.originallyPaid = "0";
                    if (lbl) {
                        lbl.style.borderColor = '#cbd5e1';
                        lbl.style.background = '#ffffff';
                        lbl.style.color = '#1e293b';
                        lbl.title = '';
                    }
                }
            });
        }
        updateMultiYearTotals();
    })
    .catch(err => {
        console.error('Error al cargar meses pagados multiaño:', err);
    });
}

function resetMultiYearPaidStyles() {
    for (let y = 2018; y <= 2029; y++) {
        const monthCbs = document.querySelectorAll(`.ymonth-cb-${y}`);
        monthCbs.forEach(mcb => {
            mcb.disabled = false;
            mcb.checked = false;
            mcb.dataset.originallyPaid = "0";
            const val = mcb.value;
            const lbl = document.getElementById(`ymlabel_${y}_${val}`);
            if (lbl) {
                lbl.style.borderColor = '#cbd5e1';
                lbl.style.background = '#ffffff';
                lbl.style.color = '#1e293b';
                lbl.title = '';
            }
        });
    }
    updateMultiYearTotals();
}

function switchRangeTab(tab) {
    activeRangeTab = tab;
    const tabBtnMulti = document.getElementById('tabBtnMulti');
    const tabBtnRange = document.getElementById('tabBtnRange');
    const tabContentMulti = document.getElementById('tabContentMulti');
    const tabContentRange = document.getElementById('tabContentRange');

    if (tab === 'multi') {
        if (tabBtnMulti) {
            tabBtnMulti.classList.add('active');
            tabBtnMulti.style.background = '#ffffff';
            tabBtnMulti.style.color = '#0284c7';
            tabBtnMulti.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        }
        if (tabBtnRange) {
            tabBtnRange.classList.remove('active');
            tabBtnRange.style.background = 'transparent';
            tabBtnRange.style.color = '#64748b';
            tabBtnRange.style.boxShadow = 'none';
        }
        if (tabContentMulti) tabContentMulti.style.display = 'block';
        if (tabContentRange) tabContentRange.style.display = 'none';
    } else {
        if (tabBtnRange) {
            tabBtnRange.classList.add('active');
            tabBtnRange.style.background = '#ffffff';
            tabBtnRange.style.color = '#0284c7';
            tabBtnRange.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        }
        if (tabBtnMulti) {
            tabBtnMulti.classList.remove('active');
            tabBtnMulti.style.background = 'transparent';
            tabBtnMulti.style.color = '#64748b';
            tabBtnMulti.style.boxShadow = 'none';
        }
        if (tabContentMulti) tabContentMulti.style.display = 'none';
        if (tabContentRange) tabContentRange.style.display = 'block';
    }
    updateMultiYearTotals();
}

function toggleYearCard(year) {
    const cb = document.querySelector(`.year-enable-cb[data-year="${year}"]`);
    const card = document.getElementById(`ycard_${year}`);
    const monthCbs = document.querySelectorAll(`.ymonth-cb-${year}`);

    if (cb && cb.checked) {
        if (card) {
            card.style.borderColor = '#0284c7';
            card.style.background = '#f0f9ff';
        }
        monthCbs.forEach(mcb => {
            mcb.checked = true;
        });
    } else {
        if (card) {
            card.style.borderColor = '#cbd5e1';
            card.style.background = '#f8fafc';
        }
        monthCbs.forEach(mcb => {
            mcb.checked = false;
        });
    }
    updateMultiYearTotals();
}

function setYearMonths(year, mode) {
    const yearCb = document.querySelector(`.year-enable-cb[data-year="${year}"]`);
    const monthCbs = document.querySelectorAll(`.ymonth-cb-${year}`);

    if (mode === 'all') {
        if (yearCb) yearCb.checked = true;
        monthCbs.forEach(mcb => {
            mcb.checked = true;
        });
    } else if (mode === 'clear') {
        if (yearCb) yearCb.checked = false;
        monthCbs.forEach(mcb => {
            mcb.checked = false;
        });
    }
    toggleYearCard(year);
}

function selectAllMonthsGlobal(enable) {
    for (let y = 2018; y <= 2029; y++) {
        const yearCb = document.querySelector(`.year-enable-cb[data-year="${y}"]`);
        if (yearCb) yearCb.checked = enable;
        const monthCbs = document.querySelectorAll(`.ymonth-cb-${y}`);
        monthCbs.forEach(mcb => {
            mcb.checked = enable;
        });
        const card = document.getElementById(`ycard_${y}`);
        if (card) {
            card.style.borderColor = enable ? '#0284c7' : '#cbd5e1';
            card.style.background = enable ? '#f0f9ff' : '#f8fafc';
        }
    }
    updateMultiYearTotals();
}

function applyGlobalMonto() {
    const globalVal = document.getElementById('globalMontoInput').value;
    const montoInputs = document.querySelectorAll('.year-monto-input');
    montoInputs.forEach(input => {
        input.value = globalVal;
    });
    updateMultiYearTotals();
}

function applyDefaultRates() {
    for (let y = 2018; y <= 2029; y++) {
        const input = document.getElementById(`ymonto_${y}`);
        if (input) {
            input.value = (y <= 2024) ? "30.00" : "50.00";
        }
    }
    updateMultiYearTotals();
}


function selectAllYears(enable) {
    const yearCbs = document.querySelectorAll('.year-enable-cb');
    yearCbs.forEach(cb => {
        cb.checked = enable;
        const y = cb.getAttribute('data-year');
        toggleYearCard(y);
    });
}

function updateMultiYearTotals() {
    if (activeRangeTab === 'multi') {
        let totalYearsCount = 0;
        let totalNewMonths = 0;
        let totalDeletedMonths = 0;
        let totalMontoSum = 0;

        for (let y = 2018; y <= 2029; y++) {
            const yearCb = document.querySelector(`.year-enable-cb[data-year="${y}"]`);
            const montoInput = document.getElementById(`ymonto_${y}`);
            const montoVal = parseFloat(montoInput ? montoInput.value : 50) || 0;
            const monthCbs = document.querySelectorAll(`.ymonth-cb-${y}`);
            const card = document.getElementById(`ycard_${y}`);
            
            let yearNewMonths = 0;
            let yearDeletedMonths = 0;
            let totalCheckedThisYear = 0;

            monthCbs.forEach(mcb => {
                const val = mcb.value;
                const lbl = document.getElementById(`ymlabel_${y}_${val}`);
                const wasOriginallyPaid = (mcb.dataset.originallyPaid === "1");

                if (wasOriginallyPaid) {
                    if (mcb.checked) {
                        // Pagado mantenido (verde suave)
                        totalCheckedThisYear++;
                        if (lbl) {
                            lbl.style.borderColor = '#16a34a';
                            lbl.style.background = '#dcfce7';
                            lbl.style.color = '#15803d';
                            lbl.title = 'Mes ya pagado en BD (Desmarca si deseas eliminarlo)';
                        }
                    } else {
                        // Pagado desmarcado (rojo suave - pendiente de eliminación al guardar)
                        yearDeletedMonths++;
                        if (lbl) {
                            lbl.style.borderColor = '#ef4444';
                            lbl.style.background = '#fee2e2';
                            lbl.style.color = '#b91c1c';
                            lbl.title = 'Mes desmarcado: se eliminará de la base de datos al guardar';
                        }
                    }
                } else {
                    if (mcb.checked) {
                        // Nuevo mes a registrar (verde selección)
                        yearNewMonths++;
                        totalCheckedThisYear++;
                        if (lbl) {
                            lbl.style.borderColor = '#27AE60';
                            lbl.style.background = '#e8f5e9';
                            lbl.style.color = '#1e293b';
                            lbl.title = 'Nuevo mes a registrar';
                        }
                    } else {
                        // No pagado no marcado (blanco normal)
                        if (lbl) {
                            lbl.style.borderColor = '#cbd5e1';
                            lbl.style.background = '#ffffff';
                            lbl.style.color = '#1e293b';
                            lbl.title = '';
                        }
                    }
                }
            });

            if (yearCb) {
                if (totalCheckedThisYear > 0 || yearDeletedMonths > 0) {
                    yearCb.checked = true;
                    if (card) {
                        card.style.borderColor = yearDeletedMonths > 0 ? '#ef4444' : '#0284c7';
                        card.style.background = yearDeletedMonths > 0 ? '#fff5f5' : '#f0f9ff';
                    }
                } else {
                    yearCb.checked = false;
                    if (card) {
                        card.style.borderColor = '#cbd5e1';
                        card.style.background = '#f8fafc';
                    }
                }
            }

            const yearSubtotal = yearNewMonths * montoVal;
            const subtotalEl = document.getElementById(`ysubtotal_${y}`);
            if (subtotalEl) {
                let subText = `Subtotal ${yearNewMonths} nuevos = Bs. ${yearSubtotal.toFixed(2)}`;
                if (yearDeletedMonths > 0) {
                    subText += ` | <span style="color:#dc2626; font-weight:700;">${yearDeletedMonths} a eliminar</span>`;
                }
                subtotalEl.innerHTML = subText;
            }

            if (yearNewMonths > 0 || yearDeletedMonths > 0) {
                totalYearsCount++;
                totalNewMonths += yearNewMonths;
                totalDeletedMonths += yearDeletedMonths;
                totalMontoSum += yearSubtotal;
            }
        }

        const textEl = document.getElementById('summaryTotalText');
        const montoEl = document.getElementById('summaryTotalMonto');
        if (textEl) {
            let summaryStr = `${totalNewMonths} Meses Nuevos a Registrar`;
            if (totalDeletedMonths > 0) {
                summaryStr += ` | <span style="color:#f87171; font-weight:800;">${totalDeletedMonths} Meses a Eliminar</span>`;
            }
            textEl.innerHTML = summaryStr;
        }
        if (montoEl) montoEl.innerText = `Bs. ${totalMontoSum.toFixed(2)}`;

    } else {
        // Range Tab Summary
        const textEl = document.getElementById('summaryTotalText');
        const montoEl = document.getElementById('summaryTotalMonto');
        if (textEl) textEl.innerText = `Carga en Modo Rango Continuo`;
        if (montoEl) montoEl.innerText = `Calculado al procesar`;
    }
}

function submitRangeLoad() {
    const id_socio = document.getElementById('range_id_socio').value;
    const numero_accion = document.getElementById('range_numero_accion').value;
    const numero_recibo = document.getElementById('range_numero_recibo').value;

    const btn = document.getElementById('btnSubmitRange');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando Cambios...';
    }

    const formData = new FormData();

    if (activeRangeTab === 'multi') {
        formData.append('action', 'save_multi_year');
        formData.append('id_socio', id_socio);
        formData.append('numero_accion', numero_accion);
        formData.append('numero_recibo', numero_recibo);

        const yearsData = [];
        let hasAnyChange = false;

        for (let y = 2018; y <= 2029; y++) {
            const monthCbs = document.querySelectorAll(`.ymonth-cb-${y}`);
            const selectedNewMonths = [];
            const deletedPaidMonths = [];

            monthCbs.forEach(mcb => {
                const wasOriginallyPaid = (mcb.dataset.originallyPaid === "1");
                if (wasOriginallyPaid) {
                    if (!mcb.checked) {
                        deletedPaidMonths.push(mcb.value);
                    }
                } else {
                    if (mcb.checked) {
                        selectedNewMonths.push(mcb.value);
                    }
                }
            });

            if (selectedNewMonths.length > 0 || deletedPaidMonths.length > 0) {
                hasAnyChange = true;
                const montoInput = document.getElementById(`ymonto_${y}`);
                const montoVal = parseFloat(montoInput ? montoInput.value : 50) || 50;
                yearsData.push({
                    anio: y,
                    monto: montoVal,
                    meses: selectedNewMonths,
                    meses_eliminar: deletedPaidMonths
                });
            }
        }

        if (!hasAnyChange) {
            alert('No has realizado cambios (no hay meses nuevos marcados ni meses pagados desmarcados para eliminar).');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Cambios Multiaño';
            }
            return;
        }

        formData.append('years_json', JSON.stringify(yearsData));
    } else {
        const form = document.getElementById('rangeForm');
        if (!form) return;
        formData.append('action', 'save_range');
        formData.append('id_socio', id_socio);
        formData.append('numero_accion', numero_accion);
        formData.append('numero_recibo', numero_recibo);
        
        const fData = new FormData(form);
        for (let [k, v] of fData.entries()) {
            if (k !== 'action') formData.append(k, v);
        }
    }

    fetch('../Controller/mensualidad.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            closeRangeModal();
            showToast(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error al cargar multiaño:', err);
        alert('Ocurrió un error al procesar la carga masiva.');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Registrar Pagos Multiaño';
        }
    });
}

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('mensualidadModal');
    const rangeModal = document.getElementById('rangeModal');
    if (event.target == modal) {
        closeMensualidadModal();
    }
    if (event.target == rangeModal) {
        closeRangeModal();
    }
}



