<?php include "_headers.php"; include "../config/conexion.php";
$usuario_id=(int)($_POST['usuario_id'] ?? 0); $curso_id=(int)($_POST['curso_id'] ?? 0); $estrellas=(int)($_POST['estrellas'] ?? 0); $comentario=$conn->real_escape_string($_POST['comentario'] ?? '');
$sql="INSERT INTO calificaciones(usuario_id,curso_id,estrellas,comentario) VALUES($usuario_id,$curso_id,$estrellas,'$comentario')";

if ($conn->query($sql)) {
    $archivo = "certificado_usuario_{$usuario_id}_curso_{$curso_id}.pdf";
    $reconocimiento=$conn->query("SELECT id FROM reconocimientos WHERE usuario_id=$usuario_id AND curso_id=$curso_id LIMIT 1")->fetch_assoc();
    if ($reconocimiento) {
        $conn->query("UPDATE reconocimientos SET archivo='$archivo' WHERE id=".(int)$reconocimiento['id']);
    } else {
        $conn->query("INSERT INTO reconocimientos(usuario_id,curso_id,archivo) VALUES($usuario_id,$curso_id,'$archivo')");
    }
    echo json_encode(['mensaje'=>'Curso calificado']);
} else {
    echo json_encode(['error'=>'Error al calificar']);
}
?>
