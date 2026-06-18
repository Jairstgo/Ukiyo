<?php
session_start();
header('Content-Type: application/json');
include 'conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sin sesión activa']);
    exit();
}

// Leer JSON del body
$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit();
}

$tipo        = $datos['tipo'] ?? '';
$nombre      = $datos['nombre_cliente'] ?? '';
$direccion   = $datos['direccion'] ?? '';
$telefono    = $datos['telefono'] ?? '';
$metodo_pago = $datos['metodo_pago'] ?? 'efectivo';
$carrito     = $datos['carrito'] ?? [];
$id_usuario  = $_SESSION['idUsuario']; // tomado de la sesión, no del POST

if (empty($tipo) || empty($carrito)) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos del pedido']);
    exit();
}

mysqli_begin_transaction($conn);

try {
    // Insertar pedido (fechaRegistro usa DEFAULT CURRENT_TIMESTAMP)
    $sql = "INSERT INTO pedidos (tipo, nombre_cliente, direccion, telefono, metodo_pago, idUsuario) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssi', $tipo, $nombre, $direccion, $telefono, $metodo_pago, $id_usuario);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al insertar pedido');
    }

    $idPedido = mysqli_insert_id($conn);

    foreach ($carrito as $item) {
        // Buscar idPlatillo por nombre
        $sqlBuscar = "SELECT idPlatillo FROM platillos WHERE nombre = ? LIMIT 1";
        $stmtB = mysqli_prepare($conn, $sqlBuscar);
        mysqli_stmt_bind_param($stmtB, 's', $item['nombre']);
        mysqli_stmt_execute($stmtB);
        $resultB = mysqli_stmt_get_result($stmtB);
        $platillo = mysqli_fetch_assoc($resultB);

        if (!$platillo) {
            throw new Exception('Platillo no encontrado: ' . $item['nombre']);
        }

        $idPlatillo      = $platillo['idPlatillo'];
        $cantidad        = intval($item['cantidad']);
        $precio_unitario = floatval($item['precio']);

        $sqlDet = "INSERT INTO detalle_pedido (idPedido, idPlatillo, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmtD  = mysqli_prepare($conn, $sqlDet);
        mysqli_stmt_bind_param($stmtD, 'iiid', $idPedido, $idPlatillo, $cantidad, $precio_unitario);

        if (!mysqli_stmt_execute($stmtD)) {
            throw new Exception('Error al insertar detalle del pedido');
        }

        $idDetalle = mysqli_insert_id($conn);

        // Insertar toppings si el platillo los tiene
        if (!empty($item['toppings'])) {
            foreach ($item['toppings'] as $topping) {
                $idTopping     = intval($topping['id']);
                $nombreTopping = $topping['nombre'];
                $precioTopping = floatval($topping['precio']);

                $sqlTop = "INSERT INTO detalle_pedido_toppings (idDetalle, idTopping, nombre_topping, precio_unitario) VALUES (?, ?, ?, ?)";
                $stmtT  = mysqli_prepare($conn, $sqlTop);
                mysqli_stmt_bind_param($stmtT, 'iisd', $idDetalle, $idTopping, $nombreTopping, $precioTopping);

                if (!mysqli_stmt_execute($stmtT)) {
                    throw new Exception('Error al insertar topping');
                }
            }
        }
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'idPedido' => $idPedido]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}
?>
