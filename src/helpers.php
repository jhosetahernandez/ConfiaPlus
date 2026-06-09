<?php
// Funciones pequenas usadas por todo el proyecto.

function h($value) {
    // Evita que texto escrito por usuarios se ejecute como HTML o JavaScript.
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($url) {
    // Redirecciona y corta la ejecucion para evitar que se siga pintando HTML.
    header("Location: $url");
    exit;
}

function safe_return_page($value) {
    // Limpia el destino de retorno de los formularios para aceptar solo parametros simples.
    $value = preg_replace('/[^a-zA-Z0-9_=&-]/', '', (string) $value);
    return $value ?: 'dashboard';
}

function ai_feedback($text) {
    // Simulacion simple de IA: detecta palabras o frases que pueden necesitar seguimiento terapeutico.
    $lower = mb_strtolower($text);
    if (str_contains($lower, 'no se') || str_contains($lower, 'despues') || str_contains($lower, 'da igual')) {
        return 'Lenguaje evasivo detectado. Se recomienda reforzar validacion emocional antes de seguir.';
    }
    if (str_contains($lower, 'miedo') || str_contains($lower, 'ansiedad') || str_contains($lower, 'estres')) {
        return 'El mensaje contiene emociones intensas. Sugerencia: pedir descripcion corporal y nivel de intensidad.';
    }
    return 'Comunicacion clara. Mantener seguimiento del estado emocional.';
}
