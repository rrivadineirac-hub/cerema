<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query('SHOW TABLES');
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "\nTable: {$row[0]}\n";
    $desc = $db->query("DESCRIBE {$row[0]}");
    while ($col = $desc->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
}
?>
