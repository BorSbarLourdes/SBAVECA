<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=sbaveca;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$userId = 1;
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE idUsu = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$firstname = $user['nombreUsu'];
$lastname = $user['apellidoUsu'];
if (($lastname === '-' || empty($lastname)) && strpos($firstname, ' ') !== false) {
    $parts = explode(' ', $firstname);
    $firstname = $parts[0];
    $lastname = implode(' ', array_slice($parts, 1));
} else if ($lastname === '-') {
    $lastname = '';
}

print_r([
    "id" => (int)$user['idUsu'],
    "username" => $user['usuarioUsu'] ?? explode('@', $user['correoUsu'])[0],
    "fullname" => trim($firstname . ' ' . $lastname),
    "firstname" => $firstname,
    "lastname" => $lastname,
    "email" => $user['correoUsu'],
    "phone" => $user['telefonoUsu']
]);
?>
