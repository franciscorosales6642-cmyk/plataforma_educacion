<?php include "_headers.php"; include "../config/conexion.php";
$r=$conn->query("SELECT c.id,c.nombre,c.descripcion,p.nombre profesor FROM cursos c INNER JOIN profesores p ON c.profesor_id=p.id ORDER BY c.id DESC"); $datos=[]; while($row=$r->fetch_assoc()){$datos[]=$row;} echo json_encode($datos);
?>
