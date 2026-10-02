// js/categorias_ingreso.js

const modal = document.getElementById('categoriaModal');

function closeCategoriaModal() {
    modal.style.display = 'none';
}

const monthsList = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

function calculateIncomeTotals() {
    const y1El = document.getElementById('anio_inicio');
    const y2El = document.getElementById('anio_fin');
    const m1El = document.getElementById('mes_inicio');
    const m2El = document.getElementById('mes_fin');
    const mMensualEl = document.getElementById('monto_mensual');
    
    if (!y1El || !y2El || !m1El || !m2El || !mMensualEl) return;

    const y1 = parseInt(y1El.value, 10) || new Date().getFullYear();
    const m1Str = m1El.value;
    const m1 = monthsList.indexOf(m1Str) !== -1 ? monthsList.indexOf(m1Str) : 0;

    const y2 = parseInt(y2El.value, 10) || new Date().getFullYear();
    const m2Str = m2El.value;
    const m2 = monthsList.indexOf(m2Str) !== -1 ? monthsList.indexOf(m2Str) : 11;

    const montoMensual = parseFloat(mMensualEl.value) || 0;

    const totalMonths = (y2 - y1) * 12 + (m2 - m1) + 1;
    const summaryBox = document.getElementById('calcSummaryBox');

    if (totalMonths <= 0) {
        document.getElementById('calc_total_meses').innerText = 'Periodo inválido';
        document.getElementById('calc_total_meses').style.color = '#ef4444';
        document.getElementById('calc_monto_total').innerText = 'Bs. 0.00';
        document.getElementById('monto_total').value = '0.00';
        if (summaryBox) {
            summaryBox.style.background = '#fef2f2';
            summaryBox.style.borderColor = '#fca5a5';
        }
        return;
    }

    if (summaryBox) {
        summaryBox.style.background = '#e0f2fe';
        summaryBox.style.borderColor = '#bae6fd';
    }

    document.getElementById('calc_total_meses').innerText = `${totalMonths} Meses`;
    document.getElementById('calc_total_meses').style.color = '#0284c7';

    const montoTotal = totalMonths * montoMensual;
    document.getElementById('calc_monto_total').innerText = `Bs. ${montoTotal.toFixed(2)}`;
    document.getElementById('monto_total').value = montoTotal.toFixed(2);
}

function openCreateModal() {
    document.getElementById('categoriaForm').reset();
    document.getElementById('id_cat_ingreso').value = '';
    document.getElementById('form_action').value = 'create';
    document.getElementById('nombre_categoria').readOnly = false;
    document.getElementById('modalTitle').innerText = 'Crear Nuevo Ingreso';

    const currYear = new Date().getFullYear();
    document.getElementById('anio_inicio').value = currYear;
    document.getElementById('mes_inicio').value = 'Enero';
    document.getElementById('anio_fin').value = currYear;
    document.getElementById('mes_fin').value = 'Diciembre';
    document.getElementById('monto_mensual').value = '';

    calculateIncomeTotals();
    modal.style.display = 'block';
}

function editCategoria(id) {
    fetch(`../Controller/categorias_ingreso.controller.php?action=get&id_cat_ingreso=${id}`)
    .then(response => response.json())
    .then(data => {
        if(data.status === 'error') {
            alert(data.message);
            return;
        }
        
        document.getElementById('categoriaForm').reset();
        document.getElementById('form_action').value = 'update';
        document.getElementById('id_cat_ingreso').value = data.id_cat_ingreso;
        
        document.getElementById('modalTitle').innerText = 'Editar Ingreso: ' + data.nombre;
        
        if (document.getElementById('id_gestion_modal')) {
            document.getElementById('id_gestion_modal').value = data.id_gestion || '';
        }
        document.getElementById('requiere_socio').value = data.requiere_socio || 1;
        document.getElementById('nombre_categoria').value = data.nombre || '';
        
        const currYear = new Date().getFullYear();
        document.getElementById('anio_inicio').value = data.anio_inicio || currYear;
        document.getElementById('mes_inicio').value = data.mes_inicio || 'Enero';
        document.getElementById('anio_fin').value = data.anio_fin || currYear;
        document.getElementById('mes_fin').value = data.mes_fin || 'Diciembre';
        
        document.getElementById('monto_mensual').value = data.monto_mensual > 0 ? data.monto_mensual : data.monto_sugerido;
        
        calculateIncomeTotals();
        modal.style.display = 'block';
    })
    .catch(error => {
        console.error('Error fetching categoria:', error);
        alert('No se pudo obtener la información de la categoría.');
    });
}

function saveCategoria() {
    const form = document.getElementById('categoriaForm');
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const y1 = parseInt(document.getElementById('anio_inicio').value, 10) || new Date().getFullYear();
    const m1Str = document.getElementById('mes_inicio').value;
    const m1 = monthsList.indexOf(m1Str);
    const y2 = parseInt(document.getElementById('anio_fin').value, 10) || new Date().getFullYear();
    const m2Str = document.getElementById('mes_fin').value;
    const m2 = monthsList.indexOf(m2Str);

    const totalMonths = (y2 - y1) * 12 + (m2 - m1) + 1;
    if (totalMonths <= 0) {
        alert('La fecha de fin debe ser posterior o igual a la fecha de inicio.');
        return;
    }

    const formData = new FormData(form);

    fetch('../Controller/categorias_ingreso.controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            closeCategoriaModal();
            showToast(data.message);
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving categoria:', error);
        alert('Ocurrió un error al guardar. Verifica la consola.');
    });
}

function deleteCategoria(id, name) {
    if (confirm(`¿Estás seguro de eliminar el ingreso "${name}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id_cat_ingreso', id);

        fetch('../Controller/categorias_ingreso.controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message);
                setTimeout(() => { location.reload(); }, 1200);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting categoria:', error);
            alert('Ocurrió un error al eliminar.');
        });
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('show'); }, 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.remove(); }, 400);
    }, 3000);
}

window.onclick = function(event) {
    if (event.target == modal) {
        closeCategoriaModal();
    }
}
