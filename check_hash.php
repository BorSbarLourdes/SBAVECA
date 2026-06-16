<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "");
$stmt = $pdo->query("SELECT * FROM usuario WHERE correoUsu = 'raymonnie27@gmail.com'");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
