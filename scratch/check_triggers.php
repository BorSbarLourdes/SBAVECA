<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->query("SHOW TRIGGERS LIKE 'usuario';");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
