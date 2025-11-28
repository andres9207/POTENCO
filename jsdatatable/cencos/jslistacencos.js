/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

    var selected = [];
    var nom = $('#busnombre').val();
    var cod = $('#buscodigo').val();
    var table2 = $('#tbCencos').DataTable({
        "columnDefs": [
            { "targets":[2], "visible": false},
        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "searching":false,
        "autoWidth": true,
        "responsive": true,
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [10, 15, 20, -1],
            [10, 15, 20, "Todos"]
        ],
        "language": {
            "lengthMenu": "Ver _MENU_ registros",
            "zeroRecords": "No se encontraron datos",
            "info": "Resultado _START_ - _END_ de _TOTAL_ registros",
            "infoEmpty": "No se encontraron datos",
            "infoFiltered": "",
            "paginate": { "previous": "Anterior", "next": "siguiente" },
            "search": "",
            "sSearchPlaceholder": "Busqueda"
        },
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "json/cencos/cencosjson.php",
            "data": { nom: nom, cod: cod },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Codigo", "className": "dt-left " },
            { "data": "Membrete", "className": "dt-left " },
            { "data": "Editar", "className": "dt-center", "orderable": false, "searchable": false },
            { "data": "Eliminar", "className": "dt-center", "orderable": false, "searchable": false },
        ],
        "order": [
            [0, 'asc']
        ],
        rowCallback: function(row, data, index) {
            console.log(data.Clave);
            $('#view' + data.Clave).fullsizable();
        }
    });

    $('#tbCencos tbody').on('click', 'tr', function() {
        var id = this.id;
        var index = $.inArray(id, selected);

        if (index === -1) {
            selected.push(id);
        } else {
            selected.splice(index, 1);
        }
        $(this).toggleClass('selected');
    });

    var table2 = $('#tbCencos').DataTable();
    // Apply the search
    table2.columns().every(function() {
        var that = this;

        $('input', this.footer()).on('keyup change', function() {
            that
                .search(this.value)
                .draw();
        });
    });
});

function tbObras(id) {
    var table2 = $('#tbObras' + id).DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},
        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [10, 15, 20, -1],
            [10, 15, 20, "Todos"]
        ],
        "language": {
            "lengthMenu": "Ver _MENU_ registros",
            "zeroRecords": "No se encontraron datos",
            "info": "Resultado _START_ - _END_ de _TOTAL_ registros",
            "infoEmpty": "No se encontraron datos",
            "infoFiltered": "",
            "paginate": { "previous": "Anterior", "next": "siguiente" },
            "search": "",
            "sSearchPlaceholder": "Busqueda"
        },
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "json/consorcios/obrasjson.php",
            "data": { id: id },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Referencia", "className": "dt-left " },
            { "data": "Editar", "className": "dt-center" },
            { "data": "Eliminar", "className": "dt-center", "orderable": false, "searchable": false },
        ],
        "order": [
            [0, 'asc']
        ],

    });
}

$('a.fullsizable').fullsizable();