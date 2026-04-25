package com.example.plataformaeducacion

import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Call
import retrofit2.http.*

interface ApiService {
    @FormUrlEncoded
    @POST("login.php")
    fun login(@Field("correo") correo: String, @Field("password") password: String): Call<UsuarioResponse>

    @FormUrlEncoded
    @POST("registro.php")
    fun registro(@Field("nombre") nombre: String, @Field("correo") correo: String, @Field("password") password: String): Call<MensajeResponse>

    @GET("cursos.php")
    fun obtenerCursos(): Call<List<CursoResponse>>

    @GET("videos.php")
    fun obtenerVideos(@Query("curso_id") cursoId: Int): Call<List<VideoResponse>>

    @GET("progreso.php")
    fun obtenerProgreso(@Query("usuario_id") usuarioId: Int, @Query("curso_id") cursoId: Int): Call<ProgresoResponse>

    @FormUrlEncoded
    @POST("progreso.php")
    fun guardarProgreso(@Field("usuario_id") usuarioId: Int, @Field("curso_id") cursoId: Int, @Field("video_id") videoId: Int): Call<MensajeResponse>

    @FormUrlEncoded
    @POST("calificar.php")
    fun calificarCurso(@Field("usuario_id") usuarioId: Int, @Field("curso_id") cursoId: Int, @Field("estrellas") estrellas: Int, @Field("comentario") comentario: String): Call<MensajeResponse>

    @GET("certificado.php")
    fun obtenerCertificado(@Query("usuario_id") usuarioId: Int, @Query("curso_id") cursoId: Int): Call<CertificadoResponse>

    @GET("perfil.php")
    fun obtenerPerfil(@Query("usuario_id") usuarioId: Int): Call<PerfilResponse>

    @Multipart
    @POST("perfil_actualizar.php")
    fun actualizarPerfil(
        @Part("usuario_id") usuarioId: RequestBody,
        @Part("password") password: RequestBody,
        @Part("tema") tema: RequestBody,
        @Part imagen: MultipartBody.Part?
    ): Call<PerfilResponse>
}
