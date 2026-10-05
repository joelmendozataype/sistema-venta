-- --------------------------------------------------------
-- DATOS DE PRUEBA (opcional): un usuario por rol, según docs/Guia_Roles_y_Permisos.docx
-- Importar DESPUÉS de DB/cotizaciones.sql. Se puede ejecutar varias veces sin duplicar.
-- Contraseña de todos: 123456   <-- SOLO PARA PRUEBAS, no usar con datos reales.
-- --------------------------------------------------------
USE `cotizaciones`;

-- Gerente: 52 permisos
INSERT INTO `usuarios` (`nombre`, `correo`, `clave`, `id_caja`) SELECT 'GERENTE GENERAL', 'gerente@tuempresa.com', '$2y$10$YYnRsgEUDv2QKRq2QpUgs.7tJpweoWo8TjnsCdfcJ4CUM4QgzjwxK', 1 FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'gerente@tuempresa.com');
INSERT INTO `detalle_permisos` (`id_usuario`, `id_permiso`) SELECT u.id, p.id FROM `usuarios` u JOIN `permisos` p ON p.id IN (1,2,3,4,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,46,47,48,49,50,51,52,53,54)
  WHERE u.correo = 'gerente@tuempresa.com' AND NOT EXISTS (SELECT 1 FROM `detalle_permisos` d WHERE d.id_usuario = u.id AND d.id_permiso = p.id);

-- Cajero: 8 permisos
INSERT INTO `usuarios` (`nombre`, `correo`, `clave`, `id_caja`) SELECT 'CAJERO UNO TIENDA', 'caja1@tuempresa.com', '$2y$10$YYnRsgEUDv2QKRq2QpUgs.7tJpweoWo8TjnsCdfcJ4CUM4QgzjwxK', 1 FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'caja1@tuempresa.com');
INSERT INTO `detalle_permisos` (`id_usuario`, `id_permiso`) SELECT u.id, p.id FROM `usuarios` u JOIN `permisos` p ON p.id IN (13,14,37,39,42,43,44,47)
  WHERE u.correo = 'caja1@tuempresa.com' AND NOT EXISTS (SELECT 1 FROM `detalle_permisos` d WHERE d.id_usuario = u.id AND d.id_permiso = p.id);

-- Almacenero: 21 permisos
INSERT INTO `usuarios` (`nombre`, `correo`, `clave`, `id_caja`) SELECT 'ALMACEN', 'almacen@tuempresa.com', '$2y$10$YYnRsgEUDv2QKRq2QpUgs.7tJpweoWo8TjnsCdfcJ4CUM4QgzjwxK', 1 FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'almacen@tuempresa.com');
INSERT INTO `detalle_permisos` (`id_usuario`, `id_permiso`) SELECT u.id, p.id FROM `usuarios` u JOIN `permisos` p ON p.id IN (17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,36,40,41)
  WHERE u.correo = 'almacen@tuempresa.com' AND NOT EXISTS (SELECT 1 FROM `detalle_permisos` d WHERE d.id_usuario = u.id AND d.id_permiso = p.id);

-- Asesor: 3 permisos
INSERT INTO `usuarios` (`nombre`, `correo`, `clave`, `id_caja`) SELECT 'ASESOR COMERCIAL', 'ventas@tuempresa.com', '$2y$10$YYnRsgEUDv2QKRq2QpUgs.7tJpweoWo8TjnsCdfcJ4CUM4QgzjwxK', 1 FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'ventas@tuempresa.com');
INSERT INTO `detalle_permisos` (`id_usuario`, `id_permiso`) SELECT u.id, p.id FROM `usuarios` u JOIN `permisos` p ON p.id IN (13,14,47)
  WHERE u.correo = 'ventas@tuempresa.com' AND NOT EXISTS (SELECT 1 FROM `detalle_permisos` d WHERE d.id_usuario = u.id AND d.id_permiso = p.id);

-- Supervisor: 10 permisos
INSERT INTO `usuarios` (`nombre`, `correo`, `clave`, `id_caja`) SELECT 'SUPERVISOR CONTADOR', 'supervisor@tuempresa.com', '$2y$10$YYnRsgEUDv2QKRq2QpUgs.7tJpweoWo8TjnsCdfcJ4CUM4QgzjwxK', 1 FROM DUAL
  WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `correo` = 'supervisor@tuempresa.com');
INSERT INTO `detalle_permisos` (`id_usuario`, `id_permiso`) SELECT u.id, p.id FROM `usuarios` u JOIN `permisos` p ON p.id IN (35,36,38,39,40,41,42,51,52,54)
  WHERE u.correo = 'supervisor@tuempresa.com' AND NOT EXISTS (SELECT 1 FROM `detalle_permisos` d WHERE d.id_usuario = u.id AND d.id_permiso = p.id);
