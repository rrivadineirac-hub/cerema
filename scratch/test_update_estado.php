<?php
// scratch/test_update_estado.php

require_once 'c:/xampp/htdocs/cerema/Config/database.php';
require_once 'c:/xampp/htdocs/cerema/Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

echo "=== INSPECCIONAR COLUMNA 'estado' EN LA TABLA 'asociados' ===\n";
$stmtCol = $db->query("SHOW COLUMNS FROM asociados LIKE 'estado'");
$colInfo = $stmtCol->fetch(PDO::FETCH_ASSOC);
print_r($colInfo);

$socioModel = new SocioModel($db);
$socio9 = $socioModel->getById(9); // Reina Rosmery Arce Velasco (SOC-09 from user screenshot)
echo "\nSocio ID 9 actual: Nombre: {$socio9['nombre']} | Estado: '{$socio9['estado']}'\n";

// Probar actualizar a 'Inactivo'
$socio9['estado'] = 'Inactivo';
$res1 = $socioModel->update($socio9);
$socio9After1 = $socioModel->getById(9);
echo "Resultado update Inactivo: " . ($res1 ? "OK" : "ERROR") . " | Estado en BD: '{$socio9After1['estado']}'\n";

// Probar actualizar a 'Pasivo'
$socio9['estado'] = 'Pasivo';
$res2 = $socioModel->update($socio9);
$socio9After2 = $socioModel->getById(9);
echo "Resultado update Pasivo: " . ($res2 ? "OK" : "ERROR") . " | Estado en BD: '{$socio9After2['estado']}'\n";

// Restaurar a 'Activo'
$socio9['estado'] = 'Activo';
$socioModel->update($socio9);
echo "Restaurado a Activo OK.\n";
