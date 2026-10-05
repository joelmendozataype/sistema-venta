let t_auditoria;
document.addEventListener("DOMContentLoaded", function() {
    t_auditoria = $("#t_auditoria").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "auditoria/listar",
            data: function(d) {
                d.desde = document.getElementById("desde").value;
                d.hasta = document.getElementById("hasta").value;
                d.usuario = document.getElementById("usuario").value;
                d.accion = document.getElementById("accion").value;
            },
            dataSrc: "",
        },
        columns: [
            { data: "id" },
            { data: "fecha" },
            { data: "usuario" },
            { data: "accion" },
            { data: "id_registro", defaultContent: "" },
            { data: "motivo", defaultContent: "" },
            { data: "detalle", defaultContent: "" },
            { data: "ip", defaultContent: "" },
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

function cargarAuditoria() {
    const desde = document.getElementById("desde").value;
    const hasta = document.getElementById("hasta").value;
    if (desde == "" || hasta == "" || desde > hasta) {
        alertas("Selecciona un rango de fechas válido", "warning");
        return;
    }
    t_auditoria.ajax.reload();
}
