<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Usuarios extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $data['existe'] = Auth::puede('crear_usuario');
        $data['cajas'] = $this->model->getCajas();
        $data['modal'] = 'usuario';
        $this->views->getView('usuarios',  "index", $data);
    }
    public function listar()
    {
        $id_user = Auth::idUsuario();
        $data = $this->model->getUsuarios(1);
        $modificar = Auth::puede('modificar_usuario');
        $eliminar = Auth::puede('eliminar_usuario');
        $roles = Auth::puede('asignar_permisos');
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['rol'] = '';
            $data[$i]['editar'] = '';
            $data[$i]['eliminar'] = '';
            $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
            // el super admin y el propio usuario no se gestionan desde aquí (usar Perfil)
            if ($data[$i]['id'] != SUPER_ADMIN_ID && $data[$i]['id'] != $id_user) {
                if ($modificar) {
                    $data[$i]['editar'] = '<button class="btn btn-outline-primary" type="button" onclick="btnEditarUser(' . $data[$i]['id'] . ');"><i class="fas fa-edit"></i></button>';
                }
                if ($eliminar) {
                    $data[$i]['eliminar'] = '<button class="btn btn-outline-danger" type="button" onclick="btnEliminarUser(' . $data[$i]['id'] . ');"><i class="fas fa-trash-alt"></i></button>';
                }
                if ($roles) {
                    $data[$i]['rol'] = '<a class="btn btn-outline-dark" href="' . BASE_URL . 'usuarios/permisos/' . $data[$i]['id'] . '"><i class="fas fa-key"></i></a>';
                }
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function validar()
    {
        $correo = strClean($_POST['correo'] ?? '');
        $clave = strClean($_POST['clave'] ?? '');
        if (empty($correo) || empty($clave)) {
            $msg = "Los campos estan vacios";
        } else {
            $data = $this->model->getUsuario($correo);
            if ($data && claveVerificar($clave, $data['clave'])) {
                if (claveRehash($data['clave'])) {
                    // migra automáticamente las claves antiguas SHA256 a bcrypt
                    $this->model->modificarPass(claveHash($clave), $data['id']);
                }
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = $data['id'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['correo'] = $data['correo'];
                $_SESSION['perfil'] = $data['perfil'];
                $_SESSION['activo'] = true;
                $msg = "ok";
            } else {
                $msg = "Usuario o contraseña incorrecta";
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if (isset($_POST['nombre']) && isset($_POST['correo'])) {
            $nombre = strClean($_POST['nombre']);
            $cantNombre = mb_strlen($nombre);
            $correo = strClean($_POST['correo']);
            $id = strClean($_POST['id'] ?? '');
            if ($id != '' && !$this->puedeGestionar($id)) {
                $msg = array('msg' => 'No puedes modificar este usuario', 'icono' => 'warning');
            } else if ($cantNombre > 2 && !is_numeric($nombre)) {
                if ($this->is_valid_email($correo)) {
                    $clave = strClean($_POST['clave'] ?? '');
                    $confirmar = strClean($_POST['confirmar'] ?? '');
                    $caja = intval(strClean($_POST['caja'] ?? ''));
                    if (empty($nombre) || empty($correo) || empty($caja)) {
                        $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
                    } else {
                        if ($id == "") {
                            if (!empty($clave) && !empty($confirmar)) {
                                $cantClave = strlen($clave);
                                $cantConfirmar = strlen($confirmar);
                                if ($cantClave > 4 && $cantConfirmar > 4) {
                                    if ($clave != $confirmar) {
                                        $msg = array('msg' => 'Las contraseña no coinciden', 'icono' => 'warning');
                                    } else {
                                        $data = $this->model->registrarUsuario($nombre, $correo, claveHash($clave), $caja);
                                        if ($data == "ok") {
                                            $nuevo = $this->model->getCorreo($correo);
                                            $this->model->registrarAuditoria('crear_usuario', $nuevo['id'] ?? null, null, array('nombre' => $nombre, 'correo' => $correo, 'caja' => $caja));
                                            $msg = array('msg' => 'Usuario registrado, ahora asígnale sus permisos', 'icono' => 'success');
                                        } else if ($data == "existe") {
                                            $msg = array('msg' => 'El usuario ya existe', 'icono' => 'warning');
                                        } else {
                                            $msg = array('msg' => 'Error al registrar el usuario', 'icono' => 'error');
                                        }
                                    }
                                } else {
                                    $msg = array('msg' => 'la clave y confirmar debe tener como minímo 5 caracteres', 'icono' => 'warning');
                                }
                            } else {
                                $msg = array('msg' => 'La contraseña es requerido', 'icono' => 'warning');
                            }
                        } else {
                            $antes = $this->model->editarUser(intval($id));
                            $data = $this->model->modificarUsuario($nombre, $correo, $caja, $id);
                            if ($data == "modificado") {
                                $this->model->registrarAuditoria('modificar_usuario', intval($id), null, array(
                                    'antes' => array('nombre' => $antes['nombre'], 'correo' => $antes['correo'], 'caja' => $antes['id_caja']),
                                    'despues' => array('nombre' => $nombre, 'correo' => $correo, 'caja' => $caja),
                                ));
                                $msg = array('msg' => 'Usuario modificado', 'icono' => 'success');
                            } else if ($data == "existe") {
                                $msg = array('msg' => 'El usuario ya existe', 'icono' => 'warning');
                            } else {
                                $msg = array('msg' => 'Error al modificar el usuario', 'icono' => 'error');
                            }
                        }
                    }
                } else {
                    $msg = array('msg' => 'Ingresa un correo valido', 'icono' => 'warning');
                }
            } else {
                $msg = array('msg' => 'el nombre debe contener como minímo 3 caracteres', 'icono' => 'warning');
            }
        } else {
            $msg = array('msg' => 'error fatal - acceso denegado', 'icono' => 'warning');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar(int $id)
    {
        $data = $this->puedeGestionar($id) ? $this->model->editarUser($id) : array();
        // nunca enviar al navegador el hash de la clave ni el token
        unset($data['clave'], $data['token']);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        if (!$this->puedeGestionar($id)) {
            $msg = array('msg' => 'No puedes dar de baja este usuario', 'icono' => 'warning');
        } else {
            $data = $this->model->accionUser(0, $id);
            if ($data == 1) {
                $usuario = $this->model->editarUser($id);
                $this->model->registrarAuditoria('baja_usuario', $id, null, array('nombre' => $usuario['nombre'], 'correo' => $usuario['correo']));
                $msg = array('msg' => 'Usuario dado de baja', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'Error al eliminar el usuario', 'icono' => 'error');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionUser(1, $id);
        if ($data == 1) {
            $usuario = $this->model->editarUser($id);
            $this->model->registrarAuditoria('reactivar_usuario', $id, null, array('nombre' => $usuario['nombre'], 'correo' => $usuario['correo']));
            $msg = array('msg' => 'Usuario reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reingresar el usuario', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function cambiarPass()
    {
        $actual = strClean($_POST['clave_actual'] ?? '');
        $nueva = strClean($_POST['clave_nueva'] ?? '');
        $confirmar = strClean($_POST['confirmar_clave'] ?? '');
        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            $mensaje = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
        } else if (strlen($nueva) < 5) {
            $mensaje = array('msg' => 'La nueva contraseña debe tener como minímo 5 caracteres', 'icono' => 'warning');
        } else {
            if ($nueva != $confirmar) {
                $mensaje = array('msg' => 'Las contraseña no coinciden', 'icono' => 'warning');
            } else {
                $id = Auth::idUsuario();
                $data = $this->model->getPass($id);
                if (!empty($data) && claveVerificar($actual, $data['clave'])) {
                    $verificar = $this->model->modificarPass(claveHash($nueva), $id);
                    if ($verificar == 1) {
                        $mensaje = array('msg' => 'Contraseña Modificada', 'icono' => 'success');
                    } else {
                        $mensaje = array('msg' => 'Error al modificar la contraseña', 'icono' => 'error');
                    }
                } else {
                    $mensaje = array('msg' => 'La contraseña actual incorrecta', 'icono' => 'warning');
                }
            }
        }
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function permisos($id)
    {
        $id = intval($id);
        $usuario = $this->model->editarUser($id);
        if (empty($usuario) || !$this->puedeGestionar($id)) {
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        $data['datos'] = $this->model->getPermisos();
        $permisos = $this->model->getDetallePermisos($id);
        $data['asignados'] = array();
        foreach ($permisos as $permiso) {
            $data['asignados'][$permiso['id_permiso']] = true;
        }
        // solo se pueden otorgar los permisos que tiene quien asigna
        $data['otorgables'] = $this->permisosOtorgables();
        $data['id_usuario'] = $id;
        $data['nombre'] = $usuario['nombre'];
        $this->views->getView('usuarios',  "permisos", $data);
    }
    public function registrarPermisos()
    {
        $id_user = intval($_POST['id_usuario'] ?? 0);
        $usuario = $this->model->editarUser($id_user);
        if (empty($usuario) || !$this->puedeGestionar($id_user)) {
            $msg = array('msg' => 'No puedes asignar permisos a este usuario', 'icono' => 'warning');
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
        $solicitados = array_map('intval', (array) ($_POST['permisos'] ?? array()));
        $otorgables = $this->permisosOtorgables();
        $final = array();
        foreach ($solicitados as $permiso) {
            if (isset($otorgables[$permiso])) {
                $final[$permiso] = true;
            }
        }
        // se conservan los permisos que quien asigna no puede gestionar
        foreach ($this->model->getDetallePermisos($id_user) as $actual) {
            if (!isset($otorgables[$actual['id_permiso']])) {
                $final[$actual['id_permiso']] = true;
            }
        }
        $nombres = array_column($this->model->getPermisos(), 'permiso', 'id');
        $anteriores = array_column($this->model->getDetallePermisos($id_user), 'id_permiso');
        $agregados = array_diff(array_keys($final), $anteriores);
        $quitados = array_diff($anteriores, array_keys($final));
        try {
            $this->model->iniciarTransaccion();
            $this->model->deletePermisos($id_user);
            foreach (array_keys($final) as $permiso) {
                $this->model->actualizarPermisos($id_user, $permiso);
            }
            if (!empty($agregados) || !empty($quitados)) {
                $this->model->registrarAuditoria('cambiar_permisos', $id_user, null, array(
                    'usuario' => $usuario['nombre'] . ' (' . $usuario['correo'] . ')',
                    'agregados' => array_values(array_map(function ($p) use ($nombres) { return $p . ' ' . ($nombres[$p] ?? ''); }, $agregados)),
                    'quitados' => array_values(array_map(function ($p) use ($nombres) { return $p . ' ' . ($nombres[$p] ?? ''); }, $quitados)),
                    'total_permisos' => count($final),
                ));
            }
            $this->model->confirmar();
            $msg = array('msg' => 'Permisos Modificado', 'icono' => 'success');
        } catch (Throwable $e) {
            $this->model->revertir();
            $msg = array('msg' => 'Error al modificar los permisos', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function perfil()
    {
        $data = $this->model->editarUser(Auth::idUsuario());
        $this->views->getView('usuarios',  "perfil", $data);
    }
    public function actualizarDato()
    {
        $nombre = strClean($_POST['nombre'] ?? '');
        $correo = strClean($_POST['correo'] ?? '');
        $telefono = strClean($_POST['telefono'] ?? '');
        $direccion = strClean($_POST['direccion'] ?? '');
        $apellido = strClean($_POST['apellido'] ?? '');
        $id = Auth::idUsuario();
        $perfil = $_FILES['imagen'] ?? array('name' => '', 'tmp_name' => '');
        $name = $perfil['name'];
        $tmpname = $perfil['tmp_name'];
        $fecha = date("YmdHis");
        if (!empty($name)) {
            $formatos_permitidos =  array('png', 'jpeg', 'jpg');
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, $formatos_permitidos)) {
                $msg = array('msg' => 'Archivo no permitido', 'icono' => 'warning');
                echo json_encode($msg, JSON_UNESCAPED_UNICODE);
                die();
            }
            $imgNombre = $fecha . ".jpg";
            $destino = "assets/img/users/" . $imgNombre;
        } else {
            $imgNombre = basename(strClean($_POST['foto_actual'] ?? 'avatar.svg'));
        }
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($direccion)) {
            $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
        } else if (!$this->is_valid_email($correo)) {
            $msg = array('msg' => 'Ingresa un correo valido', 'icono' => 'warning');
        } else if ($this->model->existeCorreo($correo, $id)) {
            $msg = array('msg' => 'El correo ya está registrado por otro usuario', 'icono' => 'warning');
        } else {
            if (!empty($name)) {
                $imgDelete = $this->model->editarUser($id);
                if ($imgDelete['perfil'] != 'avatar.svg') {
                    if (file_exists("assets/img/users/" . $imgDelete['perfil'])) {
                        unlink("assets/img/users/" . $imgDelete['perfil']);
                    }
                }
            }
            $data = $this->model->modificarDato($nombre, $apellido, $correo, $telefono, $direccion, $imgNombre, $id);
            if ($data == 1) {
                if (!empty($name)) {
                    move_uploaded_file($tmpname, $destino);
                }
                $_SESSION['nombre'] = $nombre;
                $_SESSION['correo'] = $correo;
                $_SESSION['perfil'] = $imgNombre;
                $msg = array('msg' => 'Usuario modificado', 'icono' => 'success');
            } else {
                $msg = array('msg' => 'Error al modificar el usuario', 'icono' => 'error');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function salir()
    {
        Auth::cerrarSesion();
        header("location: " . BASE_URL);
        exit;
    }
    public function enviarCorreo()
    {
        if ($this->is_valid_email($_POST['correo'] ?? '')) {
            $correo = strClean($_POST['correo']);
            $data = $this->model->getCorreo($correo);
            $empresa = $this->model->getEmpresa();
            // mismo mensaje exista o no el correo, para no revelar qué cuentas existen
            $mensaje = array('msg' => 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña', 'icono' => 'success');
            if (!empty($data)) {
                require 'vendor/autoload.php';
                $mail = new PHPMailer(true);
                try {
                    $token = bin2hex(random_bytes(16));
                    $datos = $this->model->actualizarToken($token, $correo);
                    if ($datos == 'ok') {
                        $mail->SMTPDebug = 0;
                        $mail->isSMTP();                                            //Send using SMTP
                        $mail->Host       = HOST_SMTP;                     //Set the SMTP server to send through
                        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                        $mail->Username   = USER_SMTP;                     //SMTP username
                        $mail->Password   = CLAVE_SMTP;                               //SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                        $mail->Port       = PUERTO_SMTP;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                        //Recipients
                        $mail->setFrom($empresa['correo'], $empresa['nombre']);
                        $mail->addAddress($correo);     //Add a recipient
                        //Content
                        $mail->isHTML(true);                                  //Set email format to HTML
                        $mail->Subject = $empresa['nombre'];
                        $mail->Body    = '<h1>Restablecer contraseña</h1>
                    <p>Has pedido restablecer tu contraseña, si no has sido tu puedes omitir este correo
                    <b>Atentamente ' . $empresa['nombre'] . '</b>
                    <h6>Para restablecer has click en el siguiente enlace</h6>
                    ' . BASE_URL . 'usuarios/restablecer/' . $token . '
                    </p>
                    ';
                        $mail->CharSet = 'UTF-8';
                        $mail->send();
                    }
                } catch (Exception $e) {
                    $mensaje = array('msg' => 'No se pudo enviar el correo, verifique la configuración SMTP', 'icono' => 'error');
                }
            }
        } else {
            $mensaje = array('msg' => 'Ingresa un correo valido', 'icono' => 'warning');
        }

        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restablecer($token)
    {
        $data = empty($token) ? array() : $this->model->getToken($token);
        if (empty($data)) {
            header('location: ' . BASE_URL);
            exit;
        } else {
            $this->views->getView('usuarios',  'restablecer', $token);
        }
    }
    public function resetear()
    {
        $token = strClean($_POST['token'] ?? '');
        $clave = strClean($_POST['clave_nueva'] ?? '');
        $confirmar = strClean($_POST['confirmar'] ?? '');
        if (empty($token) || empty($this->model->getToken($token))) {
            $msg = array('msg' => 'El enlace no es válido o ya fue utilizado', 'icono' => 'warning');
        } else if (empty($clave) || empty($confirmar)) {
            $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
        } else if (strlen($clave) < 5) {
            $msg = array('msg' => 'La contraseña debe tener como minímo 5 caracteres', 'icono' => 'warning');
        } else {
            if ($clave != $confirmar) {
                $msg = array('msg' => 'Las contraseñas no coinciden', 'icono' => 'warning');
            } else {
                $data = $this->model->resetearPass(claveHash($clave), $token);
                if ($data == 'ok') {
                    $msg = array('msg' => 'Contraseña restablecida con exito', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al restablecer la contraseña', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['existe'] = Auth::puede('restaurar_usuario');
        $data['usuarios'] = $this->model->getUsuarios(0);
        $this->views->getView('usuarios',  "inactivos", $data);
    }
    function is_valid_email($str)
    {
        return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
    }
    // Nadie gestiona al super admin ni a sí mismo desde el módulo de usuarios
    private function puedeGestionar($id)
    {
        $id = intval($id);
        return $id > 0 && $id != SUPER_ADMIN_ID && $id != Auth::idUsuario();
    }
    // [id_permiso => true] de los permisos que el usuario actual puede otorgar
    private function permisosOtorgables()
    {
        $otorgables = array();
        foreach ($this->model->getPermisos() as $permiso) {
            if (Auth::puede($permiso['permiso'])) {
                $otorgables[$permiso['id']] = true;
            }
        }
        return $otorgables;
    }
}
