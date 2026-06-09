<?php
// Estructura HTML compartida por todas las pantallas.

function layout_start($title, $bodyClass = '', $flash = '') {
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?> | Confia+</title>
        <link rel="stylesheet" href="assets/css/styles.css">
    </head>
    <body class="<?= h($bodyClass) ?>">
        <?php if ($flash): ?>
            <div class="flash"><?= h($flash) ?></div>
        <?php endif; ?>
    <?php
}

function layout_end() {
    ?>
        <script src="assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

function sidebar($role, $active) {
    $items = $role === 'psychologist'
        ? ['patients' => 'Pacientes', 'analysis' => 'IA Analisis', 'agenda' => 'Agenda', 'messages' => 'Mensajes']
        : ['home' => 'Inicio', 'messages' => 'Mensajes', 'appointments' => 'Mis Citas'];
    ?>
    <aside class="sidebar">
        <img src="assets/img/logo-dark.png" alt="Confia+" class="logo-dark">
        <nav>
            <?php foreach ($items as $key => $label): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="index.php?page=<?= h($key) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form method="post">
            <input type="hidden" name="action" value="logout">
            <button class="logout">Salir</button>
        </form>
        <div class="side-mark">Confia+</div>
    </aside>
    <?php
}

function app_start($title, $active, $role, $flash = '') {
    layout_start($title, 'app ' . $role, $flash);
    ?>
    <div class="app-shell">
        <?php sidebar($role, $active); ?>
        <main class="workspace">
            <header><span></span><strong><?= h($title) ?></strong><span></span></header>
    <?php
}

function app_end() {
    ?>
        </main>
    </div>
    <?php
    layout_end();
}
