/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function (e) {

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
            easing: '' // Transition animation easing. Not supported without a jQuery easing plugin
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

    var emp = $('#busempleado').val();
    var table2 = $('#tbCompensatorios').DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},

        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "searching": false,
        "pagingType": "simple_numbers",
        "lengthMenu": [[10, 15, 20, -1], [10, 15, 20, "Todos"]],
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
            "url": "json/informes/compensatoriosjson.php",
            "data": { emp: emp },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Generados", "className": "text-center dt-Generados" },
            { "data": "Compensados", "className": "text-center dt-Compensados" },
            {
                "data": "Compensados", "className": "text-center dt-Compensados-mes", "render": function (data, type, row) {
                    return "<a class='btn btn-info btn-sm rounded-circle' title='Ver compensados por mes'><i class='fas fa-eye'></i></a>";
                }
            },
            {
                "data": "Compensados", "className": "text-center dt-Compensados-ano", "render": function (data, type, row) {
                    return "<a class='btn btn-info btn-sm rounded-circle' title='Ver compensados por ano'><i class='fas fa-eye'></i></a>";
                }
            },
            { "data": "Tomados", "className": "text-center dt-Tomados" },
            { "data": "Remunerados", "className": "text-center dt-Remunerados" },
        ],
        "order": [[0, 'asc']]
    });
    var idtr = "";
    $('#tbCompensatorios tbody').on('click', 'tr', function () {
        var id = $(this).attr('id');
        if ($(this).hasClass('selected') && id != idtr) {
            $(this).removeClass('selected');
        }
        else {
            table2.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            idtr = id;
        }
    });

    var opca = "";
    var empa = "";
    var detailRows = [];
    $('#tbCompensatorios tbody').on('click', 'tr td', function () {

        var tr = $(this).closest('tr');
        var row = table2.row(tr);
        var dat = row.data();
        var id = dat.Clave;
        console.log("Clic");
        $('#contentmodal').html("");

        var opc = $(this).hasClass('dt-Generados') ? 'Generados' :
        $(this).hasClass('dt-Tomados') ? "Tomados" :
            $(this).hasClass('dt-Remunerados') ? "Remunerados" :
                $(this).hasClass('dt-Compensados') ? "Compensados" :
                    $(this).hasClass('dt-Compensados-mes') ? "CompensadosMes" :
                    $(this).hasClass('dt-Compensados-ano') ? "CompensadosAno" :
                        "";

        if (opc != "" && opc!="CompensadosMes" && opc!="CompensadosAno") {
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-xl');
            $('#titlemodal').html("Detalle " + opc + " - " + dat.Nombre);
            $('#btnguardar').hide(); $('#btnexportar').hide();

            var table1 = "<div class='col-md-12'>" +
                "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalle_" + id + "_" + opc + "_1'>" +
                "<thead>" +
                "<tr>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Fecha</th>" +
                "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Mañana</th>" +
                "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Tarde</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horas Dia</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horario</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Observación</th>" +
                "</tr>" +
                "<tr>" +
                "<th class='dt-head-center align-middle'>Desde</th>" +
                "<th class='dt-head-center align-middle'>Hasta</th>" +
                "<th class='dt-head-center align-middle'>#</th>" +
                "<th class='dt-head-center align-middle'>Desde</th>" +
                "<th class='dt-head-center align-middle'>Hasta</th>" +
                "<th class='dt-head-center align-middle'>#</th>" +
                "</tr>" +
                "</thead>" +
                "<tfoot>" +
                "<tr>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "</tr>" +
                "</tfoot>" +
                "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleCompensatorio(id, opc, 1)
            }, 100);
        }
        else if (opc == "CompensadosMes") {
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-xl');
            $('#titlemodal').html("Detalle Compensados x Mes - " + dat.Nombre);
            $('#btnguardar').hide(); $('#btnexportar').hide();

            var table1 = "<div class='col-md-12'>" +
                "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalle_" + id + "_" + opc + "_1'>" +
                "<thead>" +
                "<tr>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Mes</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Generados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Compensados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Remunerados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Tomados</th>" +
                "</tr>" +
                "</thead>" +
                "<tfoot>" +
                "<tr>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +   
                "</tr>" +
                "</tfoot>" +
                "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleCompensatorioMes(id, opc,1)
            }, 100);
        }
        else if (opc == "CompensadosAno") {
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-xl');
            $('#titlemodal').html("Detalle Compensados x Año - " + dat.Nombre);
            $('#btnguardar').hide(); $('#btnexportar').hide();

            var table1 = "<div class='col-md-12'>" +
                "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalle_" + id + "_" + opc + "_1'>" +
                "<thead>" +
                "<tr>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Ano</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Generados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Compensados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Remunerados</th>" +
                    "<th class='dt-head-center bg-secondary align-middle'>Tomados</th>" +
                "</tr>" +
                "</thead>" +
                "<tfoot>" +
                "<tr>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +
                    "<th></th>" +   
                "</tr>" +
                "</tfoot>" +
                "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleCompensatorioAno(id, opc, 1)
            }, 100);
        }
    });

    // On each draw, loop over the `detailRows` array and show any child rows
    table2.on('draw', function () {
        $.each(detailRows, function (i, id) {
            $('#' + id + ' td').trigger('click');
        });
    });

    var detailRows2 = [];
    var table3 = $('#tbCompensatoriosObra').DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},

        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "searching": false,
        "pagingType": "simple_numbers",
        "lengthMenu": [[10, 15, 20, -1], [10, 15, 20, "Todos"]],
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
            "url": "json/informes/compensatoriosobrajson.php",
            "data": { emp: emp },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Generados", "className": "text-center dt-Generados" },
            { "data": "Compensados", "className": "text-center dt-Compensados" },
            { "data": "Tomados", "className": "text-center dt-Tomados" },
            { "data": "Remunerados", "className": "text-center dt-Remunerados" },
        ],
        "order": [[0, 'asc']]
    });
    var idtro = "";
    $('#tbCompensatoriosObra tbody').on('click', 'tr', function () {
        var id = $(this).attr('id');
        if ($(this).hasClass('selected') && id != idtro) {
            $(this).removeClass('selected');
        }
        else {
            table3.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            idtro = id;
        }
    });

    $('#tbCompensatoriosObra tbody').on('click', 'tr td', function () {

        var tr = $(this).closest('tr');
        var row = table3.row(tr);
        var dat = row.data();
        var id = dat.Clave;
        $('#contentmodal').html("");

        var opc = $(this).hasClass('dt-Generados') ? 'Generados' :
            $(this).hasClass('dt-Tomados') ? "Tomados" :
                $(this).hasClass('dt-Remunerados') ? "Remunerados" :
                    $(this).hasClass('dt-Compensados') ? "Compensados" :
                        $(this).hasClass('dt-Compensados-mes') ? "CompensadosMes" :
                            "";

        if (opc != "" && opc != "CompensadosMes") {
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-xl');
            $('#titlemodal').html("Detalle " + opc + " - " + dat.Nombre);
            $('#btnguardar').hide(); $('#btnexportar').hide();

            var table1 = "<div class='col-md-12'>" +
                "<table style='width:100%; font-size: 12px'  class='table table-bordered table-striped' id='tbDetalle_" + id + "_" + opc + "_2'>" +
                "<thead>" +
                "<tr>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Fecha</th>" +
                "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Mañana</th>" +
                "<th class='dt-head-center bg-secondary align-middle' colspan='3'>Tarde</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horas Día</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Horario</th>" +
                "<th class='dt-head-center bg-secondary align-middle' rowspan='2'>Observación</th>" +
                "</tr>" +
                "<tr>" +
                "<th class='dt-head-center align-middle'>Desde</th>" +
                "<th class='dt-head-center align-middle'>Hasta</th>" +
                "<th class='dt-head-center align-middle'>#</th>" +
                "<th class='dt-head-center align-middle'>Desde</th>" +
                "<th class='dt-head-center align-middle'>Hasta</th>" +
                "<th class='dt-head-center align-middle'>#</th>" +
                "</tr>" +
                "</thead>" +
                "<tfoot>" +
                "<tr>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "<th></th>" +
                "</tr>" +
                "</tfoot>" +
                "</table></div>";
            $('#contentmodal').html(table1);
            setTimeout(() => {
                detalleCompensatorio(id, opc, 2)
            }, 100);


        }
      
    });

});


