function updateDataTableSelectAllCtrl(table){
    var $table             = table.table().node();
    var $chkbox_all        = $('tbody .sele', $table);
    var $chkbox_checked    = $('tbody .sele:checked', $table);
    var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);
 
    // If none of the checkboxes are checked
    if($chkbox_checked.length === 0){
       chkbox_select_all.checked = false;
       if('indeterminate' in chkbox_select_all){
          chkbox_select_all.indeterminate = false;
       }
 
    // If all of the checkboxes are checked
    } else if ($chkbox_checked.length === $chkbox_all.length){
       chkbox_select_all.checked = true;
       if('indeterminate' in chkbox_select_all){
          chkbox_select_all.indeterminate = false;
       }
 
    // If some of the checkboxes are checked
    } else {
       chkbox_select_all.checked = true;
       if('indeterminate' in chkbox_select_all){
          chkbox_select_all.indeterminate = true;
       }
    }
 }

var selected = [];
var codselected = [];
$(document).ready(function(e) {
    
    var est = localStorage.getItem('lstipoconsulta');
    var emp = $('#busempleado').val();
    var obr = $('#busobra').val();
    var ano = $('#busano').val();
    var ini = $('#busperiodo>option:selected').attr('data-inicio');
    var fin = $('#busperiodo>option:selected').attr('data-fin');
    var table = $('#tbLiquidacionesObra').DataTable({
        "dom": '<"top">rt<"bottom"lp><"clear">',
        'columnDefs': [{
            'targets': 0,
            'searchable':false,
            'orderable':false,
            'width':'1%',
            'className': 'dt-body-center',
            'render': function (data, type, full, meta){
                return '<input type="checkbox" class="sele" value="2">';
                }
           },
           
         ],
         select: {
             style:    'multi',
             selector: 'td:first-child'
         },
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
            "url": "json/jornadas/liquidacionobrajson.php",
            "data": { est:est,emp:emp,obr:obr,ano:ano, ini: ini, fin: fin},
            "type": "POST"
        },
        "columns": [		
                {
                //"class":          "select-checkbox",
                "orderable":      false,
                "data":           null,
                "defaultContent": ""
                },
                { "data" : "Previa", "className": "dt-center", "orderable":false, "searchable":false },             
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
        "order": [[9, 'asc'],[10, 'asc'], [5, 'asc']], // ORDENAMIENTO POR FECHA INICIO Y FIN Y LUEGO POR EMPLEADO
        "rowCallback": function(row, data) {
            if ( $.inArray(data.DT_RowId, selected) !== -1 ) 
            {
                $(row).find('.sele').prop('checked', true);
                //$(row).addClass('selected');          
            }			           
            $('td', row).addClass('pt-0 pb-0 align-middle')
        }
  
    });
    $('#tbLiquidacionesObra tbody').on('click','.sele', function (e) {
		// console.log("Hola");
      var $row = $(this).closest('tr');
	  
	    id = $row.attr('id');

      // Get row data
      //var data = table.row($row).data();

      // Get row ID
      //var rowId = data[0];

      // Determine whether row ID is in the list of selected row IDs 
      var index = $.inArray(id, selected);

      // If checkbox is checked and row ID is not in list of selected row IDs
      if(this.checked && index === -1){
         selected.push(id);

      // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
      } else if (!this.checked && index !== -1){
         selected.splice(index, 1);
      }

      if(this.checked){
		  //console.log(this.value);
         $row.addClass('selected');
      } else {
		  
         $row.removeClass('selected');
      }

      // Update state of "Select all" control
      updateDataTableSelectAllCtrl(table);

      // Prevent click event from propagating to parent
      e.stopPropagation();
    } );
	
	 $('#tbLiquidacionesObra').on('click', 'tbody td, thead th:first-child', function(e){
      //$(this).parent().find('.sele').trigger('click');
   });
   
   $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
      if(this.checked){
         $('#tbLiquidacionesObra tbody .sele:not(:checked)').trigger('click');
      } else {
         $('#tbLiquidacionesObra tbody .sele:checked').trigger('click');
      }
      // Prevent click event from propagating to parent
      e.stopPropagation();
   });

})
  
