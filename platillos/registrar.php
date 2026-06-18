<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];
    $subcategoria = !empty($_POST['subcategoria']) ? $_POST['subcategoria'] : NULL;
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $imagen = NULL;

    if (empty($nombre) || empty($precio) || empty($categoria)) {
        $mensaje = 'Nombre, precio y categoria son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $permitidas)) {
                $mensaje = 'Solo se permiten imagenes JPG, PNG o WEBP.';
                $tipo_mensaje = 'danger';
            } else {
                $nombreArchivo = uniqid('platillo_') . '.' . $extension;
                $destino = '../uploads/platillos/' . $nombreArchivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                    $imagen = $nombreArchivo;
                } else {
                    $mensaje = 'Error al subir la imagen.';
                    $tipo_mensaje = 'danger';
                }
            }
        }

        if (empty($mensaje)) {
            $sql = "INSERT INTO platillos (nombre, precio, idCategoria, idSubcategoria, disponible, imagen) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sdiiss', $nombre, $precio, $categoria, $subcategoria, $disponible, $imagen);

            if (mysqli_stmt_execute($stmt)) {
                header('Location: listar.php?exito=1');
                exit();
            } else {
                $mensaje = 'Error al registrar el platillo.';
                $tipo_mensaje = 'danger';
            }
        }
    }
}

$categorias = mysqli_query($conn, "SELECT * FROM categorias ORDER BY nombre");
$subcategorias = mysqli_query($conn, "SELECT * FROM subcategorias ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Registrar Platillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>

<nav class="navbar-ukiyo">
    <a class="navbar-brand-ukiyo" href="../index.php">
        <div class="brand-icon"><img src="../assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div>
        <div class="brand-text">
            <span class="brand-name">UKIYO</span>
            <span class="brand-sub">Restaurante Japonés</span>
        </div>
    </a>
    <div class="nav-right">
        <div class="usuario-badge">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
            <span class="d-none d-md-inline"><?php echo $_SESSION['nombre']; ?></span>
        </div>
        <a href="../cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</nav>

<div class="inner-content">
    <div class="section-header">
        <a href="listar.php" class="btn-back"><i class="bi bi-arrow-left"></i> Platillos</a>
        <span class="section-title">Registrar platillo</span>
    </div>
    <div class="divider-rojo"></div>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> mb-3"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="card-ukiyo">
        <div class="card-header-ukiyo"><i class="bi bi-plus-circle"></i> Nuevo platillo</div>
        <div class="card-body-ukiyo">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Nombre del platillo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre en el menú" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Disponible</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="disponible" checked>
                            <label class="form-check-label" style="font-size:13px;">Activo</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select" id="categoria" onchange="filtrarSubcategorias()" required>
                            <option value="">Seleccionar...</option>
                            <?php while ($cat = mysqli_fetch_assoc($categorias)): ?>
                            <option value="<?php echo $cat['idCategoria']; ?>"><?php echo $cat['nombre']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subcategoría</label>
                        <select name="subcategoria" class="form-select" id="subcategoria">
                            <option value="">Ninguna</option>
                            <?php while ($sub = mysqli_fetch_assoc($subcategorias)): ?>
                            <option value="<?php echo $sub['idSubcategoria']; ?>" data-categoria="<?php echo $sub['idCategoria']; ?>"><?php echo $sub['nombre']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Imagen del platillo</label>
                        <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp" onchange="previsualizarImagen(this)">
                        <div class="form-text">Opcional. JPG, PNG o WEBP. Máximo 2MB.</div>
                    </div>
                    <div class="col-12" id="previewContainer" style="display:none;">
                        <label class="form-label">Vista previa</label>
                        <div>
                            <img id="previewImg" src="" alt="Vista previa" style="width:160px; height:160px; object-fit:cover; border-radius:10px; border:2px solid #eee;">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-ukiyo"><i class="bi bi-plus-lg"></i> Registrar platillo</button>
                        <a href="listar.php" class="btn-outline-ukiyo ms-2"><i class="bi bi-x"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
function filtrarSubcategorias() {
    const idCat = document.getElementById('categoria').value;
    const opciones = document.querySelectorAll('#subcategoria option');
    opciones.forEach(op => {
        if (op.value === '' || op.dataset.categoria === idCat) {
            op.style.display = '';
        } else {
            op.style.display = 'none';
        }
    });
    document.getElementById('subcategoria').value = '';
}

function previsualizarImagen(input) {
    const container = document.getElementById('previewContainer');
    const img = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.style.display = 'none';
    }
}
</script>
</body>
</html>