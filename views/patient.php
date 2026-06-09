<?php
// HTML de todas las pantallas del paciente.

function render_patient_page($page, $user, $flash = '') {
    $active = in_array($page, ['home', 'messages', 'appointments'], true) ? $page : 'home';
    $titles = [
        'home' => 'Panel de Control Paciente',
        'messages' => 'Modulo de Mensajes',
        'appointments' => 'Mis Citas Programadas',
    ];

    app_start($titles[$active], $active, 'patient', $flash);
    $psychologist = assigned_psychologist($user['id']);

    if ($active === 'home') {
        render_patient_home($user);
    }
    if ($active === 'messages') {
        echo '<div class="ai-helper">Quieres ayuda para explicarle al psicologo que sensacion fisica tuviste: miedo, sudoracion, presion o respiracion?</div>';
        render_messages_module($user, $psychologist);
    }
    if ($active === 'appointments') {
        render_patient_appointments($user, $psychologist);
    }

    app_end();
}

function render_patient_home($user) {
    $plans = plans_for_patient($user['id']);
    $logs = logs_for_patient($user['id']);
    $profile = profile_for_patient($user['id']);
    ?>
    <div class="patient-home">
        <section class="progress-band">
            <strong><?= (int) ($profile['bravery_points'] ?? 0) ?> pts</strong>
            <span>Medallas de valentia</span>
            <b><?= count($logs) ?> avances registrados</b>
        </section>

        <section class="cards">
            <?php foreach ($plans as $plan): ?>
                <article>
                    <b><?= h($plan['title']) ?> <?= $plan['challenge_of_day'] ? '*' : '' ?></b>
                    <span><?= h($plan['status']) ?></span>
                    <p><?= h($plan['description']) ?></p>

                    <?php if ($plan['status'] !== 'completed'): ?>
                        <form method="post" class="panel-form compact">
                            <input type="hidden" name="action" value="complete_activity">
                            <input type="hidden" name="return" value="home">
                            <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                            <input name="emotion" required placeholder="Emocion principal">
                            <input type="range" name="stress_score" min="1" max="10" value="5">
                            <textarea name="experience" required placeholder="Describe tu experiencia"></textarea>
                            <button>Registrar avance</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>

        <h3>Historial de actividades</h3>
        <div class="timeline">
            <?php foreach ($logs as $log): ?>
                <p><b><?= h($log['title']) ?></b> <?= h($log['emotion']) ?> - estres <?= (int) $log['stress_score'] ?></p>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function render_patient_appointments($user, $psychologist) {
    $appointments = appointments_for_patient($user['id']);
    $reminders = reminders_for_patient($user['id']);
    ?>
    <div class="cards two">
        <?php foreach ($appointments as $appointment): ?>
            <article>
                <b>Sesion con <?= h($psychologist['name'] ?? 'Psicologo') ?></b>
                <p><?= h($appointment['appointment_at']) ?></p>
                <span><?= h($appointment['summary']) ?></span>
            </article>
        <?php endforeach; ?>

        <?php foreach ($reminders as $reminder): ?>
            <article>
                <b>Recordatorio</b>
                <p><?= h($reminder['reminder_at']) ?></p>
                <span><?= h($reminder['title']) ?></span>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="ai-alert">Recuerda anotar como te sentiste al completar tu reto antes de entrar a la cita.</div>
    <?php
}
