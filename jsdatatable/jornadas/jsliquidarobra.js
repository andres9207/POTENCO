$(document).ready(function(e) {

    var selected = [];
    var est = localStorage.getItem('lstipoconsulta');
    var emp = $('#busempleado').val();
    var obr = $('#busobra').val();
    var ano = $('#busano').val();
    var ini = $('#busperiodo>option:selected').attr('data-inicio');
    var fin = $('#busperiodo>option:selected').attr('data-fin');

    var table2 = $('#tbLiquidarObra').DataTable({
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
            "url": "json/jornadas/liquidacionesobrajson.php",
            "data": { est:est,emp:emp,obr:obr,ini:ini,fin:fin},
            "type": "POST"
        },
        "columns": [		
              { "data" : "Previa", "className": "dt-center", "orderable":false, "searchable":false }, 
              { "data" : "Codigo", "className": "dt-left" },
              { "data" : "Obra", "className": "dt-left" }, 
              { "data" : "Cenco", "className": "dt-left" }, 
              { "data" : "HorasMaquina", "className": "dt-left" }, 
              { "data" : "HorasContrato", "className": "dt-center" },
              { "data" : "Extras", "className": "dt-left" },
              { "data" : "ValorHoraMaquina", "className": "dt-right", render: $.fn.dataTable.render.number( ',', '.', 0, '$' )},
              { "data" : "Total", "className": "dt-center" },
              { "data" : "Observacion", "className": "dt-center" },
              { "data" : "Liquidador", "className": "dt-left" }, 
              { "data" : "Estado", "className": "dt-left" }, 
                     
           ],
        "order": [[1, 'asc']], // ORDENAMIENTO POR FECHA INICIO Y FIN Y LUEGO POR EMPLEADO
        "rowCallback": function(row, data) {
            //if ($.inArray(data.DT_RowId, selected) !== -1) {
            // $(row).addClass('selected');
            // }            
            $('td', row).addClass('pt-0 pb-0 align-middle')           
        }
  
    });
  })
  