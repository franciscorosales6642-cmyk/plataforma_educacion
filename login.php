<?php
session_start();
include "config/conexion.php";
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conn->real_escape_string($_POST["correo"]);
    $password = $conn->real_escape_string($_POST["password"]);
    $sql = "SELECT * FROM usuarios WHERE correo='$correo' AND password='$password'";
    $resultado = $conn->query($sql);
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION["id"] = $usuario["id"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["rol"] = $usuario["rol"];
        header($usuario["rol"] == "ADMIN" ? "Location: admin/dashboard.php" : "Location: usuario/inicio.php");
        exit;
    } else {
        $mensaje = "Correo o contrasena incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-showcase">
            <div class="auth-copy">
                <span class="eyebrow">Plataforma profesional</span>
                <h1>Aprende, organiza y avanza desde un solo lugar.</h1>
                <p class="auth-lead">Accede a una experiencia educativa moderna con seguimiento de progreso, cursos estructurados y administracion centralizada.</p>
            </div>

            <div class="auth-feature-list">
                <article class="auth-feature-card">
                    <strong>Panel intuitivo</strong>
                    <p>Una interfaz ordenada para alumnos y administradores con enfoque en productividad.</p>
                </article>
                <article class="auth-feature-card">
                    <strong>Seguimiento real</strong>
                    <p>Consulta progreso por curso, lecciones completadas y resultados de aprendizaje.</p>
                </article>
                <article class="auth-feature-card">
                    <strong>Acceso seguro</strong>
                    <p>Inicia sesion y continua exactamente donde te quedaste.</p>
                </article>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-head">
                    <span class="eyebrow">Bienvenido</span>
                    <h2>Inicia sesion</h2>
                    <p class="muted-text">Ingresa tus credenciales para entrar a la plataforma.</p>
                </div>

                <?php if ($mensaje) { ?>
                <div class="alert alert-error"><?= htmlspecialchars($mensaje) ?></div>
                <?php } ?>

                <form method="POST" class="auth-form">
                    <label class="field-group">
                        <span>Correo electronico</span>
                        <input type="email" name="correo" placeholder="tu@correo.com" required>
                    </label>
                    <label class="field-group">
                        <span>Contrasena</span>
                        <input type="password" name="password" placeholder="Ingresa tu contrasena" required>
                    </label>
                    <button type="submit">Entrar a la plataforma</button>
                </form>

                <div class="demo-access">
                    <span class="demo-title">Accesos de prueba</span>
                    <p><strong>Admin:</strong> admin@gmail.com / 12345</p>
                    <p><strong>Usuario:</strong> usuario@gmail.com / 12345</p>
                </div>

                <a class="auth-link" href="registro.php">Crear cuenta de usuario</a>
            </div>
        </section>
    </main>
</body>
</html>
