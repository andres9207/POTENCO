/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

   
    var emp =  $('#busempleado').val();  
    // var des = $('#busdesde').val();
    // var has = $('#bushasta').val();	

    var des =  $("#busperiodos option:selected").attr('data-inicio');
    var has =  $("#busperiodos option:selected").attr('data-fin');

    var table2 = $('#tbAlimentacion').DataTable( { 
        "columnDefs":[
            //{ "targets":[5], "visible": hideperfil},
            
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
        // "processing": true,
        // "serverSide": true,
        // "ajax": {
        //     "url": "json/informes/contabilidadjson.php",
        //     "data": { emp:emp, des: des, has:has },
        //     "type":"POST"
        // },
        
        // "order": [[0, 'asc']]
    } );
    var idtr = "";
    $('#tbAlimentacion tbody').on('click', 'tr', function () {
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

    var opca = "";
    var empa = "";
    var detailRows = [];
     // On each draw, loop over the `detailRows` array and show any child rows
     table2.on( 'draw', function () {
        $.each( detailRows, function ( i, id ) {
            $('#'+id+' td').trigger( 'click' );
        } );
    } );
    
});