<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Fallback for getallheaders() if not running under Apache
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// Database configuration
$db_host = '127.0.0.1';
$db_name = 'sbaveca';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de conexion a la base de datos.",
        "error" => $e->getMessage(),
        "hint" => "Asegurate de que WampServer este activo, que la base de datos '$db_name' exista y que hayas importado el archivo 'bdSbavecaScript.sql'."
    ]);
    exit;
}

// Simple JWT signature helper
define('JWT_SECRET', 'sbaveca_super_secret_key_123456');

function generate_token($userId) {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode([
        'sub' => $userId,
        'exp' => time() + 3600 * 24 // 24 hours
    ]));
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64url_encode($signature);
    return "$header.$payload.$signature";
}

function verify_token($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    list($header, $payload, $signature) = $parts;
    $validSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $validSignature = base64url_encode($validSignature);
    if ($signature !== $validSignature) return false;
    $data = json_decode(base64url_decode($payload), true);
    if (time() > $data['exp']) return false;
    return $data['sub'];
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

// Helper to send JSON responses
function send_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Simple Router logic
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
$route = str_replace($basePath, '', $requestUri);
$route = parse_url($route, PHP_URL_PATH);
$route = '/' . trim($route, '/');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle POST /auth/register
if ($route === '/auth/register' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Normalize fields (accept both Spanish/English inputs)
    $firstname = $input['firstname'] ?? $input['nombre'] ?? '';
    $lastname = $input['lastname'] ?? $input['apellido'] ?? '';
    $email = $input['email'] ?? $input['correo'] ?? '';
    $password = $input['password'] ?? $input['contrasena'] ?? '';
    $phone = $input['phone'] ?? $input['telefono'] ?? '';
    $address = $input['address'] ?? $input['direccion'] ?? '';
    $dob = $input['dob'] ?? $input['fechaNacimiento'] ?? '';

    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        send_response(["success" => false, "message" => "Faltan campos obligatorios (nombre, apellido, email, password)"], 400);
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT idUsu FROM usuario WHERE correoUsu = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        send_response(["success" => false, "message" => "El correo electronico ya esta registrado"], 400);
    }

    // Generate unique CUIL/DNI matching trigger logic (20 + 8 digits + 9)
    $cuil = '20' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '9';

    // Hash password using BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Insert into usuario
        $stmt = $pdo->prepare("INSERT INTO usuario (nombreUsu, apellidoUsu, correoUsu, contrasenaUsu, CUILUsu, telefonoUsu, estadoUsu, rolUsu) VALUES (?, ?, ?, ?, ?, ?, 'Activo', 'Cliente')");
        $stmt->execute([$firstname, $lastname, $email, $hashedPassword, $cuil, $phone]);
        $userId = $pdo->lastInsertId();

        // Trigger 'sync_usuario_to_persona' will automatically insert the persona.
        // We will update the persona with the address details now.
        $stmt = $pdo->prepare("UPDATE persona SET direccionFiscalPers = ? WHERE idUsuarioPers = ?");
        $stmt->execute([$address, $userId]);

        $pdo->commit();

        send_response([
            "success" => true,
            "message" => "Usuario registrado con exito",
            "user_id" => $userId
        ], 201);

    } catch (Exception $e) {
        $pdo->rollBack();
        send_response(["success" => false, "message" => "Error al registrar el usuario: " . $e->getMessage()], 500);
    }
}

// Handle POST /auth/login
if ($route === '/auth/login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? $input['correo'] ?? '';
    $password = $input['password'] ?? $input['contrasena'] ?? '';

    if (empty($email) || empty($password)) {
        send_response(["success" => false, "message" => "Faltan credenciales (email y password)"], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correoUsu = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && (password_verify($password, $user['contrasenaUsu']) || $password === $user['contrasenaUsu'])) {
        // Generate JWT token
        $token = generate_token($user['idUsu']);
        
        send_response([
            "authToken" => $token,
            "refreshToken" => $token, // simplify for frontend
            "expiresIn" => date('c', time() + 3600 * 24)
        ]);
    } else {
        send_response(["success" => false, "message" => "El email y/o la contrasena son incorrectos"], 401);
    }
}

// Handle GET /auth/me
if ($route === '/auth/me' && $method === 'GET') {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        $userId = verify_token($token);
        
        if ($userId) {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE idUsu = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Get additional info from persona
                $stmtPers = $pdo->prepare("SELECT * FROM persona WHERE idUsuarioPers = ?");
                $stmtPers->execute([$userId]);
                $persona = $stmtPers->fetch();

                send_response([
                    "id" => (int)$user['idUsu'],
                    "username" => explode('@', $user['correoUsu'])[0],
                    "fullname" => $user['nombreUsu'] . ' ' . $user['apellidoUsu'],
                    "email" => $user['correoUsu'],
                    "phone" => $user['telefonoUsu'],
                    "roles" => [1], // Default role index
                    "pic" => "./assets/media/avatars/blank.png",
                    "address" => [
                        "addressLine" => $persona['direccionFiscalPers'] ?? '',
                        "city" => "",
                        "state" => "",
                        "postCode" => ""
                    ]
                ]);
            }
        }
    }
    
    send_response(["success" => false, "message" => "No autorizado"], 401);
}

// 404 Route handler
send_response(["success" => false, "message" => "Ruta no encontrada"], 404);
