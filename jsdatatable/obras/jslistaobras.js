$(document).ready(function(e) {

    var selected = [];
    var nom = $('#busnombre').val();
    var ubi = $('#busubicacion').val();
    var nompro = $('#busnombreproyecto').val();
    var cen = $('#buscenco').val();
    var dir = $('#busdirector').val();
    var est = $('#busestado').val();
    var con = $('#buscontrato').val();
    
    var table2 = $('#tbObras').DataTable({
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
            "url": "json/obras/obrasjson.php",
            "data": { nom:nom, cen:cen ,dir:dir, est: est, ubi: ubi, nompro: nompro, con:con },
            "type": "POST"
        },
        "columns": [	
            { "data": "Editar",   "className": "dt-center align-middle", "orderable": false, "searchable": false },
            { "data": "Eliminar", "className": "dt-center align-middle", "orderable": false, "searchable": false },	
            { "data" : "Nombre",    "className": "dt-left align-middle" },
            { "data" : "NombreProyecto",    "className": "dt-left align-middle" },
            { "data" : "Contrato",    "className": "dt-left align-middle" },
            { "data" : "FechaInicio",    "className": "dt-left align-middle" },
            { "data" : "Ubicacion",    "className": "dt-left align-middle" },
            { "data" : "Director",  "className": "dt-left align-middle" },
            { "data" : "Cencos",    "className": "dt-left align-middle" },  
            { "data" : "Horas", "className": "dt-center align-middle" }, 
            { "data" : "HorasSemana", "className": "dt-left align-middle" }, 
            { "data" : "ValorOperario", "className": "dt-left align-middle", render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) }, 
            { "data" : "ValorSenalero", "className": "dt-left align-middle", render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) }, 
            { "data" : "ValorElevador", "className": "dt-left align-middle", render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) }, 
            { "data" : "ValorMaquina", "className": "dt-left align-middle", render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) }, 
           
            { "data" : "Lun", "className": "text-center bg-gray-light align-middle"/*,render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/ },
            { "data" : "Mar", "className": "text-center bg-gray-light align-middle"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/ },
            { "data" : "Mie", "className": "text-center bg-gray-light align-middle"/*,render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/ },		
            { "data" : "Jue", "className": "text-center bg-gray-light align-middle"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) */},
            { "data" : "Vie", "className": "text-center bg-gray-light align-middle"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/ },
            { "data" : "Sab", "className": "text-center bg-gray-light align-middle"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) */},
            { "data" : "Dom", "className": "text-center bg-gray-light align-middle"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' )*/ },   
            { "data" : "Estado", "className": "dt-center align-middle" }, 
           
            //{ "data" : "total", "className": "text-center bg-gray"/*, render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) */},
            //{ "data" : "promedio", "className": "text-center",render: $.fn.dataTable.render.number( ',', '.', 0, '$' ) },
          
        ],
        "order": [[2, 'asc']],
        "rowCallback": function(row, data) {
            //if ($.inArray(data.DT_RowId, selected) !== -1) {
            // $(row).addClass('selected');
            // }
        }  
    });
    $('#tbObras tbody')
    .on( 'mouseenter', 'td', function () {
        var colIdx = table2.cell(this).index().column;

        $( table2.cells().nodes() ).removeClass( 'highlight' );
        $( table2.column( colIdx ).nodes() ).addClass( 'highlight' );
    } );
  })