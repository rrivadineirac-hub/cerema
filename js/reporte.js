// js/reporte.js

let currentPage = 1;
let pageSize = 15;
let filteredRows = [];

function getTableRows() {
    const table = document.getElementById('reporteSociosTable');
    if (!table) return [];
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    // Filtrar fila de "no datos" / id emptyReportRow
    return rows.filter(r => r.cells.length > 1 && r.id !== 'emptyReportRow');
}

function updateFilteredRows() {
    const allRows = getTableRows();
    const selectSocio = document.getElementById('filterSocio');
    const selectMes = document.getElementById('filterMes');
    const selectGestion = document.getElementById('filterGestion');
    const selectTipoActivo = document.getElementById('filterTipoActivo');
    const selectEstadoOperativo = document.getElementById('filterEstadoOperativo');
    const searchInput = document.getElementById('searchReporte');

    const valSocio = selectSocio ? selectSocio.value : 'all';
    const valMes = selectMes ? selectMes.value : 'all';
    const valGestion = selectGestion ? selectGestion.value : 'all';
    const valTipoActivo = selectTipoActivo ? selectTipoActivo.value : 'all';
    const valEstadoOperativo = selectEstadoOperativo ? selectEstadoOperativo.value : 'all';
    const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';

    filteredRows = allRows.filter(row => {
        const rowIdSocio = row.dataset.idSocio || '';
        const rowMes = (row.dataset.mes || '').toLowerCase();
        const rowAnio = (row.dataset.anio || '').toLowerCase();
        const rowTipo = (row.dataset.tipo || '').toLowerCase();
        const rowEstado = (row.dataset.estado || '').toLowerCase();
        const rowText = row.textContent.toLowerCase();

        // 1. Filtrar por Socio
        if (valSocio !== 'all' && rowIdSocio !== '' && rowIdSocio !== valSocio) {
            return false;
        }

        // 2. Filtrar por Mes
        if (valMes !== 'all') {
            const mesTarget = valMes.toLowerCase();
            if (rowMes !== '' && rowMes !== mesTarget) {
                return false;
            } else if (rowMes === '' && !rowText.includes(mesTarget)) {
                return false;
            }
        }

        // 3. Filtrar por Gestión (Año)
        if (valGestion !== 'all') {
            const gestionTarget = valGestion.toLowerCase();
            if (rowAnio !== '' && rowAnio !== gestionTarget) {
                return false;
            } else if (rowAnio === '' && !rowText.includes(gestionTarget)) {
                return false;
            }
        }

        // 4. Filtrar por Tipo de Activo
        if (valTipoActivo !== 'all') {
            const tipoTarget = valTipoActivo.toLowerCase();
            if (rowTipo !== '' && rowTipo !== tipoTarget) {
                return false;
            }
        }

        // 5. Filtrar por Estado Operativo de Activo
        if (valEstadoOperativo !== 'all') {
            const estadoTarget = valEstadoOperativo.toLowerCase();
            if (rowEstado !== '' && rowEstado !== estadoTarget) {
                return false;
            }
        }

        // 6. Texto libre opcional
        if (searchText !== '' && !rowText.includes(searchText)) {
            return false;
        }

        return true;
    });
}

let hasSearched = false;

function triggerReportSearch() {
    hasSearched = true;
    currentPage = 1;
    renderTablePagination();
    updateReportButtonsState();
}

function resetReportFilters() {
    const filterSocio = document.getElementById('filterSocio');
    const filterMes = document.getElementById('filterMes');
    const filterGestion = document.getElementById('filterGestion');
    const searchInput = document.getElementById('searchReporte');

    const filterTipoActivo = document.getElementById('filterTipoActivo');
    const filterEstadoOperativo = document.getElementById('filterEstadoOperativo');

    if (filterSocio) filterSocio.value = 'all';
    if (filterMes) filterMes.value = 'all';
    if (filterGestion) filterGestion.value = 'all';
    if (filterTipoActivo) filterTipoActivo.value = 'all';
    if (filterEstadoOperativo) filterEstadoOperativo.value = 'all';
    if (searchInput) searchInput.value = '';

    hasSearched = false;
    currentPage = 1;
    renderTablePagination();
    updateReportButtonsState();
}

