package com.example.plataformaeducacion

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class CursosActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var progressBar: ProgressBar
    private lateinit var textEmpty: TextView
    private lateinit var containerCursos: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_cursos)
        progressBar = findViewById(R.id.progressCursos)
        textEmpty = findViewById(R.id.textEmptyCursos)
        containerCursos = findViewById(R.id.containerCursos)
        cargarCursos()
    }

    private fun cargarCursos() {
        progressBar.visibility = View.VISIBLE
        textEmpty.visibility = View.GONE
        containerCursos.removeAllViews()

        RetrofitClient.api.obtenerCursos().enqueue(object : Callback<List<CursoResponse>> {
            override fun onResponse(call: Call<List<CursoResponse>>, response: Response<List<CursoResponse>>) {
                progressBar.visibility = View.GONE
                val cursos = response.body().orEmpty()
                if (!response.isSuccessful || cursos.isEmpty()) {
                    textEmpty.visibility = View.VISIBLE
                    return
                }

                val inflater = LayoutInflater.from(this@CursosActivity)
                cursos.forEach { curso ->
                    val view = inflater.inflate(R.layout.item_course_card, containerCursos, false)
                    view.findViewById<TextView>(R.id.textCursoNombre).text = curso.nombre
                    view.findViewById<TextView>(R.id.textCursoProfesor).text = "Profesor: ${curso.profesor}"
                    view.findViewById<TextView>(R.id.textCursoDescripcion).text = curso.descripcion
                    view.setOnClickListener {
                        val intent = Intent(this@CursosActivity, DetalleCursoActivity::class.java)
                        intent.putExtra(DetalleCursoActivity.EXTRA_CURSO_ID, curso.id)
                        intent.putExtra(DetalleCursoActivity.EXTRA_CURSO_NOMBRE, curso.nombre)
                        startActivity(intent)
                    }
                    containerCursos.addView(view)
                }
            }

            override fun onFailure(call: Call<List<CursoResponse>>, t: Throwable) {
                progressBar.visibility = View.GONE
                textEmpty.visibility = View.VISIBLE
                textEmpty.text = "No se pudieron cargar los cursos"
            }
        })
    }
}
