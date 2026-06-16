<?php
/**
 * SBAVECA — Script de carga de datos de prueba (Seed)
 * Ejecutar desde el navegador: http://localhost/SBAVECA/seed_data.php
 * 
 * ATENCIÓN: Solo ejecutar sobre una base de datos vacía o recién importada.
 * Este script puede fallar si ya existen registros con los mismos emails/usernames.
 */

$db_host = '127.0.0.1';
$db_name = 'sbaveca';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("<h2 style='color:red'>Error de conexión: " . $e->getMessage() . "</h2>");
}

$log = [];
$errors = [];

function ok($msg) {
    global $log;
    $log[] = "✅ $msg";
}

function fail($msg) {
    global $errors;
    $errors[] = "❌ $msg";
}

function exec_safe($pdo, $sql, $params = [], $label = '') {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        ok($label ?: "Query OK");
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        fail(($label ?: "Query") . " — " . $e->getMessage());
        return null;
    }
}

// Desactivar triggers temporalmente para insertar sin efectos secundarios inesperados
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// ============================================================
// 1. ROLES DEL DASHBOARD (dashboard_permisos y roles)
// ============================================================
echo "<h3>Verificando roles y permisos del dashboard...</h3>";

// Los roles y permisos del dashboard se crean automáticamente en api/index.php
// Pero los insertamos aquí también para garantizar que existan
$permsDefault = [
    'Acceder al Dashboard',
    'Gestionar Stock',
    'Gestionar Recetas',
    'Gestionar Pedidos',
    'Gestionar Ventas',
    'Gestionar Clientes',
    'Gestionar Roles',
    'Ver Historial de Ventas',
    'Modificar Mi Perfil',
];

foreach ($permsDefault as $pname) {
    exec_safe($pdo,
        "INSERT IGNORE INTO dashboard_permisos (nombreDashPer) VALUES (?)",
        [$pname], "Permiso: $pname"
    );
}

$rolesData = [
    ['Administrador', $permsDefault],
    ['Empleado', ['Acceder al Dashboard','Gestionar Stock','Gestionar Pedidos','Gestionar Ventas','Gestionar Clientes','Ver Historial de Ventas','Modificar Mi Perfil']],
    ['Cliente',   ['Acceder al Dashboard','Gestionar Pedidos','Modificar Mi Perfil']],
];

$roleIds = [];
foreach ($rolesData as [$rname, $rperms]) {
    $existingId = $pdo->query("SELECT idRol FROM roles WHERE nombreRol = " . $pdo->quote($rname))->fetchColumn();
    if (!$existingId) {
        $rid = exec_safe($pdo, "INSERT IGNORE INTO roles (nombreRol) VALUES (?)", [$rname], "Rol: $rname");
    } else {
        $rid = $existingId;
        ok("Rol ya existe: $rname (ID $rid)");
    }
    $roleIds[$rname] = $rid;

    foreach ($rperms as $pname) {
        $pid = $pdo->query("SELECT idDashPer FROM dashboard_permisos WHERE nombreDashPer = " . $pdo->quote($pname))->fetchColumn();
        if ($pid && $rid) {
            exec_safe($pdo,
                "INSERT IGNORE INTO rol_permiso (idRol, idDashPer, puede_ver, puede_crear, puede_modificar, puede_eliminar) VALUES (?, ?, 1, 1, 1, 1)",
                [$rid, $pid], "Asignar permiso '$pname' a '$rname'"
            );
        }
    }
}

// ============================================================
// 2. USUARIOS: Administrador + Empleados
// ============================================================
echo "<h3>Creando usuarios...</h3>";

$usuarios = [
    [
        'nombre'    => 'Admin',
        'apellido'  => 'SBAVECA',
        'correo'    => 'admin@sbaveca.com',
        'usuario'   => 'admin',
        'password'  => 'Admin123!',
        'cuil'      => '20123456789',
        'telefono'  => '3512000001',
        'rol'       => 'Administrador',
        'rolKey'    => 'Administrador',
    ],
    [
        'nombre'    => 'Laura',
        'apellido'  => 'Bordón',
        'correo'    => 'laura.bordon@sbaveca.com',
        'usuario'   => 'lbordon',
        'password'  => 'Empleado123!',
        'cuil'      => '27345678901',
        'telefono'  => '3512000002',
        'rol'       => 'Empleado',
        'rolKey'    => 'Empleado',
    ],
    [
        'nombre'    => 'Matías',
        'apellido'  => 'Sbardella',
        'correo'    => 'matias.sbardella@sbaveca.com',
        'usuario'   => 'msbardella',
        'password'  => 'Empleado123!',
        'cuil'      => '20456789012',
        'telefono'  => '3512000003',
        'rol'       => 'Empleado',
        'rolKey'    => 'Empleado',
    ],
    [
        'nombre'    => 'Valentina',
        'apellido'  => 'Cáceres',
        'correo'    => 'vale.caceres@sbaveca.com',
        'usuario'   => 'vcaceres',
        'password'  => 'Empleado123!',
        'cuil'      => '27567890123',
        'telefono'  => '3512000004',
        'rol'       => 'Empleado',
        'rolKey'    => 'Empleado',
    ],
];

