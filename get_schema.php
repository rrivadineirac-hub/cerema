<?php
require_once 'Config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("DESCRIBE asociados");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
