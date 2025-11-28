/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

  var step = localStorage.getItem('step'); 
  var visr = step==0?false:true
  var emp = $('#busempleado').val();  
  var des = $('#busdesde').val();
  var has = $('#bushasta').val();		
  var table2 = $('#tbHoras_' + step).DataTable( { 
      "columnDefs":[
          { "targets":[3], "visible": visr },
          
      ],      
      "dom": '<"top"fi>rt<"bottom"lp><"clear">',
      "ordering": true,
      "info": true,
      "autoWidth": true,
      // "responsive": true,
      "searching": false,
      "pagingType": "simple_numbers",
      "lengthMenu": [[10,15,20,-1], [10,15,20,"Todos"]],
      "language": {
          "lengthMenu": "Ver _MENU_ registros",
          "zeroRecords": "No se encontraron datos",
          "info": "Resultado _START_ - _END_ de _TOTAL_ registros",
          "infoEmpty": "No se encontraron datos",
          "infoFiltered": "",
          "paginate": {"previous": "Anterior","next":"siguiente"},
          "search":"",
          "sSearchPlaceholder":"Busqueda"
      },	
      "processing": true,
      "serverSide": true,
      "ajax": {
          "url": "json/informes/horasjson.php",
          "data": { emp:emp, des: des, has:has, opc: step },
          "type":"POST"
      },
      "columns": [					
          
          { "data" : "Nombre",     "className" : "dt-left "  },
          { "data" : "Cedula",     "className" : "text-center" },
          { "data" : "Agrupacion", "className" : "text-center" },
          { "data" : "Rango", "className" : "text-center" },
          { "data" : "HorasLaboradas", "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' )  },	
          { "data" : "hedo",       "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "are",        "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) },
          { "data" : "rn",         "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "hnf",        "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "heno",       "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "henf",       "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "hedf",       "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "rd",         "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) }, 
          { "data" : "hdf",        "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' ) },
          { "data" : "totalhoras",      "className" : "text-center", render: $.fn.dataTable.render.number( ',', '.', 1, '' )  },             
      ],
      "order": [[0, 'asc']],
      "rowCallback": function( row, data ) {
        $('td', row).addClass(data.Color);
      }
  } );
  var idtr = "";
  $('#tbHoras_' + step + ' tbody').on('click', 'tr', function () {
      var id = $(this).attr('id');
      if ( $(this).hasClass('selected') && id!=idtr ) {
          $(this).removeClass('selected');
      }
      else {
          table2.$('tr.selected').removeClass('selected');
          $(this).addClass('selected');
          idtr = id;
      }
  } );
  var detailRows = [];
   // On each draw, loop over the `detailRows` array and show any child rows
   table2.on( 'draw', function () {
      $.each( detailRows, function ( i, id ) {
          $('#'+id+' td').trigger( 'click' );
      } );
  } );
  
});