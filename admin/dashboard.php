<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "ADMIN") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$totalCursos = (int) $conn->query("SELECT COUNT(*) total FROM cursos")->fetch_assoc()["total"];
$totalProfesores = (int) $conn->query("SELECT COUNT(*) total FROM profesores")->fetch_assoc()["total"];
$totalUsuarios = (int) $conn->query("SELECT COUNT(*) total FROM usuarios WHERE rol='USUARIO'")->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administracion</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="admin-body">
    <div class="container admin-shell">
        <section class="hero-panel">
            <div>
                <span class="eyebrow">Administracion academica</span>
                <h1>Panel del Administrador</h1>
                <p class="hero-text">Supervisa el ecosistema educativo desde un dashboard con accesos directos, indicadores clave y modulos mejor organizados.</p>
            </div>
            <div class="hero-user-card">
                <span class="hero-user-label">Sesion activa</span>
                <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong>
                <span class="hero-user-role">Administrador</span>
            </div>
        </section>

        <section class="dashboard-grid admin-dashboard-grid">
            <div class="dashboard-main">
                <section class="stats-grid stats-grid-admin">
                    <article class="stat-card stat-card-accent">
                        <span class="stat-label">Total profesores</span>
                        <strong class="stat-value"><?php echo $totalProfesores; ?></strong>
                        <p>Docentes disponibles para la operacion academica.</p>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Total cursos</span>
                        <strong class="stat-value"><?php echo $totalCursos; ?></strong>
                        <p>Oferta educativa publicada y lista para los estudiantes.</p>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Total usuarios</span>
                        <strong class="stat-value"><?php echo $totalUsuarios; ?></strong>
                        <p>Usuarios activos con acceso al entorno de aprendizaje.</p>
                    </article>
                </section>

                <section class="card admin-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">Accesos directos</span>
                            <h2>Gestion principal</h2>
                        </div>
                    </div>
                    <div class="action-cards-grid">
                        <a class="action-card" href="profesores.php">
                            <span class="action-card-tag">Modulo</span>
                            <h3>Profesores</h3>
                            <p>Administra altas y manten actualizada la informacion del equipo docente.</p>
                            <span class="action-link">Abrir modulo</span>
                        </a>
                        <a class="action-card" href="cursos.php">
                            <span class="action-card-tag">Modulo</span>
                            <h3>Cursos</h3>
                            <p>Organiza la oferta academica y su relacion con cada profesor.</p>
                            <span class="action-link">Abrir modulo</span>
                        </a>
                        <a class="action-card" href="videos.php">
                            <span class="action-card-tag">Contenido</span>
                            <h3>Videos</h3>
                            <p>Gestiona materiales audiovisuales y el orden de aprendizaje.</p>
                            <span class="action-link">Abrir modulo</span>
                        </a>
                        <a class="action-card" href="reportes.php">
                            <span class="action-card-tag">Analitica</span>
                            <h3>Reportes</h3>
                            <p>Consulta comentarios, valoraciones y comportamiento general.</p>
                            <span class="action-link">Abrir modulo</span>
                        </a>
                    </div>
                </section>
            </div>

            <aside class="dashboard-side">
                <section class="card admin-card side-panel-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">Acciones</span>
                            <h2>Navegacion rapida</h2>
                        </div>
                    </div>
                    <div class="side-action-list">
                        <a class="btn btn-block" href="profesores.php">Gestionar profesores</a>
                        <a class="btn btn-light-solid btn-block" href="cursos.php">Gestionar cursos</a>
                        <a class="btn btn-light-solid btn-block" href="videos.php">Gestionar videos</a>
                        <a class="btn btn-light-solid btn-block" href="reportes.php">Ver reportes</a>
                        <a class="btn btn-danger btn-block" href="../logout.php">Cerrar sesion</a>
                    </div>
                </section>

                <section class="card admin-card side-panel-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">Resumen</span>
                            <h2>Estado del sistema</h2>
                        </div>
                    </div>
                    <div class="info-panel">
                        <p>Este panel prioriza lectura rapida, mejor espaciado y acceso inmediato a cada area operativa del sistema.</p>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</body>
</html>
