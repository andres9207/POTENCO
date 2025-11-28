/* Formatting function for row details - modify as you need */

// JavaScript Document
$(document).ready(function (e) {


    var emp = $('#busempleado').val();
    var fre = $('#busfrecuencia').val();
    // var des = $('#busdesde').val();
    // var has = $('#bushasta').val();		

    var des = fre == 2 ? $("#busperiodos option:selected").attr('data-inicio') : $('#busdesde').val();
    var has = fre == 2 ? $("#busperiodos option:selected").attr('data-fin') : $('#bushasta').val();

    var table2 = $('#tbContabilidad').DataTable({
        "columnDefs": [
            //{ "targets":[5], "visible": hideperfil},

        ],
        "dom": '<"top"fi>rt<"bottom"lp><"clear">',
        "ordering": true,
        "info": true,
        "autoWidth": true,
        // "responsive": true,
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
            "url": "json/informes/contabilidadjson.php",
            "data": { emp: emp, des: des, has: has },
            "type": "POST"
        },
        "columns": [

            { "data": "Nombre", "className": "dt-left " },
            { "data": "Cedula", "className": "text-center" },
            { "data": "Basico", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },

            { "data": "vhedo", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vhedf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vhdf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vheno", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vhenf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vrn", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vrd", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vhnf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            { "data": "vare", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },

            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    console.log({ hedo, heno, henf, hedf, hdf })
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horasextras = horasextrasgeneradas - horasajustar
                    console.log("horasextras: ", horasextras);
                    return formato_numero(horasextras, 2);
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    console.log("horasextrasgeneradas: ", horasextrasgeneradas);
                    return formato_numero(horasextrasgeneradas, 2);
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    console.log("horasajustar: ", horasajustar);
                    return formato_numero(horasextrasgeneradas>24? horasajustar: 0, 2);
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horasajustadas = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? horasajustar : Number(hedo) - horasajustar) : Number(hdf) - horasajustar;
                    console.log("horasajustadas: ", horasajustadas);
                    return formato_numero(horasextrasgeneradas>24? horasajustadas: 0, 2);
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horasbonificacionfestivas = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? horasajustar - Number(hedo) : 0)
                        :
                        (horasajustar > Number(hdf) ? Number(hdf) : horasajustar)
                    console.log("horasbonificacionfestivas: ", horasbonificacionfestivas);
                    return formato_numero(horasextrasgeneradas>24? horasbonificacionfestivas: 0, 2);
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horasbonificacionordinarias = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? Number(hedo) : horasajustar) :
                        (horasajustar > Number(hdf) ? Number(hedo) - (horasajustar - Number(hdf)) : 0)
                    console.log("horasbonificacionordinarias: ", horasbonificacionordinarias);
                    return formato_numero(horasextrasgeneradas>24? horasbonificacionordinarias: 0, 2);
                }
            },


            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horashedo = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? 0 : Number(hedo) - horasajustar) :
                        Number(hedo)
                    console.log("horashedo: ", horashedo);
                    return formato_numero(horasextrasgeneradas>24?horashedo: hedo, 2);
                }
            },
            { "data": "are", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "rn", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "hnf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "heno", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "henf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "hedf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            { "data": "rd", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            // { "data": "hdf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 1, '') },
            {
                "data": "hdf", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var difhedo = horasajustar - Number(hedo);
                    var horashdf = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? Number(hdf) - difhedo : 0) :
                        (horasajustar > Number(hdf) ? 0 : Number(hdf) - horasajustar)
                    console.log("horashdf: ", horashdf);
                    return formato_numero(horasextrasgeneradas>24?horashdf: hdf, 2);
                }
            },


            // { "data": "thedo", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            {
                "data": "hedo", "className": "text-center", render: function (data, type, row) {
                    const { hedo, hedf, hdf, heno, henf, vhedo } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horashedo = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? 0 : Number(hedo) - horasajustar) :
                        Number(hedo)
                    return formatCurrency(Number(horasextrasgeneradas>24?horashedo: hedo) * Number(vhedo), "$")
                }
            },
            // { "data": "trn", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "thnf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "thedf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "thdf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "theno", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "trd", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "thenf", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            // { "data": "tare", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },

            {
                "data": "rn", "className": "text-center", render: function (data, type, row) {
                    const { rn, vrn } = row
                    return formatCurrency(Number(rn) * Number(vrn), "$")
                }
            },
            {
                "data": "hnf", "className": "text-center", render: function (data, type, row) {
                    const { hnf, vhnf } = row
                    return formatCurrency(Number(hnf) * Number(vhnf), "$")
                }
            },
            {
                "data": "hedf", "className": "text-center", render: function (data, type, row) {
                    const { hedf, vhedf } = row
                    return formatCurrency(Number(hedf) * Number(vhedf), "$")
                }
            },
            {
                "data": "hdf", "className": "text-center", render: function (data, type, row) { //
                    const { hedo, heno, henf, hedf, hdf, vhdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var difhedo = horasajustar - Number(hedo);
                    var horashdf = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? Number(hdf) - difhedo : 0) :
                        (horasajustar > Number(hdf) ? 0 : Number(hdf) - horasajustar)
                    return formatCurrency(Number(horasextrasgeneradas>24?horashdf: hdf) * Number(vhdf), "$")
                }
            },
            {
                "data": "heno", "className": "text-center", render: function (data, type, row) {
                    const { heno, vheno } = row
                    return formatCurrency(Number(heno) * Number(vheno), "$")
                }
            },
            {
                "data": "rd", "className": "text-center", render: function (data, type, row) {
                    const { rd, vrd } = row
                    return formatCurrency(Number(rd) * Number(vrd), "$")
                }
            },
            {
                "data": "henf", "className": "text-center", render: function (data, type, row) {
                    const { henf, vhenf } = row
                    return formatCurrency(Number(henf) * Number(vhenf), "$")
                }
            },
            {
                "data": "are", "className": "text-center", render: function (data, type, row) {
                    const { are, vare } = row
                    return formatCurrency(Number(are) * Number(vare), "$")
                }
            },

            // { "data": "total", "className": "text-center", render: $.fn.dataTable.render.number(',', '.', 0, '$') },
            {
                "data": "hedo", "className": "text-center", render: function (data, type, row) {
                    const { hedo, hedf, hdf, heno, henf, rn, rd, hnf, are, vhedo, vhedf, vhdf, vheno, vhenf, vrn, vrd, vhnf, vare } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    var horasajustar = horasextrasgeneradas - 24;
                    var horashedo = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? 0 : Number(hedo) - horasajustar) :
                        Number(hedo)

                    var difhedo = horasajustar - Number(hedo);
                    var horashdf = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                        (horasajustar > Number(hedo) ? Number(hdf) - difhedo : 0) :
                        (horasajustar > Number(hdf) ? 0 : Number(hdf) - horasajustar)

                    var total = Number(horasextrasgeneradas > 24 ? horashedo : hedo) * Number(vhedo)
                    total += Number(hedf) * Number(vhedf);
                    total += Number(horasextrasgeneradas > 24 ? horashdf : hdf) * Number(vhdf);
                    total += Number(heno) * Number(vheno);
                    total += Number(rn) * Number(vrn);
                    total += Number(rd) * Number(vrd);
                    total += Number(hnf) * Number(vhnf);
                    total += Number(henf) * Number(vhenf);
                    total += Number(are) * Number(vare);
                    return formatCurrency(total, "$");
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf, vhedo } = row
                    if (horasextrasgeneradas > 24) {
                        var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                        var horasajustar = horasextrasgeneradas - 24;
                        var horasbonificacionordinarias = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                            (horasajustar > Number(hedo) ? Number(hedo) : horasajustar) :
                            (horasajustar > Number(hdf) ? Number(hedo) - (horasajustar - Number(hdf)) : 0)
                        return formatCurrency(horasbonificacionordinarias * Number(vhedo), "$");
                    }
                    else {

                        return formatCurrency(0, "$");
                    }
                }
            },
            {
                "data": "hdf", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf, vhdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    if (horasextrasgeneradas > 24) {
                        var horasajustar = horasextrasgeneradas - 24;
                        var horasbonificacionfestivas = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                            (horasajustar > Number(hedo) ? horasajustar - Number(hedo) : 0)
                            :
                            (horasajustar > Number(hdf) ? Number(hdf) : horasajustar)
                        return formatCurrency(horasbonificacionfestivas * Number(vhdf), "$");
                    }
                    else {

                        return formatCurrency(0, "$");
                    }
                }
            },
            {
                "data": "hedo", "className": "text-center", "render": function (data, type, row) {
                    const { hedo, heno, henf, hedf, hdf, vhedo, vhdf } = row
                    var horasextrasgeneradas = Number(hedo) + Number(heno) + Number(henf) + Number(hedf) + Number(hdf)
                    if (horasextrasgeneradas > 24) {
                        var horasajustar = horasextrasgeneradas - 24;
                        var horasbonificacionordinarias = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                            (horasajustar > Number(hedo) ? Number(hedo) : horasajustar) :
                            (horasajustar > Number(hdf) ? Number(hedo) - (horasajustar - Number(hdf)) : 0)

                        var horasbonificacionfestivas = Number(hedo) > Number(heno) && Number(hedo) > Number(henf) && Number(hedo) > Number(hedf) && Number(hedo) > Number(hdf) ?
                            (horasajustar > Number(hedo) ? horasajustar - Number(hedo) : 0)
                            :
                            (horasajustar > Number(hdf) ? Number(hdf) : horasajustar)
                        var totalbonificacion = (horasbonificacionordinarias * Number(vhedo)) + (horasbonificacionfestivas * Number(vhdf));
                        return formatCurrency(totalbonificacion, "$");
                    }
                    else {

                        return formatCurrency(0, "$");
                    }
                }
            },
        ],
        "order": [[0, 'asc']]
    });
    var idtr = "";
    $('#tbContabilidad tbody').on('click', 'tr', function () {
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
    // On each draw, loop over the `detailRows` array and show any child rows
    table2.on('draw', function () {
        $.each(detailRows, function (i, id) {
            $('#' + id + ' td').trigger('click');
        });
    });

});