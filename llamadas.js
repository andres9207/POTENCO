jQuery(document).ready(function ($) {
    var $input = $('#txtbusqueda2');
    $input.typeahead({
        source: function (query, process) {
            return $.post('search_radicado.php', { query: query }, function (data) {
                //return process(objects);
                data = $.parseJSON(data);
                //console.log(data);
                //return data;
                return process(data);
            });
        },
        autoSelect: false,

        highlighter: function (item, ele) {
            //console.log(item);
            //console.log("Name: " + ele.name);
            //var parts = item.split('#'),
            html = '<div><div class="typeahead-inner" id="' + ele.id + '">';
            //html += '<div class="item-img" style="background-image: url(' + ele.img + ')"></div>';

            html += '<div class="item-body">';
            html += '<p class="item-heading">' + ele.radicado + '</p>';
            html += '</div>';
            html += '<div class="item-precio">';
            html += '<p class="item-heading">' + ele.tipo + '</p>';
            html += '</div>';
            html += '</div></div>';

            var query = this.query;
            var reEscQuery = query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            var reQuery = new RegExp('(' + reEscQuery + ')', "gi");
            var jElem = $(html);
            var textNodes = $(jElem.find('*')).add(jElem).contents().filter(function () {
                return this.nodeType === 3;
            });
            textNodes.replaceWith(function () {
                return $(this).text().replace(reQuery, '<strong>$1</strong>');
            });

            return jElem.html();
        },
        updater: function (selectedName) {
            console.log("opcion" + selectedName.id);
            var name = selectedName.radicado;
            var id = selectedName.id;
            $(document).prop('title', "RADICADO - " + name);
            var mod = selectedName.mod;

            if (id > 0) {
                window.location.href = "#/Documentos/Preview?ID=" + id;
            } else {
                window.location.href = "#/Documentos/Preview?ID=" + id;
            }
            //CRUDCATALOGO('LISTAPRODUCTOS','');
            return name;
        }
    });
    $input.on('keydown', function (e) {
        if (e.which == 13) { }
    })
});

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

function ok(msn) {
    Toast.fire({
        type: 'success',
        title: msn
    })
}

function ok2(msn) {
    swal({
        position: 'center',
        type: 'success',
        title: msn,
        showConfirmButton: true,
        confirmButtonText: "Aceptar"
    })
}

function error(msn) {
    Toast.fire({
        type: 'error',
        title: msn
    })
}

function error2(msn) {
    swal({
        position: 'center',
        type: 'error',
        title: msn,
        text: "Cualquier inquietud comunicate a ### ## ## / ### ### ####",
        showConfirmButton: true,
        confirmButtonText: "Aceptar"
        //timer: 1500
    })
}

function validar_texto(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    //Tecla de retroceso para borrar, siempre la permite
    if (tecla == 8) {
        return true;
    }
    // Patron de entrada, en este caso solo acepta numeros
    patron = /[0-9\.]/g, "0";
    tecla_final = String.fromCharCode(tecla);
    return patron.test(tecla_final);
}

String.prototype.trim = function () {
    return this.replace(/^\s+/, '').replace(/\s+$/, '');
};
String.prototype.capitalizeParagraph = function () {
    var res = "";
    //var paragraphs = this.split(".")
    res = this.toUpperCase();
    /*for(var i = 0; i < paragraphs.length ; i++) {
        var temp = paragraphs[i];
        res += "." + temp.charAt(0).toUpperCase() + temp.slice(1);
    }*/
    return res; //.slice(1);
};

function setpreview(rut, inp, formu, tmp = 'tmp', opc = "", id = "") // creamos la función
{
    var datos = new FormData();

    datos.append('ruta', rut);
    datos.append('input', $('#' + inp)[0].files[0]);
    datos.append('formu', formu);
    datos.append('tmp', tmp);

    /*$("#loadMe").modal({
    backdrop: "static", //remove ability to close modal with click
    keyboard: false, //remove option to close with keyboard
    show: true //Display loader!
    });
    $('#msnload').text("Cargando archivo por favor espera");*/

    //info("Cargando archivo por favor espera")
    $.ajax({
        url: "upload.php",
        type: "POST",
        data: datos,
        async: false,
        contentType: false,
        cache: false,
        processData: false,
        dataType: "json",
        crossDomain: true,
    }).
        done(function (data) {
            console.log(data);
            var res = data.res;
            var url = data.url;
            var msn = data.msn;
            var name = data.name;
            console.log(msn);

            if ($.trim(res) == "error") {
                error("No se cargo el archivo");
                ///$("#loadMe").modal("hide");
            } else if (jQuery.trim(res) == "ok") {
                $('#' + rut).html(name).attr('data-name', name).attr('data-rut', url);

                if (opc == 1) //cargar documento
                {
                    CRUDCORRESPONDENCIA('AGREGARDOCUMENTO', id)
                }
                //ok("Archivo cargado");
                //$("#loadMe").modal("hide");
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            if (console && console.log) {
                console.log("La solicitud a fallado: " + textStatus);
                result = true;
                //$("#loadMe").modal("hide");
            }
        });
}

function INICIALIZARHOME() {
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d')

    var salesChartData = {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [{
            label: 'Digital Goods',
            backgroundColor: 'rgba(60,141,188,0.9)',
            borderColor: 'rgba(60,141,188,0.8)',
            pointRadius: false,
            pointColor: '#3b8bba',
            pointStrokeColor: 'rgba(60,141,188,1)',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            data: [28, 48, 40, 19, 86, 27, 90]
        },
        {
            label: 'Electronics',
            backgroundColor: 'rgba(210, 214, 222, 1)',
            borderColor: 'rgba(210, 214, 222, 1)',
            pointRadius: false,
            pointColor: 'rgba(210, 214, 222, 1)',
            pointStrokeColor: '#c1c7d1',
            pointHighlightFill: '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            data: [65, 59, 80, 81, 56, 55, 40]
        },
        ]
    }

    var salesChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                }
            }],
            yAxes: [{
                gridLines: {
                    display: false,
                }
            }]
        }
    }

    // This will get the first returned node in the jQuery collection.
    var salesChart = new Chart(salesChartCanvas, {
        type: 'line',
        data: salesChartData,
        options: salesChartOptions
    })

    //---------------------------
    //- END MONTHLY SALES CHART -
    //---------------------------

    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d')
    var pieData = {
        labels: [
            'Chrome',
            'IE',
            'FireFox',
            'Safari',
            'Opera',
            'Navigator',
        ],
        datasets: [{
            data: [700, 500, 400, 600, 300, 100],
            backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
        }]
    }
    var pieOptions = {
        legend: {
            display: false
        }
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    var pieChart = new Chart(pieChartCanvas, {
        type: 'doughnut',
        data: pieData,
        options: pieOptions
    })

    //-----------------
    //- END PIE CHART -
    //-----------------

    /* jVector Maps
     * ------------
     * Create a world map with markers
     */
    $('#world-map-markers').mapael({
        map: {
            name: "usa_states",
            zoom: {
                enabled: true,
                maxLevel: 10
            },
        },
    });
}

function INICIALIZARCONTENIDO() {
    $(".dropify").dropify({
        messages: {
            'default': 'Arrastre imagen o haga click aqui',
            'replace': 'Arrastre y suelte o haga clic para reemplazar',
            'remove': 'Eliminar',
            'error': 'Ooops, algo pasó mal'
        }
    });
    $('a.fullsizable').fullsizable();
    autosize($('textarea'));
    $('[data-toggle="tooltip"]').tooltip()

    $('.timepicker1').timepicker({
        //timeFormat: 'h:mm p',
        dropdown: true,
        minuteStep: 5,
        dropdown: true,
        scrollbar: true,
        template: 'modal',
        modalBackdrop: true,
    });
    //lista con filtros
    $('.selectpicker').selectpicker({
        liveSearch: true,
        hideDisabled: true

    }).on('change', function () {
        //$(this).selectpicker('toggle');	
        ////console.log("Por aca");	
    });
    $('.currencyinf').formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 2, colorize: true });
    $('.currencyex').formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 2 });
    $('.currency4').formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 0 });
    $('.currency').formatCurrency({ symbol: '$', eventOnDecimalsEntered: true, roundToDecimalPlace: 0 });
    $('.currency3').formatCurrency({ symbol: '%', eventOnDecimalsEntered: true, roundToDecimalPlace: 1 });
    $('.currency2').formatCurrency({ symbol: '$', eventOnDecimalsEntered: true, roundToDecimalPlace: 0 });

    $('.currency2').blur(function () {
        //$('.currency').html(null);
        $(this).formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 0 });
    })
        .focus(function () {
            $(this).formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 0, digitGroupSymbol: '', });
        })
        .bind('decimalsEntered', function (e, cents) {
            var errorMsg = 'Please do not enter any cents (0.' + cents + ')';
            //console.log('Event on decimals entered: ' + errorMsg);
        });

    $('.currency').blur(function () {
        //$('.currency').html(null);
        $(this).formatCurrency({ symbol: '$', eventOnDecimalsEntered: true, roundToDecimalPlace: 0 });
    })
        .focus(function () {
            $(this).formatCurrency({ symbol: '', eventOnDecimalsEntered: true, roundToDecimalPlace: 0, digitGroupSymbol: '', });
        })
        .bind('decimalsEntered', function (e, cents) {
            var errorMsg = 'Please do not enter any cents (0.' + cents + ')';
            //console.log('Event on decimals entered: ' + errorMsg);
        });
}

function CRUDHOME(o, id) {
    if (o == "CARGARHOME") {
        $.post('funciones/home/fnHome.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#divhome').html(data);
        })
    }
}

