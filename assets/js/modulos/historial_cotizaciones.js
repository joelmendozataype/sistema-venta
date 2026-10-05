let t_cotizaciones;
document.addEventListener("DOMContentLoaded", function() {
    t_cotizaciones = $("#t_historial").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "cotizaciones/listar_historial",
            dataSrc: "",
        },
        columns: [
            { data: "id" },
            { data: "nombre" },
            { data: "fecha" },
            { data: "hora" },
            { data: "total" },
            { data: "validez" },
            { data: "accion" }
        ],
        language: {
            url: base_url + "assets/js/datatables-es.json",
        },
        dom,
        buttons,
        resonsieve: true,
        bDestroy: true,
        iDisplayLength: 10,
        order: [
            [0, "desc"]
        ],
    });
})