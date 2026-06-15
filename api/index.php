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

    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($dob)) {
        send_response(["success" => false, "message" => "Faltan campos obligatorios (nombre, apellido, email, password, fecha de nacimiento)"], 400);
    }

    // Validate age range (between 14 and 105 years old based on current year)
    $birthDate = strtotime($dob);
    if (!$birthDate) {
        send_response(["success" => false, "message" => "Formato de fecha de nacimiento invalido"], 400);
    }
    $birthYear = (int)date('Y', $birthDate);
    $currentYear = (int)date('Y');
    $age = $currentYear - $birthYear;
    if ($age < 14 || $age > 105) {
        send_response(["success" => false, "message" => "No cumple con el limite de edad"], 400);
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT idUsu FROM usuario WHERE correoUsu = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        send_response(["success" => false, "message" => "El correo electronico ya esta registrado"], 400);
    }

    // Check or generate unique username
    $username = $input['username'] ?? $input['usuario'] ?? '';
    if (!empty($username)) {
        $username = preg_replace('/[^a-zA-Z0-9_\.]/', '', $username);
        $stmt = $pdo->prepare("SELECT idUsu FROM usuario WHERE usuarioUsu = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            send_response(["success" => false, "message" => "El nombre de usuario ya esta registrado"], 400);
        }
    } else {
        $username = explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9_\.]/', '', $username);
        $baseUsername = $username;
        $counter = 1;
        do {
            $stmt = $pdo->prepare("SELECT idUsu FROM usuario WHERE usuarioUsu = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetch();
            if ($exists) {
                $username = $baseUsername . $counter;
                $counter++;
            }
        } while ($exists);
    }

    // Generate unique CUIL/DNI matching trigger logic (20 + 8 digits + 9)
    $cuil = '20' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '9';

    // Hash password using BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // Insert into usuario
        $stmt = $pdo->prepare("INSERT INTO usuario (nombreUsu, apellidoUsu, correoUsu, usuarioUsu, contrasenaUsu, CUILUsu, telefonoUsu, estadoUsu, rolUsu) VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', 'Cliente')");
        $stmt->execute([$firstname, $lastname, $email, $username, $hashedPassword, $cuil, $phone]);
        $userId = $pdo->lastInsertId();

        // Trigger 'sync_usuario_to_persona' will automatically insert the persona.
        // We will update the persona with the address details now.
        $stmt = $pdo->prepare("UPDATE persona SET direccionFiscalPers = ? WHERE idUsuarioPers = ?");
        $stmt->execute([$address, $userId]);

        $pdo->commit();

        send_response([
            "success" => true,
            "message" => "Usuario registrado con exito",
            "user_id" => $userId,
            "username" => $username
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

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correoUsu = ? OR SUBSTRING_INDEX(correoUsu, '@', 1) = ? OR nombreUsu = ?");
    $stmt->execute([$email, $email, $email]);
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

// Handle POST /auth/google
if ($route === '/auth/google' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accessToken = $input['accessToken'] ?? '';

    if (empty($accessToken)) {
        send_response(["success" => false, "message" => "Falta el token de acceso de Google"], 400);
    }

    // Call Google UserInfo API to verify token and get user details
    $url = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . urlencode($accessToken);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        send_response(["success" => false, "message" => "Token de Google invalido o expirado"], 401);
    }

    $googleUser = json_decode($response, true);
    $email = $googleUser['email'] ?? '';
    $firstname = $googleUser['given_name'] ?? $googleUser['name'] ?? 'Google';
    $lastname = $googleUser['family_name'] ?? 'Usuario';
    
    if (empty($email)) {
        send_response(["success" => false, "message" => "No se pudo obtener el correo electronico de Google"], 400);
    }

    // Check if email already exists in usuario
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correoUsu = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Generate unique username (usuarioUsu)
        $baseUsername = strtolower($firstname . substr($lastname, 0, 1));
        $username = $baseUsername;
        $counter = 1;
        do {
            $stmt = $pdo->prepare("SELECT idUsu FROM usuario WHERE usuarioUsu = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetch();
            if ($exists) {
                $username = $baseUsername . $counter;
                $counter++;
            }
        } while ($exists);

        // Generate unique CUIL/DNI (20 + 8 digits + 9)
        $cuil = '20' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '9';
        // Set a random password for Google-registered users
        $randomPassword = bin2hex(random_bytes(16)) . '!1a';
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
        $phone = '';
        $address = '';

        try {
            $pdo->beginTransaction();

            // Insert into usuario
            $stmt = $pdo->prepare("INSERT INTO usuario (nombreUsu, apellidoUsu, correoUsu, usuarioUsu, contrasenaUsu, CUILUsu, telefonoUsu, estadoUsu, rolUsu) VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', 'Cliente')");
            $stmt->execute([$firstname, $lastname, $email, $username, $hashedPassword, $cuil, $phone]);
            $userId = $pdo->lastInsertId();

            $pdo->commit();

            // Fetch the newly created user
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE idUsu = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

        } catch (Exception $e) {
            $pdo->rollBack();
            send_response(["success" => false, "message" => "Error al registrar usuario de Google: " . $e->getMessage()], 500);
        }
    }

    // Generate JWT token for the user
    $token = generate_token($user['idUsu']);
    
    send_response([
        "authToken" => $token,
        "refreshToken" => $token,
        "expiresIn" => date('c', time() + 3600 * 24)
    ]);
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
                    "username" => $user['usuarioUsu'] ?? explode('@', $user['correoUsu'])[0],
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

// Helper function to save base64 uploaded image as physical file in /uploads
function save_base64_image($base64_string) {
    if (empty($base64_string)) {
        return '';
    }
    // If it's already a saved URL or path, keep it
    if (!preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
        return $base64_string;
    }
    
    $output_dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0755, true);
    }
    
    $ext = strtolower($type[1]);
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $ext = 'jpg';
    }
    
    $data = substr($base64_string, strpos($base64_string, ',') + 1);
    $data = base64_decode($data);
    if ($data === false) {
        return '';
    }
    
    $fileName = uniqid() . '.' . $ext;
    $filePath = $output_dir . '/' . $fileName;
    
    if (file_put_contents($filePath, $data)) {
        return 'uploads/' . $fileName;
    }
    
    return '';
}

