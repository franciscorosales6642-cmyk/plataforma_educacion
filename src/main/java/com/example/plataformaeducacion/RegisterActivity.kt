package com.example.plataformaeducacion

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

class RegisterActivity : AppCompatActivity() {

    private lateinit var inputNombre: TextInputEditText
    private lateinit var inputCorreo: TextInputEditText
    private lateinit var inputPassword: TextInputEditText
    private lateinit var buttonRegistrar: MaterialButton
    private lateinit var progressBar: ProgressBar
    private lateinit var textEstado: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        ThemeUtils.applyTheme(SessionManager(this).getTema())
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_register)

        inputNombre = findViewById(R.id.inputNombre)
        inputCorreo = findViewById(R.id.inputCorreoRegistro)
        inputPassword = findViewById(R.id.inputPasswordRegistro)
        buttonRegistrar = findViewById(R.id.buttonRegistrar)
        progressBar = findViewById(R.id.progressRegister)
        textEstado = findViewById(R.id.textEstadoRegister)

        buttonRegistrar.setOnClickListener { registrarUsuario() }
    }

    private fun registrarUsuario() {
        val nombre = inputNombre.text?.toString()?.trim().orEmpty()
        val correo = inputCorreo.text?.toString()?.trim().orEmpty()
        val password = inputPassword.text?.toString()?.trim().orEmpty()

        if (nombre.isBlank()) {
            inputNombre.error = "Ingresa tu nombre completo"
            return
        }
        if (correo.isBlank()) {
            inputCorreo.error = "Ingresa tu correo"
            return
        }
        if (password.isBlank()) {
            inputPassword.error = "Ingresa tu contrasena"
            return
        }

        progressBar.visibility = View.VISIBLE
        buttonRegistrar.isEnabled = false
        textEstado.text = "Creando cuenta..."

        RetrofitClient.api.registro(nombre, correo, password).enqueue(object : Callback<MensajeResponse> {
            override fun onResponse(call: Call<MensajeResponse>, response: Response<MensajeResponse>) {
                progressBar.visibility = View.GONE
                buttonRegistrar.isEnabled = true
                val body = response.body()
                textEstado.text = if (!response.isSuccessful || body == null || !body.error.isNullOrBlank()) {
                    body?.error ?: "No se pudo registrar"
                } else {
                    finish()
                    body.mensaje ?: "Usuario registrado"
                }
            }

            override fun onFailure(call: Call<MensajeResponse>, t: Throwable) {
                progressBar.visibility = View.GONE
                buttonRegistrar.isEnabled = true
                textEstado.text = "Error de conexion con la API"
            }
        })
    }
}
