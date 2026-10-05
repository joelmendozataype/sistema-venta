let t_reporte_cajas;
document.addEventListener("DOMContentLoaded", function() {
    t_reporte_cajas = $("#t_reporte_cajas").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "cajas/listarReporte",
            data: function(d) {
                d.desde = document.getElementById("desde").value;
                d.hasta = document.getElementById("hasta").value;
                d.usuario = document.getElementById("usuario").value;
            },
            dataSrc: function(res) {
                document.getElementById("total_contado").textContent = res.totales.contado;
                document.getElementById("total_cobros").textContent = res.totales.cobros;
                document.getElementById("total_credito").textContent = res.totales.credito;
                document.getElementById("total_efectivo").textContent = res.totales.efectivo;
                return res.cierres;
            },
        },
        columns: [
            { data: "id" },
            { data: "usuario" },
            { data: "caja" },
            { data: "fecha_apertura" },
            { data: "fecha_cierre", defaultContent: "" },
            { data: "monto_inicial" },
            { data: "ventas_contado" },
            { data: "total_abonos" },
            { data: "ventas_credito" },
            { data: "total_ventas" },
            { data: "monto_total" },
            { data: "estado" },
        ],
        language: {
            url: base_url + "assets/js/datatables-es.json",
        },
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 25,
        order: [
            [0, "desc"]
        ],
    });
});

function cargarReporte() {
    const desde = document.getElementById("desde").value;
    const hasta = document.getElementById("hasta").value;
    if (desde == "" || hasta == "" || desde > hasta) {
        alertas("Selecciona un rango de fechas válido", "warning");
        return;
    }
    t_reporte_cajas.ajax.reload();
}