function CRUDPERFIL(o, id, idu) {
    //$('#myModal>.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
        $('#titlemodal').html("Nuevo Perfil");
        $('#btnguardar').attr('onclick', "CRUDPERFIL('GUARDAR','" + id + "')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/perfiles/fnPerfiles.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
        $('#titlemodal').html("Edición Perfil");
        $('#btnguardar').attr('onclick', "CRUDPERFIL('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/perfiles/fnPerfiles.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmperfil').parsley().validate();
        if ($('#frmperfil').parsley().isValid()) {
            var txtnombre = $('#txtnombre').val();
            var ventanas = $('#selventana').val();
            var est = $('input:radio[name=radestado]:checked').val();

            $.post('funciones/perfiles/fnPerfiles.php', {
                opcion: o,
                id: id,
                nombre: txtnombre,
                est: est,
                ventanas: ventanas
            },
                function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        $('#myModal').modal('hide');
                        var table = $('#tbperfiles').DataTable();
                        if (o == "GUARDAR") {
                            table.draw('full-hold');
                        } else {
                            table.row('#row_' + id).draw(false);
                        }
                        ok(msn);
                    } else {
                        error(msn);
                    }
                }, "json");

        }
    }
    else if (o == "FILTROS") {
        $.post('funciones/perfiles/fnPerfiles.php', { opcion: o }, function (data) {
            $('#divfiltros').html(data);
        })
    }
    else if (o == "LISTAPERFILES") {
        $('#tabladatos').html("");
        $.post('funciones/perfiles/fnPerfiles.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "ELIMINAR") {


        swal({
            title: "Realmente desea eliminar el perfil seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {

                $.post('funciones/perfiles/fnPerfiles.php', {
                    opcion: o,
                    id: id

                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbperfiles').DataTable();
                            table.row('#row_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    } else if (o == "ASIGNARPERMISOS") {
        //$('#btnguardar').attr('onclick',"CRUDUSUARIOS('GUARDAREDICION','"+id+"','')").show();

        $('#titlemodal').html("Permisos Usuario");
        $('#contentmodal').html('');
        $('#myModal>.modal-dialog').addClass("modal-lg");
        $('#btnguardar').html("Guardar").hide();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/perfiles/fnPerfiles.php', { opcion: o, id: id },
            function (data) {
                $('#contentmodal').html(data);

            })
    } else if (o == "LISTAPERMISOS") {
        $.post('funciones/perfiles/fnPerfiles.php', { opcion: o, id: id, ven: idu },
            function (data) {
                $('#ventana-' + idu).html(data);
            })
    } else if (o == "GUARDARPERMISOS") {

        $.post('funciones/perfiles/fnPerfiles.php', { opcion: o, idp: id, idu: idu },
            function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok("");
                } else {
                    error(msn);
                }
            }, "json")
    }
}

function CRUDMOTIVOS(o, id, idu) {
    $('#myModal>.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
    if (o == "NUEVO") {


        $('#titlemodal').html("Nuevo Motivo Rechazo");
        $('#btnguardar').attr('onclick', "CRUDMOTIVOS('GUARDAR','" + id + "')");
        $('#btnguardar').html("Guardar").show();
        $.post('funciones/motivos/fnMotivos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "EDITAR") {


        $('#titlemodal').html("Edición Motivo Rechazo");
        $('#btnguardar').attr('onclick', "CRUDMOTIVOS('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $.post('funciones/motivos/fnMotivos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmmotivos').parsley().validate();
        if ($('#frmmotivos').parsley().isValid()) {
            var txtnombre = $('#txtnombre').val();
            var est = $('input:radio[name=radestado]:checked').val();

            $.post('funciones/motivos/fnMotivos.php', {
                opcion: o,
                id: id,
                nombre: txtnombre,
                est: est
            },
                function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        $('#myModal').modal('hide');
                        var table = $('#tbmotivos').DataTable();
                        if (o == "GUARDAR") {
                            table.draw('full-hold');
                        } else {
                            table.row('#row_' + id).draw(false);
                        }
                        ok(msn);
                    } else {
                        error(msn);
                    }
                }, "json");

        }
    } else if (o == "LISTAMOTIVOS") {
        $.post('funciones/motivos/fnMotivos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "ELIMINAR") {


        swal({
            title: "Realmente desea eliminar el motivo seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {

                $.post('funciones/motivos/fnMotivos.php', {
                    opcion: o,
                    id: id

                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbmotivos').DataTable();
                            table.row('#row_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

function CRUDUSUARIOS(o, id, idu) {
    //$('#myModal>.modal-dialog').removeClass('modal-lg').removeClass('modal-xl');
    var ti = localStorage.getItem('lstipousuario');
    var tex = (ti == 2) ? 'empleado' : 'usuario';
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Nuevo " + tex);
        $('#btnguardar').attr('onclick', "CRUDUSUARIOS('GUARDAR','')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id,
            ti: ti
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "AJUSTECUENTA") {

        $('#titlemodal').html("Mi cuenta");
        $('#btnguardar').attr('onclick', "CRUDUSUARIOS('GUARDARAJUSTE','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id

        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass('modal-lg').removeClass('modal-lx');
        $('#titlemodal').html("Edición " + tex);
        $('#btnguardar').attr('onclick', "CRUDUSUARIOS('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id,
            ti: ti
        }, function (data) {
            $('#contentmodal').html(data);

        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION" || o == "GUARDARCLIENTE") {
        $('#frmusuario').parsley().validate();
        if ($('#frmusuario').parsley().isValid()) {
            var obr = 0;
            var ruta = $('#rutausuario').attr('data-rut'); //.text();
            var rutaa = $('#rutausuario').attr('data-ruta');
            var txtnombre = $('#txtnombre').val();
            var txtapellido = $('#txtapellido').val();
            var txtcelular = $('#txtcelular').val();
            var txtfijo = $('#txttelefono').val();
            var txtemail = $('#txtemail').val();
            var txtnit = $('#txtnit').val();
            var txtusuario = $('#txtusuario').val();
            var txtdireccion = $('#txtdireccion').val();
            var txtcontacto = $('#txtcontacto').val();
            var txtbarrio = $('#stxtbarrio').val();
            var txtpass = $('#txtcontrasena').val();
            var lc = txtpass.length
            var txtpass1 = $('#txtverificar').val();
            var selperfil = $('#selperfil').val();
            var est = $('input:radio[name=radestado]:checked').val();
            if (o == "GUARDARCLIENTE") {
                cen = $('#tbClientes').attr('data-cenco');
            } else {
                cen = $('#selcencos').val();
            }

            var ing = $('#txtfechaingreso').val();
            var sal = $('#txtsalario').val()
            sal = sal.replace("$", "")
            sal = sal.replace(",", "").replace(",", "").replace(",", "");

            var aux = $('#txtauxilio').val()
            aux = aux.replace("$", "")
            aux = aux.replace(",", "").replace(",", "").replace(",", "");

            var hor = $('#selhorario').val();

            if ((txtemail.indexOf('@', 0) == -1 || txtemail.indexOf('.', 0) == -1) && txtemail != "") {
                error('Formato de correo electrónico no valido. Ejemplo:example@mail.com. Verificar');
            } else {
                $.post('funciones/usuarios/fnUsuarios.php', {
                    opcion: o,
                    id: id,
                    ruta: ruta,
                    rutaa: rutaa,
                    nombre: txtnombre,
                    apellido: txtapellido,
                    doc: txtnit,
                    usu: txtusuario,
                    cel: txtcelular,
                    fij: txtfijo,
                    ema: txtemail,
                    dir: txtdireccion,
                    bar: txtbarrio,
                    pass: txtpass,
                    perfil: selperfil,
                    est: est,
                    tex: tex,
                    ti: ti,
                    contacto: txtcontacto,
                    cen: cen,
                    ing: ing,
                    sal: sal,
                    hor: hor,
                    aux: aux
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            $('#myModal').modal('hide');
                            var table = $('#tbusuarios').DataTable();
                            ok(msn);
                            if (o == "GUARDAR") {
                                $('#txtnombre').val("");
                                $('#txtapellido').val("");
                                $('#txtcelular').val("");
                                $('#txttelefono').val("");
                                $('#txtemail').val("");
                                $('#txtnit').val("");
                                $('#txtusuario').val("");
                                $('#txtdireccion').val("");
                                $('#txtbarrio').val("");
                                $('#txtcontrasena').val("");
                                $('#txtverificar').val("");
                                $('#txtfechaingreso').val("");
                                $('#txtsalario').val("");
                                $('#txtauxilio').val("");
                                table.draw('full-hold');
                            } else {
                                table.row('#row_' + id).draw(false);
                            }
                            if (ti != 2) {
                                setTimeout(CRUDUSUARIOS('VALORESACTUAL', ''), 1000);
                            }
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        }
    } else if (o == "GUARDARAJUSTE") {
        $('#frmusuario').parsley().validate();
        if ($('#frmusuario').parsley().isValid()) {
            var txtnombre = $('#txtnombre').val();
            var txtapellido = $('#txtapellido').val();
            var txtcelular = $('#txtcelular').val();
            var txtfijo = $('#txtfijo').val();
            var txtcorreo = $('#txtcorreo').val();
            var txtpass = $('#txtcontrasena').val();
            var lc = txtpass.length
            var txtpass1 = $('#txtverificar').val();

            if (txtpass != txtpass1) {
                error("Las contraseñas deben coincidir");
            } else {
                $.post('funciones/usuarios/fnUsuarios.php', {
                    opcion: o,
                    id: id,
                    nom: txtnombre,
                    ape: txtapellido,
                    cel: txtcelular,
                    fij: txtfijo,
                    ema: txtcorreo,
                    pass: txtpass,
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            $('#myModal').modal('hide');
                            ok(msn);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        }
    } else if (o == "FILTROS") {
        if (id == "") {

        } else {
            localStora('lsperfil', id);
        }
        $.post('funciones/usuarios/fnUsuarios.php', { opcion: o, id }, function (data) {
            $('#divfiltros').html(data);
        })
    } else if (o == "LISTAUSUARIOS") {

        if (id == "") {

        } else {
            localStora('lsperfil', id);
        }
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "VALORESACTUAL") {
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o
        }, function (data) {
            $('#divvalores').html(data);
        })
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar el " + tex + " seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {

                $.post('funciones/usuarios/fnUsuarios.php', {
                    opcion: o,
                    id: id,
                    tex: tex
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbusuarios').DataTable();
                            table.row('#row_' + id).remove().draw(false);
                            if (ti == 1) {
                                setTimeout(CRUDUSUARIOS('VALORESACTUAL', ''), 1000);
                            }
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    } else if (o == "ASIGNARPERMISOS") {
        //$('#btnguardar').attr('onclick',"CRUDUSUARIOS('GUARDAREDICION','"+id+"','')").show();

        $('#titlemodal').html("Permisos Usuario");
        $('#contentmodal').html('');
        $('#myModal>.modal-dialog').addClass("modal-lg").removeClass('modal-xl');
        $('#btnguardar').html("Guardar").hide();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/usuarios/fnUsuarios.php', { opcion: o, id: id },
            function (data) {
                $('#contentmodal').html(data);

            });
    } else if (o == "LISTAPERMISOS") {
        $.post('funciones/usuarios/fnUsuarios.php', { opcion: o, id: id, ven: idu },
            function (data) {
                $('#ventana-' + idu).html(data);
            });

    } else if (o == "GUARDARPERMISOS") {

        $.post('funciones/usuarios/fnUsuarios.php', { opcion: o, idp: id, idu: idu },
            function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok("");
                } else {
                    error(msn);
                }
            }, "json");
    }
    else if (o == "REGLANEGOCIO") {
        $('#myModal>.modal-dialog').removeClass("modal-lg").addClass('modal-xl');
        $('#titlemodal').html("REGLAS DE NEGOCIO");
        $('#btnguardar').attr('onclick', "CRUDUSUARIOS('GUARDARREGLA','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();

        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        });
    }
    else if (o == "GUARDARREGLA") {
        $('#frmreglas').parsley().validate();
        if ($('#frmreglas').parsley().isValid()) {
            var hirn = $('#selhirn').val();
            var hfrn = $('#selhfrn').val();

            var han = $('#selhan').val();
            var hanpm = $('#selhanpm').val();
            var hao = $('#selhao').val();

            var vhan = $('#txtvhan').val();
            var vhanpm = $('#txtvhanpm').val();
            var vhao = $('#txtvhao').val();
            var hmes = $('#txthmes').val();
            var hsemana = $('#txthsemana').val();
            var limex = $('#txtlimex').val();

            var limexsemana = $('#txtlimexsemana').val();

            vhan = vhan.replace("$", "")
            vhan = vhan.replace(",", "").replace(",", "").replace(",", "");
            vhao = vhao.replace("$", "")
            vhao = vhao.replace(",", "").replace(",", "").replace(",", "");

            vhanpm = vhanpm.replace("$", "")
            vhanpm = vhanpm.replace(",", "").replace(",", "").replace(",", "");
            $.post('funciones/usuarios/fnUsuarios.php', {
                opcion: o,
                id: id,
                hirn: hirn,
                hfrn: hfrn,
                han: han,
                hao: hao,
                vhan: vhan,
                vhao: vhao,
                hmes: hmes,
                hsemana: hsemana,
                limex: limex,
                limexsemana: limexsemana,
                vhanpm: vhanpm,
                hanpm: hanpm

            }, function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok(msn);
                } else {
                    error(msn);
                }
            }, "json");
        }
    }
    else if (o == "UPDATEEXTRA") {
        var por = $('#txtpor_' + id).val();
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o,
            id: id,
            por: por
        }, function (data) {
            var res = data[0].res;
            var msn = data[0].msn;
            if (res == "ok") {

            }
            else {
                error(msn);
            }
        }, "json");

    }
    else if (o == "MIINFORMACION") {
        $.post('funciones/perfil/fnPerfil.php', {
            opcion: o
        }, function (data) {
            $('#miperfil').html(data);
        });
    }
    else if (o == "AGUARDAREDIPER") {
        var ruta = $('#rutausuario').attr('data-rut'); //.text();
        var txtnombre = $('#txtnombre1').val();
        var txtapellido = $('#txtapellido1').val();
        var celular = $('#txtcelular1').val();
        var fijo = $('#txtfijo1').val();
        var txtemail = $('#txtcorreo1').val();
        var direccion = $('#txtdireccion1').val();
        var txtpass = $('#txtclave1').val();
        var txtpass1 = $('#txtclave22').val();
        if (txtpass == txtpass1) {
            $.post('funciones/perfil/fnPerfil.php', {
                opcion: o,
                ruta: ruta,
                id: id,
                nombre: txtnombre,
                apellido: txtapellido,
                celular: celular,
                fijo: fijo,
                email: txtemail,
                direccion: direccion,
                pass1: txtpass1
            },
                function (data) {
                    var res = data[0].res;
                    if (res == "error_sesion") {

                    } else if (res == "error") {
                        var msn = data[0].msn;
                        error(msn);
                    }
                    var msn = data[0].msn;
                    ok(msn);
                }, "json");
        } else {
            error("Contraseña no coincide");
        }
    }
    else if (o == "INFOHORARIO") {
        console.log("Enviar ")
        var hor = $('#selhorario').val();
        $('#divinfohorario').html('');
        $.post('funciones/usuarios/fnUsuarios.php', {
            opcion: o, id: hor
        }, function (data) {
            $('#divinfohorario').html(data);
        })
    }
}

function CRUDTIPODOCUMENTO(o, id) {
    if (o == "LISTATIPODOCUMENTO") {
        $('#tabladatos').html('');
        $.post('funciones/documentos/fnTipoDocumentos.php', { opcion: o },
            function (data) {
                $('#tabladatos').html(data);
            });
    } else if (o == "NUEVO") {
        $('#btnguardar').html("Guardar").show();
        $('#btnguardar').attr('onclick', "CRUDTIPODOCUMENTO('GUARDAR','')");
        $('#titlemodal').html("Nuevo Tipo Documento");
        $('#myModal>.modal-dialog').addClass("modal-lg").removeClass('modal-xl');
        $('#contentmodal').html("");
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();

        $.post('funciones/documentos/fnTipoDocumentos.php', { opcion: o, id: id },
            function (data) {
                $('#contentmodal').html(data);
            });
    } else if (o == "VALIDARAPROBACION") {
        var apro = $('input:radio[name="radaprobacion"]:checked').val();
        console.log(apro);
        if (apro != 1) {
            $('#selaprobadores').prop('required', false);
            $('#divaprobadores').addClass('hide');
        } else if (apro == 1) {
            $('#divaprobadores').removeClass('hide');
            $('#selaprobadores').prop('required', true);
        }
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass("modal-lg");
        $('#btnguardar').html("Guardar").show();
        $('#btnguardar').attr('onclick', "CRUDTIPODOCUMENTO('GUARDAREDICION','" + id + "')");
        $('#titlemodal').html("Nuevo Tipo Documento");
        $('#contentmodal').html("");
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/documentos/fnTipoDocumentos.php', { opcion: o, id: id },
            function (data) {
                $('#contentmodal').html(data);
            });
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmtipodocumento').parsley().validate();
        if ($('#frmtipodocumento').parsley().isValid()) {
            var apro = $("input:radio[name=radaprobacion]:checked").val();
            var met = (apro != 1) ? 0 : $("input:radio[name=radmetodo]:checked").val();
            var aprobadores = $('#selaprobadores').val();
            aprobadores = (apro != 1) ? "" : aprobadores;

            var datos = new FormData();
            datos.append("opcion", o);
            datos.append("nombre", $('#txtnombre').val());
            datos.append("sigla", $('#txtsigla').val());
            datos.append("prioridad", $('#selprioridad').val());
            datos.append("apro", apro);
            datos.append("aprobadores", aprobadores);
            datos.append("met", met);
            datos.append("tie", $('#txttiempo').val());
            datos.append("tiecon", $('#txttiempoconfirmacion').val());
            datos.append("id", id);

            $.ajax({
                type: 'POST',
                url: 'funciones/documentos/fnTipoDocumentos.php',
                data: datos,
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    // console.log(data);
                    var res = data[0].res;
                    var msn = data[0].msn;

                    if (res == "ok") {
                        ok(msn);
                        var table = $('#tbListaTipo').DataTable();
                        if (o == "GUARDAR") {
                            var idt = data[0].idt;
                            $('#txtnombre').val('');
                            $('#txtsigla').val('');
                            $('#selprioridad>option[value=""]').attr('selected', 'selected');
                            table.draw(false);
                            setTimeout(function () {
                                //CRUDCARPETAS('CREARFOLDER',idt);
                            }, 100)
                        } else {
                            table.row('#rowt_' + id).draw(false);
                        }
                        setTimeout(function () {
                            $('#selprioridad').selectpicker('refresh');
                        }, 1500);
                    } else if (res == "error") {
                        error(msn);
                    }
                }
            });
        }
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar el tipo de documento seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {

                $.post('funciones/documentos/fnTipoDocumentos.php', { opcion: o, id: id },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbListaTipo').DataTable();
                            table.row('#rowt_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

async function APROBARCORRESPONDENCIA2(iddoc) {
    const { value: obser } = await Swal.fire({
        title: '¿Realmente desea aprobar la correspondencia?',
        html: 'Observación (opcional): <textarea id="swal-observacion" class="swal2-input" placeholder="Desear agregar alguna observación"></textarea>',

        //inputPlaceholder: 'Desear agregar alguna observación (opcional)',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return [
                document.getElementById('swal-observacion').value
            ]
        }
        /*inputValidator: (value) => {
          if (!value) {
            //return 'Debe ingresar la observación'
            
          }
        }*/
    })
    console.log(Swal.DismissReason)

    if (obser) {
        console.log(obser);
        //obser = obser.replace(new RegExp("\n","g"), "<br>");
        var ob = $('#swal-observacion').val().replace(new RegExp("\n", "g"), "<br>")
        // Swal.fire(`Entered email: ${factura}`);
        $.post('funciones/documentos/fnDocumentos.php', { opcion: "GUARDARAPROBARCORRESPONDENCIA", id: iddoc, obser: ob }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;
            var mover = dato[0].mover;
            if (res == "ok") {
                ok2(msn);
                //actualizar contador
                //var table = $('#tbordenes').DataTable();
                //table.row('#rowp_' + idpedido).draw(false);
                CRUDCORRESPONDENCIA('APROBARCORRESPONDENCIA', iddoc);
                if (mover == 1) {
                    setTimeout(CRUDCARPETAS('MOVERDOCUMENTOS', iddoc), 100);
                }
                setTimeout(CRUDCORRESPONDENCIA('CONTADORCORRESPONDENCIA', ''), 300);


            } else {
                error(msn);
            }
        }, "json");
    }

}
async function CANCELARCORRESPONDENCIA(iddoc) {
    var inputOptionsPromise = new Promise(function (resolve) {
        setTimeout(function () {
            $.post('funciones/documentos/fnDocumentos.php', {
                opcion: "LISTAMOTIVOS"
            }, function (data) {
                var res = data[0].res;
                if (res == "no") {
                    error("No hay motivo de rechazo de correspondencia");
                } else {
                    var options = {};
                    $.map(data, function (o) {
                        options[o.id] = o.literal;
                    });
                    resolve(options)
                }
            }, "json")

        }, 100)
    })

    const { value: motivo } = await Swal.fire({
        title: '¿Realmente desea rechazar la correspondencia?',
        html: 'Observación (opcional): <textarea id="swal-observacion" class="swal2-input"></textarea><br>Motivo de Rechazo:',
        input: 'select',

        inputOptions: inputOptionsPromise,
        /* {
          apples: 'Apples',
          bananas: 'Bananas',
          grapes: 'Grapes',
          oranges: 'Oranges'
        },*/
        inputPlaceholder: 'Selecciona motivo',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            /*if (!value) {
              return 'Debe ingresar la observación'
            }*/
            return new Promise((resolve) => {
                //var hf = $('#swal-horafinal').val();
                if (value /*&& hf!=""*/) {
                    resolve()
                } else {
                    resolve('Debe seleccionar el motivo de rechazo')
                }
            })
        }
    })

    if (motivo) {
        var ob = $('#swal-observacion').val().replace(new RegExp("\n", "g"), "<br>")
        //var mot = motivo.replace(new RegExp("\n","g"), "<br>")
        //Swal.fire(`You selected: ${motivo}`)
        $.post('funciones/documentos/fnDocumentos.php', { opcion: "GUARDARRECHAZARCORRESPONDENCIA", id: iddoc, mot: motivo, ob: ob }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;
            var doc = dato[0].doc;
            //var idpedido = dato[0].idpedido;
            if (res == "ok") {
                ok2(msn);
                //var table = $('#tbordenes').DataTable();
                //table.row('#rowp_' + idpedido).draw(false);
                CRUDCORRESPONDENCIA('APROBARCORRESPONDENCIA', doc);
                setTimeout(CRUDCORRESPONDENCIA('CONTADORORDENES', ''), 500);
            } else {
                error(msn);
            }
        }, "json");
    }
}

function CARGARLABORES(sel1, sel2) {
    var obr = $('#' + sel2);
    var vobr = obr.val();
    var soc = $('#' + sel1).val();

    obr.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    $.post('funciones/cencos/fnCencos.php', { opcion: 'CARGARLABORES', soc: soc },
        function (data) {

            obr.selectpicker('refresh').empty();
            obr.append('<option value=""></option>');
            var res = data[0].res;
            if (res == "no") {
                setTimeout(function () {
                    $('#' + sel2 + '>option:selected').attr('selected', false);
                    obr.selectpicker('refresh').trigger('change');
                }, 100)
            } else {
                $('#' + sel2 + '>option:selected').attr('selected', false);
                for (var i = 0; i < data.length; i++) {
                    var sel = "";
                    if (data[i].id == vobr) { sel = "selected"; }
                    obr.append('<option value="' + data[i].id + '" data-subtext="' + data[i].ref + '" ' + sel + '>' + data[i].nom + '</option>');
                }
                setTimeout(function () {
                    obr.selectpicker('refresh').trigger('change');
                }, 100)
            }
        }, "json");
}

async function CONFIRMARENTREGA(iddoc) {
    const { value: motivo } = await Swal.fire({
        title: 'Confirmación Correspondencia Recibida',
        html: "<div class='row'><div class='col-md-6'>Radicado de documento:<input type='text' id='swal-radicado' class='swal2-input'></div><div class='col-md-6'>Evidencia de recibido:<div class='custom-file'><input type='file' id='swal-file-radicado' name='swal-file-radicado' onChange=setpreview('rutaradicado','swal-file-radicado','frmusuario') class='custom-file-input form-control-sm'><label class='custom-file-label bg-warning text-center' for='swal-file-radicado' id='rutaradicado' style='font-size: 14px;'>Agregar</label></div></div></div>Observación de recibido:",
        input: 'textarea',
        inputPlaceholder: 'Ingresar observación de recibido',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            /*if (!value) {
              return 'Debe ingresar la observación'
            }*/
            return new Promise((resolve) => {
                var rad = $('#swal-radicado').val();
                var ruta = $('#rutaradicado').attr('data-rut'); //.text();
                //var hf = $('#swal-horafinal').val();
                if (rad != "" && (value != "" || ruta != "") /*&& hf!=""*/) {
                    resolve()
                } else {
                    resolve('Debe ingresar radicado y adjuntar un documento u observación de recibido')
                }
            })
        }
    })

    if ((motivo || ($('#rutaradicado').attr('data-rut') != "Agregar" && $('#rutaradicado').attr('data-rut') != "")) && $('#swal-radicado').val() != "") {
        var rad = $('#swal-radicado').val();
        var ruta = $('#rutaradicado').attr('data-rut'); /*text();*/
        ruta = (ruta == "Agregar") ? "" : ruta;
        var name = $('#rutaradicado').attr('data-name');
        var ob = motivo.replace(new RegExp("\n", "g"), "<br>")
        //Swal.fire(`You selected: ${motivo}`)
        $.post('funciones/documentos/fnDocumentos.php', { opcion: "GUARDARENTREGA", id: iddoc, observacion: ob, rad: rad, ruta: ruta, name: name }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;
            var idarchivo = dato[0].idarchivo;
            //var idpedido = dato[0].idpedido;
            if (res == "ok") {

                ok2(msn);
                if (idarchivo != "") {
                    setTimeout(CRUDCARPETAS('MOVERARCHIVO', iddoc, idarchivo), 300);
                }
                window.location.href = "#/Inicio";
            } else {
                error(msn);
            }
        }, "json");
    } else {
        console.log("Cancela la confirmación");
    }
}

async function ELIMINARCORRESPONDENCIA(iddoc) {
    const { value: motivo } = await Swal.fire({
        title: 'Realmente desea eliminar la correspondencia seleccionada',
        input: 'textarea',
        inputPlaceholder: 'Ingresar motivo de eliminación',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            return new Promise((resolve) => {

                if (value) {
                    resolve()
                } else {
                    resolve('Debe ingresar el motivo de eliminación')
                }
            })
        }
    })

    if (motivo) {

        var ob = motivo.replace(new RegExp("\n", "g"), "<br>")
        //Swal.fire(`You selected: ${motivo}`)
        $.post('funciones/documentos/fnDocumentos.php', { opcion: "ELIMINARCORRESPONDENCIA", id: iddoc, mot: ob }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;

            if (res == "ok") {

                ok2(msn);
                var table = $('#tbdocumentos').DataTable();
                table.row('#rowp_' + iddoc).remove().draw(false);
            } else {
                error(msn);
            }
        }, "json");
    }
}

async function CERRARORDEN(idpedido) {
    var inputOptionsPromise = new Promise(function (resolve) {
        setTimeout(function () {
            $.post('funciones/ordenes/fnOrdenes.php', {
                opcion: "LISTAFACTURAS"
            }, function (data) {
                var res = data[0].res;
                if (res == "no") {
                    error("No hay facturas pendientes por entregar");
                } else {
                    var options = {};
                    $.map(data, function (o) {
                        options[o.id] = o.literal;
                    });
                    resolve(options)
                }
            }, "json")

        }, 2000)
    })

    const { value: factura } = await Swal.fire({
        title: 'Seleccionar factura que pertenece a la orden de compra',
        input: 'select',
        inputOptions: inputOptionsPromise,
        /* {
              apples: 'Apples',
              bananas: 'Bananas',
              grapes: 'Grapes',
              oranges: 'Oranges'
            },*/
        inputPlaceholder: 'Selecciona número de factura',
        showCancelButton: true,
        inputValidator: (value) => {
            return new Promise((resolve) => {
                if (value) {
                    resolve()
                } else {
                    resolve('Debe seleccionar el número de factura')
                }
            })
        }
    })

    if (factura) {
        //Swal.fire(`You selected: ${motivo}`)
        $.post('funciones/ordenes/fnOrdenes.php', { opcion: "CERRARORDEN", idpedido: idpedido, numfactura: factura }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;

            if (res == "ok") {
                ok(msn);
                $('#myModal').modal('hide');
                var table = $('#tbordenes').DataTable();
                table.row('#rowp_' + idpedido).draw(false);
                setTimeout(CRUDCORRESPONDENCIA('CONTADORORDENES', ''), 1000);
            } else {
                error(msn);
            }
        }, "json");
    }

}

async function CERRARORDEN2(idpedido) {
    const { value: factura } = await Swal.fire({
        title: 'Realmente desea cerrar la orden de compra',
        input: 'text',
        inputPlaceholder: 'Ingresa el numero de factura',
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar el numero de factura'
            }
        }
    })

    if (factura) {

        // Swal.fire(`Entered email: ${factura}`);
        $.post('funciones/ordenes/fnOrdenes.php', { opcion: "CERRARORDEN", idpedido: idpedido, numfactura: factura }, function (dato) {
            var res = dato[0].res;
            var msn = dato[0].msn;
            if (res == "ok") {
                ok(msn);
                //actualizar contador
                var table = $('#tbordenes').DataTable();
                table.row('#rowp_' + idpedido).draw(false);

            } else {
                error(msn);
            }
        }, "json");
    }
}


function CRUDCENCOS(o, id) {
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Nuevo Centro de Costos");
        $('#btnguardar').attr('onclick', "CRUDCENCOS('GUARDAR','')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/cencos/fnCencos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Edición Centro de Costos");
        $('#btnguardar').attr('onclick', "CRUDCENCOS('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/cencos/fnCencos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmcencos').parsley().validate('grcenco');
        if ($('#frmcencos').parsley().isValid({ group: 'grcenco' })) {
            var nom = $('#txtnombre').val();
            var cod = $('#txtcodigo').val();
            var ruta = $('#rutamembrete').attr('data-rut');
            var rutaa = $('#rutamembrete').attr('data-rutaa')
            $.post('funciones/cencos/fnCencos.php', {
                opcion: 'GUARDAR',
                nom: nom,
                cod: cod,
                ruta: ruta,
                rutaa: rutaa,
                id: id
            }, function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                var ids = data[0].id;
                if (res == "ok") {
                    ok(msn);
                    var table = $('#tbCencos').DataTable();
                    if (o == "GUARDAR") {
                        table.draw(false);
                        setTimeout(CRUDCENCOS('EDITAR', ids), 100);
                    } else {
                        table.row('#rowce_' + id).draw(false);
                    }
                    //$('#myModal').modal('hide');
                } else {
                    error(msn);
                }
            }, "json");
        }
    } else if (o == "FILTROS") {
        $.post('funciones/cencos/fnCencos.php', {
            opcion: o
        }, function (data) {
            $('#divfiltros').html(data);
        })
    } else if (o == "LISTACENCOS") {
        $.post('funciones/cencos/fnCencos.php', {
            opcion: o
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "AGREGARLABOR") {
        $('#frmcencos').parsley().validate('grlabor');
        if ($('#frmcencos').parsley().isValid({ group: 'grlabor' })) {
            var labor = $('#txtnombrelabor').val();
            var des = $('#txtdescripcionlabor').val().replace(new RegExp("\n", "g"), "<br>");
            $.post('funciones/cencos/fnCencos.php', {
                opcion: o,
                labor: labor,
                des: des,
                id: id
            }, function (data) {
                var res = data[0].res;
                var msn = data[0].mns;
                var idl = data[0].idl;
                if (res == "ok") {
                    $('#txtnombrelabor').val('');
                    $('#txtdescripcionlabor').val('');
                    ok(msn);
                    var table = $('#tbLabor').DataTable();
                    table.draw(false)
                } else {
                    error(msn);
                }

            }, "json");
        }
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar el centro de costos seleccionados",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {
                $.post('funciones/cencos/fnCencos.php', {
                    opcion: o,
                    id: id
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbCencos').DataTable();
                            table.row('#rowce_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");

            }
        });
    } else if (o == "ELIMINARLABOR") {
        swal({
            title: "Realmente desea eliminar la labor seleccionada",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {
                $.post('funciones/cencos/fnCencos.php', {
                    opcion: o,
                    id: id
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbLabor').DataTable();
                            table.row('#rowl_' + id).draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

function CRUDOBRAS(o, id) {
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Nueva Obra");
        $('#btnguardar').attr('onclick', "CRUDOBRAS('GUARDAR','')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/obras/fnObras.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Edición Obra");
        $('#btnguardar').attr('onclick', "CRUDOBRAS('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/obras/fnObras.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmobras').parsley().validate();
        if ($('#frmobras').parsley().isValid()) {
            var nom = $('#txtnombre').val();
            var nompro = $('#txtnombreproyecto').val();
            var ubi = $('#txtubicacion').val();
            var fecini = $('#txtfechainicio').val();

            var cen = $('#txtcenco').val();
            var dir = $('#seldirector').val();
            var hor = $('#txthoras').val();
            var horsem = $('#txtsemana').val();
            var vo = $('#txtvaloroperador').val();
            vo = vo.replace("$", "")
            vo = vo.replace(",", "").replace(",", "").replace(",", "");

            var vs = $('#txtvalorsenalero').val();
            vs = vs.replace("$", "")
            vs = vs.replace(",", "").replace(",", "").replace(",", "");

            var vm = $('#txtvalormaquina').val();
            vm = vm.replace("$", "")
            vm = vm.replace(",", "").replace(",", "").replace(",", "");

            var lun = $('#txtlunes').val();
            var mar = $('#txtmartes').val();
            var mie = $('#txtmiercoles').val();
            var jue = $('#txtjueves').val();
            var vie = $('#txtviernes').val();
            var sab = $('#txtsabado').val();
            var dom = $('#txtdomingo').val();

            var est = $('input:radio[name=radestado]:checked').val();
            var aux = $('input:radio[name=radauxilio]:checked').val();
            var va = $('#txtvalorauxilio').val();
            va = va.replace("$", "")
            va = va.replace(",", "").replace(",", "").replace(",", "");

            var ve = $('#txtvalorelevador').val();
            ve = ve.replace("$", "")
            ve = ve.replace(",", "").replace(",", "").replace(",", "");

            var operador = $('#txtoperador').val();
            var senalero = $('#txtsenalero').val();

            var contrato = $('#txtcontrato').val();

            if (aux == 1 && va <= 0) {
                error("El valor del auxilio debe ser mayor a cero. Verificar");
            }
            else {
                $.post('funciones/obras/fnObras.php', {
                    opcion: 'GUARDAR',
                    nom: nom,
                    cen: cen,
                    dir: dir,
                    vo: vo,
                    vm: vm,
                    lun: lun,
                    mar: mar,
                    mie: mie,
                    jue: jue,
                    vie: vie,
                    sab: sab,
                    dom: dom,
                    hor: hor,
                    horsem: horsem,
                    vs: vs,
                    est: est,
                    aux: aux,
                    va: va,
                    ve: ve,
                    nompro: nompro,
                    ubi: ubi,
                    fecini: fecini,
                    senalero: senalero,
                    operador: operador,
                    contrato: contrato,
                    id: id
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        ok(msn);
                        var table = $('#tbObras').DataTable();
                        if (o == "GUARDAR") {
                            table.draw(false);
                        } else {
                            table.row('#rowo_' + id).draw(false);
                        }
                        //$('#myModal').modal('hide');
                    } else {
                        error(msn);
                    }
                }, "json");
            }
        }
    } else if (o == "FILTROS") {
        $.post('funciones/obras/fnObras.php', {
            opcion: o
        }, function (data) {
            $('#divfiltros').html(data);
        })
    } else if (o == "LISTAOBRAS") {
        $.post('funciones/obras/fnObras.php', {
            opcion: o
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar la obra seleccionada",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {
                $.post('funciones/obras/fnObras.php', {
                    opcion: o,
                    id: id
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbObras').DataTable();
                            table.row('#rowo_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

function CRUDHORARIOS(o, id) {
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Nuevo Horario");
        $('#btnguardar').attr('onclick', "CRUDHORARIOS('GUARDAR','')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/horarios/fnHorarios.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Edición Horario");
        $('#btnguardar').attr('onclick', "CRUDHORARIOS('GUARDAREDICION','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/horarios/fnHorarios.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "GUARDAR" || o == "GUARDAREDICION") {
        $('#frmhorarios').parsley().validate();
        if ($('#frmhorarios').parsley().isValid()) {
            var nom = $('#txtnombre').val();
            var lun = $('#txtlunes').val();
            var mar = $('#txtmartes').val();
            var mie = $('#txtmiercoles').val();
            var jue = $('#txtjueves').val();
            var vie = $('#txtviernes').val();
            var sab = $('#txtsabado').val();
            var dom = $('#txtdomingo').val();

            $.post('funciones/horarios/fnHorarios.php', {
                opcion: 'GUARDAR',
                nom: nom,
                lun: lun,
                mar: mar,
                mie: mie,
                jue: jue,
                vie: vie,
                sab: sab,
                dom: dom,
                id: id
            }, function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok(msn);
                    var table = $('#tbHorarios').DataTable();
                    if (o == "GUARDAR") {
                        table.draw(false);
                    } else {
                        table.row('#rowh_' + id).draw(false);
                    }
                    //$('#myModal').modal('hide');
                } else {
                    error(msn);
                }
            }, "json");
        }
    } else if (o == "FILTROS") {
        $.post('funciones/horarios/fnHorarios.php', {
            opcion: o
        }, function (data) {
            $('#divfiltros').html(data);
        })
    } else if (o == "LISTAHORARIOS") {
        $.post('funciones/horarios/fnHorarios.php', {
            opcion: o
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar el horario seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {
                $.post('funciones/horarios/fnHorarios.php', {
                    opcion: o,
                    id: id
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbHorarios').DataTable();
                            table.row('#rowh_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

function CRUDFESTIVOS(o, id) {
    if (o == "NUEVO") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Nuevo Festivo");
        $('#btnguardar').attr('onclick', "CRUDFESTIVOS('GUARDAR','')");
        $('#btnguardar').html("Guardar").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/festivos/fnFestivos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "EDITAR") {
        $('#myModal>.modal-dialog').addClass('modal-lg');
        $('#titlemodal').html("Edición Festivo");
        $('#btnguardar').attr('onclick', "CRUDFESTIVOS('GUARDAR','" + id + "')");
        $('#btnguardar').html("Guardar Cambios").show();
        $('#btnexportar').attr('onclick', "");
        $('#btnexportar').hide();
        $.post('funciones/festivos/fnFestivos.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#contentmodal').html(data);
        })
    } else if (o == "GUARDAR") {
        $('#frmfestivos').parsley().validate();
        if ($('#frmfestivos').parsley().isValid()) {
            var fec = $('#txtfecha').val();
            var des = $('#txtdescripcion').val();
            $.post('funciones/festivos/fnFestivos.php', {
                opcion: 'GUARDAR',
                fec: fec,
                des: des,
                id: id
            }, function (data) {
                var res = data[0].res;
                var msn = data[0].msn;
                var ids = data[0].id;
                if (res == "ok") {
                    ok(msn);
                    var table = $('#tbFestivos').DataTable();
                    if (o == "GUARDAR") {
                        table.draw(false);
                        //$('#myModal').modal('hide');
                    } else {
                        table.row('#rowf_' + id).draw(false);
                    }
                    //$('#myModal').modal('hide');
                } else {
                    error(msn);
                }
            }, "json");
        }
    } else if (o == "FILTROS") {
        $.post('funciones/festivos/fnFestivos.php', {
            opcion: o
        }, function (data) {
            $('#divfiltros').html(data);
        })
    } else if (o == "LISTAFESTIVOS") {
        $.post('funciones/festivos/fnFestivos.php', {
            opcion: o
        }, function (data) {
            $('#tabladatos').html(data);
        })
    } else if (o == "ELIMINAR") {
        swal({
            title: "Realmente desea eliminar el festivo seleccionado",
            text: "",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, eliminar!',
            cancelButtonText: 'No eliminar!'
        }).then(function (result) {
            if (result.value) {
                $.post('funciones/festivos/fnFestivos.php', {
                    opcion: o,
                    id: id
                },
                    function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            var table = $('#tbFestivos').DataTable();
                            table.row('#rowf_' + id).remove().draw(false);
                        } else {
                            error(msn);
                        }
                    }, "json");
            }
        });
    }
}

function CRUDJORNADA(o, id, ids) {
    if (o == "NUEVO" || o == "EDITAR" || o == "APPROVED") {
        $.post('funciones/jornada/fnJornada.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#tabladatos').html(data);
        })
    }
    else
        if (o == "EDITARJORNADA") {
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-xl');
            $('#titlemodal').html("Edición Jornada");
            $('#btnguardar').attr('onclick', "CRUDLIQUIDAR('GUARDARSEMANA','" + id + "')");
            $('#btnguardar').html("Guardar Cambios").hide();
            $('#btnexportar').hide();

            $.post('funciones/jornada/fnJornada.php', {
                opcion: o,
                id: id
            }, function (data) {
                $('#contentmodal').html(data);
            })
        } else
            if (o == "REGISTROMASIVO") {

                var emp = $('#selempleado').val();
                var sem = $('#selsemana').val();
                if (emp == "" || sem == "") {
                    error("Seleccionar empleado y semana");
                }
                else {
                    $('#myModal').modal('show');
                    $('#myModal>.modal-dialog').addClass('modal-xl');
                    $('#titlemodal').html("Registro Masivo");
                    $('#btnguardar').attr('onclick', "CRUDJORNADA('GUARDARMASIVO','')");
                    $('#btnguardar').html("Guardar").show();
                    $('#btnexportar').hide();

                    $.post('funciones/jornada/fnJornada.php', {
                        opcion: o,
                        emp: emp,
                        sem: sem
                    }, function (data) {
                        $('#contentmodal').html(data);
                    })
                }
            }
            else if (o == "GUARDARMASIVO") {
                $('#frmmasivo').parsley().validate();
                if ($('#frmmasivo').parsley().isValid()) {
                    var emp = $('#selempleado').val();
                    var sem = $('#selsemana').val();
                    var nov = $('#selnovedad').val();
                    var obr = $('#selobramasivo').val();
                    var dias = $('#seldias').val();
                    var hi = $('#selinicio').val();
                    var hf = $('#selfin').val();
                    var obs = $('#txtobservacion').val().replace(new RegExp("\n", "g"), "<br>");
                    $.post('funciones/jornada/fnJornada.php', {
                        opcion: o,
                        emp: emp,
                        sem: sem,
                        nov: nov,
                        obr: obr,
                        dias: dias,
                        obs: obs,
                        hi: hi,
                        hf: hf
                    }, function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            $('#selsemana').trigger('change');
                        }
                        else {
                            error(msn);
                        }
                    }, "json");
                }
                else {
                    error("Hay información obligatoria por ingresar");
                }
            }
            else if (o == "CARGARSEMANA") {
                var emp = $('#selempleado').val();
                var ano = $('#selano').val();
                var mes = $('#selmes').val();
                var sem = $('#selsemana').val();
                var ini = $('#selsemana>option:selected').attr('data-inicio');
                var fin = $('#selsemana>option:selected').attr('data-fin');
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o,
                    ano: ano,
                    mes: mes,
                    sem: sem,
                    emp: emp,
                    ini: ini,
                    fin: fin
                }, function (data) {
                    $('#divsemana').html(data);
                })
            }
            else if (o == "ACTUALIZARHORAOLD") {
                var emp = $('#selempleado').val();
                var ano = $('#selano').val();
                var mes = $('#selmes').val();
                var sem = $('#selsemana').val();
                var ini = $('#selsemana>option:selected').attr('data-inicio');
                var fin = $('#selsemana>option:selected').attr('data-fin');
                var inicio = $('#selinicio' + ids).val();
                var fin = $('#selfin' + ids).val();
                var iniciot = $('#seliniciot' + ids).val();
                var fint = $('#selfint' + ids).val();
                var inicion = $('#selinicion' + ids).val();
                var finn = $('#selfinn' + ids).val();
                var fec = $('#rowh_' + ids).attr('data-fecha');
                var obs = $('#txtobservacion' + ids).val(); //.replace(new RegExp("\n", "g"), "<br>");
                if (emp > 0 && ano != "" && sem != "") {

                    $.post('funciones/jornada/fnJornada.php', {
                        opcion: o,
                        ano: ano,
                        mes: mes,
                        sem: sem,
                        emp: emp,
                        //ini: ini,
                        //fin: fin,
                        //DATOS DE FECHA
                        id: id, //detalle si ya fue agregado antes
                        fec: fec,
                        inicio: inicio,
                        fin: fin,
                        iniciot: iniciot,
                        fint: fint,
                        inicion: inicion,
                        finn: finn,
                        obs: obs,
                        ids: ids

                    }, function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok("");
                        } else {
                            error(msn);
                        }

                    }, "json");
                }
            }
            else if (o == "AGREGARHORA") {
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o, id: id
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        CRUDJORNADA('CARGARHORARIOS', id)
                    }
                    else {
                        error(msn);
                    }
                }, "json")
            }
            else if (o == "ELIMINARHORA") {
                var url = String(window.location); //window.location;
                var indv = url.lastIndexOf("/");
                var ven = url.substring(indv + 1);
                swal({
                    title: "Realmente desea eliminar la hora seleccionada",
                    text: "",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, eliminar!',
                    cancelButtonText: 'No eliminar!'
                }).then(function (result) {
                    if (result.value) {

                        $.post('funciones/jornada/fnJornada.php', {
                            opcion: o,
                            id: id,
                            idfec: ids

                        },
                            function (data) {
                                var res = data[0].res;
                                var msn = data[0].msn;
                                if (res == "ok") {
                                    ok(msn);
                                    $('#rowh_' + id).remove().fadeOut('slow');
                                    /*var rowCount = $('#tbHoras_'+ids+' tbody tr').length
                                  
                                    if(rowCount<=0)
                                    {
                                        $('#tdfec' + ids).html("<div class='alert alert-light text-center m-0'>No hay Horarios Laborados</div>");
                                    }*/
                                    setTimeout(function () {
                                        CRUDJORNADA('CALCULOHORAS', '', ids);
                                    }, 100)
                                    if (ven == "NewSettle") {
                                        setTimeout(() => {
                                            var emp = localStorage.getItem('emp');
                                            var step = localStorage.getItem('step');
                                            CRUDLIQUIDAR('VERDETALLELIQUIDAR', emp, step);
                                        }, 200);
                                    }
                                    else if (ven != "New") {
                                        setTimeout(() => {
                                            CRUDLIQUIDAR('CARGARLIQUIDAR');
                                        }, 200);
                                    }
                                } else {
                                    error(msn);
                                }
                            }, "json");
                    }
                });
            }
            else if (o == "ACTUALIZARDIA") {
                var url = String(window.location); //window.location;
                var indv = url.lastIndexOf("/");
                var ven = url.substring(indv + 1);

                var nov = $('#selnovedaddia_' + id).val();
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o, id: id, nov: nov
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        CRUDJORNADA('CARGARHORARIOS', id);
                        if (ven == "NewSettle") {
                            setTimeout(() => {
                                var emp = localStorage.getItem('emp');
                                var step = localStorage.getItem('step');
                                CRUDLIQUIDAR('VERDETALLELIQUIDAR', emp, step);
                            }, 100);
                        }
                        else if (ven != "New") {
                            setTimeout(() => {
                                CRUDLIQUIDAR('CARGARLIQUIDAR');
                            }, 200);
                        }
                    }
                    else {
                        error(msn);
                    }
                }, "json")
            }
            else if (o == "CARGARHORARIOS") {
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o, id: id
                }, function (data) {
                    $('#tdfec' + id).html(data);
                })
            }
            else if (o == "ACTUALIZARHORA") {

                var url = String(window.location); //window.location;
                var indv = url.lastIndexOf("/");
                var ven = url.substring(indv + 1);
                console.log(ven);
                var obr = $('#selobra' + id).val();
                var tin = $('#seltiponovedad' + id).val();
                var ini = $('#selinicio' + id).val();
                var fin = $('#selfin' + id).val();
                var obs = $('#txtobservacion' + id).val().replace(new RegExp("\n", "g"), "<br>");
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o,
                    id: id,
                    obr: obr,
                    tin: tin,
                    ini: ini,
                    fin: fin,
                    obs: obs,
                    idfec: ids
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {
                        var tot = data[0].tot;
                        $('#spantot' + id).html(tot);
                        CRUDJORNADA('CALCULOHORAS', '', ids);
                        console.log({ven});
                        if (ven == "NewSettle") {
                            setTimeout(() => {
                                // console.log()
                                var emp = localStorage.getItem('emp');
                                var step = localStorage.getItem('step');
                                CRUDLIQUIDAR('VERDETALLELIQUIDAR', emp, step);
                            }, 100);
                        }
                        else if (ven != "New") {
                            setTimeout(() => {
                                CRUDLIQUIDAR('CARGARLIQUIDAR');
                            }, 200);
                        }

                    }
                    else {
                        error(msn);
                    }

                }, "json")
            }
            else if (o == "CALCULOHORAS") {
                var emp = $('#selempleado').val();
                var sem = $('#selsemana').val();
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o, emp: emp, sem: sem, idfec: ids
                }, function (data) {
                    $('#spantotalhoras').html(data[0].totalhoras);
                    if (ids > 0) {
                        $('#totaldia_' + ids).html(data[0].totalhorasdia);
                    }
                }, "json")
            }
            else if (o == "GUARDARSEMANA") {
                $('#frmjornada').parsley().validate();
                if ($('#frmjornada').parsley().isValid()) {
                    var emp = $('#selempleado').val();
                    var sem = $('#selsemana').val();
                    var ano = $('#selano').val();
                    var not = $('#txtnota').val().replace(new RegExp("\n", "g"), "<br>")
                    var tot = $('#spantotalhoras').html();
                    $.post('funciones/jornada/fnJornada.php', {
                        opcion: o,
                        emp: emp,
                        sem: sem,
                        ano: ano,
                        not: not,
                        tot: tot
                    }, function (data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn)
                        }
                        else {
                            error(msn);
                        }
                    }, "json")
                }
                else {
                    error("Hay información obligatoria por diligenciar");
                }
            }
            else if (o == "FILTROS") {
                $('#divfiltros').html("");
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o,
                }, function (data) {

                    $('#divfiltros').html(data);
                })
            }
            else if (o == "LISTAPLANILLAS") {
                $('#tabladatos').html("");
                $.post('funciones/jornada/fnJornada.php', {
                    opcion: o
                }, function (data) {
                    $('#tabladatos').html(data);
                })
            }

}
function CRUDLIQUIDAR(o, id, ids) {
    if (o == "NUEVO" || o == "EDITAR" || o == "APPROVED" || o == "NUEVALIQUIDACION") {
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: o,
            id: id
        }, function (data) {
            $('#tabladatos').html(data);
        })
    }
    else if (o == "CARGARLIQUIDAR" || o == "VEREMPLEADOSLIQUIDAR") {
        var emp = $('#selempleadoliquidar').val();
        var ano = $('#selano').val();
        var mes = $('#selmes').val();
        var tip = $('#seltipo').val();
        var obr = $('#selobra').val();
        var ini = $('#txtdesde').val();
        var fin = $('#txthasta').val();
        $('#divplanilla').html('');
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: o,
            ano: ano,
            mes: mes,
            tip: tip,
            emp: emp,
            ini: ini,
            fin: fin,
            obr: obr
        }, function (data) {
            $('#divplanilla').html(data);
        })
    }
    else if (o == "VERDETALLELIQUIDAR") {
        var emp = id;
        var div = $('#step-' + ids);
        var ano = $('#selano').val();
        var mes = $('#selmes').val();
        var tip = $('#seltipo').val();
        var obr = $('#selobra').val();
        var ini = $('#txtdesde').val();
        var fin = $('#txthasta').val();
        div.html("");

        $("#smartwizard").smartWizard("loader", "show");
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: 'CARGARLIQUIDAR',
            ano: ano,
            mes: mes,
            tip: tip,
            emp: emp,
            ini: ini,
            fin: fin,
            obr: obr
        }, function (data) {
            div.html(data);
            $("#smartwizard").smartWizard("loader", "hide");
        })
    }
    else if (o == "CALCULOHORAS") {
        var emp = $('#selempleadoliquidar').val();
        var sem = $('#selsemana').val();
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: o, emp: emp, sem: sem
        }, function (data) {
            $('#spantotalhoras').html(data[0].totalhoras);
        }, "json")
    }

    else if (o == "FILTROS" || o == "FILTROSLIQUIDAROBRA") {
        var tip = localStorage.getItem('lstipoconsulta');
        $('#divfiltros').html("");
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: o, tip: tip
        }, function (data) {

            $('#divfiltros').html(data);
        })
    }
    else if (o == "LISTALIQUIDACIONES") {
        $('#tabladatos').html("");
        $.post('funciones/jornada/fnLiquidar.php', {
            opcion: o
        }, function (data) {
            $('#tabladatos').html(data);
        })
    }
    else
        if (o == "GENERARPROFORMA") {
            $('#frmliquidar').parsley().validate();
            if ($('#frmliquidar').parsley().isValid()) {
                var emp = ids; // $('#selempleadoliquidar').val();
                var ano = $('#selano').val();
                var mes = $('#selmes').val();
                var tip = $('#seltipo').val();
                var obr = $('#selobra').val();
                var ini = $('#txtdesde').val();
                var fin = $('#txthasta').val();
                obr = (tip == 1) ? 0 : obr;

                if (emp <= 0) {
                    error("Seleccionar el empleado");
                }
                else
                    if (obr <= 0 && tip == 2) {
                        error("Seleccionar la obra");
                    }
                    else {
                        $.post('funciones/jornada/fnLiquidar.php', {
                            opcion: o,
                            emp: emp,
                            ano: ano,
                            mes: mes,
                            tip: tip,
                            obr: obr,
                            ini: ini,
                            fin: fin
                        }, function (data) {
                            var res = data[0].res;
                            var msn = data[0].msn;
                            var id = data[0].idliquidar;
                            if (res == "ok") {
                                ok(msn);
                                CRUDLIQUIDAR('PREVIAPROFORMA', id);
                                // window.location.href="#/Inicio";
                            }
                            else {
                                error(msn);
                            }

                        }, "json")
                    }
            }
        }
        else if (o == "GENERARALLPROFORMA") {
            $('#frmliquidar').parsley().validate();
            if ($('#frmliquidar').parsley().isValid()) {
                var emp = $('#selempleadoliquidar').val();
                var ano = $('#selano').val();
                var mes = $('#selmes').val();
                var tip = $('#seltipo').val();
                var obr = $('#selobra').val();
                var ini = $('#txtdesde').val();
                var fin = $('#txthasta').val();
                obr = (tip == 1) ? 0 : obr;

                if (emp <= 0) {
                    error("Seleccionar el empleado");
                }
                else
                    if (obr <= 0 && tip == 2) {
                        error("Seleccionar la obra");
                    }
                    else {
                        $.post('funciones/jornada/fnLiquidar.php', {
                            opcion: o,
                            emp: emp,
                            ano: ano,
                            mes: mes,
                            tip: tip,
                            obr: obr,
                            ini: ini,
                            fin: fin
                        }, function (data) {
                            var res = data[0].res;
                            var msn = data[0].msn;
                            var numgeneradas = data[0].numgeneradas;
                            var numnogeneradas = data[0].numnogeneradas;
                            var generadas = data[0].generadas;
                            var nogeneradas = data[0].nogeneradas;

                            if (res == "ok") {
                                if (numgeneradas > 0 && numnogeneradas <= 0) {
                                    ok(msn)
                                }
                                else if (numgeneradas > 0 && numnogeneradas > 0) {
                                    ok2(msn + '<br>Liquidaciones Generadas' + generadas + "<br>Liquidaciones no generadas<br>" + nogeneradas)
                                }
                                else {
                                    ok(msn);
                                }
                                setTimeout(CRUDLIQUIDAR('VEREMPLEADOSLIQUIDAR', ''), 200)
                                // CRUDLIQUIDAR('PREVIAPROFORMA',id);
                                // window.location.href="#/Inicio";
                            }
                            else {
                                error(msn);
                            }

                        }, "json")
                    }
            }
        }
        else
            if (o == "CARGAREMPLEADO" || o == "CARGAREMPLEADOSLIQUIDAR") {
                var sel = (o == "CARGAREMPLEADO") ? $('#selempleado') : $('#selempleadoliquidar');
                var selemp = (o == "CARGAREMPLEADO") ? 'selempleado' : 'selempleadoliquidar';
                var obr = $('#selobra').val();
                var ini = $('#txtdesde').val();
                var fin = $('#txthasta').val();
                var tip = $('#seltipo').val();
                var val = sel.val();
                if ((obr > 0 && ini != "" && fin != "" && tip != "1") || tip == 1) {
                    //VALOR SELECCIONADO
                    sel.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                    $.post('funciones/jornada/fnLiquidar.php', { opcion: o, obr: obr, ini: ini, fin: fin, tip: tip, emp: val },
                        function (data) {
                            sel.selectpicker('refresh').empty();
                            if (data.length) {
                                $('#' + selemp + '>option:selected').attr('selected', false);
                                for (var i = 0; i < data.length; i++) {
                                    var selected = "";
                                    if (data[i].id == val) { selected = "selected"; }
                                    sel.append('<option data-subtext= "' + data[i].cor + '" ' + selected + ' value="' + data[i].id + '">' + data[i].nom + '</option>');
                                }
                                setTimeout(function () {
                                    sel.selectpicker('refresh').trigger('change');
                                }, 100)

                            } else {
                                setTimeout(function () {
                                    $('#' + selemp + '>option:selected').attr('selected', false);
                                    sel.selectpicker('refresh').trigger('change');
                                }, 100)
                            }
                        }, "json");
                }
            }
            else if (o == "PREVIAPROFORMA") {
                $('#myModal').modal('show');
                $('#myModal>.modal-dialog').addClass('modal-xl');
                $('#titlemodal').html("Previa Liquidación");
                $('#btnguardar').attr('onclick', "CRUDLIQUIDAR('ENVIARPROFORMA','" + id + "')");
                $('#btnguardar').html("Enviar").hide();
                $('#btnexportar').attr('onclick', "CRUDLIQUIDAR('EXPORTARPROFORMA','" + id + "')");
                $('#btnexportar').hide();

                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o,
                    id: id
                }, function (data) {
                    $('#contentmodal').html(data);
                })
            }
            else if (o == "PREVIAPROFORMAOBRA") {
                $('#myModal').modal('show');
                $('#myModal>.modal-dialog').addClass('modal-xl');
                $('#titlemodal').html("Previa Liquidación Obra");
                $('#btnguardar').attr('onclick', "CRUDLIQUIDAR('ENVIARPROFORMAOBRA','" + id + "')");
                $('#btnguardar').html("Enviar").hide();
                $('#btnexportar').attr('onclick', "CRUDLIQUIDAR('EXPORTARPROFORMAOBRA','" + id + "')");
                $('#btnexportar').hide();

                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o,
                    id: id
                }, function (data) {
                    $('#contentmodal').html(data);
                })
            }
            else if (o == "EXPORTARPROFORMA") {
                window.open("modulos/informes/informe/informeproformaexcell.php?id=" + id);
            }
            else if (o == "EXPORTARPROFORMAOBRA") {
                window.open("modulos/informes/informe/informeproformaobraexcell.php?id=" + id);
            }
            else if (o == "ENVIARPROFORMA") {

            }
            else if (o == "ENVIARPROFORMAOBRA") {

            }
            else if (o == "UPDATEPROCEDE") {
                var pro = $('input:radio[name=radprocede' + id + ']:checked').val();
                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o,
                    id: id,
                    pro: pro
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res != "ok") {
                        error(msn);
                    }
                }, "json")
            }
            else if (o == "UPDATEAUX") {
                var bon = $('#bon_' + id).val();
                var aux = $('#aux_' + id).val();
                aux = aux.replace("$", "")
                aux = aux.replace(",", "").replace(",", "").replace(",", "");
                var tip = ids;
                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o,
                    id: id,
                    aux: aux,
                    tip: tip
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res != "ok") {
                        error(msn);
                    }
                }, "json")
            }
            else if (o == "UPDATEBON") {
                var bon = $('#bon_' + id).val();
                bon = bon.replace("$", "")
                bon = bon.replace(",", "").replace(",", "").replace(",", "");
                var tip = ids;
                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o,
                    id: id,
                    bon: bon,
                    tip: tip
                }, function (data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res != "ok") {
                        error(msn);
                    }
                }, "json")
            }

            //FUNCIONES PARA LIQUIDACION DE OBRA
            else if (o == "LISTALIQUIDAROBRA") {
                var tip = localStorage.getItem('lstipoconsulta');
                $('#tabladatos').html("");
                $.post('funciones/jornada/fnLiquidar.php', {
                    opcion: o, tip: tip
                }, function (data) {
                    $('#tabladatos').html(data);
                })
            }
}

