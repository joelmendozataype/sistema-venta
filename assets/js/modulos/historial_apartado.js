document.addEventListener("DOMContentLoaded", function() {
    $("#t_historial_apart").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "apartados/listarApartados",
            dataSrc: "",
        },
        columns: [
            { data: "id" },
            { data: "title" },
            { data: "fecha_apartado" },
            { data: "start" },
            { data: "total" },
            { data: "abono" },
            { data: "restante" },
            { data: "estado" },
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
});