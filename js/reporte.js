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

    const recordsPerPage = 45;
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

        html += `
            <div class="print-page-block ${pageBreakClass}">
                <div class="print-header">
                    <div class="print-header-top">
                        <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo">
                        <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                            <h2>CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                            <h3>${titleText}</h3>
                        </div>
                        <div style="width: 60px;"></div>
                    </div>
                    <div class="print-meta" style="font-size: 10.5px; display: flex; justify-content: space-between; margin-top: 4px;">
                        <span><strong>Asociado:</strong> ${socioText} | <strong>Mes:</strong> ${mesText} | <strong>Gestión:</strong> ${gestionText}</span>
                        <span><strong>Fecha:</strong> ${new Date().toLocaleDateString('es-ES')} | <strong>Hoja ${pageIdx + 1} de ${totalPages}</strong></span>
                    </div>
                </div>

                <table class="data-table print-table" style="font-size: 11px; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>${headerHTML}</tr>
                    </thead>
                    <tbody>`;

        chunk.forEach((tr, rowIdx) => {
            const globalNum = (pageIdx * recordsPerPage) + rowIdx + 1;
            let cells = Array.from(tr.cells);
            let rowHTML = '';
            cells.forEach((cell, cellIdx) => {
                let cellContent = cell.innerHTML;
                if (cellIdx === 0) {
                    cellContent = globalNum;
                }
                let cellStyle = cell.getAttribute('style') || '';
                rowHTML += `<td style="${cellStyle}">${cellContent}</td>`;
            });
            html += `<tr style="border-bottom: 1px solid #cbd5e1 !important;">${rowHTML}</tr>`;
        });

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
                    padding: 0.25cm 0.6cm 0.25cm 0.6cm !important;
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
                .print-header {
                    display: block !important;
                    text-align: center;
                    margin-bottom: 4px;
                    border-bottom: 2px solid #27AE60;
                    padding-bottom: 2px;
                }
                .print-header-top {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 2px;
                }
                .print-logo {
                    height: 32px;
                    width: auto;
                }
                .print-title-group h2 {
                    font-size: 13.5px;
                    margin: 0;
                    color: #1a252f;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    line-height: 1.1;
                }
                .print-title-group h3 {
                    font-size: 10.5px;
                    margin: 1px 0 0 0;
                    color: #27AE60;
                    font-weight: 600;
                    line-height: 1.1;
                }
                .print-meta {
                    font-size: 9.5px;
                    color: #333;
                    display: flex;
                    justify-content: space-between;
                    margin-top: 2px;
                    line-height: 1.1;
                }
                .print-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    font-size: 10px !important;
                    margin-bottom: 0px !important;
                }
                .print-table th, .print-table td {
                    border: 1px solid #cbd5e1 !important;
                    padding: 3.8px 4px !important;
                    color: #000000 !important;
                    white-space: nowrap !important;
                    line-height: 1.18 !important;
                    font-size: 10px !important;
                }
                .print-table th {
                    background-color: #f1f5f9 !important;
                    font-weight: 700 !important;
                    text-transform: uppercase !important;
                    font-size: 10px !important;
                    padding: 4px 4px !important;
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
