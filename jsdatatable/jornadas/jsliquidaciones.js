$(document).ready(function(e) {

    var selected = [];
    var est = localStorage.getItem('lstipoconsulta');
    var emp = $('#busempleado').val();
    var obr = $('#busobra').val();
    var ano = $('#busano').val();
    var table2 = $('#tbLiquidaciones').DataTable({
        "dom": '<"top">rt<"bottom"lp><"clear">',
        "columnDefs": [],
        "ordering": true,
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
            "url": "json/jornadas/liquidacionjson.php",
            "data": { est:est,emp:emp,obr:obr,ano:ano},
            "type": "POST"
        },
        "columns": [		
              { "data" : "Previa", "className": "dt-center", "orderable":false, "searchable":false },
              { "data" : "Editar", "className": "dt-center", "orderable":false, "searchable":false },
              { "data" : "Aprobar", "className": "dt-center", "orderable":false, "searchable":false },
              { "data" : "Eliminar", "className": "dt-center" , "orderable": false, "searchable":false},
              { "data" : "Codigo", "className": "dt-left" },
              { "data" : "Obra", "className": "dt-left" }, 
              { "data" : "Cenco", "className": "dt-left" }, 
              { "data" : "Empleado", "className": "dt-left" }, 
              { "data" : "FechaRegistro", "className": "dt-center" },
              { "data" : "CreadaPor", "className": "dt-left" },
              { "data" : "Tipo", "className": "dt-left" },
              { "data" : "Desde", "className": "dt-center" },
              { "data" : "Hasta", "className": "dt-center" },
              { "data" : "Estado", "className": "dt-left" }, 
                     
           ],
        "order": [[11, 'asc'],[12, 'asc'], [7, 'asc']], // ORDENAMIENTO POR FECHA INICIO Y FIN Y LUEGO POR EMPLEADO
        "rowCallback": function(row, data) {
            //if ($.inArray(data.DT_RowId, selected) !== -1) {
            // $(row).addClass('selected');
            // }
            
            $('td', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(1)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(2)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(3)', row).addClass('pt-0 pb-0 align-middle') 
            // $('td:eq(4)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(5)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(6)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(7)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(8)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(9)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(10)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(11)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(12)', row).addClass('pt-0 pb-0 align-middle')
            // $('td:eq(13)', row).addClass('pt-0 pb-0 align-middle')
        }
  
    });
  })
  