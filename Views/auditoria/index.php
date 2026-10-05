<?php include "Views/templates/header.php"; ?>
<div class="card">
    <div class="card-header">
        <i class="fas fa-shield-alt me-2"></i> Auditoría
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Registro de acciones sensibles. Es de solo lectura: no se puede editar ni borrar desde el sistema.</p>
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="desde">Desde</label>
                <input class="form-control" id="desde" type="date" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-2">
                <label for="hasta">Hasta</label>
                <input class="form-control" id="hasta" type="date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label for="usuario">Usuario</label>
                <select class="form-control" id="usuario">
                    <option value="0">Todos</option>
                    <?php foreach ($data['usuarios'] as $usuario) { ?>
                        <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="accion">Acción</label>
                <select class="form-control" id="accion">
                    <option value="">Todas</option>
                    <?php foreach ($data['acciones'] as $clave => $nombre) { ?>
                        <option value="<?php echo $clave; ?>"><?php echo $nombre; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <label>&nbsp;</label>
                    <button class="btn btn-outline-primary" type="button" onclick="cargarAuditoria()"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover display responsive nowrap" id="t_auditoria" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha y hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Registro</th>
                        <th>Motivo</th>
                        <th>Detalle</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "Views/templates/footer.php"; ?>

<script src="<?php echo asset('assets/js/modulos/auditoria.js'); ?>"></script>

</body>

</html>
