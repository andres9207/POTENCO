

$(document).ready(function(e) {

    var selected = [];
    var est = localStorage.getItem('lstipoconsulta');
    var emp = $('#busempleado').val();
    var obr = $('#busobra').val();
    var ano = $('#busano').val();
    var sem = $('#bussemana').val();
    var table2 = $('#tbJornada').DataTable({
        "dom": '<"top">rt<"bottom"lp><"clear">',
        "columnDefs": [],
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [20, 40, 100, -1],
            [20, 40, 100, "Todos"]
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
            "url": "json/jornadas/jornadasjson.php",
            "data": { est:est,emp:emp,obr:obr,ano:ano,sem:sem,},
            "type": "POST"
        },
        "columns": [		
              { "data" : "Editar", "className": "dt-lef", "orderable":false, "searchable":false },
              { "data" : "Aprobar", "className": "dt-left" , "orderable": false, "searchable":false},
              { "data" : "Empleado", "className": "dt-left" },
              { "data" : "Ano", "className": "text-left" },
              { "data" : "Semana", "className": "text-left" },
              { "data" : "FechaMin", "className": "text-center" },
              { "data" : "FechaMax", "className": "text-center" },
              { "data" : "Total", "className": "text-center" },
              { "data" : "Estado", "className": "dt-left" },    
              { "data" : "CreadaPor", "className": "dt-left" } ,  
              { "data" : "FechaRegistro", "className": "text-center" }         
           ],
        "order": [[2, 'asc'],[3, 'asc']],
        "rowCallback": function(row, data) {
            //if ($.inArray(data.DT_RowId, selected) !== -1) {
            // $(row).addClass('selected');
            // }
            $('td:eq(0)', row).addClass('pt-0 pb-0')
            $('td:eq(1)', row).addClass('pt-0 pb-0')
            $('td:eq(2)', row).addClass('pt-0 pb-0')
            $('td:eq(3)', row).addClass('pt-0 pb-0') 
            $('td:eq(4)', row).addClass('pt-0 pb-0')
            $('td:eq(5)', row).addClass('pt-0 pb-0')
            $('td:eq(6)', row).addClass('pt-0 pb-0')
            $('td:eq(7)', row).addClass('pt-0 pb-0')
            $('td:eq(8)', row).addClass('pt-0 pb-0')
            $('td:eq(9)', row).addClass('pt-0 pb-0')
            $('td:eq(10)', row).addClass('pt-0 pb-0')
            

        }
  
    });
  })