package com.example.plataformaeducacion

import androidx.appcompat.app.AppCompatDelegate

object ThemeUtils {
    fun applyTheme(theme: String?) {
        val mode = if (theme == "oscuro") {
            AppCompatDelegate.MODE_NIGHT_YES
        } else {
            AppCompatDelegate.MODE_NIGHT_NO
        }
        AppCompatDelegate.setDefaultNightMode(mode)
    }
}