// Handle CRUD for /insumos (Stock)
if ($route === '/insumos') {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT i.idIns as id, i.nombreIns as name, i.categoriaIns as category, 
                                    i.unidadMedidaIns as unit, i.precioCompraIns as costPrice, 
                                    i.stockMinimoIns as minThreshold, i.imagenBlobIns as image, 
                                    i.proveedorIdPro as supplierId, inv.stockActualInv as quantity 
                             FROM insumo i 
                             LEFT JOIN inventario inv ON i.idIns = inv.insumoIdInv 
                             WHERE i.estadoIns = 'Activo'");
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            $item['quantity'] = $item['quantity'] !== null ? (int)$item['quantity'] : 0;
            $item['minThreshold'] = (int)$item['minThreshold'];
            $item['costPrice'] = (float)$item['costPrice'];
            $item['supplierId'] = (int)$item['supplierId'];
            
            $cat = $item['category'];
            if ($cat === 'Producto Terminado') {
                $item['category'] = 'producto';
            } else if ($cat === 'Utensilio') {
                $item['category'] = 'utensilio';
            } else {
                $item['category'] = 'ingrediente';
            }
        }
        send_response($items);
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $name = $input['name'] ?? '';
        $category = $input['category'] ?? 'ingrediente';
        $unit = $input['unit'] ?? '';
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
        $minThreshold = isset($input['minThreshold']) ? (int)$input['minThreshold'] : 0;
        $costPrice = isset($input['costPrice']) ? (float)$input['costPrice'] : 0.0;
        $supplierId = isset($input['supplierId']) ? (int)$input['supplierId'] : 0;
        $image = $input['image'] ?? '';

        if (empty($name)) {
            send_response(["success" => false, "message" => "El nombre es obligatorio"], 400);
        }
        
        if ($supplierId <= 0) {
            $stmt = $pdo->query("SELECT idPro FROM proveedor LIMIT 1");
            $defaultSup = $stmt->fetch();
            $supplierId = $defaultSup ? (int)$defaultSup['idPro'] : 1;
        }
        
        $mysqlCategory = 'Ingrediente';
        if ($category === 'producto') {
            $mysqlCategory = 'Producto Terminado';
        } else if ($category === 'utensilio') {
            $mysqlCategory = 'Utensilio';
        }
        
        $imagePath = save_base64_image($image);

        try {
            $pdo->beginTransaction();
            
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE insumo SET nombreIns = ?, categoriaIns = ?, unidadMedidaIns = ?, precioCompraIns = ?, stockMinimoIns = ?, imagenBlobIns = ?, proveedorIdPro = ? WHERE idIns = ?");
                $stmt->execute([$name, $mysqlCategory, $unit, $costPrice, $minThreshold, $imagePath, $supplierId, $id]);
                
                $stmtCheck = $pdo->prepare("SELECT idInv FROM inventario WHERE insumoIdInv = ?");
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetch()) {
                    $stmtInv = $pdo->prepare("UPDATE inventario SET stockActualInv = ?, stockMinimoInv = ?, unidadMedidaInv = ? WHERE insumoIdInv = ?");
                    $stmtInv->execute([$quantity, $minThreshold, $unit, $id]);
                } else {
                    $stmtInv = $pdo->prepare("INSERT INTO inventario (insumoIdInv, stockActualInv, stockMinimoInv, unidadMedidaInv) VALUES (?, ?, ?, ?)");
                    $stmtInv->execute([$id, $quantity, $minThreshold, $unit]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO insumo (nombreIns, categoriaIns, unidadMedidaIns, precioCompraIns, stockMinimoIns, imagenBlobIns, proveedorIdPro, estadoIns) VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
                $stmt->execute([$name, $mysqlCategory, $unit, $costPrice, $minThreshold, $imagePath, $supplierId]);
                $id = $pdo->lastInsertId();
                
                $stmtInv = $pdo->prepare("INSERT INTO inventario (insumoIdInv, stockActualInv, stockMinimoInv, unidadMedidaInv) VALUES (?, ?, ?, ?)");
                $stmtInv->execute([$id, $quantity, $minThreshold, $unit]);
            }
            
            $pdo->commit();
            send_response(["success" => true, "id" => (int)$id, "image" => $imagePath]);
        } catch (Exception $e) {
            $pdo->rollBack();
            send_response(["success" => false, "message" => "Error al guardar el insumo: " . $e->getMessage()], 500);
        }
    }
    
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            send_response(["success" => false, "message" => "ID no proporcionado"], 400);
        }
        
        try {
            // Check if insumo is used in active recipes
            $stmtCheck = $pdo->prepare("SELECT r.nombreReceta FROM receta_ingrediente ri JOIN recetas r ON ri.recetaIdRecIng = r.idReceta WHERE ri.insumoIdRecIng = ? AND r.estadoReceta = 'Activa'");
            $stmtCheck->execute([$id]);
            $usedInRecipes = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($usedInRecipes)) {
                $recipeNames = implode(', ', $usedInRecipes);
                send_response([
                    "success" => false, 
                    "message" => "No se puede eliminar el insumo porque esta siendo utilizado en las siguientes recetas activas: $recipeNames. Por favor, remueva el insumo de esas recetas antes de continuar."
                ], 400);
            }
            
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE insumo SET estadoIns = 'Inactivo' WHERE idIns = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            send_response(["success" => true]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            send_response(["success" => false, "message" => "Error al eliminar el insumo: " . $e->getMessage()], 500);
        }
    }
}

