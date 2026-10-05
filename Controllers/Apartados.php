<?php

use Luecano\NumeroALetras\NumeroALetras;

class Apartados extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $data['modal'] = 'apartado';
        $this->views->getView('apartados', "index", $data);
    }
    public function agregar($id_producto)
    {
        $id = intval($id_producto);
        $datos = $this->model->getProducto($id);
        if (empty($datos) || $datos['estado'] != 1) {
            echo json_encode(array('msg' => 'El producto no existe o está inactivo', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
            die();
        }
        $id_usuario = $_SESSION['id_usuario'];
        $precio = $datos['precio_venta'];
        $cantidad = 1;
        $comprobar = $this->model->consultarDetalle($id, $id_usuario);
        $cantidad_dis = $datos['cantidad'];
        if (empty($comprobar)) {
            if ($cantidad_dis < $cantidad) {
                $msg = array('msg' => 'No hay Stock, te quedan ' . $cantidad_dis, 'icono' => 'warning');
            } else {
                $sub_total = $precio * $cantidad;
                $data = $this->model->registrarDetalle($id, $id_usuario, $precio, $cantidad, $sub_total);
                if ($data == "ok") {
                    $msg = array('msg' => 'Producto agregado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al agregar', 'icono' => 'error');
                }
            }
        } else {
            $total_cantidad = $comprobar['cantidad'] + $cantidad;
            $sub_total = $total_cantidad * $precio;
            $stock_disponible = $cantidad_dis - $comprobar['cantidad'];
            if ($cantidad_dis < $total_cantidad) {
                $msg = array('msg' => 'No hay Stock, te quedan ' . $stock_disponible, 'icono' => 'warning');
            } else {
                $data = $this->model->actualizarDetalle('temp_apartados', $precio, $total_cantidad, $sub_total, $id, $id_usuario);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Producto actualizado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al actualizar', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    //agregar cantidades
    public function cantidadApartado()
    {
        if (isset($_POST['id']) && isset($_POST['cantidad'])) {
            $id = intval($_POST['id']);
            $cantidad = strClean($_POST['cantidad']);
            $temp = $this->model->detalle($id, 'temp_apartados');
            if (empty($temp) || $temp['id_usuario'] != $_SESSION['id_usuario']) {
                echo json_encode(array('msg' => 'El producto no está en tu apartado', 'icono' => 'warning'), JSON_UNESCAPED_UNICODE);
                die();
            }
            $producto = $this->model->getProducto($temp['id_producto']);
            if (!ctype_digit((string) $cantidad) || $cantidad <= 0) {
                // temp_apartados.cantidad es entera
                $msg = array('msg' => 'La cantidad debe ser un número entero mayor a 0', 'icono' => 'warning');
            } else if ($producto['cantidad'] >= $cantidad) {
                $data = $this->model->actualizarCantidad('temp_apartados', $cantidad, $id);
                if ($data == 'ok') {
                    $msg = array('msg' => 'ok', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'error al agregar', 'icono' => 'warning');
                }
            } else {
                $msg = array('msg' => 'No hay Stock, te quedan ' . $producto['cantidad'], 'icono' => 'warning');
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        die();
    }
    public function delete($id)
    {
        $temp = $this->model->detalle(intval($id), 'temp_apartados');
        if (empty($temp) || $temp['id_usuario'] != $_SESSION['id_usuario']) {
            echo json_encode(array('msg' => 'El producto no está en tu apartado', 'icono' => 'warning'));
            die();
        }
        $data = $this->model->deleteDetalle('temp_apartados', intval($id));
        if ($data == 'ok') {
            $msg = array('msg' => 'Producto eliminado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'success');
        }
        echo json_encode($msg);
        die();
    }
    public function listar()
    {
        $id_usuario = $_SESSION['id_usuario'];
        $data['detalle'] = $this->model->getDetalle($id_usuario);
        $total = 0.00;
        for ($i = 0; $i < count($data['detalle']); $i++) {
            $precio = $data['detalle'][$i]['precio'];
            $cantidad = $data['detalle'][$i]['cantidad'];
            $data['detalle'][$i]['subTotal'] = number_format($precio * $cantidad, 2);
            $total = $total + ($precio * $cantidad);
        }
        $data['total_pagar'] = number_format($total, 2, '.', ',');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        $f_retiro = $_POST['start'] ?? '';
        $fecha_actual = date('Y-m-d');
        $id_usuario = $_SESSION['id_usuario'];
        $id_cliente = intval($_POST['id'] ?? 0);
        $abono = str_replace(',', '.', trim($_POST['abono'] ?? ''));
        $hora = $_POST['hora'] ?? '';
        $detalle = $this->model->getDetalle($id_usuario);
        $total = 0.00;
        foreach ($detalle as $row) {
            $total += $row['precio'] * $row['cantidad'];
        }
        $total = round($total, 2);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_retiro) || $f_retiro < $fecha_actual) {
            $msg = array('msg' => 'Seleccione una fecha de retiro desde hoy en adelante', 'icono' => 'warning');
        } else if (empty($id_cliente) || !preg_match('/^\d{2}:\d{2}/', $hora)) {
            $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
        } else if (empty($detalle)) {
            $msg = array('msg' => 'No hay productos para apartar', 'icono' => 'warning');
        } else if (!is_numeric($abono) || $abono <= 0) {
            $msg = array('msg' => 'Ingresa un anticipo válido mayor a 0', 'icono' => 'warning');
        } else if (round((float) $abono, 2) > $total) {
            $msg = array('msg' => 'El anticipo no puede ser mayor al total (' . number_format($total, 2) . ')', 'icono' => 'warning');
        } else if (empty($this->model->verificarCaja($id_usuario))) {
            // el anticipo es dinero que entra a la caja
            $msg = array('msg' => 'La caja esta cerrada, ábrela para registrar el apartado', 'icono' => 'warning');
        } else if (($sinStock = $this->sinStock($detalle)) != '') {
            $msg = array('msg' => 'Stock insuficiente: ' . $sinStock, 'icono' => 'warning');
        } else {
            $abono = round((float) $abono, 2);
            // apartado, detalle, stock reservado y anticipo: todo o nada
            try {
                $this->model->iniciarTransaccion();
                $data = $this->model->registrarApartado($f_retiro . ' ' . $hora, $abono, $total, $id_cliente);
                if (!($data > 0)) {
                    throw new Exception('Error al Apartar los productos');
                }
                foreach ($detalle as $row) {
                    if (!$this->model->moverStock($row['id_producto'], -$row['cantidad'])) {
                        throw new Exception('Stock insuficiente: ' . $row['descripcion']);
                    }
                    $this->model->registrarDetalleApartado($row['cantidad'], $row['precio'], $row['id_producto'], $data);
                }
                $this->model->registrarPago($data, $abono, $id_usuario);
                $this->model->vaciarDetalle($id_usuario);
                $this->model->confirmar();
                $msg = array('msg' => 'Productos Apartado', 'icono' => 'success', 'id_apartado' => $data);
            } catch (Throwable $e) {
                $this->model->revertir();
                $msg = array('msg' => ($e instanceof PDOException) ? 'Error al Apartar los productos' : $e->getMessage(), 'icono' => 'warning');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function listarApartados()
    {
        $data = $this->model->getApartados();
        for ($i=0; $i < count($data); $i++) {
            $restante = number_format($data[$i]['total'] - $data[$i]['abono'], 2);
            $data[$i]['restante'] = '<span class="badge badge-danger">'.$restante.'</span>';
            $data[$i]['entregado'] = ($data[$i]['estado'] == 0);
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge bg-warning">Apartado</span>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['title'] = 'ANULADO - ' . $data[$i]['title'];
                $data[$i]['restante'] = '<span class="badge bg-secondary">0.00</span>';
                $data[$i]['estado'] = '<span class="badge bg-secondary">Anulado</span>';
            } else {
                $data[$i]['estado'] = '<span class="badge badge-success">Entregado</span>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function verficar($id_apartado)
    {
        $data = $this->model->getVerificar(intval($id_apartado));
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    // Entrega: se cobra el saldo pendiente (entra a la caja) y se marca como entregado
    public function entrega($id_apartado)
    {
        $id_apartado = intval($id_apartado);
        $apartado = $this->model->getVerificar($id_apartado);
        $id_usuario = $_SESSION['id_usuario'];
        if (empty($apartado)) {
            $mensaje = array('msg' => 'El apartado no existe', 'icono' => 'error');
        } else if ($apartado['estado'] == 2) {
            $mensaje = array('msg' => 'Este apartado fue anulado, no se puede entregar', 'icono' => 'warning');
        } else if ($apartado['estado'] != 1) {
            $mensaje = array('msg' => 'Este apartado ya fue entregado', 'icono' => 'info');
        } else if (empty($this->model->verificarCaja($id_usuario))) {
            $mensaje = array('msg' => 'La caja esta cerrada, ábrela para cobrar el saldo y entregar', 'icono' => 'warning');
        } else {
            $saldo = round($apartado['total'] - $apartado['abono'], 2);
            try {
                $this->model->iniciarTransaccion();
                if ($this->model->actualizarApartado($id_apartado) != 1) {
                    throw new Exception('Error en la Entrega');
                }
                if ($saldo > 0) {
                    $this->model->registrarPago($id_apartado, $saldo, $id_usuario);
                }
                $this->model->confirmar();
                $mensaje = array('msg' => 'Productos entregados' . ($saldo > 0 ? ', saldo cobrado: ' . number_format($saldo, 2) : ''), 'icono' => 'success');
            } catch (Throwable $e) {
                $this->model->revertir();
                $mensaje = array('msg' => 'Error en la Entrega', 'icono' => 'error');
            }
        }
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        die();
    }
    /*
     * Anular un apartado que el cliente no recogerá: los productos vuelven al stock
     * y, si se indica, se devuelve el anticipo (sale de la caja como pago negativo).
     */
    public function anular($id_apartado)
    {
        $id_apartado = intval($id_apartado);
        $apartado = $this->model->getVerificar($id_apartado);
        $id_usuario = $_SESSION['id_usuario'];
        $devolver = !empty($_POST['devolver']) && $_POST['devolver'] !== 'false';
        $motivo = motivoAuditoria();
        if ($motivo === null) {
            $mensaje = MSG_MOTIVO;
        } else if (empty($apartado)) {
            $mensaje = array('msg' => 'El apartado no existe', 'icono' => 'error');
        } else if ($apartado['estado'] != 1) {
            $mensaje = array('msg' => 'Solo se pueden anular apartados pendientes de entrega', 'icono' => 'warning');
        } else if ($devolver && $apartado['abono'] > 0 && empty($this->model->verificarCaja($id_usuario))) {
            $mensaje = array('msg' => 'La caja esta cerrada, ábrela para devolver el anticipo', 'icono' => 'warning');
        } else {
            try {
                $this->model->iniciarTransaccion();
                foreach ($this->model->getDetalleApartado($id_apartado) as $row) {
                    $this->model->moverStock($row['id_producto'], $row['cantidad']);
                }
                if ($this->model->anularApartado($id_apartado) != 1) {
                    throw new Exception('Error al anular');
                }
                if ($devolver && $apartado['abono'] > 0) {
                    $this->model->registrarPago($id_apartado, -$apartado['abono'], $id_usuario);
                }
                $cliente = $this->model->getCliente($id_apartado);
                $this->model->registrarAuditoria('anular_apartado', $id_apartado, $motivo, array(
                    'apartado' => $id_apartado,
                    'cliente' => empty($cliente) ? '' : $cliente['nombre'],
                    'fecha_retiro' => $apartado['fecha_retiro'],
                    'total' => $apartado['total'],
                    'anticipo' => $apartado['abono'],
                    'anticipo_devuelto' => ($devolver && $apartado['abono'] > 0),
                ));
                $this->model->confirmar();
                $texto = 'Apartado anulado, los productos volvieron al stock';
                if ($apartado['abono'] > 0) {
                    $texto .= $devolver ? '. Anticipo devuelto: ' . number_format($apartado['abono'], 2) : '. El anticipo de ' . number_format($apartado['abono'], 2) . ' no se devolvió';
                }
                $mensaje = array('msg' => $texto, 'icono' => 'success');
            } catch (Throwable $e) {
                $this->model->revertir();
                $mensaje = array('msg' => 'Error al anular el apartado', 'icono' => 'error');
            }
        }
        echo json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        die();
    }
    // Productos del carrito que ya no tienen stock suficiente
    private function sinStock(array $detalle)
    {
        $faltantes = array();
        foreach ($detalle as $row) {
            $producto = $this->model->getProducto($row['id_producto']);
            if (empty($producto) || $producto['estado'] != 1 || $producto['cantidad'] < $row['cantidad']) {
                $faltantes[] = $row['descripcion'] . ' (disponible: ' . (empty($producto) ? 0 : $producto['cantidad']) . ')';
            }
        }
        return implode(', ', $faltantes);
    }
    public function generarPdf($id_apartado)
    {
        if (is_numeric($id_apartado)) {
            $id_user = $_SESSION['id_usuario'];
            if (Auth::puede(array("apartados", "reporte_apartados"))) {
                $empresa = $this->model->getEmpresa();
                $productos = $this->model->getDetalleApartado($id_apartado);
                if (empty($productos)) {
                    echo 'No hay registros';
                    exit;
                } else {
                    $clientes = $this->model->getCliente($id_apartado);
                    require('Libraries/fpdf/html2pdf.php');
                    $pdf = new PDF_HTML('P', 'mm', array(80, 200));
                    $pdf->AddPage();
                    $pdf->SetMargins(5, 0, 5);
                    $pdf->SetTitle('Reporte Venta');
                    $pdf->SetFont('Arial', '', 14);
                    $pdf->MultiCell(50, 10, utf8_decode($empresa['nombre']), 0, 'L');
                    $pdf->Image('assets/img/logo.png', 63, 5, 15, 15);
                    $pdf->SetFont('Arial', '', 9);
                    $pdf->Cell(18, 5, 'Ruc: ', 0, 0, 'L');
                    $pdf->Cell(40, 5, $empresa['ruc'], 0, 1, 'L');
                    $pdf->Cell(18, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
                    $pdf->Cell(40, 5, $empresa['telefono'], 0, 1, 'L');
                    $pdf->Cell(18, 5, utf8_decode('Dirección: '), 0, 0, 'L');
                    $pdf->MultiCell(53, 5, utf8_decode($empresa['direccion']), 0, 'L');
                    $pdf->Cell(13, 5, 'Fecha: ', 0, 0, 'L');
                    $pdf->Cell(20, 5, $clientes['fecha_apartado'], 0, 1, 'L');
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(72, 5, '-------------------------------------------------------------', 0, 1, 'C');
                    //Encabezado de Clientes
                    
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, 'Dni: ', 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->MultiCell(40, 5, utf8_decode($clientes['dni']), 0, 'L');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, 'Nombre: ', 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->MultiCell(40, 5, utf8_decode($clientes['nombre']), 0, 'L');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(45, 5, $clientes['telefono'], 0, 1, 'L');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, utf8_decode('Dirección:'), 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->MultiCell(40, 5, utf8_decode($clientes['direccion']), 0, 'L');

                    //Encabezado de productos
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(72, 5, '-------------------------------------------------------------', 0, 1, 'C');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(9, 5, 'Cant', 0, 0, 'L');
                    $pdf->Cell(38, 5, utf8_decode('Descripción'), 0, 0, 'L');
                    $pdf->Cell(12, 5, 'Precio', 0, 0, 'L');
                    $pdf->Cell(12, 5, 'Sub Total', 0, 1, 'L');
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(72, 5, '-------------------------------------------------------------', 0, 1, 'C');
                    $total = $clientes['total'];
                    $pdf->SetFont('Arial', '', 7);
                    foreach ($productos as $row) {
                        $pdf->Cell(9, 5, $row['cantidad'], 0, 0, 'L');
                        $x = $pdf->GetX();
                        $pdf->myCell(38, 5, $x, utf8_decode($row['descripcion']));
                        $pdf->Cell(12, 5, number_format($row['precio'], 2), 0, 0, 'R');
                        $pdf->Cell(12, 5, number_format($row['precio'] * $row['cantidad'], 2, '.', ','), 0, 1, 'R');
                        $pdf->Cell(72, 5, '____________________________________________________', 0, 1, 'C');
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(35, 5, 'Total a pagar', 0, 0, 'R');
                    $pdf->Cell(36, 5, $empresa['simbolo'] . ' ' . number_format($total, 2, '.', ','), 0, 1, 'R');
                    $pdf->Ln();
                    require 'vendor/autoload.php';
                    $formatter = new NumeroALetras();
                    $pdf->MultiCell(72, 5, $formatter->toMoney(utf8_decode($total), 2, '', ''), 0, 'R');
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->Cell(20, 5, 'Fecha Retiro: ', 0, 0, 'L');
                    $pdf->Cell(52, 5, $clientes['fecha_retiro'], 0, 1, 'L');

                    $pdf->SetFont('Arial', '', 8);
                    $pdf->WriteHTML(utf8_decode($empresa['mensaje']));
                    $pdf->Output();
                }
            } else {
                header('Location: ' . BASE_URL . 'administracion/permisos');
            }
        } else {
            header('Location: ' . BASE_URL . 'Errors');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(18, 5, utf8_decode('Teléfono: '), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(20, 5, $empresa['telefono'], 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(18, 5, utf8_decode('Dirección: '), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(20, 5, utf8_decode($empresa['direccion']), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(18, 5, 'Folio: ', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(20, 5, $id_apartado, 0, 1, 'L');
        $pdf->Ln();
        //Encabezado de productos
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(10, 5, 'Cant', 0, 0, 'L', true);
        $pdf->Cell(35, 5, utf8_decode('Descripción'), 0, 0, 'L', true);
        $pdf->Cell(10, 5, 'Precio', 0, 0, 'L', true);
        $pdf->Cell(15, 5, 'Sub Total', 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $total = 0.00;
        foreach ($productos as $row) {
            $sub_total = $row['cantidad'] * $row['precio'];
            $total = $total + $sub_total;
            $pdf->Cell(10, 5, $row['cantidad'], 0, 0, 'L');
            $pdf->Cell(35, 5, utf8_decode($row['descripcion']), 0, 0, 'L');
            $pdf->Cell(10, 5, $row['precio'], 0, 0, 'R');
            $pdf->Cell(15, 5, number_format($sub_total, 2, '.', ','), 0, 1, 'R');
        }
        $pdf->Ln();
        $pdf->Cell(70, 5, 'Total a pagar', 0, 1, 'R');
        $pdf->Cell(70, 5, number_format($total, 2, '.', ','), 0, 1, 'R');
        $pdf->Output();
    }
    //historial
    public function historial()
    {
        $this->views->getView('apartados', "historial");
    }
}
