<?php

class Productos extends Controller{
    public function __construct() {
        parent::__construct();
    }
    public function index()
    {
        $data['medidas'] = $this->model->getMedidas();
        $data['categorias'] = $this->model->getCategorias();
        $id_user = $_SESSION['id_usuario'];
        $data['permisos'] = $this->model->verificarPermisos($id_user, "crear_producto");
        if (!empty($data['permisos']) || $id_user == 1) {
            $data['existe'] = true;
        } else {
            $data['existe'] = false;
        }
        $data['modal'] = 'producto';
        $this->views->getView('productos',  "index", $data);
    }
    public function listar()
    {
        $id_user = $_SESSION['id_usuario'];
        $data = $this->model->getProductos(1);
        $date = date('Y-m-d');
        $modificar = $this->model->verificarPermisos($id_user, "modificar_producto");
        $eliminar = $this->model->verificarPermisos($id_user, "eliminar_producto");
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['date'] = $date;
            $data[$i]['imagen'] = '<img class="img-thumbnail" src="' . BASE_URL . "assets/img/pro/" . $data[$i]['foto'] . '" width="50">';
            $data[$i]['editar'] = '';
            $data[$i]['eliminar'] = '';
            $data[$i]['subTotal'] = '<span class="badge bg-info">'.number_format($data[$i]['cantidad'] * $data[$i]['precio_venta'], 2).'</span>';
            $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
            if (!empty($modificar) || $id_user == 1) {
                $data[$i]['editar'] = '<button class="btn btn-outline-primary" type="button" onclick="btnEditarPro(' . $data[$i]['id'] . ');"><i class="fas fa-edit"></i></button>';
            }
            if (!empty($eliminar) || $id_user == 1) {
                $data[$i]['eliminar'] = '<button class="btn btn-outline-danger" type="button" onclick="btnEliminarPro(' . $data[$i]['id'] . ');"><i class="fas fa-trash-alt"></i></button>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if (isset($_POST['codigo']) && isset($_POST['descripcion']) && isset($_POST['precio_compra'])) {
            $codigo = strClean($_POST['codigo']);
            $nombre = strClean($_POST['descripcion']);
            $precio_compra = strClean($_POST['precio_compra']);
            $precio_venta = strClean($_POST['precio_venta']);
            $categoria = strClean($_POST['categoria']);
            $medida = strClean($_POST['medida']);
            $id = strClean($_POST['id']);
            $img = $_FILES['imagen'];
            $name = $img['name'];
            $tmpname = $img['tmp_name'];        
            $fecha = date("YmdHis");
            if (empty($codigo) || empty($nombre) || empty($precio_compra) || empty($precio_venta)
            || empty($categoria) || empty($medida)) {
                $msg = array('msg' => 'Todo los campos con * son obligatorios', 'icono' => 'warning');
            }else{
                if (mb_strlen($codigo) < 3) {
                    $msg = array('msg' => 'el código debe contener un mínimo 3 caracteres', 'icono' => 'warning');
                } else {
                    if (mb_strlen($nombre) < 3) {
                        $msg = array('msg' => 'El nombre debe contener un mínimo 3 caracteres', 'icono' => 'warning');
                    } else {
                        if (!is_numeric($precio_compra) || $precio_compra < 0) {
                            $msg = array('msg' => 'El precio de compra debe ser un número válido', 'icono' => 'warning');
                        } else {
                            if (!is_numeric($precio_venta) || $precio_venta <= 0) {
                                $msg = array('msg' => 'El precio de venta debe ser un número mayor a 0', 'icono' => 'warning');
                            } else {
                                if (!empty($name)) {
                                    $formatos_permitidos =  array('png', 'jpeg', 'jpg');
                                    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                    if (!in_array($extension, $formatos_permitidos)) {
                                        // antes el producto se guardaba igual con la imagen inválida
                                        echo json_encode(array('msg' => 'Archivo no permitido, solo png o jpg', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
                                        die();
                                    }
                                    $imgNombre = $fecha . ".jpg";
                                    $destino = "assets/img/pro/" . $imgNombre;
                                }else if(!empty($_POST['foto_actual']) && empty($name)){
                                    $imgNombre = basename($_POST['foto_actual']);
                                }else{
                                    $imgNombre = "default.png";
                                }
                                if ($id == "") {
                                        $data = $this->model->registrarProducto($codigo, $nombre, $precio_compra, $precio_venta, $medida, $categoria, $imgNombre);
                                        if ($data == 0) {
                                            $msg = array('msg' => 'El producto ya existe', 'icono' => 'warning');                          
                                        } else if ($data > 0) {
                                            if (!empty($name)) {
                                                move_uploaded_file($tmpname, $destino);
                                            }
                                            $msg = array('msg' => 'Producto registrado', 'icono' => 'success');
                                        } else {
                                            $msg = array('msg' => 'Error al registrar el producto', 'icono' => 'error');
                                        }
                                }else{
                                    $imgDelete = $this->model->editarPro($id);
                                    $data = $this->model->modificarProducto($codigo, $nombre, $precio_compra, $precio_venta, $medida, $categoria, $imgNombre, $id);
                                    if ($data == "modificado") {
                                        // la foto anterior solo se borra si se subió una nueva
                                        // (antes se borraba siempre y el producto quedaba sin imagen)
                                        if (!empty($name)) {
                                            move_uploaded_file($tmpname, $destino);
                                            if (!empty($imgDelete['foto']) && $imgDelete['foto'] != 'default.png' && $imgDelete['foto'] != $imgNombre && file_exists("assets/img/pro/" . $imgDelete['foto'])) {
                                                unlink("assets/img/pro/" . $imgDelete['foto']);
                                            }
                                        }
                                        $msg = array('msg' => 'Producto modificado', 'icono' => 'success');
                                    } else if ($data == "existe") {
                                        $msg = array('msg' => 'El producto ya existe', 'icono' => 'warning');
                                    } else {
                                        $msg = array('msg' => 'Error al modificar el producto', 'icono' => 'error');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }else{
            $msg = array('msg' => 'error fatal', 'icono' => 'error');
        }        
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar(int $id)
    {
        $data = $this->model->editarPro($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionPro(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Producto dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar el producto', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionPro(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Producto reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error la reingresar el producto', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inventario()
    {
        $id_user = $_SESSION['id_usuario'];
        $inventario = $this->model->verificarPermisos($id_user, "inventario");
        $reporte = $this->model->verificarPermisos($id_user, "reporte_pdf_inventario");
        if (!empty($inventario) || $id_user == 1) {
            $data['inventario'] = true;
        } else {
            $data['inventario'] = false;
        }
        if (!empty($reporte) || $id_user == 1) {
            $data['reporte'] = true;
        } else {
            $data['reporte'] = false;
        }
        $data['modal'] = 'inventario';
        $this->views->getView('productos',  "inventario", $data);
    }
    public function registrarInventario()
    {
        $id_user = $_SESSION['id_usuario'];
        $agregar = str_replace(',', '.', trim($_POST['agregar'] ?? ''));
        $id = intval($_POST['id'] ?? 0);
        $fecha = date('Y-m-d');
        $motivo = motivoAuditoria();
        $actual = $id > 0 ? $this->model->editarPro($id) : array();
        if (empty($id) || $agregar === '') {
            $msg = array('msg' => 'Todo los campos con * son obligatorios', 'icono' => 'warning');
        } else if (!is_numeric($agregar) || $agregar == 0) {
            $msg = array('msg' => 'Ingresa una cantidad válida distinta de 0 (negativa para descontar)', 'icono' => 'warning');
        } else if ($motivo === null) {
            $msg = MSG_MOTIVO;
        } else if (empty($actual)) {
            $msg = array('msg' => 'El producto no existe', 'icono' => 'error');
        } else if ($actual['cantidad'] + $agregar < 0) {
            $msg = array('msg' => 'No puedes descontar más del stock actual (' . $actual['cantidad'] . ')', 'icono' => 'warning');
        } else {
            // movimiento de inventario, stock y auditoría: todo o nada
            try {
                $this->model->iniciarTransaccion();
                if (!$this->model->moverStock($id, $agregar)) {
                    throw new Exception('No puedes descontar más del stock actual');
                }
                if ($agregar > 0) {
                    $this->model->ingresarEntrada($id, $id_user, $agregar, $fecha);
                } else {
                    $this->model->ingresarSalida($id, $id_user, abs($agregar), $fecha);
                }
                $this->model->registrarAuditoria('ajuste_inventario', $id, $motivo, array(
                    'producto' => $actual['codigo'] . ' - ' . $actual['descripcion'],
                    'stock_anterior' => $actual['cantidad'],
                    'ajuste' => ($agregar > 0 ? '+' : '') . $agregar,
                    'stock_nuevo' => number_format($actual['cantidad'] + $agregar, 2, '.', ''),
                ));
                $this->model->confirmar();
                $msg = array('msg' => 'Cantidad del producto Ajustado', 'icono' => 'success');
            } catch (Throwable $e) {
                $this->model->revertir();
                $msg = array('msg' => ($e instanceof PDOException) ? 'Error al ajustar' : $e->getMessage(), 'icono' => 'error');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function listarInventario()
    {
        $data = $this->model->getInventarios();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function pdfInventario($accion)
    {
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "reporte_pdf_inventario");
        if (!empty($perm) || $id_user == 1) {
            $empresa = $this->model->getEmpresa();
            if ($accion == 'all') {
                $productos = $this->model->getInventarios();
            } else {
                $array = explode(',', $accion);
                $desde = $array[0];
                $hasta = $array[1];
                $productos = $this->model->filtroInventarios($desde, $hasta);
            }
            if (empty($productos)) {
                echo 'No hay registro';
            } else {
                require('Libraries/fpdf/fpdf.php');
                include('Libraries/phpqrcode/qrlib.php');
                $pdf = new FPDF('P', 'mm', 'A4');
                $pdf->AddPage();
                $pdf->SetMargins(10, 0, 0);
                $pdf->SetTitle('Reporte Inventario');
                $pdf->SetFont('Arial', '', 14);
                $pdf->Cell(195, 8, utf8_decode($empresa['nombre']), 0, 1, 'C');
                QRcode::png($empresa['ruc'], 'assets/qr.png');
                $pdf->Image('assets/qr.png', 95, 18, 25, 25);
                $pdf->Image('assets/img/logo.png', 170, 10, 25, 25);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(18, 5, 'Ruc: ', 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['ruc'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['telefono'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Dirección: '), 0, 0, 'L');
                $pdf->Cell(20, 5, utf8_decode($empresa['direccion']), 0, 1, 'L');
                $pdf->Ln(10);
                //Encabezado de productos
                $pdf->SetFillColor(0, 0, 0);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(190, 5, 'Detalle de Productos', 1, 1, 'C', true);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(0, 100, 50);
                $pdf->SetTextColor(255, 255, 255);
				$pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'L', true);
                $pdf->Cell(100, 5, utf8_decode('Descripción'), 1, 0, 'L', true);
                $pdf->Cell(25, 5, 'Fecha', 1, 0, 'L', true);
                $pdf->Cell(25, 5, 'Entradas.', 1, 0, 'L', true);
                $pdf->Cell(25, 5, 'Salidas', 1, 1, 'L', true);
                $pdf->SetTextColor(0, 0, 0);
				$i = 1;
                foreach ($productos as $row) {
                    $pdf->Cell(15, 5, $i, 1, 0, 'L');
					$pdf->Cell(100, 5, utf8_decode($row['descripcion']), 1, 0, 'L');
                    $pdf->Cell(25, 5, $row['fecha'], 1, 0, 'L');
                    $pdf->Cell(25, 5, $row['total_entradas'], 1, 0, 'R');
                    $pdf->Cell(25, 5, $row['total_salidas'], 1, 1, 'R');
					$i++;
                }
                $pdf->Output();
            }
        } else {
            header('Location: Administracion/permisos'); 
        }
    }
    public function pdfCompra($accion)
    {
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "reporte_pdf_compras");
        if (!empty($perm) || $id_user == 1) {
            $empresa = $this->model->getEmpresa();
            if ($accion == 'all') {
                $productos = $this->model->getCompras();
            } else {
                $array = explode(',', $accion);
                $desde = $array[0];
                $hasta = $array[1];
                $productos = $this->model->filtroCompras($desde, $hasta);
            }
            if (empty($productos)) {
                echo 'No hay registro';
            } else {
                require('Libraries/fpdf/fpdf.php');
                include('Libraries/phpqrcode/qrlib.php');
                $pdf = new FPDF('P', 'mm', 'A4');
                $pdf->AddPage();
                $pdf->SetMargins(5, 0, 0);
                $pdf->SetTitle('Reporte Compras');
                $pdf->SetFont('Arial', '', 14);
                $pdf->Cell(195, 8, utf8_decode($empresa['nombre']), 0, 1, 'C');
                QRcode::png($empresa['ruc'], 'assets/qr.png');
                $pdf->Image('assets/qr.png', 95, 18, 25, 25);
                $pdf->Image('assets/img/logo.png', 170, 10, 25, 25);
                $pdf->SetFont('Arial', '', 9);

                $pdf->Cell(18, 5, 'Ruc: ', 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['ruc'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['telefono'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Dirección: '), 0, 0, 'L');
                $pdf->Cell(20, 5, utf8_decode($empresa['direccion']), 0, 1, 'L');
                $pdf->Ln(10);
                //Encabezado de productos
                $pdf->SetFillColor(0, 0, 0);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(195, 5, 'Detalle de Compras', 1, 1, 'C', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(0, 100, 50);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(20, 5, utf8_decode('N°'), 1, 0, 'L', true);
                $pdf->Cell(50, 5, 'Total', 1, 0, 'L', true);
                $pdf->Cell(40, 5, 'Fecha', 1, 0, 'L', true);
                $pdf->Cell(35, 5, 'Hora', 1, 0, 'L', true);
                $pdf->Cell(50, 5, 'Usuario', 1, 1, 'L', true);
                $pdf->SetTextColor(0, 0, 0);
                foreach ($productos as $row) {
                    $pdf->Cell(20, 5, $row['id'], 1, 0, 'L');
                    $pdf->Cell(50, 5, $row['total'], 1, 0, 'L');
                    $pdf->Cell(40, 5, $row['fecha'], 1, 0, 'L');
                    $pdf->Cell(35, 5, $row['hora'], 1, 0, 'L');
                    $pdf->Cell(50, 5, utf8_decode($row['nombre']), 1, 1, 'L');
                }
                $pdf->Output();
            }
        } else {
            header('Location: Administracion/permisos');
        }
    }
    public function pdfVenta($accion)
    {
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "reporte_pdf_ventas");
        if (!empty($perm) || $id_user == 1) {
            $empresa = $this->model->getEmpresa();
            if ($accion == 'all') {
                $productos = $this->model->getVentas();
            } else {
                $array = explode(',', $accion);
                $desde = $array[0];
                $hasta = $array[1];
                $productos = $this->model->filtroVentas($desde, $hasta);
            }
            if (empty($productos)) {
                echo 'No hay registro';
            } else {
                require('Libraries/fpdf/fpdf.php');
                include('Libraries/phpqrcode/qrlib.php');
                $pdf = new FPDF('P', 'mm', 'A4');
                $pdf->AddPage();
                $pdf->SetMargins(5, 0, 0);
                $pdf->SetTitle('Reporte Ventas');
                $pdf->SetFont('Arial', '', 14);
                $pdf->Cell(195, 8, utf8_decode($empresa['nombre']), 0, 1, 'C');
                QRcode::png($empresa['ruc'], 'assets/qr.png');
                $pdf->Image('assets/qr.png', 95, 18, 25, 25);
                $pdf->Image('assets/img/logo.png', 170, 10, 25, 25);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(18, 5, 'Ruc: ', 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['ruc'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
                $pdf->Cell(20, 5, $empresa['telefono'], 0, 1, 'L');
                $pdf->Cell(18, 5, utf8_decode('Dirección: '), 0, 0, 'L');
                $pdf->Cell(20, 5, utf8_decode($empresa['direccion']), 0, 1, 'L');
                $pdf->Ln(10);
                //Encabezado de productos
                $pdf->SetFillColor(0, 0, 0);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(195, 5, 'Detalle de Ventas', 1, 1, 'C', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetFillColor(0, 100, 50);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(12, 5, utf8_decode('N°'), 1, 0, 'L', true);
                $pdf->Cell(53, 5, 'Cliente', 1, 0, 'L', true);
                $pdf->Cell(30, 5, 'Total', 1, 0, 'L', true);
                $pdf->Cell(25, 5, 'Fecha', 1, 0, 'L', true);
                $pdf->Cell(25, 5, 'Hora', 1, 0, 'L', true);
                $pdf->Cell(50, 5, 'Usuario', 1, 1, 'L', true);
                $pdf->SetTextColor(0, 0, 0);
                foreach ($productos as $row) {
                    $pdf->Cell(12, 5, $row['id'], 1, 0, 'L');
                    $pdf->Cell(53, 5, utf8_decode($row['cliente']), 1, 0, 'L');
                    $pdf->Cell(30, 5, $row['total'], 1, 0, 'L');
                    $pdf->Cell(25, 5, $row['fecha'], 1, 0, 'L');
                    $pdf->Cell(25, 5, $row['hora'], 1, 0, 'L');
                    $pdf->Cell(50, 5, utf8_decode($row['nombre']), 1, 1, 'L');
                }
                $pdf->Output();
            }
        } else {
            header('Location: Administracion/permisos');
        }
    }
    public function inactivos()
    {
        $id_user = $_SESSION['id_usuario'];
        $data['permisos'] = $this->model->verificarPermisos($id_user, "restaurar_producto");
        if (!empty($data['permisos']) || $id_user == 1) {
            $data['existe'] = true;
        } else {
            $data['existe'] = false;
        }
        $data['productos'] = $this->model->getProductos(0);
        $this->views->getView('productos',  "inactivos", $data);
    }
}
