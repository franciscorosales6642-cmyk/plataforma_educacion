<?php include "_headers.php"; include "../config/conexion.php";
$nombre=$conn->real_escape_string($_POST['nombre'] ?? ''); $correo=$conn->real_escape_string($_POST['correo'] ?? ''); $password=$conn->real_escape_string($_POST['password'] ?? '');
$sql="INSERT INTO usuarios(nombre,correo,password,rol) VALUES('$nombre','$correo','$password','USUARIO')";
echo $conn->query($sql)?json_encode(['mensaje'=>'Usuario registrado']):json_encode(['error'=>'No se pudo registrar']);
?>
