<?php
/*
 * Mapa de permisos por ruta: controlador => [metodo => regla]
 *
 * Reglas:
 *   'publico'                  sin iniciar sesión (login, recuperar clave)
 *   'sesion'                   cualquier usuario con sesión activa
 *   'superadmin'               exclusivo del Super Administrador (no es un permiso asignable)
 *   'permiso'                  requiere ese permiso
 *   ['p1', 'p2']               requiere al menos uno de esos permisos
 *   ['guardar' => [crear, modificar]]
 *                              formularios que crean o modifican según $_POST['id']:
 *                              id vacío => crear, id con valor => modificar
 *   'metodo/parametro'         regla específica para un parámetro de la ruta
 *
 * Toda ruta que no esté en este mapa queda denegada (incluso para el super admin).
 */

$crud = function (string $modulo, ?string $restaurar = null) {
    $restaurar = $restaurar ?? "restaurar_$modulo";
    $todos = ["crear_$modulo", "modificar_$modulo", "eliminar_$modulo", $restaurar];
    return [
        'index' => $todos,
        'listar' => $todos,
        'registrar' => ['guardar' => ["crear_$modulo", "modificar_$modulo"]],
        'editar' => "modificar_$modulo",
        'eliminar' => "eliminar_$modulo",
        'reingresar' => $restaurar,
        'inactivos' => $restaurar,
    ];
};

$moneda = ['crear_moneda', 'modificar_moneda', 'eliminar_moneda', 'restaurar_moneda'];
$buscarProducto = ['nueva_compra', 'nueva_venta', 'cotizaciones', 'apartados', 'inventario'];
$creditos = ['creditos', 'registrar_abono'];

