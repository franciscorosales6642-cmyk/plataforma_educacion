package com.example.plataformaeducacion

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.ProgressBar
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.button.MaterialButton
import com.google.android.material.textfield.TextInputEditText
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var inputCorreo: TextInputEditText
    private lateinit var inputPassword: TextInputEditText
    private lateinit var buttonLogin: MaterialButton
    private lateinit var buttonRegister: MaterialButton
    private lateinit var progressBar: ProgressBar
    private lateinit var textEstado: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (sessionManager.isLoggedIn()) {
            startActivity(Intent(this, MainActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_login)

        inputCorreo = findViewById(R.id.inputCorreo)
        inputPassword = findViewById(R.id.inputPassword)
        buttonLogin = findViewById(R.id.buttonLogin)
        buttonRegister = findViewById(R.id.buttonIrRegistro)
        progressBar = findViewById(R.id.progressLogin)
        textEstado = findViewById(R.id.textEstadoLogin)

        buttonLogin.setOnClickListener { iniciarSesion() }
        buttonRegister.setOnClickListener { startActivity(Intent(this, RegisterActivity::class.java)) }
    }

    private fun iniciarSesion() {
        val correo = inputCorreo.text?.toString()?.trim().orEmpty()
        val password = inputPassword.text?.toString()?.trim().orEmpty()

        if (correo.isBlank()) {
            inputCorreo.error = "Ingresa tu correo"
            return
        }
        if (password.isBlank()) {
            inputPassword.error = "Ingresa tu contrasena"
            return
        }

        progressBar.visibility = View.VISIBLE
        buttonLogin.isEnabled = false
        textEstado.text = "Validando acceso..."

        RetrofitClient.api.login(correo, password).enqueue(object : Callback<UsuarioResponse> {
            override fun onResponse(call: Call<UsuarioResponse>, response: Response<UsuarioResponse>) {
                progressBar.visibility = View.GONE
                buttonLogin.isEnabled = true
                val body = response.body()
                if (!response.isSuccessful || body == null || !body.error.isNullOrBlank() || body.id == null) {
                    textEstado.text = body?.error ?: "No se pudo iniciar sesion"
                    return
                }

                sessionManager.saveUser(body)
                ThemeUtils.applyTheme(body.tema)
                startActivity(Intent(this@LoginActivity, MainActivity::class.java))
                finish()
            }

            override fun onFailure(call: Call<UsuarioResponse>, t: Throwable) {
                progressBar.visibility = View.GONE
                buttonLogin.isEnabled = true
                textEstado.text = "Error de conexion con la API"
            }
        })
    }
}
