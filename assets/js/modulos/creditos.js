let tblCreditos;
document.addEventListener("DOMContentLoaded", function() {
    tblCreditos = $("#tblCreditos").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "creditos/listar/1",
            dataSrc: "",
        },
        columns: [
            { data: "id" },
            { data: "nombre" },
            { data: "fecha" },
            { data: "monto" },
            { data: "abonado" },
            { data: "restante" },
            { data: "accion" },
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
    $("#tblFinalizados").DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: base_url + "creditos/listar",
            dataSrc: "",
        },
        columns: [
            { data: "id" },
            { data: "nombre" },
            { data: "fecha" },
            { data: "monto" },
            { data: "abonado" },
            { data: "restante" }
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

// Consulta el saldo y pide el monto; el servidor vuelve a validar todo al registrar
function btnAddAbono(id_credito) {
    const url = base_url + "creditos/verificarMonto/" + id_credito;
    const http = new XMLHttpRequest();
    http.open("GET", url, true);
    http.send();
    http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            const restante = parseFloat(res.restante);
            Swal.fire({
                title: "MONTO A ABONAR",
                text: "Falta pagar: " + restante.toFixed(2),
                input: "number",
                inputAttributes: {
                    min: "0.01",
                    max: restante.toFixed(2),
                    step: "0.01",
                },
                showCancelButton: true,
                confirmButtonText: "Abonar",
                cancelButtonText: "Cancelar",
                inputValidator: (valor) => {
                    const monto = parseFloat(valor);
                    if (isNaN(monto) || monto <= 0) {
                        return "Ingresa un monto mayor a 0";
                    }
                    if (Math.round(monto * 100) > Math.round(restante * 100)) {
                        return "El monto no puede ser mayor a " + restante.toFixed(2);
                    }
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    insertarAbono(id_credito, parseFloat(result.value).toFixed(2));
                }
            });
        }
    };
}

function insertarAbono(id_credito, monto) {
    const url = base_url + "creditos/registrarAbono";
    let data = new FormData();
    data.append("monto", monto);
    data.append("id_credito", id_credito);
    const http = new XMLHttpRequest();
    http.open("POST", url, true);
    http.send(data);
    http.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            alertas(res.msg, res.icono);
            if (res.icono == 'success') {
                tblCreditos.ajax.reload();
            }
        }
    }
}