function liquidarObra()
{
   var obr = $('#busobra').val();
   var ano = $('#busano').val();
   var ini = $('#busperiodo>option:selected').attr('data-inicio');
   var fin = $('#busperiodo>option:selected').attr('data-fin');
   var table = $('#tbLiquidacionesObra').DataTable();
   var selecte1 = $.map(selected,function(elemento,i) { return elemento.replace('rowl_',''); } );
   var col = table.rows('.selected').data().length;
   if(obr=="" || obr==null)
   {      
      error("Seleccionar la obra a la cual va a liquidar. Verificar");      
   }
   else
   if(ini=="" || ini==null ||  fin=="" || fin==null )
   {      
      error("Seleccionar periodo a liquidar. Verificar");      
   }
   else
   if(col<=0)
   {
      error("No ha seleccionado ningun empleado liquidar. Verificar");
   }
   else
   {
      var selecte = selecte1.join('-');
      $('#myModal').modal('show');
      $('#myModal>.modal-dialog').addClass('modal-xl');
      $('#btnguardar').attr('onclick',"liquidarSave()");
      $('#btnguardar').text('Guardar Liquidación').show();
      $('#titlemodal').html("Liquidación  de Obra");
      $('#btnexportar').hide();
      $('#contentmodal').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div> <div class="loader-section section-right"></div></div>');
      $.post('funciones/jornada/fnLiquidar.php',{ opcion:'LIQUIDAROBRA', liquidaciones:selecte, obr:obr, ini:ini, fin:fin },
      function(data)
      {
          $('#contentmodal').html(data);          
      });
   }
}


function liquidarSave()
{
   var obr = $('#busobra').val();  
   var ini = $('#busperiodo>option:selected').attr('data-inicio');
   var fin = $('#busperiodo>option:selected').attr('data-fin');
   var table = $('#tbLiquidacionesObra').DataTable();
   var selecte1 = $.map(selected,function(elemento,i) { return elemento.replace('rowl_',''); } );
   var col = table.rows('.selected').data().length;

   var horascontrato = $('#tbHorasObra').data('horas');
   var horasmaquina =  $('#tbHorasObra').data('maquina');
   var extras =  $('#tbHorasObra').data('extras');
   var vrhoramaquina = $('#tbHorasObra').data('valor-maquina');
   var vrhoraempleado = $('#tbHorasObra').data('valor-hora');
   var obs = $('#txtobservacionliquidacion').val().replace(new RegExp("\n", "g"), "<br>")

   if(obr=="" || obr==null)
   {      
      error("Seleccionar la obra a la cual va a liquidar. Verificar");      
   }
   else
   if(ini=="" || ini==null ||  fin=="" || fin==null )
   {      
      error("Seleccionar periodo a liquidar. Verificar");      
   }
   else if(col<=0)
   {
      error("No ha seleccionado ningun empleado liquidar. Verificar");
   }
   else
   {
      var selecte = selecte1.join('-');
      $.post('funciones/jornada/fnLiquidar.php',{ 
      opcion:'GUARDARLIQUIDAROBRA', 
      liquidaciones:selecte, 
      obr:obr, 
      ini:ini, 
      fin:fin,
      horascontrato: horascontrato,
      horasmaquina: horasmaquina,
      extras: extras,
      vrhoramaquina: vrhoramaquina,
      vrhoraempleado: vrhoraempleado, obs:obs
      },
      function(data)
      {
         var res = data[0].res;
         var msn = data[0].msn;
         if(res=="ok")
         {
            ok2(msn);
            var table = $('#tbLiquidacionesObra').DataTable();
            table.draw('full-hold');
            selected.length = 0;
            $('#myModal').modal('hide');
         }  
         else{
            error(msn);
         }

      }, "json");

   }
}