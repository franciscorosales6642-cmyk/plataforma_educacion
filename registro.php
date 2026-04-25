<?php
include "config/conexion.php";
$mensaje = "";
$tipoMensaje = "error";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST["nombre"]);
    $correo = $conn->real_escape_string($_POST["correo"]);
    $password = $conn->real_escape_string($_POST["password"]);
    $sql = "INSERT INTO usuarios(nombre, correo, password, rol) VALUES('$nombre','$correo','$password','USUARIO')";
    if ($conn->query($sql)) {
        header("Location: login.php");
        exit;
    } else {
        $mensaje = "No se pudo crear la cuenta. Verifica que el correo no este repetido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-showcase">
            <div class="auth-copy">
                <span class="eyebrow">Nuevo acceso</span>
                <h1>Crea tu cuenta y entra a la plataforma educativa.</h1>
                <p class="auth-lead">Registra tu perfil para explorar cursos, avanzar por lecciones y llevar seguimiento de tu aprendizaje en un solo lugar.</p>
            </div>
            <div class="auth-feature-list">
                <article class="auth-feature-card">
                    <strong>Ruta de aprendizaje</strong>
                    <p>Accede a cursos estructurados con avance por lecciones y progreso visible.</p>
                </article>
                <article class="auth-feature-card">
                    <strong>Experiencia unificada</strong>
                    <p>El mismo estilo visual acompana registro, acceso, panel y reproduccion del curso.</p>
                </article>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-head">
                    <span class="eyebrow">Registro</span>
                    <h2>Crear cuenta</h2>
                    <p class="muted-text">Completa tus datos para comenzar a usar la plataforma.</p>
                </div>

                <?php if ($mensaje) { ?>
                <div class="alert <?= $tipoMensaje === 'success' ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($mensaje) ?></div>
                <?php } ?>

                <form method="POST" class="auth-form">
                    <label class="field-group">
                        <span>Nombre completo</span>
                        <input type="text" name="nombre" placeholder="Tu nombre completo" required>
                    </label>
                    <label class="field-group">
                        <span>Correo electronico</span>
                        <input type="email" name="correo" placeholder="tu@correo.com" required>
                    </label>
                    <label class="field-group">
                        <span>Contrasena</span>
                        <input type="password" name="password" placeholder="Crea una contrasena" required>
                    </label>
                    <button type="submit">Crear cuenta</button>
                </form>

                <a class="auth-link" href="login.php">Volver al inicio de sesion</a>
            </div>
        </section>
    </main>
</body>
</html>