function detalleCompensatorio(emp, opc, tip) {

    var table4 = $('#tbDetalle_' + emp + "_" + opc + "_" + tip).DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},            
        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": false,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "searching": false,
        "pagingType": "simple_numbers",
        "lengthMenu": [[10, 15, 20, -1], [10, 15, 20, "Todos"]],
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
            "url": "json/informes/detallecompensatoriojson.php",
            "data": { emp: emp, tip: tip, opc: opc },
            "type": "POST"
        },
        "columns": [

            { "data": "Fecha", "className": "text-center" },
            { "data": "InicioAm", "className": "text-center" },
            { "data": "FinAm", "className": "text-center" },
            { "data": "TotalAm", "className": "text-center" },
            { "data": "InicioPm", "className": "text-center" },
            { "data": "FinPm", "className": "text-center" },
            { "data": "TotalPm", "className": "text-center" },
            { "data": "HorasDia", "className": "text-center" },
            { "data": "Horario", "className": "text-center" },
            { "data": "Observacion", "className": "text-left" }
        ],
        "order": [[0, 'asc']]
    });
}

function detalleCompensatorioMes(emp, opc,tip) {

    var table5 = $('#tbDetalle_' + emp + "_" + opc + "_" + tip).DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},            
        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": false,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "searching": false,
        "pagingType": "simple_numbers",
        "lengthMenu": [[10, 15, 20, -1], [10, 15, 20, "Todos"]],
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
            "url": "json/informes/detallecompensatoriomesjson.php",
            "data": { emp: emp, tip: tip, opc: opc },
            "type": "POST"
        },
        "columns": [

            { "data": "Mes", "className": "text-center" },
            { "data": "Generados", "className": "text-center" ,"render": function (data, type, row) { 
                console.log(data)
                return data>=3?data: 0 
            }} ,
            { "data": "Compensados", "className": "text-center" },
            { "data": "Remunerados", "className": "text-center" },
            { "data": "Tomados", "className": "text-center" },
        ],
        "order": [[0, 'asc']]
    });
}

function detalleCompensatorioAno(emp, opc,tip) {

    var table5 = $('#tbDetalle_' + emp + "_" + opc + "_" + tip).DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},            
        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": false,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "searching": false,
        "pagingType": "simple_numbers",
        "lengthMenu": [[10, 15, 20, -1], [10, 15, 20, "Todos"]],
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
            "url": "json/informes/detallecompensatorioanojson.php",
            "data": { emp: emp, tip: tip, opc: opc },
            "type": "POST"
        },
        "columns": [

            { "data": "Ano", "className": "text-center" },
            { "data": "Generados", "className": "text-center" },
            { "data": "Compensados", "className": "text-center" },
            { "data": "Remunerados", "className": "text-center" },
            { "data": "Tomados", "className": "text-center" },
        ],
        "order": [[0, 'asc']]
    });
}
