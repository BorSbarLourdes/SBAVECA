<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sbaveca', 'root', '');
$stmt = $pdo->query("SELECT idUsu, usuarioUsu, correoUsu FROM usuario");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
