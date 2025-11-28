$(document).ready(function(e) {

    var selected = [];
    var nom = $('#busnombre').val();

    var table2 = $('#tbHorarios').DataTable({
        "dom": '<"top">rt<"bottom"lp><"clear">',
        "columnDefs": [],
        "ordering": true,
        "responsive": true,
        "info": true,
        "autoWidth": true,
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [10, 15, 20, -1],
            [10, 15, 20, "Todos"]
        ],
        "language": {
            "lengthMenu": "Ver _MENU_",
            "zeroRecords": "No se encontraron datos",
            "info": "Resultado _START_ - _END_ de _TOTAL_ registros",
            "infoEmpty": "No se encontraron datos",
            "infoFiltered": "",
            "paginate": { "previous": "&#9668;", "next": "&#9658;" }
        },		
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "json/horarios/horariosjson.php",
            "data": { nom:nom},
            "type": "POST"
        },
        "columns": [	 
            { "data" : "Editar",    "className": "dt-center", "orderable": false, "searchable": false },
            { "data" : "Eliminar",  "className": "dt-center", "orderable": false, "searchable": false },	
            { "data" : "Nombre",    "className": "dt-left" },           
            { "data" : "Lun",       "className": "text-center bg-gray-light"    /*,render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/   },
            { "data" : "Mar",       "className": "text-center bg-gray-light"    /*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/  },
            { "data" : "Mie",       "className": "text-center bg-gray-light"    /*,render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/   },		
            { "data" : "Jue",       "className": "text-center bg-gray-light"    /*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) */ },
            { "data" : "Vie",       "className": "text-center bg-gray-light"    /*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/  },
            { "data" : "Sab",       "className": "text-center bg-gray-light"    /*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) */ },
            { "data" : "Dom",       "className": "text-center bg-gray-light"    /*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/  },            
        ],
        "order": [[2, 'asc']],
        "rowCallback": function(row, data) {
            //if ($.inArray(data.DT_RowId, selected) !== -1) {
            // $(row).addClass('selected');
            // }
        }  
    });
  })