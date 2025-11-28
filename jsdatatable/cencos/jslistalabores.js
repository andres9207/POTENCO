/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

    var selected = [];
    var id = $('#tbLabores').attr('data-id');
    var table2 = $('#tbLabores').DataTable({
        "columnDefs": [

        ],
        "dom": '<"top"i>frt<"bottom"p><"clear">',
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
            "info": "Resultado _START_ - _END_ de _TOTAL_ registros ",
            "infoEmpty": "No se encontraron datos",
            "infoFiltered": "",
            "paginate": { "previous": "Anterior", "next": "siguiente" },
            "search": "",
            "sSearchPlaceholder": "Busqueda"
        },
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "json/cencos/laboresjson.php",
            "data": { id: id },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Descripcion", "className": "dt-left " },
            { "data": "Edicion", "className": "dt-left " },
        ],
        "order": [
            [0, 'asc']
        ]
    });

    var table2 = $('#tbLabores').DataTable();
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