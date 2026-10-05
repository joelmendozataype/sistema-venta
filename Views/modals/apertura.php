<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formulario" autocomplete="off" method="POST">
                <div class="modal-body">
                    <?php include_once "Views/templates/alerta.php"; ?>
                    
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label>Monto Inicial <span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-list"></i>
                                </div>
                            </div>
                            <input id="monto_inicial" class="form-control" type="number" step="0.01" min="0.01" name="monto_inicial" aria-describedby="valid-monto_inicial" placeholder="Monto Inicial" required>
                        </div>
                        <span class="text-danger error" id="valid-monto_inicial"></span>
                    </div>
                    <div id="ocultar_campos">
                        <?php
                        // Desglose del cierre: solo contado y abonos suman al efectivo en caja
                        $campos_cierre = array(
                            array('monto_final', 'Ventas al Contado', 'fa-money-bill-wave'),
                            array('abonos', 'Cobros de Créditos y Apartados', 'fa-hand-holding-usd'),
                            array('ventas_credito', 'Ventas a Crédito (no ingresan a caja)', 'fa-credit-card'),
                            array('total_ventas', 'Cantidad de Ventas', 'fa-list'),
                            array('monto_general', 'Efectivo en Caja (inicial + contado + cobros)', 'fa-cash-register'),
                        );
                        foreach ($campos_cierre as $campo) { ?>
                            <div class="form-group">
                                <label><?php echo $campo[1]; ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas <?php echo $campo[2]; ?>"></i>
                                        </div>
                                    </div>
                                    <input id="<?php echo $campo[0]; ?>" class="form-control" type="text" disabled>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-primary" type="submit" id="btnAccion">Registrar</button>
                    <button class="btn btn-outline-danger" type="button" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>