$userIds = [];
foreach ($usuarios as $u) {
    $existing = $pdo->prepare("SELECT idUsu FROM usuario WHERE correoUsu = ?");
    $existing->execute([$u['correo']]);
    $existId = $existing->fetchColumn();

    if ($existId) {
        ok("Usuario ya existe: {$u['correo']} (ID $existId)");
        $userIds[$u['usuario']] = $existId;
        continue;
    }

    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $uid = exec_safe($pdo,
        "INSERT INTO usuario (nombreUsu, apellidoUsu, correoUsu, usuarioUsu, contrasenaUsu, CUILUsu, telefonoUsu, estadoUsu, rolUsu)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', ?)",
        [$u['nombre'], $u['apellido'], $u['correo'], $u['usuario'], $hash, $u['cuil'], $u['telefono'], $u['rol']],
        "Usuario: {$u['correo']}"
    );

    if ($uid && isset($roleIds[$u['rolKey']])) {
        exec_safe($pdo,
            "INSERT IGNORE INTO usuario_rol (idUsu, idRol) VALUES (?, ?)",
            [$uid, $roleIds[$u['rolKey']]],
            "Asignar rol '{$u['rolKey']}' a {$u['correo']}"
        );
    }

    $userIds[$u['usuario']] = $uid;
}

// ============================================================
// 3. PROVEEDORES
// ============================================================
echo "<h3>Creando proveedores...</h3>";

$proveedores = [
    ['Distribuidora El Molino', '30-11111111-1', '3512111001', 'contacto@elmolino.com', 'Av. Vélez Sársfield 1200', 'Córdoba', 'Córdoba'],
    ['Lácteos del Centro',      '30-22222222-2', '3512111002', 'ventas@lacteosdel.com', 'Calle Las Heras 450',     'Córdoba', 'Córdoba'],
    ['Dulces y Esencias SA',    '30-33333333-3', '3512111003', 'info@dulcesesencias.com','Bv. San Juan 880',       'Córdoba', 'Córdoba'],
    ['Empaque y Más',           '30-44444444-4', '3512111004', 'pedidos@empaquemas.com', 'Ruta 9 Km 12',           'Córdoba', 'Córdoba'],
    ['Frutas del Valle',        '30-55555555-5', '3512111005', 'frutas@delvalle.com',   'Mercado Norte Local 42', 'Córdoba', 'Córdoba'],
];

$provIds = [];
foreach ($proveedores as $p) {
    $existing = $pdo->prepare("SELECT idPro FROM proveedor WHERE emailPro = ?");
    $existing->execute([$p[3]]);
    $existId = $existing->fetchColumn();

    if ($existId) {
        ok("Proveedor ya existe: {$p[0]}");
        $provIds[] = $existId;
    } else {
        $pid = exec_safe($pdo,
            "INSERT INTO proveedor (nombrePro, CUITPro, telefonoPro, emailPro, direccionPro, ciudadPro, provinciaPro)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            $p, "Proveedor: {$p[0]}"
        );
        $provIds[] = $pid;
    }
}

// ============================================================
// 4. INSUMOS + INVENTARIO
// ============================================================
echo "<h3>Creando insumos e inventario...</h3>";

// [nombre, categoría, unidad, precio, stockMin, stockActual, proveedorIdx]
$insumos = [
    ['Harina 000',           'Ingrediente',       'kg',    85.00,  20, 150, 0],
    ['Harina 0000',          'Ingrediente',       'kg',    95.00,  15, 120, 0],
    ['Azúcar',               'Ingrediente',       'kg',    70.00,  20, 200, 0],
    ['Manteca',              'Ingrediente',       'kg',   650.00,  10,  80, 1],
    ['Leche entera',         'Ingrediente',       'lt',   180.00,  20, 100, 1],
    ['Crema de leche',       'Ingrediente',       'lt',   420.00,   5,  40, 1],
    ['Huevos',               'Ingrediente',       'unidad',  8.50, 30, 300, 1],
    ['Cacao amargo',         'Ingrediente',       'kg',   900.00,   5,  30, 2],
    ['Dulce de leche',       'Ingrediente',       'kg',   450.00,  10,  60, 2],
    ['Vainilla esencia',     'Ingrediente',       'lt',   350.00,   2,  10, 2],
    ['Levadura seca',        'Ingrediente',       'kg',   800.00,   2,  15, 0],
    ['Sal fina',             'Ingrediente',       'kg',    45.00,   5,  50, 0],
    ['Polvo de hornear',     'Ingrediente',       'kg',   600.00,   2,  12, 0],
    ['Chocolate cobertura',  'Ingrediente',       'kg',  1800.00,   5,  25, 2],
    ['Frutillas frescas',    'Ingrediente',       'kg',   850.00,   3,  20, 4],
    ['Durazno en almíbar',   'Ingrediente',       'kg',   380.00,   4,  30, 4],
    ['Nueces',               'Ingrediente',       'kg',  2200.00,   2,   8, 2],
    ['Molde redondo 24cm',   'Utensilio',         'unidad', 2500.00, 5, 15, 3],
    ['Manga pastelera',      'Utensilio',         'unidad',  450.00, 5, 20, 3],
    ['Papel manteca rollo',  'Utensilio',         'rollo',   180.00, 3, 18, 3],
];

