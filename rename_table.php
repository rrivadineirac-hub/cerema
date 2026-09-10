<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec('RENAME TABLE socios TO asociados');
    echo 'Table renamed successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
