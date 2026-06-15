<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

try {
    $adminRole = $pdo->query("SELECT id FROM roles WHERE id = 1 OR name = 'Administrador'")->fetch();
    if ($adminRole) {
        $rolId = (int)$adminRole['id'];
        $pdo->exec("INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) VALUES (1, $rolId), (2, $rolId), (3, $rolId)");
        $pdo->exec("UPDATE usuario SET rolUsu = 'Administrador' WHERE idUsu IN (1, 2, 3)");
        echo "Successfully assigned Administrador role (ID $rolId) to users 1, 2, and 3!\n";
    } else {
        echo "Administrador role not found in database.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
