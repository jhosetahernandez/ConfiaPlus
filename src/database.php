<?php
// Conexion y creacion automatica de la base de datos.

function db_config() {
    // Lee una sola vez las credenciales de MySQL.
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/../config/database.php';
    }
    return $config;
}

function pdo_without_db() {
    // Conexion inicial a MySQL sin seleccionar base; se usa para crear la base de datos.
    $c = db_config();
    return new PDO(
        "mysql:host={$c['host']};port={$c['port']};charset={$c['charset']}",
        $c['user'],
        $c['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function pdo() {
    // Conexion normal a la base confia_plus. Se reutiliza para no abrir varias conexiones.
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    $c = db_config();
    $pdo = new PDO(
        "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}",
        $c['user'],
        $c['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}

function init_database() {
    // Crea la base y las tablas cuando se abre la pagina por primera vez.
    $c = db_config();
    pdo_without_db()->exec("CREATE DATABASE IF NOT EXISTS `{$c['database']}` CHARACTER SET {$c['charset']} COLLATE {$c['charset']}_unicode_ci");
    $db = pdo();

    $schema = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(160) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('psychologist','patient') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS patient_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            psychologist_id INT NULL,
            stress_level INT DEFAULT 4,
            anxiety_level INT DEFAULT 4,
            bravery_points INT DEFAULT 0,
            notes TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS intervention_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            psychologist_id INT NOT NULL,
            patient_id INT NOT NULL,
            title VARCHAR(160) NOT NULL,
            description TEXT NOT NULL,
            challenge_of_day TINYINT(1) DEFAULT 0,
            due_date DATE NULL,
            status ENUM('pending','completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plan_id INT NOT NULL,
            patient_id INT NOT NULL,
            emotion VARCHAR(80) NOT NULL,
            experience TEXT NOT NULL,
            stress_score INT DEFAULT 5,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (plan_id) REFERENCES intervention_plans(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            body TEXT NOT NULL,
            ai_feedback TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS reminders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            psychologist_id INT NOT NULL,
            patient_id INT NOT NULL,
            title VARCHAR(160) NOT NULL,
            reminder_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            psychologist_id INT NOT NULL,
            patient_id INT NOT NULL,
            appointment_at DATETIME NOT NULL,
            summary TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (psychologist_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
        )",
    ];

    foreach ($schema as $statement) {
        $db->exec($statement);
    }

    seed_demo_data($db);
}

function seed_demo_data($db) {
    // Inserta usuarios demo solo si todavia no existe el psicologo principal.
    $seed = $db->prepare("SELECT id FROM users WHERE email = ?");
    $seed->execute(['psicologo@confia.test']);
    if ($seed->fetch()) {
        return;
    }

    $db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)")
        ->execute(['Psicologa Confia', 'psicologo@confia.test', password_hash('psico123', PASSWORD_DEFAULT), 'psychologist']);
    $psychologistId = (int) $db->lastInsertId();

    $db->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)")
        ->execute(['Jhoan Felipe', 'paciente@confia.test', password_hash('paciente123', PASSWORD_DEFAULT), 'patient']);
    $patientId = (int) $db->lastInsertId();

    $db->prepare("INSERT INTO patient_profiles (user_id, psychologist_id, stress_level, anxiety_level, bravery_points, notes) VALUES (?,?,?,?,?,?)")
        ->execute([$patientId, $psychologistId, 6, 5, 20, 'Paciente con confianza baja en entornos cerrados.']);
    $db->prepare("INSERT INTO intervention_plans (psychologist_id, patient_id, title, description, challenge_of_day, due_date) VALUES (?,?,?,?,?,DATE_ADD(CURDATE(), INTERVAL 1 DAY))")
        ->execute([$psychologistId, $patientId, 'Reto de exposicion breve', 'Entrar a un espacio abierto y escribir tres sensaciones fisicas sin juzgarlas.', 1]);
    $db->prepare("INSERT INTO appointments (psychologist_id, patient_id, appointment_at, summary) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 2 DAY),?)")
        ->execute([$psychologistId, $patientId, 'Revision de avances y validacion emocional.']);
    $db->prepare("INSERT INTO reminders (psychologist_id, patient_id, title, reminder_at) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL 6 HOUR))")
        ->execute([$psychologistId, $patientId, 'Anotar como se sintio al completar el reto.']);
}
