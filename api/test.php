<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sbaveca', 'root', '');
try {
    $pdo->exec('ALTER TABLE receta_ingrediente ADD COLUMN pesoPorUnidadRecIng DECIMAL(10,3) NULL AFTER unidadMedidaRecIng');
    echo "Column added to receta_ingrediente\n";
} catch (Exception $e) {
    echo "Error receta_ingrediente: " . $e->getMessage() . "\n";
}
