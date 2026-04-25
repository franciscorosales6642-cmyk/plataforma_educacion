package com.example.plataformaeducacion

data class UsuarioResponse(
    val id: Int?,
    val nombre: String?,
    val correo: String?,
    val rol: String?,
    val tema: String?,
    val imagen: String?,
    val error: String?
)

data class CursoResponse(val id: Int, val nombre: String, val descripcion: String, val profesor: String)
data class VideoResponse(val id: Int, val curso_id: Int, val titulo: String, val url_video: String, val orden: Int, val visualizaciones: Int)
data class MensajeResponse(val mensaje: String?, val error: String?)
data class ProgresoResponse(val vistos: List<Int> = emptyList(), val error: String? = null)
data class PerfilResponse(
    val id: Int?,
    val nombre: String?,
    val correo: String?,
    val tema: String?,
    val imagen: String?,
    val imagen_url: String?,
    val mensaje: String?,
    val error: String?
)
data class CertificadoResponse(
    val usuario_id: Int?,
    val curso_id: Int?,
    val usuario: String?,
    val curso: String?,
    val profesor: String?,
    val fecha: String?,
    val folio: String?,
    val archivo: String?,
    val url_pdf: String?,
    val pdf_url: String?,
    val error: String?
)
