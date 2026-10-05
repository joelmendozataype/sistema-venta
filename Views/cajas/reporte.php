<?php include "Views/templates/header.php"; ?>
<div class="card">
    <div class="card-header">
        Reporte de Cierres de Caja
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-3">
                <label for="desde">Desde (apertura)</label>
                <input class="form-control" id="desde" type="date" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-3">
                <label for="hasta">Hasta (apertura)</label>
                <input class="form-control" id="hasta" type="date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label for="usuario">Cajero</label>
                <select class="form-control" id="usuario">
                    <option value="0">Todos</option>
                    <?php foreach ($data['usuarios'] as $usuario) { ?>
                        <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-grid">
                    <label>&nbsp;</label>
                    <button class="btn btn-outline-primary" type="button" onclick="cargarReporte()"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </div>
        </div>
        <div class="row mb-3 text-center">
            <div class="col-md-3 col-6 mb-2">
                <div class="border rounded p-2">
                    <small class="text-muted">Ventas al contado</small>
                    <h5 class="mb-0" id="total_contado">0.00</h5>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="border rounded p-2">
                    <small class="text-muted">Cobros (créditos y apartados)</small>
                    <h5 class="mb-0" id="total_cobros">0.00</h5>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="border rounded p-2">
                    <small class="text-muted">Ventas a crédito</small>
                    <h5 class="mb-0" id="total_credito">0.00</h5>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="border rounded p-2">
                    <small class="text-muted">Efectivo total en cajas</small>
                    <h5 class="mb-0" id="total_efectivo">0.00</h5>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover display responsive nowrap" id="t_reporte_cajas" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cajero</th>
                        <th>Caja</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Monto Inicial</th>
                        <th>Contado</th>
                        <th>Cobros</th>
                        <th>Crédito</th>
                        <th>Cant. Ventas</th>
                        <th>Efectivo en Caja</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "Views/templates/footer.php"; ?>

<script src="<?php echo asset('assets/js/modulos/reporte_cajas.js'); ?>"></script>

</body>

</html>