function CRUDINFORMES(o, tip, opc) {

    if (o == "PREVIAPROFORMA") {
        var emp = $('#selempleado').val();
        var ano = $('#selano').val();
        var mes = $('#selmes').val();
        var tip = $('#seltipo').val();
        var obr = $('#selobra').val();
        if (obr <= 0 && tip == 2) {
            error("Seleccionar la obra");
        }
        else if (obr > 0 && tip == 2) {
            error("Debe seleccionar la obra para generar la proforma");
        }
        else {
            obr = obr.join(",");
            $('#myModal').modal('show');
            $('#myModal>.modal-dialog').addClass('modal-lg');
            $('#titlemodal').html("Previa Proforma");
            $('#btnguardar').attr('onclick', "");
            $('#btnguardar').html("Guardar").hide();
            $.post('funciones/jornada/fnLiquidar.php', {
                opcion: o,
                emp: emp,
                ano: ano,
                mes: mes,
                tip: tip,
                obr: obr
            }, function (data) {
                $('#contentmodal').html(data);
            })
        }
    } else if (o == "GRAFICO") {
        $('#divinforme').removeClass('hide').html('<div class="loader-section"><span class="loader"></span></div>') ;
        var ano = $('#busano').val();
        $.post('funciones/informes/fnInformes.php', { opcion: o, ano: ano }, function (data) {
            $('#divinforme').html(data);
        })
    } else if (o == "CONTADORHOME") {
        $.post('funciones/informes/fnInformes.php', { opcion: o }, function (data) {

            //JORANADAS
            var aprobadas = data[0].aprobadas;
            var poraprobar = data[0].poraprobar;
            aprobadas = (aprobadas == "" || aprobadas == null || aprobadas == undefined | isNaN(aprobadas)) ? 0 : aprobadas;
            poraprobar = (poraprobar == "" || poraprobar == null || poraprobar == undefined | isNaN(poraprobar)) ? 0 : poraprobar;


            // LIQUIDACION
            var porfacturar = data[0].porfacturar;
            var rechazada = data[0].rechazada;
            var poraprobarliq = data[0].poraprobarliq;
            var facturadas = data[0].facturadas;

            porfacturar = (porfacturar == "" || porfacturar == null || porfacturar == undefined | isNaN(porfacturar)) ? 0 : porfacturar;
            facturadas = (facturadas == "" || facturadas == null || facturadas == undefined || isNaN(facturadas)) ? 0 : facturadas;
            poraprobarliq = (poraprobarliq == "" || poraprobarliq == null || poraprobarliq == undefined | isNaN(poraprobarliq)) ? 0 : poraprobarliq;
            rechazada = (rechazada == "" || rechazada == null || rechazada == undefined | isNaN(rechazada)) ? 0 : rechazada;

            //var total = parseInt(pendientes) + parseInt(aprobados) + parseInt(porentregar) + parseInt(porfacturar) ;

            var pendienteobra = data[0].pendienteobra;
            var poraprobarobra = data[0].poraprobarobra;
            pendienteobra = (pendienteobra == "" || pendienteobra == null || pendienteobra == undefined | isNaN(pendienteobra)) ? 0 : pendienteobra;
            poraprobarobra = (poraprobarobra == "" || poraprobarobra == null || poraprobarobra == undefined | isNaN(poraprobarobra)) ? 0 : poraprobarobra;


            $('#divaprobadas').html(aprobadas);
            $('#divporaprobar').html(poraprobar);


            $('#divproformasaprobar').html(poraprobarliq);
            $('#divproformasporfacturar').html(porfacturar);
            $('#divfacturas').html(facturadas);
            $('#divrechazada').html(rechazada);

            $('#divpendienteobra').html(pendienteobra);
            $('#divporaprobarobra').html(poraprobarobra);


            //$('#spanpendientes').html(pendientes);
            //$('#spanaprobadas').html(aprobados);
            //$('#spanporentregar').html(porentregar);
            //$('#spanporfacturar').html(porfacturar);
            //$('#spannotificaciones1').html(total);
            //$('#spannotificaciones2').html(total);
        }, "json");

    } else if (o == "FILTROSCOMPENSATORIOS" || o == "FILTROSLIMITE" || o == "FILTROSCONTABILIDAD" || o == "FILTROSHORAS") {
        $('#divfiltros').html('<div class="loader-section"><span class="loader"></span></div>');
        $.post('funciones/informes/fnInformes.php', { opcion: o },
            function (data) {
                $('#divfiltros').html(data);
            });
    } else if (o == "LISTACOMPENSATORIOS" || o == "LISTALIMITE" || o == "LISTACONTABILIDAD") {
        var tip = $('#bustipo').val();//$('#bustipoinforme').val();
        var des = $("#busperiodos option:selected").attr('data-inicio');
        var has = $("#busperiodos option:selected").attr('data-fin');
        var emp = $('#busempleado').val();
        $('#tabladatos').html('<div class="loader-section"><span class="loader"></span></div>');
        $.post('funciones/informes/fnInformes.php', { opcion: o, tip, des, has, emp },
            function (data) {
                $('#tabladatos').html(data);
            });
    }
    else if (o == "LISTAHORAS") {
        var step = localStorage.getItem('step');
        var div = $('#step-' + step);
        $('#tabladatos').html("");
        $.post('funciones/informes/fnInformes.php', { opcion: o, opc: step },
            function (data) {
                div.html(data);
            });
    }
    else if (o == "EXPORTARCONTABILIDAD") {
        var emp = $('#busempleado').val();
        // var des = $('#busdesde').val();
        // var has = $('#bushasta').val();	
        var fre = $('#busfrecuencia').val();
        // var des = $('#busdesde').val();
        // var has = $('#bushasta').val();		

        var des = fre == 2 ? $("#busperiodos option:selected").attr('data-inicio') : $('#busdesde').val();
        var has = fre == 2 ? $("#busperiodos option:selected").attr('data-fin') : $('#bushasta').val();
        var tip = $('#bustipo').val();
        var url = "emp=" + emp + "&des=" + des + "&has=" + has + "&tip=" + tip;
        window.open("modulos/informes/informe/informecontabilidadexcell.php?" + url);
    }
    else if (o == "EXPORTARCOMPENSATORIO") {
        var emp = $('#busempleado').val();
        emp = emp.length ? emp.join(",") : ''
        var url = "emp=" + emp + "&tip=" + tip+ "&opc=" + opc;
        window.open("modulos/informes/informe/informecompensatorioexcel.php?" + url);
    }
    else if (o == "EXPORTARHORAS") {
        var step = localStorage.getItem('step');
        var emp = $('#busempleado').val();
        var des = $('#busdesde').val();
        var has = $('#bushasta').val();
        var url = "emp=" + emp + "&des=" + des + "&has=" + has;
        window.open("modulos/informes/informe/informecontabilidadexcell.php?" + url);
    }
    else if (o == "EXPORTARINFORMEGENERAL") {
        var tip = $('#bustipoinforme').val();
        tip = (tip == null) ? "" : tip;
        var agru = $('#busagrupacion').val();
        agru = (agru == null) ? "" : agru;
        var estado = $('#busestado').val();
        estado = (estado == null) ? "" : estado;
        var cliente = $('#buscliente').val();
        cliente = (cliente == null) ? "" : cliente;
        var vendedor = $('#busvendedor').val();
        vendedor = (vendedor == null) ? "" : vendedor;
        var desde = $('#busdesde').val();
        desde = (desde == null) ? "" : desde;
        var hasta = $('#bushasta').val();
        hasta = (hasta == null) ? "" : hasta;
        var pedido = $('#buspedido').val();
        espedidoado = (pedido == null) ? "" : pedido;
        var per = $('#tbinformes').attr('data-per');
        per = (per == null) ? "" : per;
        var pro = $('#busproducto').val();
        pro = (pro == null) ? "" : pro;
        var mer = $('#busmercado').val();
        mer = (mer == null) ? "" : mer;
        var mar = $('#busmarca').val();
        mar = (mar == null) ? "" : mar;
        var distrito = $('#busdistrito').val();
        distrito = (distrito == null) ? "" : distrito;
        var desdeentrega = $('#busdesdeentrega').val();
        desdeentrega = (desdeentrega == null) ? "" : desdeentrega;
        var hastaentrega = $('#bushastaentrega').val();
        hastaentrega = (hastaentrega == null) ? "" : hastaentrega;
        var ano = $('#busano').val();
        ano = (ano == null) ? "" : ano;

        if (tip == "" || tip == null) {
            error2("Debe seleccionar el tipo de informe que desea descargar");
        } else {
            var url = "tip=" + tip + "&agru=" + agru + "&estado=" + estado + "&cliente=" + cliente + "&desde=" + desde + "&hasta=" + hasta + "&pedido=" + pedido + "&producto=" + pro + "&mercado=" + mer + "&distrito=" + distrito + "&desdeentrega=" + desdeentrega + "&hastaentrega=" + hastaentrega + "&ano=" + ano + "&vendedor=" + vendedor + "&marca=" + mar;
            window.open("modulos/informes/informe/informegeneralexcell.php?" + url);
        }
    }
}