// Handle CRUD for /proveedores (Suppliers)
if ($route === '/proveedores') {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT idPro as id, nombrePro as name, emailPro as contact, 
                                    telefonoPro as phone, direccionPro as catalogStr 
                             FROM proveedor");
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            
            $str = $item['catalogStr'];
            if (strpos($str, 'Catalog: ') === 0) {
                $catStr = substr($str, 9);
                $item['catalog'] = $catStr ? array_map('trim', explode(',', $catStr)) : [];
            } else {
                $item['catalog'] = [];
            }
            unset($item['catalogStr']);
        }
        send_response($items);
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $name = $input['name'] ?? '';
        $contact = $input['contact'] ?? '';
        $phone = $input['phone'] ?? '';
        $catalog = $input['catalog'] ?? [];
        
        if (empty($name) || empty($contact) || empty($phone)) {
            send_response(["success" => false, "message" => "Faltan campos obligatorios"], 400);
        }
        
        $catalogStr = 'Catalog: ' . implode(', ', $catalog);
        
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE proveedor SET nombrePro = ?, emailPro = ?, telefonoPro = ?, direccionPro = ? WHERE idPro = ?");
                $stmt->execute([$name, $contact, $phone, $catalogStr, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO proveedor (nombrePro, emailPro, telefonoPro, direccionPro, CUITPro, ciudadPro, provinciaPro) VALUES (?, ?, ?, ?, '00-00000000-0', 'Desconocida', 'Desconocida')");
                $stmt->execute([$name, $contact, $phone, $catalogStr]);
                $id = $pdo->lastInsertId();
            }
            send_response(["success" => true, "id" => (int)$id]);
        } catch (Exception $e) {
            send_response(["success" => false, "message" => "Error al guardar el proveedor: " . $e->getMessage()], 500);
        }
    }
    
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            send_response(["success" => false, "message" => "ID no proporcionado"], 400);
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM proveedor WHERE idPro = ?");
            $stmt->execute([$id]);
            send_response(["success" => true]);
        } catch (Exception $e) {
            // Check for foreign key constraint violation (SQLSTATE 23000)
            if ($e->getCode() == '23000') {
                send_response([
                    "success" => false, 
                    "message" => "No se puede eliminar el proveedor porque tiene insumos asociados en el inventario. Por favor, elimine o reasigne los insumos antes de continuar."
                ], 400);
            }
            send_response(["success" => false, "message" => "Error al eliminar el proveedor: " . $e->getMessage()], 500);
        }
    }
}

