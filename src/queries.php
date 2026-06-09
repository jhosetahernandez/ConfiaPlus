<?php
// Consultas de lectura. Separarlas ayuda a que las vistas no tengan SQL mezclado.

function current_user() {
    // Devuelve el usuario autenticado segun el id guardado en la sesion.
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = pdo()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function patients_for($psychologistId) {
    // Lista los pacientes vinculados al psicologo.
    $stmt = pdo()->prepare("
        SELECT u.*, pp.stress_level, pp.anxiety_level, pp.bravery_points, pp.notes
        FROM users u
        LEFT JOIN patient_profiles pp ON pp.user_id = u.id
        WHERE u.role='patient' AND (pp.psychologist_id = ? OR pp.psychologist_id IS NULL)
        ORDER BY u.name
    ");
    $stmt->execute([$psychologistId]);
    return $stmt->fetchAll();
}

function assigned_psychologist($patientId) {
    // Busca el psicologo asignado al paciente; si no tiene, usa el primero disponible.
    $stmt = pdo()->prepare("SELECT psy.* FROM patient_profiles pp JOIN users psy ON psy.id=pp.psychologist_id WHERE pp.user_id=?");
    $stmt->execute([$patientId]);
    return $stmt->fetch() ?: pdo()->query("SELECT * FROM users WHERE role='psychologist' ORDER BY id LIMIT 1")->fetch();
}

function plans_for_patient($patientId) {
    // Actividades/planes asignados a un paciente.
    $stmt = pdo()->prepare("SELECT * FROM intervention_plans WHERE patient_id=? ORDER BY status, due_date IS NULL, due_date, created_at DESC");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function logs_for_patient($patientId) {
    // Registros emocionales creados despues de realizar actividades.
    $stmt = pdo()->prepare("SELECT al.*, ip.title FROM activity_logs al JOIN intervention_plans ip ON ip.id=al.plan_id WHERE al.patient_id=? ORDER BY al.created_at DESC");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function profile_for_patient($patientId) {
    // Datos de progreso del paciente: estres, ansiedad y puntos de valentia.
    $stmt = pdo()->prepare("SELECT * FROM patient_profiles WHERE user_id=?");
    $stmt->execute([$patientId]);
    return $stmt->fetch();
}

function appointments_for_patient($patientId) {
    $stmt = pdo()->prepare("SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_at");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function reminders_for_patient($patientId) {
    $stmt = pdo()->prepare("SELECT * FROM reminders WHERE patient_id=? ORDER BY reminder_at");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function appointments_for_pair($psychologistId, $patientId) {
    $stmt = pdo()->prepare("SELECT * FROM appointments WHERE psychologist_id=? AND patient_id=? ORDER BY appointment_at");
    $stmt->execute([$psychologistId, $patientId]);
    return $stmt->fetchAll();
}

function reminders_for_pair($psychologistId, $patientId) {
    $stmt = pdo()->prepare("SELECT * FROM reminders WHERE psychologist_id=? AND patient_id=? ORDER BY reminder_at");
    $stmt->execute([$psychologistId, $patientId]);
    return $stmt->fetchAll();
}

function messages_between($userId, $otherId) {
    // Chat entre dos usuarios con el feedback automatico de IA.
    $stmt = pdo()->prepare("
        SELECT m.*, s.name AS sender_name
        FROM messages m
        JOIN users s ON s.id=m.sender_id
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at
    ");
    $stmt->execute([$userId, $otherId, $otherId, $userId]);
    return $stmt->fetchAll();
}
