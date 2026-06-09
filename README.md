# Confia+ Web

Proyecto PHP/MySQL basado en las pantallas del PDF entregado.

## Requisitos

- PHP 8.1 o superior
- MySQL o MariaDB activo
- Usuario local por defecto: `root` sin contrasena

Si tu MySQL usa otra clave, edita `config/database.php`.

## Inicio rapido

1. Copia esta carpeta a `htdocs` si usas XAMPP, o abre una terminal en la carpeta.
2. Ejecuta:

```bash
php -S localhost:8000
```

3. Entra a `http://localhost:8000`.

La app crea automaticamente la base de datos `confia_plus`, sus tablas y usuarios demo.

## Usuarios demo

- Psicologo: `psicologo@confia.test` / `psico123`
- Paciente: `paciente@confia.test` / `paciente123`

## Modulos cubiertos

- Registro e inicio/cierre de sesion por rol.
- Redireccion automatica a interfaz de psicologo o paciente.
- Gestion de pacientes.
- Creacion y eliminacion de actividades, retos y planes.
- Registro emocional del paciente al completar actividades.
- Mensajes internos con feedback automatico tipo IA.
- Agenda, citas y recordatorios.
- Estadisticas semanales simples y contador de medallas de valentia.
