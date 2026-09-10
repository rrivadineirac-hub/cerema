<?php
// scratch/check_all_estados.php

require_once 'c:/xampp/htdocs/cerema/Config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "=== VALORES DISTINTOS EN LA COLUMNA 'estado' DE 'asociados' ===\n";
$stmt = $db->query("SELECT DISTINCT estado, COUNT(*) as cantidad FROM asociados GROUP BY estado");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
