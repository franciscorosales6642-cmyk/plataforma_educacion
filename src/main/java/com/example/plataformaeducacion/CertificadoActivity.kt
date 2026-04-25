package com.example.plataformaeducacion

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class CertificadoActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var progressBar: ProgressBar
    private lateinit var contentView: View
    private lateinit var emptyView: View
    private lateinit var alumnoText: TextView
    private lateinit var cursoText: TextView
    private lateinit var profesorText: TextView
    private lateinit var fechaText: TextView
    private lateinit var folioText: TextView
    private lateinit var estadoText: TextView
    private lateinit var descargarButton: Button
    private var pdfUrl: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_certificado)

        progressBar = findViewById(R.id.progressCertificado)
        contentView = findViewById(R.id.contentCertificado)
        emptyView = findViewById(R.id.emptyCertificado)
        alumnoText = findViewById(R.id.textAlumno)
        cursoText = findViewById(R.id.textCurso)
        profesorText = findViewById(R.id.textProfesor)
        fechaText = findViewById(R.id.textFecha)
        folioText = findViewById(R.id.textFolio)
        estadoText = findViewById(R.id.textEstadoCertificado)
        descargarButton = findViewById(R.id.buttonDescargarPdf)
        descargarButton.isEnabled = false

        val usuarioId = intent.getIntExtra(EXTRA_USUARIO_ID, sessionManager.getUserId())
        val cursoId = intent.getIntExtra(EXTRA_CURSO_ID, sessionManager.getUltimoCursoId())

        descargarButton.setOnClickListener {
            val url = pdfUrl
            if (url.isNullOrBlank()) {
                Toast.makeText(this, "El PDF aun no esta disponible", Toast.LENGTH_SHORT).show()
            } else {
                startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
            }
        }

        if (usuarioId <= 0 || cursoId <= 0) {
            mostrarError("No se pudo identificar el certificado solicitado")
            return
        }

        cargarCertificado(usuarioId, cursoId)
    }

    private fun cargarCertificado(usuarioId: Int, cursoId: Int) {
        progressBar.visibility = View.VISIBLE
        contentView.visibility = View.GONE
        emptyView.visibility = View.GONE

        RetrofitClient.api.obtenerCertificado(usuarioId, cursoId).enqueue(object : Callback<CertificadoResponse> {
            override fun onResponse(call: Call<CertificadoResponse>, response: Response<CertificadoResponse>) {
                progressBar.visibility = View.GONE
                val certificado = response.body()

                if (!response.isSuccessful || certificado == null || !certificado.error.isNullOrBlank()) {
                    mostrarError(certificado?.error ?: "No fue posible cargar el certificado")
                    return
                }

                pdfUrl = certificado.pdf_url ?: certificado.url_pdf
                alumnoText.text = certificado.usuario ?: "-"
                cursoText.text = certificado.curso ?: "-"
                profesorText.text = certificado.profesor ?: "-"
                fechaText.text = certificado.fecha ?: "-"
                folioText.text = certificado.folio ?: "-"
                estadoText.text = "Certificado emitido y listo para descarga"
                descargarButton.isEnabled = !pdfUrl.isNullOrBlank()
                contentView.visibility = View.VISIBLE
            }

            override fun onFailure(call: Call<CertificadoResponse>, t: Throwable) {
                progressBar.visibility = View.GONE
                mostrarError("Error de conexion al obtener el certificado")
            }
        })
    }

    private fun mostrarError(mensaje: String) {
        descargarButton.isEnabled = false
        emptyView.visibility = View.VISIBLE
        contentView.visibility = View.GONE
        findViewById<TextView>(R.id.textEmptyCertificado).text = mensaje
    }

    companion object {
        const val EXTRA_USUARIO_ID = "usuario_id"
        const val EXTRA_CURSO_ID = "curso_id"
    }
}