$insumoIds = [];
foreach ($insumos as $ins) {
    [$nombre, $cat, $unidad, $precio, $stockMin, $stockActual, $provIdx] = $ins;
    $provId = $provIds[$provIdx] ?? $provIds[0];

    $existing = $pdo->prepare("SELECT idIns FROM insumo WHERE nombreIns = ?");
    $existing->execute([$nombre]);
    $existId = $existing->fetchColumn();

    if ($existId) {
        ok("Insumo ya existe: $nombre");
        $insumoIds[$nombre] = $existId;
        continue;
    }

    $iid = exec_safe($pdo,
        "INSERT INTO insumo (nombreIns, categoriaIns, unidadMedidaIns, precioCompraIns, stockMinimoIns, proveedorIdPro, estadoIns)
         VALUES (?, ?, ?, ?, ?, ?, 'Activo')",
        [$nombre, $cat, $unidad, $precio, $stockMin, $provId],
        "Insumo: $nombre"
    );

    if ($iid) {
        $insumoIds[$nombre] = $iid;
        // Verificar si ya tiene registro en inventario (el trigger puede haberlo creado)
        $invCheck = $pdo->prepare("SELECT idInv FROM inventario WHERE insumoIdInv = ?");
        $invCheck->execute([$iid]);
        if (!$invCheck->fetchColumn()) {
            exec_safe($pdo,
                "INSERT INTO inventario (insumoIdInv, stockActualInv, stockMinimoInv, unidadMedidaInv)
                 VALUES (?, ?, ?, ?)",
                [$iid, $stockActual, $stockMin, $unidad],
                "Inventario: $nombre (stock: $stockActual)"
            );
        } else {
            exec_safe($pdo,
                "UPDATE inventario SET stockActualInv = ?, stockMinimoInv = ? WHERE insumoIdInv = ?",
                [$stockActual, $stockMin, $iid],
                "Inventario actualizado: $nombre"
            );
        }
    }
}

// ============================================================
// 5. RECETAS CON INGREDIENTES
// ============================================================
echo "<h3>Creando recetas...</h3>";

