<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "");
$stmt = $pdo->query("SHOW TABLES");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
$stmt2 = $pdo->query("SELECT * FROM modulos");
if($stmt2) {
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
} else {
    $stmt3 = $pdo->query("SELECT * FROM modulo");
    if($stmt3) print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));
}
?>
