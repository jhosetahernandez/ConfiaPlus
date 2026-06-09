<?php
// Arranque general: inicia sesion, carga dependencias, crea la base de datos y decide que vista mostrar.
session_start();

require __DIR__ . '/helpers.php';
require __DIR__ . '/database.php';
require __DIR__ . '/queries.php';
require __DIR__ . '/actions.php';

try {
    // Crea la base de datos/tablas y usuarios demo si todavia no existen.
    init_database();

    // Si llega un formulario POST, se procesa antes de pintar cualquier HTML.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handle_post();
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Confia+ necesita MySQL activo</h1>';
    echo '<p>Revisa <code>config/database.php</code>. Error: ' . h($e->getMessage()) . '</p>';
    exit;
}

$user = current_user();
$page = $_GET['page'] ?? ($user ? 'dashboard' : 'login');
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Las vistas contienen el HTML de la interfaz.
require __DIR__ . '/../views/layout.php';
require __DIR__ . '/../views/auth.php';
require __DIR__ . '/../views/messages.php';
require __DIR__ . '/../views/psychologist.php';
require __DIR__ . '/../views/patient.php';

// Router simple: si no hay sesion, muestra login o registro.
if (!$user && $page !== 'register') {
    render_auth_screen('login', $flash);
    exit;
}

if (!$user && $page === 'register') {
    render_auth_screen('register', $flash);
    exit;
}

// Router por rol: psicologo y paciente siempre ven interfaces separadas.
if ($user['role'] === 'psychologist') {
    render_psychologist_page($page, $user, $flash);
} else {
    render_patient_page($page, $user, $flash);
}
