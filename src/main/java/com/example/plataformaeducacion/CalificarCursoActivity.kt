package com.example.plataformaeducacion

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.EditText
import android.widget.ProgressBar
import android.widget.Spinner
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class CalificarCursoActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var spinnerEstrellas: Spinner
    private lateinit var inputComentario: EditText
    private lateinit var buttonEnviar: Button
    private lateinit var buttonVerCertificado: Button
    private lateinit var textEstado: TextView
    private lateinit var progressBar: ProgressBar

    private var usuarioId: Int = 0
    private var cursoId: Int = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_calificar_curso)

        supportActionBar?.title = "Calificar curso"

        usuarioId = intent.getIntExtra(EXTRA_USUARIO_ID, sessionManager.getUserId())
        cursoId = intent.getIntExtra(EXTRA_CURSO_ID, sessionManager.getUltimoCursoId())

        spinnerEstrellas = findViewById(R.id.spinnerEstrellas)
        inputComentario = findViewById(R.id.inputComentario)
        buttonEnviar = findViewById(R.id.buttonEnviarCalificacion)
        buttonVerCertificado = findViewById(R.id.buttonVerCertificado)
        textEstado = findViewById(R.id.textEstadoCalificacion)
        progressBar = findViewById(R.id.progressCalificacion)
        buttonVerCertificado.isEnabled = false

        val opciones = listOf("5 - Excelente", "4 - Muy bueno", "3 - Bueno", "2 - Regular", "1 - Malo")
        spinnerEstrellas.adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, opciones)

        buttonEnviar.setOnClickListener { enviarCalificacion() }

        buttonVerCertificado.setOnClickListener {
            val intent = Intent(this, CertificadoActivity::class.java)
            intent.putExtra(CertificadoActivity.EXTRA_CURSO_ID, cursoId)
            startActivity(intent)
        }

        if (usuarioId <= 0 || cursoId <= 0) {
            textEstado.text = "No se pudo identificar el curso o el usuario."
            buttonEnviar.isEnabled = false
        }
    }

    private fun enviarCalificacion() {
        val comentario = inputComentario.text.toString().trim()
        if (comentario.isBlank()) {
            inputComentario.error = "Escribe un comentario"
            inputComentario.requestFocus()
            return
        }

        val estrellas = 5 - spinnerEstrellas.selectedItemPosition
        progressBar.visibility = View.VISIBLE
        buttonEnviar.isEnabled = false
        textEstado.text = "Enviando calificacion..."

        RetrofitClient.api.calificarCurso(usuarioId, cursoId, estrellas, comentario)
            .enqueue(object : Callback<MensajeResponse> {
                override fun onResponse(call: Call<MensajeResponse>, response: Response<MensajeResponse>) {
                    progressBar.visibility = View.GONE
                    buttonEnviar.isEnabled = true

                    val body = response.body()
                    if (!response.isSuccessful || body == null || !body.error.isNullOrBlank()) {
                        textEstado.text = body?.error ?: "No fue posible registrar la calificacion."
                        return
                    }

                    sessionManager.setUltimoCursoId(cursoId)
                    textEstado.text = body.mensaje ?: "Curso calificado correctamente."
                    buttonVerCertificado.visibility = View.VISIBLE
                    buttonVerCertificado.isEnabled = true
                    Toast.makeText(this@CalificarCursoActivity, "Calificacion enviada correctamente.", Toast.LENGTH_LONG).show()
                }

                override fun onFailure(call: Call<MensajeResponse>, t: Throwable) {
                    progressBar.visibility = View.GONE
                    buttonEnviar.isEnabled = true
                    buttonVerCertificado.isEnabled = false
                    textEstado.text = "Error de conexion al enviar la calificacion."
                }
            })
    }

    companion object {
        const val EXTRA_USUARIO_ID = "usuario_id"
        const val EXTRA_CURSO_ID = "curso_id"
    }
}
