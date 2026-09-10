<?php
require_once 'Config/database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "Error conectando a la base de datos: " . $database->error . "\n";
    exit(1);
}

try {
    $check = $db->query("SHOW COLUMNS FROM sueldos LIKE 'mes'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE sueldos ADD COLUMN mes VARCHAR(50) DEFAULT NULL AFTER cargo");
        echo "Columna 'mes' agregada a la tabla sueldos.\n";
    } else {
        echo "La columna 'mes' ya existe.\n";
    }

    // Actualizar registros donde mes sea NULL
    $stmt = $db->query("SELECT id_sueldo, fecha_pago FROM sueldos WHERE mes IS NULL OR mes = ''");
    $mesesEsp = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    $updateStmt = $db->prepare("UPDATE sueldos SET mes = :mes WHERE id_sueldo = :id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['fecha_pago'])) {
            $mNum = (int)date('n', strtotime($row['fecha_pago']));
            $mNombre = $mesesEsp[$mNum] ?? 'Enero';
            $updateStmt->execute([':mes' => $mNombre, ':id' => $row['id_sueldo']]);
        }
    }
    echo "Actualización de mes completada.\n";
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
}
?>