$recetas = [
    [
        'nombre'       => 'Torta de Chocolate Clásica',
        'instrucciones'=> 'Precalentar el horno a 180°C. Tamizar la harina con el cacao y el polvo de hornear. Batir los huevos con el azúcar hasta blanquear. Incorporar la manteca derretida y la leche. Agregar los secos en forma envolvente. Volcar en molde enmantecado y hornear 40 minutos.',
        'margen'       => 65.0,
        'ingredientes' => [
            ['Harina 000',    250, 'gr'],
            ['Cacao amargo',   80, 'gr'],
            ['Azúcar',        200, 'gr'],
            ['Manteca',       150, 'gr'],
            ['Huevos',          4, 'unidad'],
            ['Leche entera',  200, 'ml'],
            ['Polvo de hornear', 10, 'gr'],
        ],
    ],
    [
        'nombre'       => 'Alfajores de Maicena',
        'instrucciones'=> 'Mezclar harina con almidón, polvo de hornear y sal. Integrar la manteca pomada con el azúcar, agregar los huevos y la esencia de vainilla. Incorporar los secos. Refrigerar 30 minutos, estirar y cortar. Hornear a 170°C por 12 minutos. Rellenar con dulce de leche y unir de a pares.',
        'margen'       => 70.0,
        'ingredientes' => [
            ['Harina 0000',   200, 'gr'],
            ['Manteca',       120, 'gr'],
            ['Azúcar',         80, 'gr'],
            ['Huevos',          2, 'unidad'],
            ['Dulce de leche', 300, 'gr'],
            ['Vainilla esencia', 5, 'ml'],
            ['Polvo de hornear', 5, 'gr'],
        ],
    ],
    [
        'nombre'       => 'Tarta de Frutillas con Crema',
        'instrucciones'=> 'Preparar masa sablée con harina, manteca y azúcar. Refrigerar 20 minutos, estirar y forrar molde. Hornear en blanco a 175°C por 20 minutos. Preparar crema pastelera con leche, huevos y azúcar. Dejar enfriar, rellenar la tarta y decorar con frutillas frescas.',
        'margen'       => 60.0,
        'ingredientes' => [
            ['Harina 000',    300, 'gr'],
            ['Manteca',       180, 'gr'],
            ['Azúcar',        100, 'gr'],
            ['Huevos',          3, 'unidad'],
            ['Leche entera',  500, 'ml'],
            ['Crema de leche', 200, 'ml'],
            ['Frutillas frescas', 400, 'gr'],
        ],
    ],
    [
        'nombre'       => 'Budín de Nueces',
        'instrucciones'=> 'Batir huevos con azúcar. Incorporar manteca derretida, harina tamizada con polvo de hornear y sal. Agregar nueces picadas groseramente. Volcar en molde budinera enmantecado. Hornear a 180°C por 45 minutos.',
        'margen'       => 75.0,
        'ingredientes' => [
            ['Harina 0000',   220, 'gr'],
            ['Azúcar',        180, 'gr'],
            ['Manteca',       100, 'gr'],
            ['Huevos',          3, 'unidad'],
            ['Nueces',        150, 'gr'],
            ['Polvo de hornear', 8, 'gr'],
            ['Sal fina',        3, 'gr'],
        ],
    ],
    [
        'nombre'       => 'Mousse de Chocolate',
        'instrucciones'=> 'Derretir el chocolate a baño María. Separar yemas de claras. Batir las claras a punto nieve con el azúcar. Mezclar el chocolate con las yemas y la crema batida. Incorporar las claras en forma envolvente. Distribuir en copas y refrigerar mínimo 4 horas.',
        'margen'       => 80.0,
        'ingredientes' => [
            ['Chocolate cobertura', 300, 'gr'],
            ['Crema de leche',      300, 'ml'],
            ['Huevos',                4, 'unidad'],
            ['Azúcar',               80, 'gr'],
        ],
    ],
    [
        'nombre'       => 'Medialuna de Manteca',
        'instrucciones'=> 'Disolver levadura en leche tibia con azúcar. Mezclar con harina y sal. Integrar la manteca en cuadros fríos. Refrigerar 1 hora. Estirar, plegar y cortar en triángulos. Enrollar y dar forma de medialuna. Dejar leudar 40 minutos. Pintar con huevo y hornear a 200°C por 15 minutos.',
        'margen'       => 55.0,
        'ingredientes' => [
            ['Harina 000',    500, 'gr'],
            ['Manteca',       250, 'gr'],
            ['Levadura seca',  10, 'gr'],
            ['Leche entera',  200, 'ml'],
            ['Azúcar',         50, 'gr'],
            ['Sal fina',        8, 'gr'],
            ['Huevos',          1, 'unidad'],
        ],
    ],
];

$recetaIds = [];
foreach ($recetas as $r) {
    $existing = $pdo->prepare("SELECT idReceta FROM recetas WHERE nombreReceta = ?");
    $existing->execute([$r['nombre']]);
    $existId = $existing->fetchColumn();

    if ($existId) {
        ok("Receta ya existe: {$r['nombre']}");
        $recetaIds[$r['nombre']] = $existId;
        continue;
    }

    // Calcular costo de producción
    $costoTotal = 0;
    foreach ($r['ingredientes'] as $ing) {
        $iid = $insumoIds[$ing[0]] ?? null;
        if ($iid) {
            $precio = $pdo->prepare("SELECT precioCompraIns FROM insumo WHERE idIns = ?");
            $precio->execute([$iid]);
            $p = (float)$precio->fetchColumn();
            $costoTotal += $ing[1] * $p / 1000; // convertir gr/ml a kg/lt aproximado
        }
    }
    $precioSugerido = $costoTotal * (1 + $r['margen'] / 100);

    $rid = exec_safe($pdo,
        "INSERT INTO recetas (nombreReceta, instruccionesReceta, margenGananciaReceta, costoProduccionReceta, precioVentaSugeridoReceta, estadoReceta)
         VALUES (?, ?, ?, ?, ?, 'Activa')",
        [$r['nombre'], $r['instrucciones'], $r['margen'], round($costoTotal, 2), round($precioSugerido, 2)],
        "Receta: {$r['nombre']}"
    );

    if ($rid) {
        $recetaIds[$r['nombre']] = $rid;
        foreach ($r['ingredientes'] as $ing) {
            $iid = $insumoIds[$ing[0]] ?? null;
            if (!$iid) continue;

            $precio = $pdo->prepare("SELECT precioCompraIns FROM insumo WHERE idIns = ?");
            $precio->execute([$iid]);
            $p = (float)$precio->fetchColumn();
            $costo = round($ing[1] * $p / 1000, 2);

            exec_safe($pdo,
                "INSERT INTO receta_ingrediente (recetaIdRecIng, insumoIdRecIng, cantidadNecesaria, unidadMedidaRecIng, costoProporcional)
                 VALUES (?, ?, ?, ?, ?)",
                [$rid, $iid, $ing[1], $ing[2], $costo],
                "Ingrediente '{$ing[0]}' en receta '{$r['nombre']}'"
            );
        }
    }
}