function renderTablePagination() {
    const allRows = getTableRows();
    const paginationContainer = document.getElementById('paginationContainer');
    const btnBuscar = document.getElementById('btnBuscarReporte');
    const emptyRow = document.getElementById('emptyReportRow');
    const emptyText = document.getElementById('emptyReportText');

    if (!paginationContainer) {
        if (btnBuscar) {
            // Páginas de reporte con botón Buscar (ej. Reporte de Pagos)
            if (!hasSearched) {
                allRows.forEach(r => r.style.display = 'none');
                if (emptyRow) {
                    emptyRow.style.display = '';
                    if (emptyText) emptyText.textContent = 'Seleccione los filtros deseados y presione "Buscar" para consultar los registros.';
                }
            } else {
                updateFilteredRows();
                allRows.forEach(r => r.style.display = 'none');
                filteredRows.forEach(r => r.style.display = '');
                if (emptyRow) {
                    if (filteredRows.length === 0) {
                        emptyRow.style.display = '';
                        if (emptyText) emptyText.textContent = 'No se encontraron registros de pagos que coincidan con la búsqueda.';
                    } else {
                        emptyRow.style.display = 'none';
                    }
                }
            }
        } else {
            // Páginas directas (ej. Asociados Activos/Inactivos)
            updateFilteredRows();
            allRows.forEach(r => r.style.display = 'none');
            filteredRows.forEach(r => r.style.display = '');
            if (emptyRow) {
                emptyRow.style.display = (filteredRows.length === 0) ? '' : 'none';
            }
        }
        return;
    }

    if (!hasSearched) {
        allRows.forEach(r => r.style.display = 'none');
        
        const pageStartEl = document.getElementById('pageStart');
        const pageEndEl = document.getElementById('pageEnd');
        const totalRecordsEl = document.getElementById('totalRecords');
        const pageIndicatorEl = document.getElementById('pageIndicator');
        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');

        if (pageStartEl) pageStartEl.innerText = 0;
        if (pageEndEl) pageEndEl.innerText = 0;
        if (totalRecordsEl) totalRecordsEl.innerText = 0;
        if (pageIndicatorEl) pageIndicatorEl.innerText = `Página 0 de 0`;
        if (btnPrev) btnPrev.disabled = true;
        if (btnNext) btnNext.disabled = true;
        return;
    }

    updateFilteredRows();

    const totalRecords = filteredRows.length;
    const recordsPerPageSelect = document.getElementById('recordsPerPage');
    const selectedSize = recordsPerPageSelect ? recordsPerPageSelect.value : '15';

    if (selectedSize === 'all') {
        pageSize = totalRecords;
    } else {
        pageSize = parseInt(selectedSize, 10) || 15;
    }

    const totalPages = Math.ceil(totalRecords / pageSize) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * pageSize;
    const endIdx = selectedSize === 'all' ? totalRecords : Math.min(startIdx + pageSize, totalRecords);

    // Ocultar todas las filas primero
    allRows.forEach(r => r.style.display = 'none');

    // Mostrar sólo las filas filtradas de la página actual
    filteredRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Actualizar elementos de la interfaz de paginación
    const pageStartEl = document.getElementById('pageStart');
    const pageEndEl = document.getElementById('pageEnd');
    const totalRecordsEl = document.getElementById('totalRecords');
    const pageIndicatorEl = document.getElementById('pageIndicator');
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');

    if (pageStartEl) pageStartEl.innerText = totalRecords === 0 ? 0 : startIdx + 1;
    if (pageEndEl) pageEndEl.innerText = endIdx;
    if (totalRecordsEl) totalRecordsEl.innerText = totalRecords;
    if (pageIndicatorEl) pageIndicatorEl.innerText = `Página ${currentPage} de ${totalPages}`;

    if (btnPrev) btnPrev.disabled = (currentPage <= 1);
    if (btnNext) btnNext.disabled = (currentPage >= totalPages);
}

function imprimirPlanillaAnualDirecto(cat, anio) {
    let iframe = document.getElementById('printPlanillaAnualIframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printPlanillaAnualIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }

    let selectGestion = document.getElementById('filterGestion');
    let year = anio || (selectGestion ? selectGestion.value : '');
    if (year === 'all' || !year) {
        year = new Date().getFullYear();
    }

    let routeType = 'planilla_anual_mensualidades';
    if (cat && cat.indexOf('extraordinari') !== -1) {
        routeType = 'planilla_anual_extraordinarios';
    } else if (cat && cat.indexOf('especial') !== -1) {
        routeType = 'planilla_anual_especiales';
    }

    let url = '../Controller/reporte.controller.php?type=' + routeType + '&only_print=1&anio=' + encodeURIComponent(year) + '&t=' + new Date().getTime();

    iframe.onload = function() {
        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch(e) {
                console.error("Error al lanzar la ventana de impresión:", e);
            }
        }, 300);
    };

    iframe.src = url;
}

