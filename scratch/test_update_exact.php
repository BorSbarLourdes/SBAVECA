<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    $stmt = $pdo->prepare("UPDATE usuario SET nombreUsu = ?, apellidoUsu = '-', correoUsu = ?, telefonoUsu = ?, usuarioUsu = ?, fechaNacUsu = ?, direccionUsu = ?, estadoUsu = ? WHERE idUsu = 1");
    $stmt->execute(['Luli Cingolani', 'luli@demo.com', '123456', 'luli', null, null, 'Activo']);
    echo "Update successful\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
