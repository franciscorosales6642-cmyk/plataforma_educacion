<?php include "_headers.php"; include "../config/conexion.php";
$correo=$conn->real_escape_string($_POST['correo'] ?? ''); $password=$conn->real_escape_string($_POST['password'] ?? '');
$r=$conn->query("SELECT id,nombre,correo,rol,tema,imagen FROM usuarios WHERE correo='$correo' AND password='$password'");
echo $r->num_rows?json_encode($r->fetch_assoc()):json_encode(['error'=>'Datos incorrectos']);
?>
