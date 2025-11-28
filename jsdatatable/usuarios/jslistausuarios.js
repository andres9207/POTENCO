/* Formatting function for row details - modify as you need */

// JavaScript Document
$(function() {

    var selected = [];
    var nom = $('#busnombre').val();
    var ape = $('#busapellido').val();
    var ema = $('#buscorreo').val();
    var usu = $('#bususuario').val();
    var per = localStorage.getItem('lsperfil'); // $('#tbusuarios').attr('data-per')
    var ti = localStorage.getItem('lstipousuario');
    var hideperfil = true;
    var est = $('#busestado').val();
    
    var table2 = $('#tbusuarios').DataTable({
        "columnDefs": [
            { "targets": [1], "visible": false },            
            { "targets": [6], "visible": hideperfil },
        ],
        "dom": '<"top"i>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "pagingType": "simple_numbers",
        "lengthMenu": [
            [10, 15, 20, -1],
            [10, 15, 20, "Todos"]
        ],
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
            "url": "json/usuarios/usuariosjson.php",
            "data": { nom: nom, ape: ape, ema: ema, usu: usu, per: per, ti: ti, est: est || null },
            "type": "POST"
        },
        "columns": [
            { "data": "Edicion", "className": "dt-center", "orderable": false, "searchable": false },
            { "data": "Imagen", "className": "dt-left", "orderable": false, "searchable": false },         
            { "data": "Nombre", "className": "dt-left " }, //td_mayuscula
            { "data": "Apellido", "className": "dt-left " },
            { "data": "Usuario", "className": "dt-left" },
            { "data": "Correo", "className": "dt-left" },
            { "data": "Salario", "className": "dt-right" },
            { "data": "Perfil", "className": "dt-left " },
            { "data": "Estado", "className": "dt-center" },            
        ],
        "order": [
            [2, 'asc'],
            [3, 'asc']
        ]
    });

    $('#tbusuarios tbody').on('click', 'tr', function() {
        var id = this.id;
        var index = $.inArray(id, selected);

        if (index === -1) {
            selected.push(id);
        } else {
            selected.splice(index, 1);
        }
        $(this).toggleClass('selected');
    });

    var table2 = $('#tbusuarios').DataTable();
    // Apply the search
    table2.columns().every(function() {
        var that = this;

        $('input', this.footer()).on('keyup change', function() {
            that
                .search(this.value)
                .draw();
        });
    });
});