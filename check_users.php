<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "=== USUARIOS ===\n";
print_r($pdo->query("SELECT idUsu, nombreUsu, apellidoUsu, correoUsu, usuarioUsu, contrasenaUsu, rolUsu FROM usuario LIMIT 10")->fetchAll());