function generateDynamicPrintHTML() {
    updateFilteredRows();
    const rowsToPrint = filteredRows.length > 0 ? filteredRows : [];

    const filterSocio = document.getElementById('filterSocio');
    const filterMes = document.getElementById('filterMes');
    const filterGestion = document.getElementById('filterGestion');

    let socioText = filterSocio && filterSocio.selectedIndex >= 0 ? filterSocio.options[filterSocio.selectedIndex].text : 'Todos los Asociados';
    let mesText = filterMes && filterMes.selectedIndex >= 0 ? filterMes.options[filterMes.selectedIndex].text : 'Todos';
    let gestionText = filterGestion && filterGestion.selectedIndex >= 0 ? filterGestion.options[filterGestion.selectedIndex].text : 'Todas';

    let isSingleActionOrSocio = (filterSocio && filterSocio.value !== 'all');
    if (!isSingleActionOrSocio && rowsToPrint.length > 0) {
        const firstSocioId = rowsToPrint[0].dataset.idSocio;
        if (firstSocioId && rowsToPrint.every(r => r.dataset.idSocio === firstSocioId)) {
            isSingleActionOrSocio = true;
        }
    }

    let recordsPerPage = 45;
    if (isSingleActionOrSocio) {
        recordsPerPage = Math.max(rowsToPrint.length, 1);
    }

    let chunks = [];
    for (let i = 0; i < rowsToPrint.length; i += recordsPerPage) {
        chunks.push(rowsToPrint.slice(i, i + recordsPerPage));
    }

    if (chunks.length === 0) {
        return '<p style="text-align:center; padding: 40px; font-size: 14px;">No se encontraron registros para los filtros seleccionados.</p>';
    }

    let html = '';
    const totalPages = chunks.length;

    const mainTableHead = document.querySelector('.data-table thead tr');
    let headerHTML = mainTableHead ? mainTableHead.innerHTML : '';

    const pageTitle = document.querySelector('.page-title');
    let titleText = pageTitle ? pageTitle.innerText.toUpperCase() : 'REPORTE DE ASOCIADOS Y PAGOS';

    chunks.forEach((chunk, pageIdx) => {
        const isLastPage = (pageIdx === totalPages - 1);
        const pageBreakClass = !isLastPage ? 'page-break' : '';

        const maxRowsInChunk = chunk.length;

        let fontSize = '10px';
        let paddingTd = '3.5px 4px';
        let paddingTh = '4px 4px';
        let lineHeight = '1.18';
        let logoHeight = '32px';
        let titleH2 = '13.5px';
        let titleH3 = '10.5px';
        let headerMarginBottom = '4px';

        if (maxRowsInChunk > 40 && maxRowsInChunk <= 55) {
            fontSize = '9px';
            paddingTd = '2.2px 3.5px';
            paddingTh = '3px 3.5px';
            lineHeight = '1.1';
            logoHeight = '28px';
            titleH2 = '12px';
            titleH3 = '9.5px';
            headerMarginBottom = '3px';
        } else if (maxRowsInChunk > 55 && maxRowsInChunk <= 75) {
            fontSize = '8.2px';
            paddingTd = '1.6px 3px';
            paddingTh = '2px 3px';
            lineHeight = '1.05';
            logoHeight = '24px';
            titleH2 = '11px';
            titleH3 = '9px';
            headerMarginBottom = '2px';
        } else if (maxRowsInChunk > 75 && maxRowsInChunk <= 95) {
            fontSize = '7.5px';
            paddingTd = '1.1px 2.5px';
            paddingTh = '1.5px 2.5px';
            lineHeight = '1.02';
            logoHeight = '22px';
            titleH2 = '10.5px';
            titleH3 = '8.5px';
            headerMarginBottom = '2px';
        } else if (maxRowsInChunk > 95) {
            fontSize = '6.8px';
            paddingTd = '0.5px 2px';
            paddingTh = '1px 2px';
            lineHeight = '1.0';
            logoHeight = '20px';
            titleH2 = '10px';
            titleH3 = '8px';
            headerMarginBottom = '1px';
        }

        html += `
            <div class="print-page-block ${pageBreakClass}">
                <div class="print-header" style="display: block !important; text-align: center; margin-bottom: ${headerMarginBottom}; border-bottom: 2px solid #27AE60; padding-bottom: 2px;">
                    <div class="print-header-top" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo" style="height: ${logoHeight}; width: auto;">
                        <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                            <h2 style="font-size: ${titleH2}; margin: 0; color: #1a252f; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1;">CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                            <h3 style="font-size: ${titleH3}; margin: 1px 0 0 0; color: #27AE60; font-weight: 600; line-height: 1.1;">${titleText}</h3>
                        </div>
                        <div style="width: 60px;"></div>
                    </div>
                    <div class="print-meta" style="font-size: ${titleH3}; display: flex; justify-content: space-between; margin-top: 2px; line-height: 1.1; color: #333;">
                        <span><strong>Asociado:</strong> ${socioText} | <strong>Mes:</strong> ${mesText} | <strong>Gestión:</strong> ${gestionText}</span>
                        <span><strong>Fecha:</strong> ${new Date().toLocaleDateString('es-ES')} | <strong>Hoja ${pageIdx + 1} de ${totalPages}</strong></span>
                    </div>
                </div>

                <table class="data-table print-table" style="font-size: ${fontSize}; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f1f5f9; font-size: ${fontSize};">${headerHTML}</tr>
                    </thead>
                    <tbody>`;

        let chunkTotalMonto = 0;

        chunk.forEach((tr, rowIdx) => {
            const globalNum = (pageIdx * recordsPerPage) + rowIdx + 1;
            let cells = Array.from(tr.cells);
            let rowHTML = '';

            let cellMontoVal = 0;
            if (cells[6]) {
                let textMonto = cells[6].innerText.replace(/,/g, '').trim();
                cellMontoVal = parseFloat(textMonto) || 0;
            }
            chunkTotalMonto += cellMontoVal;

            cells.forEach((cell, cellIdx) => {
                let cellContent = cell.innerHTML;
                if (cellIdx === 0) {
                    cellContent = globalNum;
                } else if (cellIdx === 1 || cellIdx === 2 || cellIdx === 7) {
                    cellContent = '';
                }
                let cellStyle = cell.getAttribute('style') || '';
                // Limpiar font-size y font-weight excesivos heredados de la pantalla para que los números de Monto sean mas pequeños y uniformes
                cellStyle = cellStyle.replace(/font-size:[^;]+;?/gi, '').replace(/font-weight:\s*(700|800|bold)[^;]*;?/gi, 'font-weight: 600;');
                rowHTML += `<td style="padding: ${paddingTd}; line-height: ${lineHeight}; font-size: ${fontSize}; border: 1px solid #cbd5e1; ${cellStyle}">${cellContent}</td>`;
            });
            html += `<tr style="border-bottom: 1px solid #cbd5e1 !important;">${rowHTML}</tr>`;
        });

        if (chunk.length > 0) {
            html += `
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #334155;">
                    <td colspan="6" style="text-align: right; padding: ${paddingTd}; font-weight: 700; text-transform: uppercase; font-size: ${fontSize}; color: #000;">Total Recaudado:</td>
                    <td style="text-align: right; padding: ${paddingTd}; font-weight: 800; color: #000; font-size: ${fontSize};">Bs. ${chunkTotalMonto.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td style="padding: ${paddingTd};"></td>
                </tr>`;
        }

        html += `
                    </tbody>
                </table>
            </div>`;
    });

    return html;
}

