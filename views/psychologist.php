<?php
// HTML de todas las pantallas del psicologo.

function render_psychologist_page($page, $user, $flash = '') {
    $active = in_array($page, ['patients', 'analysis', 'agenda', 'messages'], true) ? $page : 'patients';
    $titles = [
        'patients' => 'Panel de Control Terapeutico',
        'analysis' => 'Modulo de Analisis IA',
        'agenda' => 'Agenda Terapeutica',
        'messages' => 'Modulo de Mensajes',
    ];

    app_start($titles[$active], $active, 'psychologist', $flash);
    $patients = patients_for($user['id']);
    $selectedId = (int) ($_GET['patient'] ?? ($patients[0]['id'] ?? 0));
    $selected = null;
    foreach ($patients as $patient) {
        if ((int) $patient['id'] === $selectedId) {
            $selected = $patient;
        }
    }
    ?>
    <div class="content-grid">
        <section class="patient-list">
            <h3>Pacientes</h3>
            <?php foreach ($patients as $patient): ?>
                <a class="<?= (int) $patient['id'] === $selectedId ? 'selected' : '' ?>" href="index.php?page=<?= h($active) ?>&patient=<?= (int) $patient['id'] ?>">
                    <?= h($patient['name']) ?>
                </a>
            <?php endforeach; ?>
        </section>

        <section class="module">
            <?php
            if ($active === 'patients') {
                render_psychologist_patients($selected);
            }
            if ($active === 'analysis' && $selected) {
                render_psychologist_analysis($selected);
            }
            if ($active === 'agenda' && $selected) {
                render_psychologist_agenda($user, $selected);
            }
            if ($active === 'messages' && $selected) {
                render_messages_module($user, $selected);
            }
            ?>
        </section>
    </div>
    <?php
    app_end();
}

function render_psychologist_patients($selected) {
    ?>
    <div class="split">
        <div>
            <h2>Gestion de pacientes</h2>
            <form method="post" class="panel-form">
                <input type="hidden" name="action" value="create_patient">
                <input type="hidden" name="return" value="patients">
                <input name="name" required placeholder="Nombre del paciente">
                <input name="email" type="email" required placeholder="Correo">
                <input name="password" placeholder="Contrasena inicial">
                <textarea name="notes" placeholder="Notas clinicas iniciales"></textarea>
                <button>Crear paciente</button>
            </form>
        </div>

        <div class="danger-box">
            <h2>Eliminar paciente</h2>
            <p><?= $selected ? 'Deseas eliminar a ' . h($selected['name']) . ' de tu lista de pacientes?' : 'Selecciona un paciente.' ?></p>
            <?php if ($selected): ?>
                <form method="post">
                    <input type="hidden" name="action" value="delete_patient">
                    <input type="hidden" name="return" value="patients">
                    <input type="hidden" name="patient_id" value="<?= (int) $selected['id'] ?>">
                    <button class="danger">Eliminar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function render_psychologist_analysis($selected) {
    $logs = logs_for_patient($selected['id']);
    $plans = plans_for_patient($selected['id']);
    $completed = count(array_filter($plans, fn($plan) => $plan['status'] === 'completed'));
    $chartValues = json_encode(array_reverse(array_column($logs, 'stress_score')) ?: [6, 5, 7, 4, 5]);
    ?>
    <h2>Modulo de analisis IA (Predictivo)</h2>
    <p class="quote">"<?= h($selected['notes'] ?: 'Se sugiere tarea de exposicion moderada y seguimiento emocional semanal.') ?>"</p>

    <div class="stats-row">
        <div><strong><?= count($plans) ?></strong><span>Tareas</span></div>
        <div><strong><?= $completed ?></strong><span>Completadas</span></div>
        <div><strong><?= (int) $selected['stress_level'] ?></strong><span>Estres</span></div>
        <div><strong><?= (int) $selected['bravery_points'] ?></strong><span>Valentia</span></div>
    </div>

    <canvas id="stressChart" data-values="<?= h($chartValues) ?>"></canvas>

    <h3>Crear actividad o reto</h3>
    <form method="post" class="panel-form horizontal">
        <input type="hidden" name="action" value="save_plan">
        <input type="hidden" name="return" value="analysis&patient=<?= (int) $selected['id'] ?>">
        <input type="hidden" name="patient_id" value="<?= (int) $selected['id'] ?>">
        <input name="title" required placeholder="Titulo de actividad">
        <input type="date" name="due_date">
        <label class="check"><input type="checkbox" name="challenge"> Desafio del dia</label>
        <textarea name="description" required placeholder="Descripcion terapeutica"></textarea>
        <button>Asignar</button>
    </form>

    <div class="cards">
        <?php foreach ($plans as $plan): ?>
            <article>
                <b><?= h($plan['title']) ?></b><span><?= h($plan['status']) ?></span>
                <p><?= h($plan['description']) ?></p>
                <form method="post">
                    <input type="hidden" name="action" value="delete_plan">
                    <input type="hidden" name="return" value="analysis&patient=<?= (int) $selected['id'] ?>">
                    <input type="hidden" name="plan_id" value="<?= (int) $plan['id'] ?>">
                    <button class="link-btn">Eliminar</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

function render_psychologist_agenda($user, $selected) {
    $appointments = appointments_for_pair($user['id'], $selected['id']);
    $reminders = reminders_for_pair($user['id'], $selected['id']);
    ?>
    <div class="split">
        <form method="post" class="panel-form">
            <h2>Nueva cita</h2>
            <input type="hidden" name="action" value="create_appointment">
            <input type="hidden" name="return" value="agenda&patient=<?= (int) $selected['id'] ?>">
            <input type="hidden" name="patient_id" value="<?= (int) $selected['id'] ?>">
            <input type="datetime-local" name="appointment_at" required>
            <textarea name="summary" placeholder="Objetivo de la sesion"></textarea>
            <button>Programar</button>
        </form>

        <form method="post" class="panel-form">
            <h2>Recordatorio</h2>
            <input type="hidden" name="action" value="create_reminder">
            <input type="hidden" name="return" value="agenda&patient=<?= (int) $selected['id'] ?>">
            <input type="hidden" name="patient_id" value="<?= (int) $selected['id'] ?>">
            <input name="title" required placeholder="Recordatorio">
            <input type="datetime-local" name="reminder_at" required>
            <button>Asignar</button>
        </form>
    </div>

    <div class="ai-alert">Analizar por que el paciente no completo el reto de la manana. Nivel de cortisol detectado: ALTO.</div>

    <div class="cards two">
        <?php foreach (array_merge($appointments, $reminders) as $item): ?>
            <article>
                <b><?= h($item['title'] ?? 'Cita terapeutica') ?></b>
                <p><?= h($item['appointment_at'] ?? $item['reminder_at']) ?></p>
                <span><?= h($item['summary'] ?? 'Recordatorio basico') ?></span>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}