function CRUDGENERAL(o, id, id2, id3, mod, act = "", emp) {
    if (o == "CARGAROBRA") {
        var sel = $('#' + id);
        var val = sel.attr('data-id'); //VALOR SELECCIONADO
        sel.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        $.post('funciones/fnGeneral.php', { opcion: o, val: val },
            function (data) {
                sel.selectpicker('refresh').empty();
                if (data.length) {
                    $('#' + id + '>option:selected').attr('selected', false);
                    for (var i = 0; i < data.length; i++) {
                        var selected = "";
                        if (data[i].id == vobr) { selected = "selected"; }
                        sel.append('<option ' + selected + ' value="' + data[i].id + '">' + data[i].nom + '</option>');
                    }
                    setTimeout(function () {
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)

                } else {
                    setTimeout(function () {
                        $('#' + id + '>option:selected').attr('selected', false);
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)
                }
            }, "json");

    } else if (o == "CARGARMES") {
        var sel3 = $('#' + id3);
        var ano = sel3.val();
        var sel = $('#' + id2);
        var val = sel.attr('data-id'); //VALOR SELECCIONADO
        sel.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        $.post('funciones/fnGeneral.php', { opcion: o, val: val, ano: ano },
            function (data) {
                sel.selectpicker('refresh').empty();

                if (data.length) {
                    $('#' + id2 + '>option:selected').attr('selected', false);
                    for (var i = 0; i < data.length; i++) {
                        var selected = "";
                        if (data[i].id == val) { selected = "selected"; }
                        sel.append('<option ' + selected + ' value="' + data[i].id + '">' + data[i].nom + '</option>');
                    }
                    setTimeout(function () {
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)

                } else {
                    setTimeout(function () {
                        $('#' + id2 + '>option:selected').attr('selected', false);
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)
                }

            }, "json");
    } else if (o == "CARGARSEMANAS") {

        var sel3 = $('#' + id3); // ano
        var ano = sel3.val();

        var sel2 = $('#' + id2); // mes
        var mes = sel2.val();

        var emp = $('#' + emp).val();

        var sel = $('#' + id); // semana
        var val = sel.attr('data-id'); //VALOR SELECCIONADO
        sel.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        $.post('funciones/fnGeneral.php', { opcion: o, val: val, ano: ano, mes: mes, emp: emp },
            function (data) {
                sel.selectpicker('refresh').empty();
                sel.append('<option value=""></option>');
                if (data.length) {
                    $('#' + id + '>option:selected').attr('selected', false);
                    for (var i = 0; i < data.length; i++) {
                        var selected = "";
                        if (data[i].id == val) { selected = "selected"; }
                        sel.append('<option data-subtext="' + data[i].subSem + '" class="' + data[i].classSem + '" data-inicio="' + data[i].inicio + '" data-fin="' + data[i].fin + '"  ' + selected + ' value="' + data[i].id + '">' + data[i].nom + ' (' + data[i].subtext + ')</option>');
                    }
                    setTimeout(function () {
                        $('#' + id).attr('data-style', $('#' + id + '>option:selected').attr('class'));
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)

                } else {
                    setTimeout(function () {
                        $('#' + id).attr('data-style', '');
                        $('#' + id + '>option:selected').attr('selected', false);
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)
                }
                if (mod == "JORNADA") //CREACION EDICIO O APROBACIO DE JORNADA
                {
                    setTimeout(function () {
                        CRUDJORNADA('CARGARSEMANA', '')
                    }, 200)
                }
                if (mod == "PLANILLA") //FILTRO DE PLANILLAS
                {
                    setTimeout(function () {
                        //CRUDJORNADA('LISTAPLANILLAS', '')
                    }, 200)
                }

            }, "json");
    } else if (o == "CARGARHORAS") {
        var sel2 = $('#' + id2);
        var hora = sel2.val();
        var tr = sel2.parent('tr');
        //var idh = tr.attr('data-id');
        //var ids = tr.attr('data-ids');
        // if (act == 0) {
        var sel = $('#' + id);
        var val = sel.val(); //VALOR SELECCIONADO
        sel.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        $.post('funciones/fnGeneral.php', { opcion: o, val: val, hora: hora, hf: mod },
            function (data) {
                sel.selectpicker('refresh').empty();
                sel.append('<option value=""></option>');
                if (data.length) {
                    $('#' + id + '>option:selected').attr('selected', false);
                    for (var i = 0; i < data.length; i++) {
                        var selected = data[i].selected;
                        sel.append('<option ' + selected + ' value="' + data[i].id + '">' + data[i].literal + '</option>');
                    }
                    setTimeout(function () {
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)

                } else {
                    setTimeout(function () {
                        $('#' + id + '>option:selected').attr('selected', false);
                        sel.selectpicker('refresh').trigger('change');
                    }, 100)
                }

            }, "json");
        // } else {

        //     setTimeout(function() {
        //         CRUDJORNADA('ACTUALIZARHORA', idh, ids)
        //     }, 200)

        // }
    }
}

function localStora(input, valor) {
    if (typeof (Storage) !== "undefined") {
        // Code for localStorage/sessionStorage.
        // Store
        localStorage.setItem(input, valor);
    } else {
        error("Sorry! No Web Storage support..")
    }
}

function formato_numero(numero, decimales, separador_decimal, separador_miles) { // v2007-08-06
    numero = parseFloat(numero);
    if (isNaN(numero)) {
        return "";
    }
    if (decimales !== undefined) {
        // Redondeamos
        numero = numero.toFixed(decimales);
    }

    // Convertimos el punto en separador_decimal
    numero = numero.toString().replace(".", separador_decimal !== undefined ? separador_decimal : ",");

    if (separador_miles) {
        // Añadimos los separadores de miles
        var miles = new RegExp("(-?[0-9]+)([0-9]{3})");
        while (miles.test(numero)) {
            numero = numero.replace(miles, "$1" + separador_miles + "$2");
        }
    }

    return numero;
}

function formatCurrency(amount, currencySymbol) {
    // Redondea el monto a dos decimales
    amount = Math.round(amount * 100) / 100;

    // Separa la parte entera de la parte decimal
    const parts = amount.toString().split('.');
    const integerPart = parts[0];
    const decimalPart = parts[1] || '00';

    // Agrega comas como separadores de miles en la parte entera
    const integerPartWithCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Crea la cadena formateada con el símbolo de la moneda
    const formattedCurrency = currencySymbol + integerPartWithCommas + '.' + decimalPart;

    return formattedCurrency;
}