// ============================================================
// 6. MENÚ SEMANAL (semana actual)
// ============================================================
echo "<h3>Creando menú semanal...</h3>";

$adminId = $userIds['admin'] ?? null;
if ($adminId) {
    // Calcular lunes y domingo de la semana actual
    $hoy = new DateTime();
    $diaSemana = (int)$hoy->format('N'); // 1=Lunes, 7=Domingo
    $lunes = (clone $hoy)->modify('-' . ($diaSemana - 1) . ' days')->format('Y-m-d');
    $domingo = (clone $hoy)->modify('+' . (7 - $diaSemana) . ' days')->format('Y-m-d');

    $existMenu = $pdo->prepare("SELECT idMenuSem FROM menu_semanal WHERE fechaInicioSem = ?");
    $existMenu->execute([$lunes]);
    $menuId = $existMenu->fetchColumn();

    if (!$menuId) {
        $menuId = exec_safe($pdo,
            "INSERT INTO menu_semanal (fechaInicioSem, fechaFinSem, estadoSem, observacionSem, usuarioCreaIdSem)
             VALUES (?, ?, 'Publicado', 'Menú de la semana generado automáticamente', ?)",
            [$lunes, $domingo, $adminId],
            "Menú semanal: $lunes al $domingo"
        );
    } else {
        ok("Menú semanal ya existe para esta semana");
    }

    if ($menuId) {
        $dias = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'];
        $recetasArray = array_values($recetaIds);
        foreach ($dias as $i => $dia) {
            $existDia = $pdo->prepare("SELECT idMenuDet FROM menu_detalle WHERE menuSemIdMenuDet = ? AND diaSemana = ?");
            $existDia->execute([$menuId, $dia]);
            if ($existDia->fetchColumn()) {
                ok("Día $dia ya tiene menú");
                continue;
            }
            $recetaId = $recetasArray[$i % count($recetasArray)] ?? null;
            if ($recetaId) {
                exec_safe($pdo,
                    "INSERT INTO menu_detalle (menuSemIdMenuDet, diaSemana, recetaIdMenuDet, cantidadPrevista)
                     VALUES (?, ?, ?, ?)",
                    [$menuId, $dia, $recetaId, rand(15, 40)],
                    "Menú $dia"
                );
            }
        }
    }
}

// ============================================================
// 7. CATEGORÍAS Y SUBCATEGORÍAS
// ============================================================
echo "<h3>Creando categorías y productos...</h3>";

$categorias = [
    [1, 'Tortas y Pasteles', 'Tortas artesanales, pasteles y preparaciones especiales'],
    [2, 'Facturas y Medialunas', 'Masas hojaldradas y panes dulces de panadería'],
    [3, 'Postres individuales', 'Mousse, budines, flanes y porciones individuales'],
];

foreach ($categorias as $cat) {
    exec_safe($pdo,
        "INSERT IGNORE INTO categoria (idCat, nombreCat, descripcionCat, estadoCat) VALUES (?, ?, ?, 1)",
        $cat, "Categoría: {$cat[1]}"
    );
}

$subcategorias = [
    [1, 1, 'Tortas Clásicas',    'Tortas de sabores tradicionales'],
    [2, 1, 'Tortas Especiales',  'Tortas temáticas y personalizadas'],
    [3, 2, 'Medialunas',         'Medialunas de manteca y grasa'],
    [4, 2, 'Facturas surtidas',  'Vigilantes, cuernitos, bolas de fraile'],
    [5, 3, 'Mousses',            'Mousses y cremas frías'],
    [6, 3, 'Budines',            'Budines y bizcochuelos'],
];

foreach ($subcategorias as $sub) {
    exec_safe($pdo,
        "INSERT IGNORE INTO subcategoria (idSubCat, idCat, nombreSubCat, descripcionSubCat, estadoSubCat) VALUES (?, ?, ?, ?, 'ACTIVO')",
        $sub, "Subcategoría: {$sub[2]}"
    );
}

// Crear un inventario genérico para los productos
$invGenId = null;
$invGenCheck = $pdo->query("SELECT idInv FROM inventario WHERE insumoIdInv IS NULL LIMIT 1")->fetchColumn();
if (!$invGenCheck) {
    // Usar el inventario del primer insumo como referencia
    $invGenId = $pdo->query("SELECT idInv FROM inventario LIMIT 1")->fetchColumn();
} else {
    $invGenId = $invGenCheck;
}

// Obtener primer proveedor disponible
$firstProv = $pdo->query("SELECT idPro FROM proveedor LIMIT 1")->fetchColumn();