return [
    'home' => [
        'index' => 'publico',
    ],
    'errors' => [
        'index' => 'sesion',
    ],
    'administracion' => [
        'home' => 'sesion',
        'actualizarGrafico' => 'sesion',
        'reporteStock' => 'sesion',
        'topProductos' => 'sesion',
        'permisos' => 'sesion',
        'index' => 'configuracion',
        'modificar' => 'configuracion',
        'importarProductos' => 'crear_producto',
        'moneda' => $moneda,
        'listarMonedas' => $moneda,
        'registrarMoneda' => ['guardar' => ['crear_moneda', 'modificar_moneda']],
        'editarMoneda' => 'modificar_moneda',
        'eliminarMoneda' => 'eliminar_moneda',
        'reingresarMoneda' => 'restaurar_moneda',
        'inactivos' => 'restaurar_moneda',
    ],
    'usuarios' => [
        'validar' => 'publico',
        'enviarCorreo' => 'publico',
        'restablecer' => 'publico',
        'resetear' => 'publico',
        'salir' => 'publico',
        'perfil' => 'sesion',
        'actualizarDato' => 'sesion',
        'cambiarPass' => 'sesion',
        'index' => ['crear_usuario', 'modificar_usuario', 'eliminar_usuario', 'restaurar_usuario', 'asignar_permisos'],
        'listar' => ['crear_usuario', 'modificar_usuario', 'eliminar_usuario', 'restaurar_usuario', 'asignar_permisos'],
        'registrar' => ['guardar' => ['crear_usuario', 'modificar_usuario']],
        'editar' => 'modificar_usuario',
        'eliminar' => 'eliminar_usuario',
        'reingresar' => 'restaurar_usuario',
        'inactivos' => 'restaurar_usuario',
        'permisos' => 'asignar_permisos',
        'registrarPermisos' => 'asignar_permisos',
    ],
    'cajas' => $crud('caja') + [
        'arqueo' => ['abrir_caja', 'cerrar_caja'],
        'listar_arqueo' => ['abrir_caja', 'cerrar_caja'],
        'getVentas' => ['abrir_caja', 'cerrar_caja'],
        'abrirArqueo' => ['guardar' => ['abrir_caja', 'cerrar_caja']],
        'reporte' => 'reporte_cajas',
        'listarReporte' => 'reporte_cajas',
    ],
    'clientes' => $crud('cliente') + [
        'buscarCliente' => ['nueva_venta', 'cotizaciones', 'apartados', 'crear_cliente', 'modificar_cliente'],
    ],
    'proveedor' => $crud('proveedor') + [
        'buscarProveedor' => ['nueva_compra', 'crear_proveedor', 'modificar_proveedor'],
    ],
    'medidas' => $crud('medida'),
    'categorias' => $crud('categoria'),
    'productos' => $crud('producto') + [
        'inventario' => ['inventario', 'reporte_pdf_inventario'],
        'listarInventario' => ['inventario', 'reporte_pdf_inventario'],
        'registrarInventario' => 'inventario',
        'pdfInventario' => 'reporte_pdf_inventario',
        'pdfCompra' => 'reporte_pdf_compras',
        'pdfVenta' => 'reporte_pdf_ventas',
    ],
    'compras' => [
        'index' => 'nueva_compra',
        'buscarProducto' => $buscarProducto,
        'agregarCompra' => 'nueva_compra',
        'cantidadCompra' => 'nueva_compra',
        'precioCompra' => 'nueva_compra',
        'delete' => 'nueva_compra',
        'registrarCompra' => 'nueva_compra',
        // detalle = carrito de compras, detalle_temp = carrito de ventas
        'listar/detalle' => 'nueva_compra',
        'listar/detalle_temp' => 'nueva_venta',
        'anularProceso/detalle' => 'nueva_compra',
        'anularProceso/detalle_temp' => 'nueva_venta',
        'historial' => ['reporte_compras', 'anular_compra', 'reporte_pdf_compras'],
        'listar_historial' => ['reporte_compras', 'anular_compra', 'reporte_pdf_compras'],
        'generarPdf' => ['reporte_compras', 'nueva_compra'],
        'generarFactura' => ['reporte_compras', 'nueva_compra'],
        'anularC' => 'anular_compra',
        'inactivos' => ['reporte_compras', 'anular_compra'],
    ],
    'ventas' => [
        'index' => 'nueva_venta',
        'agregarVenta' => 'nueva_venta',
        'cantidadVenta' => 'nueva_venta',
        'deleteVenta' => 'nueva_venta',
        'registrarVenta' => 'nueva_venta',
        'historial' => ['reporte_ventas', 'anular_venta', 'reporte_pdf_ventas'],
        'listar_historial' => ['reporte_ventas', 'anular_venta', 'reporte_pdf_ventas'],
        'generarPdf' => ['reporte_ventas', 'nueva_venta'],
        'generarFactura' => ['reporte_ventas', 'nueva_venta'],
        'anularVenta' => 'anular_venta',
        'inactivos' => ['reporte_ventas', 'anular_venta'],
    ],
    'cotizaciones' => [
        'index' => 'cotizaciones',
        'agregarCotizacion' => 'cotizaciones',
        'itemCotizacion' => 'cotizaciones',
        'deleteCotizacion' => 'cotizaciones',
        'registrarCotizacion' => 'cotizaciones',
        'historial' => 'cotizaciones',
        'listar' => 'cotizaciones',
        'listar_historial' => 'cotizaciones',
        'generarFactura' => 'cotizaciones',
    ],
    'apartados' => [
        'index' => 'apartados',
        'agregar' => 'apartados',
        'cantidadApartado' => 'apartados',
        'delete' => 'apartados',
        'listar' => 'apartados',
        'registrar' => 'apartados',
        'historial' => ['apartados', 'reporte_apartados'],
        'listarApartados' => ['apartados', 'reporte_apartados'],
        'verficar' => 'apartados',
        'entrega' => 'apartados',
        'anular' => 'apartados',
        'generarPdf' => ['apartados', 'reporte_apartados'],
    ],
    'creditos' => [
        'index' => $creditos,
        'listar' => $creditos,
        'finalizados' => $creditos,
        'abonos' => $creditos,
        'listarAbonos' => $creditos,
        'verificarMonto' => 'registrar_abono',
        'registrarAbono' => 'registrar_abono',
    ],
    // la auditoría vigila a todos los usuarios: solo la ve el Super Administrador
    'auditoria' => [
        'index' => 'superadmin',
        'listar' => 'superadmin',
    ],
    'landing' => [
        'index' => 'landing',
        'listar' => 'landing',
        'registrar' => 'landing',
        'editar' => 'landing',
        'eliminar' => 'landing',
        'agregarCliente' => 'landing',
    ],
];
