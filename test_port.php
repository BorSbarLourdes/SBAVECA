<?php
$pdo = new PDO("mysql:host=localhost;dbname=sbaveca;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "Port: " . $pdo->query("SELECT @@port")->fetchColumn() . "\n";
echo "Version: " . $pdo->query("SELECT @@version")->fetchColumn() . "\n";
echo "Comment: " . $pdo->query("SELECT @@version_comment")->fetchColumn() . "\n";
echo "Roles count: " . $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn() . "\n";
echo "Permisos count: " . $pdo->query("SELECT COUNT(*) FROM permisos")->fetchColumn() . "\n";
