$(document).ready(function(e) {

    var selected = [];
    // var est = localStorage.getItem('lstipoconsulta');
    // var emp = $('#busempleado').val();
    // var obr = $('#busobra').val();
    // var ano = $('#busano').val();
    // var sem = $('#bussemana').val();
    var table2 = $('#tbLiquidar').DataTable({
        
        //responsive: true,
        "dom": '<"top">rt<"bottom"lp><"clear">',
        "columnDefs": [
            { "targets": [0,1,2,3,4,5,7],"orderable": false } 
        ],
        "ordering": false,
        "info": true, 
        "autoWidth":false,      
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [-1,10, 15, 20, ],
            [ "Todos",10, 15, 20,]
        ],
        "language": {   
            "lengthMenu": "Ver _MENU_",
            "zeroRecords": "No se encontraron datos",
            "info": "Resultado _START_ - _END_ de _TOTAL_ registros",
            "infoEmpty": "No se encontraron datos",
            "infoFiltered": "",
            "paginate": { "previous": "&#9668;", "next": "&#9658;" }
        },	
        //"order": [[0, 'asc']],	
        // "processing": true,
        // "serverSide": true,
        // "ajax": {
        //     "url": "json/jornadas/jornadasjson.php",
        //     "data": { est:est,emp:emp,obr:obr,ano:ano,sem:sem,},
        //     "type": "POST"
        // },
        // "columns": [		
        //       { "data" : "Editar", "className": "dt-left", "orderable":false, "searchable":false },
        //       { "data" : "Aprobar", "className": "dt-left" , "orderable": false, "searchable":false},
        //       { "data" : "Empleado", "className": "dt-left" },
        //       { "data" : "Ano", "className": "dt-left" },
        //       { "data" : "Semana", "className": "dt-left" },
        //       { "data" : "Total", "className": "dt-center" },
        //       { "data" : "Estado", "className": "dt-left" },    
        //       { "data" : "CreadaPor", "className": "dt-left" } ,  
        //       { "data" : "FechaRegistro", "className": "dt-left" }         
        //    ],
        // "order": [[2, 'asc'],[3, 'asc']],
        // "rowCallback": function(row, data) {
        //     //if ($.inArray(data.DT_RowId, selected) !== -1) {
        //     // $(row).addClass('selected');
        //     // }
        // }
  
    });
    //new $.fn.dataTable.FixedHeader(table2 );
  })