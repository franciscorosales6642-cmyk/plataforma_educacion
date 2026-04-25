package com.example.plataformaeducacion

import android.content.Context

class SessionManager(context: Context) {

    private val prefs = context.getSharedPreferences("plataforma_session", Context.MODE_PRIVATE)

    fun saveUser(usuario: UsuarioResponse) {
        prefs.edit()
            .putInt(KEY_ID, usuario.id ?: 0)
            .putString(KEY_NOMBRE, usuario.nombre ?: "")
            .putString(KEY_CORREO, usuario.correo ?: "")
            .putString(KEY_TEMA, usuario.tema ?: "claro")
            .putString(KEY_IMAGEN, usuario.imagen ?: "")
            .apply()
    }

    fun updateProfile(perfil: PerfilResponse) {
        prefs.edit()
            .putInt(KEY_ID, perfil.id ?: getUserId())
            .putString(KEY_NOMBRE, perfil.nombre ?: getNombre())
            .putString(KEY_CORREO, perfil.correo ?: getCorreo())
            .putString(KEY_TEMA, perfil.tema ?: getTema())
            .putString(KEY_IMAGEN, perfil.imagen ?: getImagen())
            .apply()
    }

    fun isLoggedIn(): Boolean = getUserId() > 0
    fun getUserId(): Int = prefs.getInt(KEY_ID, 0)
    fun getNombre(): String = prefs.getString(KEY_NOMBRE, "") ?: ""
    fun getCorreo(): String = prefs.getString(KEY_CORREO, "") ?: ""
    fun getTema(): String = prefs.getString(KEY_TEMA, "claro") ?: "claro"
    fun getImagen(): String = prefs.getString(KEY_IMAGEN, "") ?: ""
    fun getUltimoCursoId(): Int = prefs.getInt(KEY_ULTIMO_CURSO_ID, 0)

    fun setUltimoCursoId(cursoId: Int) {
        prefs.edit().putInt(KEY_ULTIMO_CURSO_ID, cursoId).apply()
    }

    fun clear() {
        prefs.edit().clear().apply()
    }

    companion object {
        private const val KEY_ID = "id"
        private const val KEY_NOMBRE = "nombre"
        private const val KEY_CORREO = "correo"
        private const val KEY_TEMA = "tema"
        private const val KEY_IMAGEN = "imagen"
        private const val KEY_ULTIMO_CURSO_ID = "ultimo_curso_id"
    }
}
