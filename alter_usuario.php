<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "");
try {
    $pdo->exec("ALTER TABLE usuario ADD COLUMN direccionUsu JSON NULL");
    $pdo->exec("ALTER TABLE usuario ADD COLUMN fechaNacUsu DATE NULL");
    echo "Columns added";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
