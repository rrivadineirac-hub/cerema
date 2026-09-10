// js/categorias_ingreso.js

const modal = document.getElementById('categoriaModal');

function closeCategoriaModal() {
    modal.style.display = 'none';
}

function openCreateModal() {
    document.getElementById('categoriaForm').reset();
    document.getElementById('id_cat_ingreso').value = '';
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('nombre_categoria').readOnly = false;
    document.getElementById('nombre_categoria').style.backgroundColor = '#fff';
    if (document.getElementById('group_gestion')) document.getElementById('group_gestion').style.display = 'block';
    document.getElementById('group_tipo').style.display = 'block';
    document.getElementById('group_descripcion').style.display = 'block';
    document.getElementById('modalTitle').innerText = 'Crear Nuevo Ingreso';
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
        document.querySelector('input[name="action"]').value = 'update_monto';
        document.getElementById('id_cat_ingreso').value = data.id_cat_ingreso;
        
        // As the name input is hidden during edit, show the name in the title
        document.getElementById('modalTitle').innerText = 'Configurar Monto: ' + data.nombre;
        
        document.getElementById('monto_sugerido').value = data.monto_sugerido;
        if (document.getElementById('group_gestion')) document.getElementById('group_gestion').style.display = 'none';
        document.getElementById('group_tipo').style.display = 'none';
        document.getElementById('group_descripcion').style.display = 'none';
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
            setTimeout(() => { location.reload(); }, 2000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving categoria:', error);
        alert('Ocurrió un error al guardar. Verifica la consola.');
    });
}

window.onclick = function(event) {
    if (event.target == modal) {
        closeCategoriaModal();
    }
}
