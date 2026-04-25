package com.example.plataformaeducacion

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.button.MaterialButton
import com.google.android.material.textfield.TextInputEditText
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.io.File
import java.io.FileOutputStream

class PerfilActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var textNombre: TextView
    private lateinit var textCorreo: TextView
    private lateinit var textTemaActual: TextView
    private lateinit var imagePreview: ImageView
    private lateinit var inputPassword: TextInputEditText
    private lateinit var buttonTemaClaro: MaterialButton
    private lateinit var buttonTemaOscuro: MaterialButton
    private lateinit var buttonCambiarImagen: MaterialButton
    private lateinit var buttonGuardar: MaterialButton
    private lateinit var progressBar: ProgressBar

    private var temaSeleccionado = "claro"
    private var imagenUri: Uri? = null

    private val selectorImagen = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        uri?.let {
            imagenUri = it
            imagePreview.setImageURI(it)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        sessionManager = SessionManager(this)
        ThemeUtils.applyTheme(sessionManager.getTema())
        super.onCreate(savedInstanceState)

        if (!sessionManager.isLoggedIn()) {
            startActivity(Intent(this, LoginActivity::class.java))
            finish()
            return
        }

        setContentView(R.layout.activity_perfil)

        textNombre = findViewById(R.id.textPerfilNombre)
        textCorreo = findViewById(R.id.textPerfilCorreo)
        textTemaActual = findViewById(R.id.textTemaActual)
        imagePreview = findViewById(R.id.imagePerfilPreview)
        inputPassword = findViewById(R.id.inputNuevaPassword)
        buttonTemaClaro = findViewById(R.id.buttonTemaClaro)
        buttonTemaOscuro = findViewById(R.id.buttonTemaOscuro)
        buttonCambiarImagen = findViewById(R.id.buttonCambiarImagen)
        buttonGuardar = findViewById(R.id.buttonGuardarPerfil)
        progressBar = findViewById(R.id.progressPerfil)

        buttonTemaClaro.setOnClickListener {
            temaSeleccionado = "claro"
            actualizarTemaActual()
        }

        buttonTemaOscuro.setOnClickListener {
            temaSeleccionado = "oscuro"
            actualizarTemaActual()
        }

        buttonCambiarImagen.setOnClickListener {
            selectorImagen.launch("image/*")
        }

        buttonGuardar.setOnClickListener {
            guardarPerfil()
        }

        cargarPerfil()
    }

    private fun cargarPerfil() {
        progressBar.visibility = View.VISIBLE

        RetrofitClient.api.obtenerPerfil(sessionManager.getUserId())
            .enqueue(object : Callback<PerfilResponse> {
                override fun onResponse(
                    call: Call<PerfilResponse>,
                    response: Response<PerfilResponse>
                ) {
                    progressBar.visibility = View.GONE

                    val body = response.body()

                    if (!response.isSuccessful || body == null || !body.error.isNullOrBlank()) {
                        textNombre.text = sessionManager.getNombre()
                        textCorreo.text = sessionManager.getCorreo()
                        temaSeleccionado = sessionManager.getTema()
                    } else {
                        sessionManager.updateProfile(body)
                        textNombre.text = body.nombre
                        textCorreo.text = body.correo
                        temaSeleccionado = body.tema ?: "claro"
                    }

                    actualizarTemaActual()
                }

                override fun onFailure(call: Call<PerfilResponse>, t: Throwable) {
                    progressBar.visibility = View.GONE
                    textNombre.text = sessionManager.getNombre()
                    textCorreo.text = sessionManager.getCorreo()
                    temaSeleccionado = sessionManager.getTema()
                    actualizarTemaActual()
                }
            })
    }

    private fun actualizarTemaActual() {
        textTemaActual.text =
            "Tema actual: ${if (temaSeleccionado == "oscuro") "Oscuro" else "Claro"}"

        buttonTemaClaro.alpha = if (temaSeleccionado == "claro") 1f else 0.6f
        buttonTemaOscuro.alpha = if (temaSeleccionado == "oscuro") 1f else 0.6f
    }

    private fun guardarPerfil() {
        progressBar.visibility = View.VISIBLE
        buttonGuardar.isEnabled = false

        val usuarioId = sessionManager.getUserId()
            .toString()
            .toRequestBody("text/plain".toMediaTypeOrNull())

        val password = inputPassword.text
            ?.toString()
            ?.trim()
            .orEmpty()
            .toRequestBody("text/plain".toMediaTypeOrNull())

        val tema = temaSeleccionado.toRequestBody("text/plain".toMediaTypeOrNull())

        val imagen = imagenUri?.let { uri ->
            buildImagePart(uri)
        }

        RetrofitClient.api.actualizarPerfil(usuarioId, password, tema, imagen)
            .enqueue(object : Callback<PerfilResponse> {
                override fun onResponse(
                    call: Call<PerfilResponse>,
                    response: Response<PerfilResponse>
                ) {
                    progressBar.visibility = View.GONE
                    buttonGuardar.isEnabled = true

                    val body = response.body()

                    if (!response.isSuccessful || body == null || !body.error.isNullOrBlank()) {
                        Toast.makeText(
                            this@PerfilActivity,
                            body?.error ?: "No se pudo actualizar el perfil",
                            Toast.LENGTH_SHORT
                        ).show()
                        return
                    }

                    sessionManager.updateProfile(body)
                    Toast.makeText(
                        this@PerfilActivity,
                        "Perfil actualizado correctamente",
                        Toast.LENGTH_SHORT
                    ).show()

                    ThemeUtils.applyTheme(body.tema)
                    recreate()
                }

                override fun onFailure(call: Call<PerfilResponse>, t: Throwable) {
                    progressBar.visibility = View.GONE
                    buttonGuardar.isEnabled = true

                    Toast.makeText(
                        this@PerfilActivity,
                        "Error de conexión con la API",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            })
    }

    private fun buildImagePart(uri: Uri): MultipartBody.Part? {
        return try {
            val inputStream = contentResolver.openInputStream(uri) ?: return null
            val tempFile = File.createTempFile("perfil_upload", ".jpg", cacheDir)

            inputStream.use { input ->
                FileOutputStream(tempFile).use { output ->
                    input.copyTo(output)
                }
            }

            val body = tempFile.asRequestBody("image/*".toMediaTypeOrNull())

            MultipartBody.Part.createFormData(
                "imagen",
                tempFile.name,
                body
            )
        } catch (e: Exception) {
            Toast.makeText(this, "No se pudo cargar la imagen", Toast.LENGTH_SHORT).show()
            null
        }
    }
}