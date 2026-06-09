<?php
// Procesamiento de formularios POST: login, registro, CRUD y mensajes.

function handle_post() {
    $action = $_POST['action'] ?? '';
    $db = pdo();

    // Login: valida correo/contrasena y guarda el id del usuario en sesion.
    if ($action === 'login') {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([trim($_POST['email'] ?? '')]);
        $user = $stmt->fetch();
        if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            redirect_to('index.php');
        }
        $_SESSION['flash'] = 'Correo o contrasena incorrectos.';
        redirect_to('index.php?page=login');
    }

    // Registro: crea paciente o psicologo segun el rol elegido.
    if ($action === 'register') {
        $role = ($_POST['role'] ?? 'patient') === 'psychologist' ? 'psychologist' : 'patient';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
            $db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)")
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $userId = (int) $db->lastInsertId();
            if ($role === 'patient') {
                $psychologist = $db->query("SELECT id FROM users WHERE role='psychologist' ORDER BY id LIMIT 1")->fetch();
                $db->prepare("INSERT INTO patient_profiles (user_id,psychologist_id) VALUES (?,?)")
                    ->execute([$userId, $psychologist['id'] ?? null]);
            }
            $_SESSION['user_id'] = $userId;
            redirect_to('index.php');
        }
        $_SESSION['flash'] = 'Completa nombre, correo valido y contrasena de minimo 6 caracteres.';
        redirect_to('index.php?page=register');
    }

    $user = current_user();
    if (!$user) {
        redirect_to('index.php?page=login');
    }

    // Cierre de sesion.
    if ($action === 'logout') {
        session_destroy();
        redirect_to('index.php?page=login');
    }

    // Acciones exclusivas del psicologo.
    if ($user['role'] === 'psychologist') {
        handle_psychologist_actions($db, $user, $action);
    }

    // Accion exclusiva del paciente: completar actividad y registrar emocion.
    if ($action === 'complete_activity' && $user['role'] === 'patient') {
        $db->prepare("INSERT INTO activity_logs (plan_id, patient_id, emotion, experience, stress_score) VALUES (?,?,?,?,?)")
            ->execute([(int) $_POST['plan_id'], $user['id'], trim($_POST['emotion']), trim($_POST['experience']), (int) $_POST['stress_score']]);
        $db->prepare("UPDATE intervention_plans SET status='completed' WHERE id=? AND patient_id=?")
            ->execute([(int) $_POST['plan_id'], $user['id']]);
        $db->prepare("UPDATE patient_profiles SET bravery_points = bravery_points + 10, stress_level = ? WHERE user_id=?")
            ->execute([(int) $_POST['stress_score'], $user['id']]);
    }

    // Mensajeria interna para ambos roles.
    if ($action === 'send_message') {
        $receiver = (int) $_POST['receiver_id'];
        $body = trim($_POST['body']);
        $db->prepare("INSERT INTO messages (sender_id, receiver_id, body, ai_feedback) VALUES (?,?,?,?)")
            ->execute([$user['id'], $receiver, $body, ai_feedback($body)]);
    }

    redirect_to('index.php?page=' . safe_return_page($_POST['return'] ?? 'dashboard'));
}

function handle_psychologist_actions($db, $user, $action) {
    // CRUD basico de pacientes.
    if ($action === 'create_patient') {
        $password = $_POST['password'] ?: 'paciente123';
        $db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?, 'patient')")
            ->execute([trim($_POST['name']), trim($_POST['email']), password_hash($password, PASSWORD_DEFAULT)]);
        $patientId = (int) $db->lastInsertId();
        $db->prepare("INSERT INTO patient_profiles (user_id,psychologist_id,notes) VALUES (?,?,?)")
            ->execute([$patientId, $user['id'], trim($_POST['notes'] ?? '')]);
    }

    if ($action === 'delete_patient') {
        $db->prepare("DELETE FROM users WHERE id = ? AND role = 'patient'")->execute([(int) $_POST['patient_id']]);
    }

    // Crear/eliminar planes de intervencion o desafios del dia.
    if ($action === 'save_plan') {
        $db->prepare("INSERT INTO intervention_plans (psychologist_id, patient_id, title, description, challenge_of_day, due_date) VALUES (?,?,?,?,?,?)")
            ->execute([$user['id'], (int) $_POST['patient_id'], trim($_POST['title']), trim($_POST['description']), isset($_POST['challenge']) ? 1 : 0, $_POST['due_date'] ?: null]);
    }

    if ($action === 'delete_plan') {
        $db->prepare("DELETE FROM intervention_plans WHERE id = ? AND psychologist_id = ?")
            ->execute([(int) $_POST['plan_id'], $user['id']]);
    }

    // Recordatorios y citas asignados desde la agenda del psicologo.
    if ($action === 'create_reminder') {
        $db->prepare("INSERT INTO reminders (psychologist_id, patient_id, title, reminder_at) VALUES (?,?,?,?)")
            ->execute([$user['id'], (int) $_POST['patient_id'], trim($_POST['title']), $_POST['reminder_at']]);
    }

    if ($action === 'create_appointment') {
        $db->prepare("INSERT INTO appointments (psychologist_id, patient_id, appointment_at, summary) VALUES (?,?,?,?)")
            ->execute([$user['id'], (int) $_POST['patient_id'], $_POST['appointment_at'], trim($_POST['summary'] ?? '')]);
    }
}
