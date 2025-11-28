/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

    var selected = [];
    var fec = $('#busfecha').val();
    var des = $('#busdescripcion').val();
    var table2 = $('#tbFestivos').DataTable({
        "columnDefs": [
            //{ "targets":[2], "visible": false},
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
            "url": "json/festivos/festivosjson.php",
            "data": { fec: fec, des: des },
            "type": "POST"
        },
        "columns": [
            { "data": "Editar",         "className": "dt-center", "orderable": false, "searchable": false },
            { "data": "Eliminar",       "className": "dt-center", "orderable": false, "searchable": false },
            { "data": "Fecha",          "className": "dt-left " },
            { "data": "Descripcion",    "className": "dt-left " },           
           
        ],
        "order": [
            [2, 'asc']
        ],
        rowCallback: function(row, data, index) {
            
        }
    });

    $('#tbFestivos tbody').on('click', 'tr', function() {
        var id = this.id;
        var index = $.inArray(id, selected);

        if (index === -1) {
            selected.push(id);
        } else {
            selected.splice(index, 1);
        }
        $(this).toggleClass('selected');
    });

    var table2 = $('#tbFestivos').DataTable();
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