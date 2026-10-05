<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title><?php echo TITLE; ?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL; ?>assets/img/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>assets/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL; ?>assets/img/favicon/favicon-16x16.png">
<link rel="manifest" href="<?php echo BASE_URL; ?>assets/img/favicon/site.webmanifest">

  <!-- General CSS Files -->
  <link href="<?php echo BASE_URL; ?>assets/DataTables/datatables.css" rel="stylesheet" />

  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/app.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/bundles/prism/prism.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/full-calendar.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css">
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="<?php echo asset('assets/css/custom.css'); ?>">
  <link href="<?php echo BASE_URL; ?>assets/css/jquery-ui.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
  
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar sticky">
        <div class="form-inline me-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-bs-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn"> <i data-feather="align-justify"></i></a></li>
            <li><a href="#" class="nav-link nav-link-lg fullscreen-btn">
                <i data-feather="maximize"></i>
              </a></li>
          </ul>
        </div>
        <ul class="navbar-nav navbar-right">
          <li class="dropdown"><a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image" src="<?php echo BASE_URL; ?>assets/img/users/<?php echo $_SESSION['perfil']; ?>" class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
              <div class="dropdown-title"><?php echo $_SESSION['nombre']; ?></div>
              <a href="<?php echo BASE_URL; ?>usuarios/perfil" class="dropdown-item has-icon"> <i class="far
										fa-user"></i> Perfil
              </a>
              <div class="dropdown-divider"></div>
              <a href="<?php echo BASE_URL; ?>usuarios/salir" class="dropdown-item has-icon text-danger"> <i class="fas fa-sign-out-alt"></i>
                Cerra Sesión
              </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="<?php echo BASE_URL; ?>administracion/home"> <img alt="image" src="<?php echo BASE_URL; ?>assets/img/logo.png" class="header-logo" /> <span class="logo-name"><?php echo TITLE; ?></span>
            </a>
          </div>
          <?php
          // Menú según los permisos del usuario: se usa el mismo mapa que valida las rutas
          $menu = array(
            array('Administración', 'settings', array(
              array('Monedas', 'administracion', 'moneda'),
              array('Usuarios', 'usuarios', 'index'),
              array('Configuración', 'administracion', 'index'),
              array('Auditoría', 'auditoria', 'index'),
            )),
            array('Cajas', 'box', array(
              array('Lista Cajas', 'cajas', 'index'),
              array('Apertura y Cierre', 'cajas', 'arqueo'),
              array('Reporte de Cierres', 'cajas', 'reporte'),
            )),
            array('Clientes', 'users', 'clientes', 'index'),
            array('Ladings', 'list', 'landing', 'index'),
            array('Proveedor', 'home', 'proveedor', 'index'),
            array('Inventario', 'calendar', 'productos', 'inventario'),
            array('Mantenimiento', 'list', array(
              array('Medidas', 'medidas', 'index'),
              array('Categorias', 'categorias', 'index'),
              array('Productos', 'productos', 'index'),
            )),
            array('Compras', 'truck', array(
              array('Nueva Compra', 'compras', 'index'),
              array('Historial Compras', 'compras', 'historial'),
            )),
            array('Cotizaciones', 'list', array(
              array('Nueva Cotización', 'cotizaciones', 'index'),
              array('Historial Cotizaciónes', 'cotizaciones', 'historial'),
            )),
            array('Ventas', 'shopping-cart', array(
              array('Nueva Venta', 'ventas', 'index'),
              array('Historial Ventas', 'ventas', 'historial'),
            )),
            array('Apartados', 'save', array(
              array('Apartar Productos', 'apartados', 'index'),
              array('Historial Apartados', 'apartados', 'historial'),
            )),
            array('Creditos', 'credit-card', array(
              array('Administrar Creditos', 'creditos', 'index'),
              array('Creditos Finalizados', 'creditos', 'finalizados'),
              array('Historial Abonos', 'creditos', 'abonos'),
            )),
          );
          ?>
          <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown">
              <a href="<?php echo BASE_URL; ?>administracion/home" class="nav-link"><i data-feather="monitor"></i><span>Tablero</span></a>
            </li>
            <?php foreach ($menu as $item) {
              if (is_array($item[2])) {
                $opciones = array_filter($item[2], function ($op) {
                  return Auth::puedeRuta($op[1], $op[2]);
                });
                if (empty($opciones)) continue; ?>
                <li class="dropdown">
                  <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="<?php echo $item[1]; ?>"></i><span><?php echo $item[0]; ?></span></a>
                  <ul class="dropdown-menu">
                    <?php foreach ($opciones as $op) { ?>
                      <li><a class="nav-link" href="<?php echo BASE_URL . $op[1] . ($op[2] == 'index' ? '' : '/' . $op[2]); ?>"><?php echo $op[0]; ?></a></li>
                    <?php } ?>
                  </ul>
                </li>
              <?php } else if (Auth::puedeRuta($item[2], $item[3])) { ?>
                <li class="dropdown">
                  <a href="<?php echo BASE_URL . $item[2] . ($item[3] == 'index' ? '' : '/' . $item[3]); ?>" class="nav-link"><i data-feather="<?php echo $item[1]; ?>"></i><span><?php echo $item[0]; ?></span></a>
                </li>
            <?php }
            } ?>
          </ul>
        </aside>
      </div>
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">