$productos = [
    // [nombre, desc, SKU, marca, costo, precio, margen, stock, subcatId, enOferta, esDestacado]
    ['Torta Chocolate Grande',    'Torta de chocolate para 12 personas con ganache',       'TRT-CHOC-001', 'SBAVECA', 2800.00,  4800.00, 71.4, 8,  1, 0, 1],
    ['Torta de Frutillas',        'Tarta de frutillas con crema pastelera para 10 personas','TRT-FRUT-001', 'SBAVECA', 2200.00,  4200.00, 90.9, 6,  1, 1, 1],
    ['Alfajores x12',             'Caja de 12 alfajores de maicena rellenos con dulce de leche','ALF-001',  'SBAVECA',  480.00,   900.00, 87.5,25,  4, 0, 0],
    ['Medialunas x6',             'Bolsa de 6 medialunas de manteca artesanales',          'MED-001',      'SBAVECA',  180.00,   350.00, 94.4,30,  3, 0, 1],
    ['Mousse de Chocolate',       'Mousse individual de chocolate amargo 200gr',            'MOU-CHOC-001', 'SBAVECA',  320.00,   620.00, 93.8,20,  5, 1, 0],
    ['Budín de Nueces',           'Budín artesanal de nueces 500gr',                       'BUD-NUC-001',  'SBAVECA',  420.00,   780.00, 85.7,15,  6, 0, 0],
    ['Torta de Cumpleaños',       'Torta personalizable para cumpleaños — consultar sabores','TRT-CMP-001', 'SBAVECA', 3500.00,  6500.00, 85.7, 4,  2, 0, 1],
    ['Facturas Surtidas x12',     'Caja de 12 facturas variadas del día',                  'FAC-SUR-001',  'SBAVECA',  360.00,   700.00, 94.4,20,  4, 0, 0],
];

$productoIds = [];
if ($firstProv && $invGenId) {
    foreach ($productos as $prod) {
        $existing = $pdo->prepare("SELECT idProducto FROM producto WHERE SKUProd = ?");
        $existing->execute([$prod[2]]);
        $existId = $existing->fetchColumn();
        if ($existId) {
            ok("Producto ya existe: {$prod[0]}");
            $productoIds[$prod[0]] = $existId;
            continue;
        }
        $pid = exec_safe($pdo,
            "INSERT INTO producto (IdSubCat, nombreProd, descripcionProd, SKUProd, MarcaProd, precioCostoProd, precioVentaProd, margenGananciaProd, stockActualProd, estadoProd, enOfertaProd, esDestacadoProd, inventarioIdInv, proveedorIdPro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo', ?, ?, ?, ?)",
            [$prod[10], $prod[0], $prod[1], $prod[2], $prod[3], $prod[4], $prod[5], $prod[6], $prod[7], $prod[8], $prod[9], $invGenId, $firstProv],
            "Producto: {$prod[0]}"
        );
        $productoIds[$prod[0]] = $pid;
    }
}

// ============================================================
// 8. CLIENTES DE PRUEBA
// ============================================================
echo "<h3>Creando clientes de prueba...</h3>";

$clientes_data = [
    ['María', 'González',   'maria.gonzalez@mail.com',  'mgonzalez',  '3512200001', '20199001001'],
    ['Carlos', 'Martínez',  'carlos.martinez@mail.com', 'cmartinez',  '3512200002', '20299001002'],
    ['Ana', 'Rodríguez',    'ana.rodriguez@mail.com',   'arodriguez',  '3512200003', '27399001003'],
    ['Diego', 'López',      'diego.lopez@mail.com',     'dlopez',     '3512200004', '20499001004'],
    ['Sofía', 'Fernández',  'sofia.fernandez@mail.com', 'sfernandez', '3512200005', '27599001005'],
];

$clienteUserIds = [];
foreach ($clientes_data as $c) {
    $existing = $pdo->prepare("SELECT idUsu FROM usuario WHERE correoUsu = ?");
    $existing->execute([$c[2]]);
    $existId = $existing->fetchColumn();

    if ($existId) {
        ok("Cliente ya existe: {$c[2]}");
        $clienteUserIds[] = $existId;
        continue;
    }

    $hash = password_hash('Cliente123!', PASSWORD_DEFAULT);
    $cuil = '20' . substr($c[5], 2, 8) . '9';
    $uid = exec_safe($pdo,
        "INSERT INTO usuario (nombreUsu, apellidoUsu, correoUsu, usuarioUsu, contrasenaUsu, CUILUsu, telefonoUsu, estadoUsu, rolUsu)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', 'Cliente')",
        [$c[0], $c[1], $c[2], $c[3], $hash, $c[5], $c[4]],
        "Cliente: {$c[0]} {$c[1]}"
    );
    if ($uid && isset($roleIds['Cliente'])) {
        exec_safe($pdo,
            "INSERT IGNORE INTO usuario_rol (idUsu, idRol) VALUES (?, ?)",
            [$uid, $roleIds['Cliente']],
            "Rol Cliente a {$c[2]}"
        );
    }
    $clienteUserIds[] = $uid;
}

