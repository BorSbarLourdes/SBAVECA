<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", 'root', '');
try {
    $pdo->exec("ALTER TABLE rol_permiso ADD COLUMN puede_ver TINYINT(1) DEFAULT 1, ADD COLUMN puede_crear TINYINT(1) DEFAULT 1, ADD COLUMN puede_modificar TINYINT(1) DEFAULT 1, ADD COLUMN puede_eliminar TINYINT(1) DEFAULT 1");
    echo "Agregadas columnas a rol_permiso";
} catch(Exception $e) {
    echo $e->getMessage();
}
