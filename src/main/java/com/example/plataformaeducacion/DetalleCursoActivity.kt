package com.example.plataformaeducacion

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.button.MaterialButton
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class DetalleCursoActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var textCursoNombre: TextView
    private lateinit var textVideoTitulo: TextView
    private lateinit var textVideoEstado: TextView
    private lateinit var buttonAbrirVideo: MaterialButton
    private lateinit var buttonMarcarVisto: MaterialButton
    private lateinit var buttonCalificar: MaterialButton
    private lateinit var progressBar: ProgressBar
    private lateinit var containerVideos: LinearLayout

    private var cursoId = 0
    private var cursoNombre = ""
    private var videos: List<VideoResponse> = emptyList()
    private val vistos = mutableSetOf<Int>()
    private var selectedIndex = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_detalle_curso)
        cursoId = intent.getIntExtra(EXTRA_CURSO_ID, 0)
        cursoNombre = intent.getStringExtra(EXTRA_CURSO_NOMBRE).orEmpty()

        textCursoNombre = findViewById(R.id.textDetalleCursoNombre)
        textVideoTitulo = findViewById(R.id.textVideoTituloActual)
        textVideoEstado = findViewById(R.id.textVideoEstadoActual)
        buttonAbrirVideo = findViewById(R.id.buttonAbrirVideo)
        buttonMarcarVisto = findViewById(R.id.buttonMarcarVisto)
        buttonCalificar = findViewById(R.id.buttonIrCalificar)
        progressBar = findViewById(R.id.progressDetalleCurso)
        containerVideos = findViewById(R.id.containerVideos)

        textCursoNombre.text = cursoNombre

        buttonAbrirVideo.setOnClickListener { abrirVideoActual() }
        buttonMarcarVisto.setOnClickListener { marcarActualComoVisto() }
        buttonCalificar.setOnClickListener {
            val intent = Intent(this, CalificarCursoActivity::class.java)
            intent.putExtra(CalificarCursoActivity.EXTRA_CURSO_ID, cursoId)
            startActivity(intent)
        }

        cargarVideos()
    }

    private fun cargarVideos() {
        progressBar.visibility = View.VISIBLE
        RetrofitClient.api.obtenerVideos(cursoId).enqueue(object : Callback<List<VideoResponse>> {
            override fun onResponse(call: Call<List<VideoResponse>>, response: Response<List<VideoResponse>>) {
                val body = response.body().orEmpty()
                if (!response.isSuccessful || body.isEmpty()) {
                    progressBar.visibility = View.GONE
                    Toast.makeText(this@DetalleCursoActivity, "No hay videos disponibles", Toast.LENGTH_SHORT).show()
                    return
                }
                videos = body.sortedBy { it.orden }
                cargarProgreso()
            }

            override fun onFailure(call: Call<List<VideoResponse>>, t: Throwable) {
                progressBar.visibility = View.GONE
                Toast.makeText(this@DetalleCursoActivity, "No se pudieron cargar los videos", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun cargarProgreso() {
        RetrofitClient.api.obtenerProgreso(sessionManager.getUserId(), cursoId).enqueue(object : Callback<ProgresoResponse> {
            override fun onResponse(call: Call<ProgresoResponse>, response: Response<ProgresoResponse>) {
                progressBar.visibility = View.GONE
                vistos.clear()
                vistos.addAll(response.body()?.vistos.orEmpty())
                selectedIndex = videos.indexOfFirst { !vistos.contains(it.id) }.let { if (it == -1) 0 else it }
                renderPantalla()
            }

            override fun onFailure(call: Call<ProgresoResponse>, t: Throwable) {
                progressBar.visibility = View.GONE
                renderPantalla()
            }
        })
    }

    private fun renderPantalla() {
        if (videos.isEmpty()) return

        val actual = videos[selectedIndex.coerceIn(0, videos.lastIndex)]
        textVideoTitulo.text = actual.titulo
        textVideoEstado.text = when {
            vistos.contains(actual.id) -> "Leccion completada"
            isUnlocked(selectedIndex) -> "Leccion disponible"
            else -> "Debes completar la leccion anterior"
        }

        buttonAbrirVideo.isEnabled = isUnlocked(selectedIndex)
        buttonMarcarVisto.isEnabled = isUnlocked(selectedIndex) && !vistos.contains(actual.id)
        buttonCalificar.visibility = if (videos.all { vistos.contains(it.id) }) View.VISIBLE else View.GONE

        containerVideos.removeAllViews()
        val inflater = LayoutInflater.from(this)
        videos.forEachIndexed { index, video ->
            val view = inflater.inflate(R.layout.item_video_card, containerVideos, false)
            view.findViewById<TextView>(R.id.textLeccionNumero).text = "Leccion ${index + 1}"
            view.findViewById<TextView>(R.id.textVideoCardTitulo).text = video.titulo
            view.findViewById<TextView>(R.id.textVideoCardEstado).text = when {
                vistos.contains(video.id) -> "Vista"
                isUnlocked(index) -> "Disponible"
                else -> "Bloqueada"
            }
            view.setOnClickListener {
                if (vistos.contains(video.id) || isUnlocked(index)) {
                    selectedIndex = index
                    renderPantalla()
                } else {
                    Toast.makeText(this, "No puedes saltarte lecciones", Toast.LENGTH_SHORT).show()
                }
            }
            containerVideos.addView(view)
        }
    }

    private fun isUnlocked(index: Int): Boolean {
        if (index <= 0) return true
        return vistos.contains(videos[index - 1].id)
    }

    private fun abrirVideoActual() {
        val actual = videos.getOrNull(selectedIndex) ?: return
        if (!isUnlocked(selectedIndex)) {
            Toast.makeText(this, "No puedes saltarte lecciones", Toast.LENGTH_SHORT).show()
            return
        }
        startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(normalizeUrl(actual.url_video))))
    }

    private fun marcarActualComoVisto() {
        val actual = videos.getOrNull(selectedIndex) ?: return
        if (!isUnlocked(selectedIndex)) {
            Toast.makeText(this, "No puedes saltarte lecciones", Toast.LENGTH_SHORT).show()
            return
        }
        RetrofitClient.api.guardarProgreso(sessionManager.getUserId(), cursoId, actual.id).enqueue(object : Callback<MensajeResponse> {
            override fun onResponse(call: Call<MensajeResponse>, response: Response<MensajeResponse>) {
                vistos.add(actual.id)
                sessionManager.setUltimoCursoId(cursoId)
                if (selectedIndex < videos.lastIndex) {
                    selectedIndex += 1
                }
                renderPantalla()
            }

            override fun onFailure(call: Call<MensajeResponse>, t: Throwable) {
                Toast.makeText(this@DetalleCursoActivity, "No se pudo guardar el progreso", Toast.LENGTH_SHORT).show()
            }
        })
    }

    companion object {
        const val EXTRA_CURSO_ID = "curso_id"
        const val EXTRA_CURSO_NOMBRE = "curso_nombre"
    }

    private fun normalizeUrl(value: String): String {
        return if (value.startsWith("http://") || value.startsWith("https://")) {
            value
        } else {
            "http://10.0.2.2/plataforma_educacion/${value.trimStart('/')}"
        }
    }
}
