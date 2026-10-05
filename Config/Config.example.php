<?php
/*
 * PLANTILLA DE CONFIGURACIÓN
 * 1. Copia este archivo como Config/Config.php (ese archivo no se sube a GitHub).
 * 2. Ajusta los valores a tu equipo.
 */

// URL del proyecto en tu navegador (con / al final)
const BASE_URL = "http://localhost/venta/";

// Conexión a MySQL / MariaDB
// XAMPP usa el puerto 3306 por defecto. Si cambiaste el puerto de MySQL, ponlo aquí (ej. "127.0.0.1:3307").
const HOST = "127.0.0.1:3306";
const USER = "root";
const PASS = "";              // en XAMPP root no tiene contraseña por defecto
const DB = "cotizaciones";
const CHARSET = "charset=utf8";

// Correo para "Olvidaste tu contraseña" (opcional; con Gmail usa una contraseña de aplicación)
const HOST_SMTP = "smtp.gmail.com";
const USER_SMTP = "correo@gmail.com";
const CLAVE_SMTP = "clave_correo";
const PUERTO_SMTP = 465;

const TITLE = "SISTEMA VENTA";

// Zona horaria del negocio: PHP y MySQL deben usar la misma
const ZONA_HORARIA = "America/Lima";
const ZONA_HORARIA_MYSQL = "-05:00"; // Perú no tiene horario de verano
date_default_timezone_set(ZONA_HORARIA);

// Usuario con acceso total (no se le pueden quitar permisos ni dar de baja)
const SUPER_ADMIN_ID = 1;
