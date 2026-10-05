# Sistema Venta

Sistema web de ventas en PHP + MySQL: ventas, compras, cotizaciones, apartados, créditos, inventario, arqueo de caja y control de acceso por permisos de usuario.

## Requisitos

- [XAMPP](https://www.apachefriends.org/) con **PHP 8.0 o superior** (probado con PHP 8.2 y MariaDB 10.4)
- Git (para descargar el proyecto)

## Instalación

### 1. Descargar el proyecto

Clona el repositorio **dentro de `htdocs`** con el nombre `venta`:

```bash
cd C:\xampp\htdocs
git clone https://github.com/joelmendozataype/sistema-venta.git venta
```

> Si usas otra carpeta, cambia `BASE_URL` en el paso 3.

### 2. Crear la base de datos

1. Inicia **Apache** y **MySQL** en el panel de XAMPP.
2. Abre <http://localhost/phpmyadmin> → pestaña **Importar**.
3. Importa **`DB/cotizaciones.sql`** (crea la base de datos `cotizaciones` con sus tablas y datos iniciales).
4. *(Opcional, para pruebas)* Importa **`DB/datos_prueba.sql`** (crea un usuario por rol).

### 3. Configurar la conexión

Copia la plantilla y ajústala a tu equipo:

```bash
copy Config\Config.example.php Config\Config.php
```

Abre `Config/Config.php` y revisa:

| Constante | Valor habitual | Cuándo cambiarlo |
|---|---|---|
| `BASE_URL` | `http://localhost/venta/` | Si la carpeta no se llama `venta` |
| `HOST` | `127.0.0.1:3306` | Si tu MySQL usa otro puerto (mira el panel de XAMPP) |
| `PASS` | vacío | Si le pusiste contraseña al usuario `root` |

`Config/Config.php` **no se sube a GitHub** (está en `.gitignore`) porque contiene datos de conexión.

### 4. Entrar al sistema

Abre <http://localhost/venta/>

| Usuario | Correo | Contraseña |
|---|---|---|
| Super Administrador | `admin@agmail.com` | `admin` |

Si importaste `DB/datos_prueba.sql`, también tienes estos usuarios (contraseña `123456`):

| Rol | Correo | Qué puede hacer |
|---|---|---|
| Gerente | `gerente@tuempresa.com` | Todo, excepto la configuración de la empresa |
| Cajero | `caja1@tuempresa.com` | Abrir/cerrar caja, vender, clientes, cotizaciones |
| Almacenero | `almacen@tuempresa.com` | Productos, inventario, proveedores, compras |
| Asesor | `ventas@tuempresa.com` | Clientes y cotizaciones |
| Supervisor | `supervisor@tuempresa.com` | Reportes, anulaciones, créditos (consulta), cierres de caja |

> ⚠️ Estas contraseñas son **solo para pruebas**. En un uso real, cámbialas desde **Perfil** (incluida la del Super Administrador).

## Primeros pasos en el sistema

1. **Cajas → Apertura y Cierre**: abre la caja (sin caja abierta no se puede vender ni cobrar).
2. **Ventas → Nueva Venta**: busca un producto, elige cliente y método (Contado o Crédito).
3. Al terminar el turno, **cierra la caja**: muestra contado, cobros de créditos y apartados, y el efectivo esperado.

## Roles y permisos

- La guía completa (qué permisos dar a cada rol y por qué) está en **`docs/Guia_Roles_y_Permisos.docx`**.
- Los permisos se asignan usuario por usuario en **Administración → Usuarios → botón de la llave**.
- La **Auditoría** (anulaciones, ajustes de inventario, cambios de usuarios y permisos) es exclusiva del Super Administrador.

## Estructura

```
Config/       configuración, conexión, control de acceso (Auth.php, Permisos.php)
Controllers/  lógica de cada módulo
Models/       consultas a la base de datos
Views/        pantallas
assets/       css, js e imágenes
DB/           cotizaciones.sql (instalación) y datos_prueba.sql (usuarios de prueba)
docs/         documentación
```

## Problemas frecuentes

| Problema | Solución |
|---|---|
| "Error en la conexion" | Revisa `HOST` (puerto) y `PASS` en `Config/Config.php`, y que MySQL esté iniciado |
| La página se ve sin estilos o los enlaces fallan | `BASE_URL` no coincide con la carpeta del proyecto |
| Cambios que no se ven en el navegador | Recarga con **Ctrl + F5** |
| No aparece un menú | El usuario no tiene el permiso: asígnalo con la llave en Usuarios |