// ============================================================
// 9. VENTAS DE PRUEBA
// ============================================================
echo "<h3>Creando ventas de prueba...</h3>";

// Obtener IDs reales de clientes y empleados
$clientesDB = $pdo->query("SELECT idCli FROM cliente LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
$empleadoDB = $pdo->query("SELECT idUsu FROM usuario WHERE rolUsu = 'Empleado' LIMIT 1")->fetchColumn();
$productosDB = $pdo->query("SELECT idProducto, precioVentaProd, nombreProd FROM producto LIMIT 8")->fetchAll();

$metodoPagos = ['Efectivo', 'Tarjeta de Débito', 'Tarjeta de Crédito', 'Transferencia'];

$ventasEjemplo = [
    ['numero' => 'V-2026-001', 'estado' => 'Pagado',    'dias' => -15],
    ['numero' => 'V-2026-002', 'estado' => 'Pagado',    'dias' => -12],
    ['numero' => 'V-2026-003', 'estado' => 'Pagado',    'dias' => -10],
    ['numero' => 'V-2026-004', 'estado' => 'Pagado',    'dias' => -8],
    ['numero' => 'V-2026-005', 'estado' => 'Pagado',    'dias' => -6],
    ['numero' => 'V-2026-006', 'estado' => 'Pagado',    'dias' => -5],
    ['numero' => 'V-2026-007', 'estado' => 'Cancelado', 'dias' => -4],
    ['numero' => 'V-2026-008', 'estado' => 'Pagado',    'dias' => -3],
    ['numero' => 'V-2026-009', 'estado' => 'Pagado',    'dias' => -2],
    ['numero' => 'V-2026-010', 'estado' => 'Pendiente', 'dias' =>  0],
];

if (!empty($clientesDB) && $empleadoDB && !empty($productosDB)) {
    foreach ($ventasEjemplo as $idx => $v) {
        $existing = $pdo->prepare("SELECT idVen FROM venta WHERE numeroVen = ?");
        $existing->execute([$v['numero']]);
        if ($existing->fetchColumn()) {
            ok("Venta ya existe: {$v['numero']}");
            continue;
        }

        $cliId = $clientesDB[$idx % count($clientesDB)];
        $metodo = $metodoPagos[array_rand($metodoPagos)];
        $fecha  = date('Y-m-d H:i:s', strtotime("{$v['dias']} days"));
        $total  = 0;

        // Seleccionar 1 a 3 productos aleatorios
        $numProds = rand(1, 3);
        $selProds = array_slice($productosDB, ($idx * 2) % count($productosDB), $numProds);

        $vid = exec_safe($pdo,
            "INSERT INTO venta (numeroVen, fechaVen, estadoVen, clienteIdVen, empleadoIdVen, metodoPagoVen, TotalVen)
             VALUES (?, ?, ?, ?, ?, ?, 0)",
            [$v['numero'], $fecha, $v['estado'], $cliId, $empleadoDB, $metodo],
            "Venta: {$v['numero']}"
        );

        if ($vid) {
            foreach ($selProds as $prod) {
                $cant = rand(1, 4);
                $precio = (float)$prod['precioVentaProd'];
                $subtotal = $cant * $precio;
                $total += $subtotal;

                exec_safe($pdo,
                    "INSERT INTO detalle_venta (ventaIdDetVen, productoIdDetVen, cantidadDetVen, precioUnitarioDetVen, subtotalDetVen)
                     VALUES (?, ?, ?, ?, ?)",
                    [$vid, $prod['idProducto'], $cant, $precio, $subtotal],
                    "Detalle venta {$v['numero']}: {$prod['nombreProd']} x$cant"
                );
            }

            // Actualizar total
            exec_safe($pdo,
                "UPDATE venta SET TotalVen = ? WHERE idVen = ?",
                [$total, $vid],
                "Total venta {$v['numero']}: $" . number_format($total, 2)
            );

            // Generar recibo si está pagada
            if ($v['estado'] === 'Pagado') {
                $iva = round($total * 0.21, 2);
                $subtotalSinIva = round($total - $iva, 2);
                exec_safe($pdo,
                    "INSERT INTO recibos (ventaId, numeroReci, htmlContent, subtotalReci, ivaReci, totalReci, metodoPagoReci)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$vid, 'R-' . $v['numero'], '<p>Recibo ' . $v['numero'] . '</p>', $subtotalSinIva, $iva, $total, $metodo],
                    "Recibo para {$v['numero']}"
                );
            }
        }
    }
} else {
    fail("No se pudieron crear ventas: faltan clientes, empleados o productos");
}

// ============================================================
// 10. PEDIDOS DE PRUEBA
// ============================================================
echo "<h3>Creando pedidos de prueba...</h3>";

$personasDB = $pdo->query("SELECT idPers FROM persona LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
$estadosPed = ['Pendiente','En preparacion','Listo','Entregado'];
$canales = ['Presencial','Telefonico','Web'];

if (!empty($personasDB) && !empty($productosDB)) {
    $pedidosEjemplo = [
        ['canal' => 'Presencial', 'estado' => 'Entregado',      'dias' => -10, 'entrega' => -9],
        ['canal' => 'Web',        'estado' => 'Entregado',      'dias' => -7,  'entrega' => -6],
        ['canal' => 'Telefonico', 'estado' => 'En preparacion', 'dias' => -1,  'entrega' =>  1],
        ['canal' => 'Web',        'estado' => 'Pendiente',      'dias' =>  0,  'entrega' =>  2],
        ['canal' => 'Presencial', 'estado' => 'Listo',          'dias' =>  0,  'entrega' =>  0],
    ];

    foreach ($pedidosEjemplo as $idx => $ped) {
        $persId = $personasDB[$idx % count($personasDB)];
        $fechaPed = date('Y-m-d H:i:s', strtotime("{$ped['dias']} days"));
        $fechaEnt = date('Y-m-d H:i:s', strtotime("{$ped['entrega']} days"));

        $prod = $productosDB[$idx % count($productosDB)];
        $cant = rand(1, 3);
        $precio = (float)$prod['precioVentaProd'];
        $total = $cant * $precio;

        $pid = exec_safe($pdo,
            "INSERT INTO pedido (persona_idPers, montoPed, fechaPed, estadoPed, canalOrigenPed, fechaEntregaEstimPed, estadoSeguimientoPed)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$persId, $total, $fechaPed, $ped['estado'], $ped['canal'], $fechaEnt, $ped['estado']],
            "Pedido #{$idx} — {$ped['canal']} — {$ped['estado']}"
        );

        if ($pid) {
            exec_safe($pdo,
                "INSERT INTO detalle_pedido (pedido_idPed, productoIdDetPed, cantidadDetPed, precioUnitarioDetPed, subtotalDetPed)
                 VALUES (?, ?, ?, ?, ?)",
                [$pid, $prod['idProducto'], $cant, $precio, $total],
                "Detalle pedido #{$idx}: {$prod['nombreProd']} x$cant"
            );
        }
    }
}

// ============================================================
// FIN
// ============================================================
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SBAVECA — Seed de datos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; background: #0f0f1a; color: #e0e0e0; padding: 20px; }
        h1   { color: #7c3aed; border-bottom: 2px solid #7c3aed; padding-bottom: 10px; }
        h3   { color: #a78bfa; margin-top: 30px; }
        .ok  { background: #052e16; border-left: 4px solid #22c55e; padding: 4px 12px; margin: 3px 0; font-size: 13px; border-radius: 4px; }
        .err { background: #450a0a; border-left: 4px solid #ef4444; padding: 4px 12px; margin: 3px 0; font-size: 13px; border-radius: 4px; }
        .summary { background: #1e1b4b; border: 2px solid #7c3aed; border-radius: 8px; padding: 20px; margin-top: 30px; }
        .summary h2 { color: #a78bfa; margin-top: 0; }
        .cred { background: #0c1a0c; border: 1px solid #22c55e; border-radius: 6px; padding: 15px; margin-top: 15px; }
        .cred code { color: #86efac; }
    </style>
</head>
<body>
    <h1>🎂 SBAVECA — Carga de datos de prueba</h1>

    <?php foreach ($log as $l): ?>
        <div class="ok"><?= htmlspecialchars($l) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $e): ?>
        <div class="err"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <div class="summary">
        <h2>✅ Proceso completado</h2>
        <p><strong><?= count($log) ?></strong> operaciones exitosas &nbsp;|&nbsp;
           <strong><?= count($errors) ?></strong> errores</p>

        <div class="cred">
            <h3 style="margin-top:0; color:#86efac">🔑 Credenciales de acceso</h3>
            <p><strong>Administrador:</strong><br>
               Email: <code>admin@sbaveca.com</code><br>
               Contraseña: <code>Admin123!</code></p>
            <p><strong>Empleados:</strong><br>
               <code>laura.bordon@sbaveca.com</code> / <code>Empleado123!</code><br>
               <code>matias.sbardella@sbaveca.com</code> / <code>Empleado123!</code><br>
               <code>vale.caceres@sbaveca.com</code> / <code>Empleado123!</code></p>
            <p><strong>Clientes (para tienda web):</strong><br>
               <code>maria.gonzalez@mail.com</code> / <code>Cliente123!</code></p>
            <p><a href="http://localhost:4200" style="color:#a78bfa">
               → Ir al panel administrativo (localhost:4200)</a></p>
        </div>
    </div>
</body>
</html>
