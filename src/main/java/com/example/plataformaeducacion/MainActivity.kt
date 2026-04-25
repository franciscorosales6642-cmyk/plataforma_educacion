package com.example.plataformaeducacion

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var textBienvenida: TextView
    private lateinit var buttonCursos: Button
    private lateinit var buttonPerfil: Button
    private lateinit var buttonCalificar: Button
    private lateinit var buttonCertificado: Button
    private lateinit var buttonCerrarSesion: Button

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_main)

        textBienvenida = findViewById(R.id.textBienvenidaMain)
        buttonCursos = findViewById(R.id.buttonCursos)
        buttonPerfil = findViewById(R.id.buttonPerfil)
        buttonCalificar = findViewById(R.id.buttonAbrirCalificacion)
        buttonCertificado = findViewById(R.id.buttonAbrirCertificado)
        buttonCerrarSesion = findViewById(R.id.buttonCerrarSesion)

        textBienvenida.text = "Hola, ${sessionManager.getNombre()}"

        buttonCursos.setOnClickListener {
            startActivity(Intent(this, CursosActivity::class.java))
        }

        buttonPerfil.setOnClickListener {
            startActivity(Intent(this, PerfilActivity::class.java))
        }

        buttonCalificar.setOnClickListener {
            val ultimoCursoId = sessionManager.getUltimoCursoId()
            if (ultimoCursoId <= 0) {
                Toast.makeText(this, "Primero completa un curso", Toast.LENGTH_SHORT).show()
            } else {
                val intent = Intent(this, CalificarCursoActivity::class.java)
                intent.putExtra(CalificarCursoActivity.EXTRA_CURSO_ID, ultimoCursoId)
                startActivity(intent)
            }
        }

        buttonCertificado.setOnClickListener {
            val ultimoCursoId = sessionManager.getUltimoCursoId()
            if (ultimoCursoId <= 0) {
                Toast.makeText(this, "Todavia no tienes certificado disponible", Toast.LENGTH_SHORT).show()
            } else {
                val intent = Intent(this, CertificadoActivity::class.java)
                intent.putExtra(CertificadoActivity.EXTRA_CURSO_ID, ultimoCursoId)
                startActivity(intent)
            }
        }

        buttonCerrarSesion.setOnClickListener {
            sessionManager.clear()
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
        }
    }
}
