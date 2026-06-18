<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos_login.css">
</head>
<body>

<div class="login-wrapper">

    <div class="login-left">
        <div class="login-brand">
            <div class="brand-icon"><img src="assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div>
            <div class="brand-text">
                <span class="brand-name">UKIYO</span>
                <span class="brand-sub">Restaurante Japonés</span>
            </div>
        </div>
        <div class="login-tagline">
            Sistema de punto<br>de venta
        </div>
        <div class="login-deco">浮世</div>
    </div>

    <div class="login-right">
        <div class="login-box">
            <div class="login-titulo">Iniciar sesión</div>
            <div class="login-subtitulo">Ingresa tus credenciales para continuar</div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alerta-error">
                    <i class="bi bi-exclamation-circle"></i>
                    <?php
                        if ($_GET['error'] == 'credenciales') echo 'Usuario o contraseña incorrectos.';
                        if ($_GET['error'] == 'vacio') echo 'Por favor llena todos los campos.';
                    ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="campo-grupo">
                    <label class="campo-label">Usuario</label>
                    <div class="campo-input-wrap">
                        <i class="bi bi-person campo-icon"></i>
                       <input type="text" name="usuario" id="usuario" class="campo-input" placeholder="Nombre de usuario" required autocomplete="off">
                    </div>
                </div>

                <div class="campo-grupo">
                    <label class="campo-label">Contraseña</label>
                    <div class="campo-input-wrap">
                        <i class="bi bi-lock campo-icon"></i>
                   <input type="password" name="password" id="password" class="campo-input" placeholder="Contraseña" required>
                        <button type="button" class="toggle-pass" onclick="togglePassword()">
                            <i class="bi bi-eye" id="iconoOjo"></i>
                        </button>
                    </div>
                </div>

             <button type="button" onclick="validar_usuario()" class="btn-ingresar">
    <i class="bi bi-box-arrow-in-right"></i> Ingresar
</button>
            </form>
        </div>

        <div class="login-footer">
            © 2026 Ukiyo Restaurante Japonés
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="login.js"></script>

</body>
</html>