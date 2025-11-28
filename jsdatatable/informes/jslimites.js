/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function(e) {

    $('#smartwizard').smartWizard({
        selected: 0, // Initial selected step, 0 = first step
        theme: 'arrows', // theme for the wizard, related css need to include for other than default theme
        justified: false, // Nav menu justification. true/false
        darkMode: false, // Enable/disable Dark Mode if the theme supports. true/false
        autoAdjustHeight: false, // Automatically adjust content height
        cycleSteps: false, // Allows to cycle the navigation of steps
        backButtonSupport: true, // Enable the back button support
        enableURLhash: true, // Enable selection of the step based on url hash
        transition: {
            animation: 'none', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
            speed: '400', // Transion animation speed
            easing:'' // Transition animation easing. Not supported without a jQuery easing plugin
        },
        toolbarSettings: {
            toolbarPosition: 'top', // none, top, bottom, both
            toolbarButtonPosition: 'right', // left, right, center
            showNextButton: false, // show/hide a Next button
            showPreviousButton: false, // show/hide a Previous button
            toolbarExtraButtons: [] // Extra buttons to show on toolbar, array of jQuery input/buttons elements
        },
        anchorSettings: {
            anchorClickable: true, // Enable/Disable anchor navigation
            enableAllAnchors: true, // Activates all anchors clickable all times
            markDoneStep: true, // Add done state on navigation
            markAllPreviousStepsAsDone: false, // When a step selected by url hash, all previous steps are marked done
            removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
            enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
        },
        keyboardSettings: {
            keyNavigation: false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
            keyLeft: [37], // Left key code
            keyRight: [39] // Right key code
        },
        lang: { // Language variables for button
            next: 'Siguiente',
            previous: 'Anterior'
        },
        disabledSteps: [], // Array Steps disabled
        errorSteps: [], // Highlight step with errors
        hiddenSteps: [] // Hidden steps
    });

    var emp = $('#selempleado').val(); 
    var ano = $('#selano').val();
    var sem = $('#selsemana').val();

    var table2 = $('#tbLimites').DataTable( { 
        "columnDefs":[
            //{ "targets":[5], "visible": hideperfil},
            
        ],      
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
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
            "url": "json/informes/limitesjson.php",
            "data": { emp:emp, ano: ano, sem: sem, tip: 1 },
            "type":"POST"
        },
        "columns": [					
            
            { "data" : "Ano",      "className" : "dt-left"     },
            { "data" : "Semana",   "className" : "text-center" },
            { "data" : "Desde",   "className" : "text-center" },
            { "data" : "Hasta",   "className" : "text-center" },
            { "data" : "Nombre", "className" : "text-left" },			
            { "data" : "Extras",   "className" : "text-center" }, 
        ],
        "order": [[0, 'asc']]
    } );
    var idtr = "";
    $('#tbLimites tbody').on('click', 'tr', function () {
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
    $('#tbLimites tbody').on('click', 'tr td', function () {      
        
		var tr = $(this).closest('tr');
        var row = table2.row( tr );
		var dat = row.data();
		var id = dat.Clave;		
        var sem = dat.Semana;
        console.log("Clic");
        $('#contentmodal').html("");
       
        $('#myModal').modal('show');
        $('#myModal>.modal-dialog').addClass('modal-xl');
        $('#titlemodal').html("Detalle Semana " + sem + " - " +   dat.Nombre);
        $('#btnguardar').hide();  $('#btnexportar').hide();
        
        var table1 = "<div class='col-md-12'>"+
            "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalleLimite_"+id+"_"+sem+"_3'>"+
            "<thead>"+
                "<tr>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Fecha</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Mañana</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Tarde</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horas Dia</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horario</th>"+					
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Observación</th>"+
                "</tr>"+
                "<tr>"+
                    "<th class='dt-head-center align-middle'>Desde</th>"+
                    "<th class='dt-head-center align-middle'>Hasta</th>"+
                    "<th class='dt-head-center align-middle'>#</th>"+
                    "<th class='dt-head-center align-middle'>Desde</th>"+
                    "<th class='dt-head-center align-middle'>Hasta</th>"+
                    "<th class='dt-head-center align-middle'>#</th>"+
                "</tr>"+
            "</thead>"+
            "<tfoot>"+
                "<tr>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                "</tr>"+
            "</tfoot>"+
            "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleLimite(id, sem, 3)
            }, 100);


        
	} );   

     // On each draw, loop over the `detailRows` array and show any child rows
     table2.on( 'draw', function () {
        $.each( detailRows, function ( i, id ) {
            $('#'+id+' td').trigger( 'click' );
        } );
    } );
    
    var detailRows2 = [];
    var table3 = $('#tbLimitesObra').DataTable( { 
        "columnDefs":[
            //{ "targets":[5], "visible": hideperfil},
            
        ],      
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
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
            "url": "json/informes/limitesjson.php",
            "data": { emp:emp, ano: ano, sem:sem, tip: 2 },
            "type":"POST"
        },
        "columns": [					
            
            { "data" : "Ano",      "className" : "dt-left"     },
            { "data" : "Semana",   "className" : "text-center" },
            { "data" : "Desde",   "className" : "text-center" },
            { "data" : "Hasta",   "className" : "text-center" },
            { "data" : "Nombre", "className" : "text-left" },			
            { "data" : "Extras",   "className" : "text-center" }, 
        ],
        "order": [[0, 'asc']]
    } );
    var idtro = "";
    $('#tbLimitesObra tbody').on('click', 'tr', function () {
        var id = $(this).attr('id');
        if ( $(this).hasClass('selected') && id!=idtro ) {
            $(this).removeClass('selected');
        }
        else {
            table3.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            idtro = id;
        }
    } );   
    
    $('#tbLimitesObra tbody').on('click', 'tr td', function () {      
        
		var tr = $(this).closest('tr');
        var row = table3.row( tr );
		var dat = row.data();
		var id = dat.Clave;	
        var sem = dat.Semana;	
        $('#contentmodal').html("");        
        
        $('#myModal').modal('show');
        $('#myModal>.modal-dialog').addClass('modal-xl');
        $('#titlemodal').html("Detalle Semana " + sem + " - " +   dat.Nombre);
        $('#btnguardar').hide();  $('#btnexportar').hide();
        
        var table1 = "<div class='col-md-12'>"+
            "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalleLimite_"+id+"_"+sem+"_4'>"+
            "<thead>"+
                "<tr>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Fecha</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Mañana</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Tarde</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horas Dia</th>"+
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horario</th>"+					
                    "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Observación</th>"+
                "</tr>"+
                "<tr>"+
                    "<th class='dt-head-center align-middle'>Desde</th>"+
                    "<th class='dt-head-center align-middle'>Hasta</th>"+
                    "<th class='dt-head-center align-middle'>#</th>"+
                    "<th class='dt-head-center align-middle'>Desde</th>"+
                    "<th class='dt-head-center align-middle'>Hasta</th>"+
                    "<th class='dt-head-center align-middle'>#</th>"+
                "</tr>"+
            "</thead>"+
            "<tfoot>"+
                "<tr>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                    "<th></th>"+
                "</tr>"+
            "</tfoot>"+
            "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleLimite(id, sem, 4)
            }, 100);            
	} ); 
    
});


function detalleLimite(emp,sem,tip)
{
    
   var table4 = $('#tbDetalleLimite_' + emp + "_" + sem + "_" + tip).DataTable( { 
        "columnDefs":[
            //{ "targets":[5], "visible": hideperfil},            
        ],      
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": false,
        "info": false,
        "autoWidth": true,
        "responsive": true,
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
            "url": "json/informes/detallecompensatoriojson.php",
            "data": { emp:emp, tip: tip, sem: sem },
            "type":"POST"
        },
        "columns": [					
            
            { "data" : "Fecha",       "className" : "text-center"  },
            { "data" : "InicioAm",    "className" : "text-center"  },
            { "data" : "FinAm",       "className" : "text-center"  },
            { "data" : "TotalAm",     "className" : "text-center"  },
            { "data" : "InicioPm",    "className" : "text-center"  },
            { "data" : "FinPm",       "className" : "text-center"  },
            { "data" : "TotalPm",     "className" : "text-center"  },            
            { "data" : "HorasDia",    "className" : "text-center" } ,
            { "data" : "Horario",     "className" : "text-center" } ,  
            { "data" : "Observacion", "className" : "text-left"  }       
        ],
        "order": [[0, 'asc']]
    } );
}
