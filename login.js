function togglePassword() {
    const campo = document.getElementById('password');
    const icono = document.getElementById('iconoOjo');
    if (campo.type === 'password') {
        campo.type = 'text';
        icono.classList.remove('bi-eye');
        icono.classList.add('bi-eye-slash');
    } else {
        campo.type = 'password';
        icono.classList.remove('bi-eye-slash');
        icono.classList.add('bi-eye');
    }
}
function validar_usuario() {
    let usuario = $("#usuario").val();
    let password = $("#password").val();

    let parametros = {
        usuario: usuario,
        password: password
    };

    $.ajax({
        type: "POST",
        url: "validarlogin.php",
        data: parametros,
        dataType: "json",
        success: function(response) {
            console.log(response);

            if (response.success && response.rol === "admin") {
                Swal.fire({
                    icon: "success",
                    title: "Bienvenido",
                    text: response.mensaje,
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.href = "index.php", 1000);
            }
            else if (response.success && response.rol === "empleado") {
                Swal.fire({
                    icon: "success",
                    title: "Bienvenido",
                    text: response.mensaje,
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.href = "panel_empleado.php", 1000);
            }
            else {
                Swal.fire({
                    icon: "error",
                    title: "Acceso denegado",
                    text: response.mensaje,
                    confirmButtonColor: "#C0392B"
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en AJAX:", error);
            Swal.fire({
                icon: "error",
                title: "Error de conexion",
                text: "No se pudo conectar con el servidor.",
                confirmButtonColor: "#C0392B"
            });
        }
    });
}