function printReport() {
    let iframe = document.getElementById('printReportIframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printReportIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }

    const printHTML = generateDynamicPrintHTML();

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title></title>
            <style>
                @page {
                    size: letter portrait;
                    margin: 0mm !important;
                }
                @page :left { margin: 0mm !important; }
                @page :right { margin: 0mm !important; }
                @page :first { margin: 0mm !important; }

                html, body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                    margin: 0 !important;
                    padding: 0.15cm 0.5cm 0.15cm 0.5cm !important;
                }
                .print-page-block {
                    box-sizing: border-box;
                    width: 100% !important;
                    page-break-after: always !important;
                    break-after: page !important;
                }
                .print-page-block:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }
                .print-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    margin-bottom: 0px !important;
                }
                .print-table th, .print-table td {
                    border: 1px solid #cbd5e1 !important;
                    color: #000000 !important;
                    white-space: nowrap !important;
                }
                .print-table th {
                    background-color: #f1f5f9 !important;
                    font-weight: 700 !important;
                    text-transform: uppercase !important;
                    padding: 3px 4px !important;
                }
            </style>
        </head>
        <body>
            ${printHTML}
        </body>
        </html>
    `);
    doc.close();

    setTimeout(function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }, 250);
}

function updateReportButtonsState() {
    const filterSocio = document.getElementById('filterSocio');
    const filterMes = document.getElementById('filterMes');
    const filterGestion = document.getElementById('filterGestion');
    const btnImprimirFiltrado = document.getElementById('btnImprimirFiltrado');
    const btnPlanillaAnual = document.getElementById('btnPlanillaAnual');
    const btnLimpiarReporte = document.getElementById('btnLimpiarReporte');

    const socioVal = filterSocio ? filterSocio.value : 'all';
    const mesVal = filterMes ? filterMes.value : 'all';
    const gestionVal = filterGestion ? filterGestion.value : 'all';

    const hasFilterSelected = (socioVal !== 'all' || mesVal !== 'all' || gestionVal !== 'all');

    if (hasSearched) {
        if (btnImprimirFiltrado) {
            btnImprimirFiltrado.disabled = false;
            btnImprimirFiltrado.style.opacity = '1';
            btnImprimirFiltrado.style.cursor = 'pointer';
        }
        if (btnLimpiarReporte) {
            btnLimpiarReporte.disabled = false;
            btnLimpiarReporte.style.opacity = '1';
            btnLimpiarReporte.style.cursor = 'pointer';
        }

        if (btnPlanillaAnual) {
            if (hasFilterSelected) {
                btnPlanillaAnual.disabled = true;
                btnPlanillaAnual.style.opacity = '0.4';
                btnPlanillaAnual.style.cursor = 'not-allowed';
            } else {
                btnPlanillaAnual.disabled = false;
                btnPlanillaAnual.style.opacity = '1';
                btnPlanillaAnual.style.cursor = 'pointer';
            }
        }
    } else {
        if (btnImprimirFiltrado) {
            btnImprimirFiltrado.disabled = true;
            btnImprimirFiltrado.style.opacity = '0.4';
            btnImprimirFiltrado.style.cursor = 'not-allowed';
        }
        if (btnLimpiarReporte) {
            btnLimpiarReporte.disabled = true;
            btnLimpiarReporte.style.opacity = '0.4';
            btnLimpiarReporte.style.cursor = 'not-allowed';
        }
        if (btnPlanillaAnual) {
            btnPlanillaAnual.disabled = false;
            btnPlanillaAnual.style.opacity = '1';
            btnPlanillaAnual.style.cursor = 'pointer';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchReporte');
    const recordsSelect = document.getElementById('recordsPerPage');
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');

    ['filterSocio', 'filterMes', 'filterGestion'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updateReportButtonsState);
        }
    });
    updateReportButtonsState();

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            renderTablePagination();
        });
    }

    if (recordsSelect) {
        recordsSelect.addEventListener('change', function() {
            currentPage = 1;
            renderTablePagination();
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderTablePagination();
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function() {
            currentPage++;
            renderTablePagination();
        });
    }

    renderTablePagination();
});

let originalDocTitle = document.title;

window.addEventListener('beforeprint', function() {
    originalDocTitle = document.title;
    document.title = "";
});

window.addEventListener('afterprint', function() {
    document.title = originalDocTitle;
});

function printAllSociosIndividualSheets() {
    const allRows = getTableRows();
    const rowsToProcess = (filteredRows && filteredRows.length > 0 && hasSearched) ? filteredRows : allRows;

    if (rowsToProcess.length === 0) {
        alert("No hay registros para imprimir.");
        return;
    }

    const groupsMap = new Map();
    rowsToProcess.forEach(tr => {
        const idSocio = tr.dataset.idSocio || '0';
        const numAccion = tr.cells[4] ? tr.cells[4].innerText.trim() : '1';
        const key = `${idSocio}_acc_${numAccion}`;

        if (!groupsMap.has(key)) {
            const apPaterno = tr.dataset.paterno || '';
            const apMaterno = tr.dataset.materno || '';
            const nombre = tr.dataset.nombre || '';
            const nombreCompleto = tr.cells[3] ? tr.cells[3].innerText.trim() : 'Asociado';
            groupsMap.set(key, {
                idSocio: idSocio,
                numAccion: numAccion,
                apPaterno: apPaterno,
                apMaterno: apMaterno,
                nombre: nombre,
                nombreCompleto: nombreCompleto,
                rows: []
            });
        }
        groupsMap.get(key).rows.push(tr);
    });

    const groups = Array.from(groupsMap.values());
    if (groups.length === 0) {
        alert("No se encontraron asociados para generar el reporte.");
        return;
    }

    // Ordenar estrictamente en orden ascendente por Apellido Paterno, Apellido Materno, Nombre y N° de Acción
    groups.sort((a, b) => {
        const patA = a.apPaterno || '';
        const patB = b.apPaterno || '';
        const resPat = patA.localeCompare(patB, 'es', { sensitivity: 'base' });
        if (resPat !== 0) return resPat;

        const matA = a.apMaterno || '';
        const matB = b.apMaterno || '';
        const resMat = matA.localeCompare(matB, 'es', { sensitivity: 'base' });
        if (resMat !== 0) return resMat;

        const nomA = a.nombre || '';
        const nomB = b.nombre || '';
        const resNom = nomA.localeCompare(nomB, 'es', { sensitivity: 'base' });
        if (resNom !== 0) return resNom;

        return (parseInt(a.numAccion, 10) || 1) - (parseInt(b.numAccion, 10) || 1);
    });

    const filterMes = document.getElementById('filterMes');
    const filterGestion = document.getElementById('filterGestion');
    let mesText = filterMes && filterMes.selectedIndex >= 0 ? filterMes.options[filterMes.selectedIndex].text : 'Todos';
    let gestionText = filterGestion && filterGestion.selectedIndex >= 0 ? filterGestion.options[filterGestion.selectedIndex].text : 'Todas';

    const mainTableHead = document.querySelector('.data-table thead tr');
    let headerHTML = mainTableHead ? mainTableHead.innerHTML : '';
    const pageTitle = document.querySelector('.page-title');
    let titleText = pageTitle ? pageTitle.innerText.toUpperCase() : 'REPORTE OFICIAL DE PAGOS';

    let html = '';
    const totalSheets = groups.length;

    groups.forEach((group, groupIdx) => {
        const isLast = (groupIdx === totalSheets - 1);
        const pageBreakClass = !isLast ? 'page-break' : '';
        const chunk = group.rows;
        const maxRowsInChunk = chunk.length;

        let fontSize = '10px';
        let paddingTd = '3.5px 4px';
        let paddingTh = '4px 4px';
        let lineHeight = '1.18';
        let logoHeight = '32px';
        let titleH2 = '13.5px';
        let titleH3 = '10.5px';
        let headerMarginBottom = '4px';

        if (maxRowsInChunk > 40 && maxRowsInChunk <= 55) {
            fontSize = '9px';
            paddingTd = '2.2px 3.5px';
            paddingTh = '3px 3.5px';
            lineHeight = '1.1';
            logoHeight = '28px';
            titleH2 = '12px';
            titleH3 = '9.5px';
            headerMarginBottom = '3px';
        } else if (maxRowsInChunk > 55 && maxRowsInChunk <= 75) {
            fontSize = '8.2px';
            paddingTd = '1.6px 3px';
            paddingTh = '2px 3px';
            lineHeight = '1.05';
            logoHeight = '24px';
            titleH2 = '11px';
            titleH3 = '9px';
            headerMarginBottom = '2px';
        } else if (maxRowsInChunk > 75 && maxRowsInChunk <= 95) {
            fontSize = '7.5px';
            paddingTd = '1.1px 2.5px';
            paddingTh = '1.5px 2.5px';
            lineHeight = '1.02';
            logoHeight = '22px';
            titleH2 = '10.5px';
            titleH3 = '8.5px';
            headerMarginBottom = '2px';
        } else if (maxRowsInChunk > 95) {
            fontSize = '6.8px';
            paddingTd = '0.5px 2px';
            paddingTh = '1px 2px';
            lineHeight = '1.0';
            logoHeight = '20px';
            titleH2 = '10px';
            titleH3 = '8px';
            headerMarginBottom = '1px';
        }

        let socioLabel = `${group.nombreCompleto} (Acción N° ${group.numAccion})`;

        html += `
            <div class="print-page-block ${pageBreakClass}">
                <div class="print-header" style="display: block !important; text-align: center; margin-bottom: ${headerMarginBottom}; border-bottom: 2px solid #27AE60; padding-bottom: 2px;">
                    <div class="print-header-top" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo" style="height: ${logoHeight}; width: auto;">
                        <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                            <h2 style="font-size: ${titleH2}; margin: 0; color: #1a252f; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1;">CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                            <h3 style="font-size: ${titleH3}; margin: 1px 0 0 0; color: #27AE60; font-weight: 600; line-height: 1.1;">${titleText}</h3>
                        </div>
                        <div style="width: 60px;"></div>
                    </div>
                    <div class="print-meta" style="font-size: ${titleH3}; display: flex; justify-content: space-between; margin-top: 2px; line-height: 1.1; color: #333;">
                        <span><strong>Asociado:</strong> ${socioLabel} | <strong>Mes:</strong> ${mesText} | <strong>Gestión:</strong> ${gestionText}</span>
                        <span><strong>Fecha:</strong> ${new Date().toLocaleDateString('es-ES')} | <strong>Hoja ${groupIdx + 1} de ${totalSheets}</strong></span>
                    </div>
                </div>

                <table class="data-table print-table" style="font-size: ${fontSize}; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f1f5f9; font-size: ${fontSize};">${headerHTML}</tr>
                    </thead>
                    <tbody>`;

        let groupTotalMonto = 0;

        chunk.forEach((tr, rowIdx) => {
            const rowNum = rowIdx + 1;
            let cells = Array.from(tr.cells);
            let rowHTML = '';

            let cellMontoVal = 0;
            if (cells[6]) {
                let textMonto = cells[6].innerText.replace(/,/g, '').trim();
                cellMontoVal = parseFloat(textMonto) || 0;
            }
            groupTotalMonto += cellMontoVal;

            cells.forEach((cell, cellIdx) => {
                let cellContent = cell.innerHTML;
                if (cellIdx === 0) {
                    cellContent = rowNum;
                } else if (cellIdx === 1 || cellIdx === 2 || cellIdx === 7) {
                    cellContent = '';
                }
                let cellStyle = cell.getAttribute('style') || '';
                cellStyle = cellStyle.replace(/font-size:[^;]+;?/gi, '').replace(/font-weight:\s*(700|800|bold)[^;]*;?/gi, 'font-weight: 600;');
                rowHTML += `<td style="padding: ${paddingTd}; line-height: ${lineHeight}; font-size: ${fontSize}; border: 1px solid #cbd5e1; ${cellStyle}">${cellContent}</td>`;
            });
            html += `<tr style="border-bottom: 1px solid #cbd5e1 !important;">${rowHTML}</tr>`;
        });

        if (chunk.length > 0) {
            html += `
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #334155;">
                    <td colspan="6" style="text-align: right; padding: ${paddingTd}; font-weight: 700; text-transform: uppercase; font-size: ${fontSize}; color: #000;">Total Recaudado:</td>
                    <td style="text-align: right; padding: ${paddingTd}; font-weight: 800; color: #000; font-size: ${fontSize};">Bs. ${groupTotalMonto.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td style="padding: ${paddingTd};"></td>
                </tr>`;
        }

        html += `
                    </tbody>
                </table>
            </div>`;
    });

    let iframe = document.getElementById('printReportIframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printReportIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title></title>
            <style>
                @page {
                    size: letter portrait;
                    margin: 0mm !important;
                }
                @page :left { margin: 0mm !important; }
                @page :right { margin: 0mm !important; }
                @page :first { margin: 0mm !important; }

                html, body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                    margin: 0 !important;
                    padding: 0.15cm 0.5cm 0.15cm 0.5cm !important;
                }
                .print-page-block {
                    box-sizing: border-box;
                    width: 100% !important;
                    page-break-after: always !important;
                    break-after: page !important;
                }
                .print-page-block:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }
                .print-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    margin-bottom: 0px !important;
                }
                .print-table th, .print-table td {
                    border: 1px solid #cbd5e1 !important;
                    color: #000000 !important;
                    white-space: nowrap !important;
                }
                .print-table th {
                    background-color: #f1f5f9 !important;
                    font-weight: 700 !important;
                    text-transform: uppercase !important;
                    padding: 3px 4px !important;
                }
            </style>
        </head>
        <body>
            ${html}
        </body>
        </html>
    `);
    doc.close();

    setTimeout(function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }, 350);
}
