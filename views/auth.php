<?php
// HTML de login y registro.

function render_auth_screen($mode, $flash = '') {
    $isLogin = $mode === 'login';
    layout_start($isLogin ? 'Iniciar sesion' : 'Registro', 'auth', $flash);
    ?>
    <main class="auth-shell">
        <section class="brand-panel"></section>
        <section class="auth-panel">
            <img src="assets/img/logo-large.png" class="logo-large" alt="Confia+">
            <h1><?= $isLogin ? 'Que bueno verte de nuevo' : 'Unete a Confia+' ?></h1>
            <h2><?= $isLogin ? 'Inicia sesion para continuar' : 'Crea tu cuenta terapeutica' ?></h2>

            <form method="post" class="auth-form">
                <input type="hidden" name="action" value="<?= $isLogin ? 'login' : 'register' ?>">

                <?php if (!$isLogin): ?>
                    <label>Nombre completo
                        <input name="name" required placeholder="Tu nombre">
                    </label>
                <?php endif; ?>

                <label>Correo electronico
                    <input type="email" name="email" required placeholder="correo@ejemplo.com">
                </label>

                <label>Contrasena
                    <input type="password" name="password" required placeholder="Minimo 6 caracteres">
                </label>

                <?php if (!$isLogin): ?>
                    <div class="role-picker">
                        <span>Cual es tu rol?</span>
                        <label><input type="radio" name="role" value="psychologist"> Psicologo</label>
                        <label><input type="radio" name="role" value="patient" checked> Paciente</label>
                    </div>
                <?php endif; ?>

                <button class="pill-btn" type="submit"><?= $isLogin ? 'Iniciar sesion' : 'Registrarse' ?> <span>-&gt;</span></button>
            </form>

            <a class="switch-auth" href="index.php?page=<?= $isLogin ? 'register' : 'login' ?>">
                <?= $isLogin ? 'Aun no tienes cuenta? Registrate aqui' : 'Ya tienes cuenta? Inicia sesion' ?>
            </a>

            <?php if ($isLogin): ?>
                <p class="demo">Demo psicologo: psicologo@confia.test / psico123<br>Demo paciente: paciente@confia.test / paciente123</p>
            <?php endif; ?>
        </section>
    </main>
    <?php
    layout_end();
}
