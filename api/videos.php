<?php include "_headers.php"; include "../config/conexion.php";
$curso_id=(int)($_GET['curso_id'] ?? 0); $r=$conn->query("SELECT * FROM videos WHERE curso_id=$curso_id ORDER BY orden ASC"); $datos=[]; while($row=$r->fetch_assoc()){$datos[]=$row;} echo json_encode($datos);
?>