// Handle CRUD for /recetas (Recipes)
if ($route === '/recetas') {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT idReceta as id, nombreReceta as name, instruccionesReceta as instructions, 
                                    margenGananciaReceta as marginPercent, descripcionReceta as image 
                             FROM recetas 
                             WHERE estadoReceta = 'Activa'");
        $recipes = $stmt->fetchAll();
        
        foreach ($recipes as &$r) {
            $r['id'] = (int)$r['id'];
            $r['marginPercent'] = (float)$r['marginPercent'];
            
            $stmtIng = $pdo->prepare("SELECT insumoIdRecIng as stockId, cantidadNecesaria as quantity 
                                      FROM receta_ingrediente 
                                      WHERE recetaIdRecIng = ?");
            $stmtIng->execute([$r['id']]);
            $ingredients = $stmtIng->fetchAll();
            
            foreach ($ingredients as &$ing) {
                $ing['stockId'] = (int)$ing['stockId'];
                $ing['quantity'] = (float)$ing['quantity'];
            }
            $r['ingredients'] = $ingredients;
        }
        send_response($recipes);
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $name = $input['name'] ?? '';
        $instructions = $input['instructions'] ?? '';
        $marginPercent = isset($input['marginPercent']) ? (float)$input['marginPercent'] : 50.0;
        $image = $input['image'] ?? '';
        $ingredients = $input['ingredients'] ?? [];
        
        if (empty($name) || empty($ingredients)) {
            send_response(["success" => false, "message" => "Nombre e ingredientes son obligatorios"], 400);
        }
        
        $imagePath = save_base64_image($image);
        
        try {
            $pdo->beginTransaction();
            
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE recetas SET nombreReceta = ?, instruccionesReceta = ?, margenGananciaReceta = ?, descripcionReceta = ? WHERE idReceta = ?");
                $stmt->execute([$name, $instructions, $marginPercent, $imagePath, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO recetas (nombreReceta, instruccionesReceta, margenGananciaReceta, descripcionReceta, estadoReceta) VALUES (?, ?, ?, ?, 'Activa')");
                $stmt->execute([$name, $instructions, $marginPercent, $imagePath]);
                $id = $pdo->lastInsertId();
            }
            
            $stmtDel = $pdo->prepare("DELETE FROM receta_ingrediente WHERE recetaIdRecIng = ?");
            $stmtDel->execute([$id]);
            
            foreach ($ingredients as $ing) {
                $stockId = (int)$ing['stockId'];
                $qty = (float)$ing['quantity'];
                
                $stmtIns = $pdo->prepare("SELECT unidadMedidaIns, precioCompraIns FROM insumo WHERE idIns = ?");
                $stmtIns->execute([$stockId]);
                $insDetails = $stmtIns->fetch();
                $unit = $insDetails ? $insDetails['unidadMedidaIns'] : 'unidades';
                $cost = $insDetails ? (float)$insDetails['precioCompraIns'] : 0.0;
                $costProportional = $qty * $cost;
                
                $stmtAdd = $pdo->prepare("INSERT INTO receta_ingrediente (recetaIdRecIng, insumoIdRecIng, cantidadNecesaria, unidadMedidaRecIng, costoProporcional) VALUES (?, ?, ?, ?, ?)");
                $stmtAdd->execute([$id, $stockId, $qty, $unit, $costProportional]);
            }
            
            $pdo->commit();
            send_response(["success" => true, "id" => (int)$id, "image" => $imagePath]);
        } catch (Exception $e) {
            $pdo->rollBack();
            send_response(["success" => false, "message" => "Error al guardar la receta: " . $e->getMessage()], 500);
        }
    }
    
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            send_response(["success" => false, "message" => "ID no proporcionado"], 400);
        }
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE recetas SET estadoReceta = 'Inactiva' WHERE idReceta = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            send_response(["success" => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            send_response(["success" => false, "message" => "Error al eliminar la receta: " . $e->getMessage()], 500);
        }
    }
}

// 404 Route handler
send_response(["success" => false, "message" => "Ruta no encontrada"], 404);
