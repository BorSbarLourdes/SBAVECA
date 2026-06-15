<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", 'root', '');
$stmt = $pdo->query("SELECT idUsu, nombreUsu, rolUsu FROM usuario WHERE nombreUsu LIKE '%Lourdes%'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($user);

$stmt = $pdo->prepare("SELECT idRol FROM usuario_rol WHERE idUsu = ?");
$stmt->execute([$user['idUsu']]);
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $pdo->prepare("SELECT idDashPer FROM dashboard_permisos");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->prepare("SELECT idDashPer FROM rol_permiso WHERE idRol = 1");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
