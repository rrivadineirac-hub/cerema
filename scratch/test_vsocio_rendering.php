<?php
// scratch/test_vsocio_rendering.php

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre_usuario'] = 'Admin Test';
$_SESSION['rol'] = 'Presidente';

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';

$database = new Database();
$db = $database->getConnection();
$socioModel = new SocioModel($db);

$socios = $socioModel->getAll();

ob_start();
require 'c:/xampp/htdocs/cerema/View/vsocio.php';
$html = ob_get_clean();

echo "--- Verificación de renderizado de la Lista de Socios ---\n";
if (strpos($html, 'Bs. 500.00') !== false) {
    echo "✔ Fila con cuota inicial 500.00 muestra 'Bs. 500.00' correctamente!\n";
}

if (strpos($html, 'Bs. 0.00') === false) {
    echo "✔ 'Bs. 0.00' YA NO APARECE en la tabla (reemplazado por '-')!\n";
} else {
    echo "❌ Advertencia: Todavía se encontró 'Bs. 0.00' en la tabla.\n";
}

if (strpos($html, '<td style=\'text-align: center; color: #94a3b8; font-weight: 500;\'>-</td>') !== false || strpos($html, '<td style="text-align: center; color: #94a3b8; font-weight: 500;">-</td>') !== false) {
    echo "✔ Las celdas sin cuota inicial muestran '-' limpiamente!\n";
}
