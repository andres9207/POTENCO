function CRUDCARPETAS(o, id, fil, tip) {
    if (o == "LISTAFOLDER") {
        var per = ""; //$('#selperfil').val();
        if (id != "") {
            var div = id.replace('.', '_').replace('.', '_').replace('!', '_');
            $('#ullista_' + div).html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');
        } else {
            $("#divlistafolder").LoadingOverlay("show", {
                background: "rgba(125,162,14, 0.5)"
            });
            $('#divlistafolder').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');
        }
        $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id, fil: fil, per: per }, function(data) {
            if (id != "" && id != undefined) {
                console.log("Por aca3");
                $('#ullista_' + id).replaceWith(data);
            } else {
                $('#divlistafolder').html(data);
                $("#divlistafolder").LoadingOverlay("hide", true);
            }
        });
    } else if (o == "CONTENIDOFOLDER") {
        $('#divlistafolder li').css('backgroundColor', '#FFF');
        //$('#divlistacontenido li').css('backgroundColor','#FFF');

        //$('#txtfolderid').val(id);
        var div = id; //.replace('.','_').replace('.','_').replace('!','_');
        $('#li_' + div).css('backgroundColor', '#dff0d8');
        $('#hli_' + div).css('backgroundColor', '#dff0d8');
        //$('#txtfolderid').parsley().validate();
        $('#divlistacontenido').html('<div class="lds-facebook"><div></div><div></div><div></div></div>')
        $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id, file: fil }, function(data) {
            $('#divlistacontenido').html(data);
        });
    } else if (o == "COLLAPSEFOLDER") {
        var div = id; //.replace('.','_').replace('.','_').replace('!','_');
        var tog = $('#tog_' + div).attr('data-tog');
        var fil = $('#tog_' + div).attr('data-fil');
        var folderid = $('#tog_' + div).attr('data-folder');
        var $ICON = $('#tog_' + div).find('i');
        console.log(id);
        if (tog == "plus") {
            $('#tog_' + div).attr('data-tog', 'minus');
            $('#ullista_' + div).show();
            CRUDCARPETAS('CONTENIDOFOLDER', folderid, fil, '');
        } else {
            $('#tog_' + div).attr('data-tog', 'plus');
            //var div = folderid.replace('.','_').replace('.','_').replace('!','_');
            $('.ullista_' + div).replaceWith('<tr id="ullista_' + div + '"></tr>').hide();

        }
        $ICON.toggleClass('fa-plus fa-minus');
    } else if (o == "CREARCARPETA") {
        var carpeta = "";
        alertify.prompt("Digite el nombre de la nueva carpeta", "",
            function(evt, value) {
                if (value != "" && value != null) {
                    $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id, carpeta: value },
                        function(data) {
                            var res = data[0].res;
                            var msn = data[0].msn;
                            if (res == "ok") {
                                //$('.ullista_'+ id).replaceWith('<tr id="ullista_'+id+'"></tr>');

                                ok(msn);
                                if (id == "" || id == null || id == undefined) {
                                    setTimeout(function() {
                                        CRUDCARPETAS('CONTENIDOFOLDER', '', '', '');
                                    }, 1000);
                                } else {
                                    setTimeout(function() {
                                        CRUDCARPETAS('COLLAPSEFOLDER', id, '', '');
                                    }, 1000);
                                }
                            } else {
                                error(msn);
                            }
                        }, "json");
                } else {
                    error("Diligencie el nombre de la carpeta");
                }
            },
            function() {

            });
    } else if (o == "CREARFOLDER" || o == "CREARFOLDEROBRA" || o == "CREARFOLDERCONSORCIO") {
        $.post('modulos/drive/fnDrive.php', { opcion: o, id: id },
            function(data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok(msn);
                } else {
                    error(msn);
                }
            }, "json");
    } else if (o == "MOVERDOCUMENTOS") {
        $.post('modulos/drive/fnDrive.php', { opcion: o, id: id },
            function(data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok(msn);
                } else {
                    error(msn);
                }
            }, "json");
    } else if (o == "MOVERARCHIVO") {
        $.post('modulos/drive/fnDrive.php', { opcion: o, iddoc: id, idarchivo: fil },
            function(data) {
                var res = data[0].res;
                var msn = data[0].msn;
                if (res == "ok") {
                    ok(msn);
                } else {
                    error(msn);
                }
            }, "json");
    } else if (o == "ASIGNARPERMISOS") {
        var esc = $('#esc_' + id + ':checked').val();
        var lee = $('#lee_' + id + ':checked').val();
        var mod = $('#mod_' + id + ':checked').val();
        var eli = $('#eli_' + id + ':checked').val();
        var per = $('#selperfil').val();

        if (esc == "" || esc == undefined || esc == null) { esc = 0; }
        if (lee == "" || lee == undefined || lee == null) { lee = 0; }
        if (mod == "" || mod == undefined || mod == null) { mod = 0; }
        if (eli == "" || eli == undefined || eli == null) { eli = 0; }
        if (per == "") {
            error("Seleccionar el perfil");
        } else {

            $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id, per: per, esc: esc, lee: lee, mod: mod, eli: eli },
                function(data) {
                    var res = data[0].res;
                    var msn = data[0].msn;
                    if (res == "ok") {

                    } else {
                        error(msn);
                    }
                }, "json");
        }
    } else if (o == "ELIMINARCARPETA") {
        jConfirm("Realmente desea eliminar la carpeta seleccionada", "Diálogo Confirmación HD", null,
            function(r) {
                if (r == true) {
                    $.post('modulos/drive/fnDrive.php', { opcion: o, id: id }, function(data) {
                        var res = data[0].res;
                        var msn = data[0].msn;
                        if (res == "ok") {
                            ok(msn);
                            $('#ullista_' + id).fadeOut('slow');
                            //var table1 = $('#tbdirecciones').DataTable();
                            //var row = table1.row( '#rowd_' +id);
                            //var rowNode = row.node();
                            //row.remove().draw();
                        } else {
                            error(msn);
                        }
                    }, "json")
                } else {

                }
            });
    } else if (o == "CARGARARCHIVO") {
        $('#myModal2').modal('show');
        $('#btnguardar2').css('visibility', 'hidden');
        $('.progress').css('display', 'none');
        $('#myModalLabel2').html("Cargar Archivo");
        $('#contenido2').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

        $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id, fileid: fil, tip: tip }, function(data) {
            $('#contenido2').html(data);
        });
    } else if (o == "PREVIAARCHIVO") {
        $('#myModal2').modal('show');
        $('#btnguardar2').css('visibility', 'hidden');
        $('.progress').css('display', 'none');
        $('#myModalLabel2').html("Previa Archivo");
        $('#contenido2').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

        $.post('modulos/drive/fnDrive.php', { opcion: o, folderid: id }, function(data) {
            $('#contenido2').html($('<iframe src="' + data[0].url + '" frameborder="0" scrolling="no" id="myFrame"></iframe>'));

        }, "json");
    } else if (o == "CONFIGURARALMACENAMIENTO") {
        $('.modal-dialog').addClass('modal-lg');
        $('#btnguardar').css('display', 'none');
        $('#myModalLabel').html("Configuración Almacenamiento");
        $('#contenido').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

        $.post('modulos/drive/fnDrive.php', { opcion: o }, function(data) {
            $('#contenido').html(data);

        });
    } else if (o == "SELFOLDER2") {

        $('#spanfolder' + id).html('Cargando folders').removeAttr('onClick');

        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: id, folderid: fil }, function(data) {
            $('#spanfolder' + id).html(data);
            setTimeout(function() {
                //$('#listfolder' + id).selectpicker();
            }, 1000);

        });
    } else if (o == "SELMEDIO") {
        var med = $('input:radio[name=radmedio' + id + ']:checked').val();
        fil = "";
        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: id, folderid: fil, med: med }, function(data) {
            var res = data[0].res;
            var msn = data[0].msn;
            var medact = data[0].medact;
            if (res == "ok") {
                $('#spanfolder' + id).html(data);
                if (med == 1 && medact == 1) {
                    $('#btnmigrar' + id).removeClass('hide');
                    $('#spanfolder' + id).removeAttr('onClick');
                } else if (med == 0 && medact == 1) {
                    $('#spanfolder' + id).removeAttr('onClick').html("Local");
                    $('#btnmigrar' + id).addClass('hide');
                    $('#spanfolder' + id).removeClass('toltipfolder').removeClass('tooltipstered');
                } else if (med == 1 && medact == 0) {
                    $('#btnmigrar' + id).removeClass('hide');
                    $('#spanfolder' + id).addClass('toltipfolder').html("Seleccionar");
                    setTimeout(function() {
                        TooltipDrive();
                    }, 500);
                }
            } else {
                error(msn);
            }

        }, "json");

    } else if (o == "UPDFOLDER") {
        var fol = $('#listfolder' + id).val();
        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: id, folderid: fol }, function(data) {
            var res = data[0].res;
            var msn = data[0].msn;

            if (res == "ok") {
                ok(msn);
            } else {
                error(msn);
            }

        }, "json");
    } else if (o == "UPDFOLDER2") {
        var fol = fil;
        $.post('modulos/drive/fnDrive.php', { opcion: "UPDFOLDER", adj: id, folderid: fol }, function(data) {
            var res = data[0].res;
            var msn = data[0].msn;
            var nam = data[0].nam;

            if (res == "ok") {
                ok(msn);
                $('#spanfolder' + id).html(nam);
                $('#spanfolder' + id).attr('data-folder', fol);
            } else {
                error(msn);
            }

        }, "json");
    } else if (o == "UPDNOMENCLATURA") {
        var nom = $('#nomenclatura' + id).val();
        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: id, nom: nom }, function(data) {
            var res = data[0].res;
            var msn = data[0].msn;

            if (res == "ok") {
                ok(msn);
            } else {
                error(msn);
            }

        }, "json");
    } else if (o == "MIGRARFOLDER") {
        jConfirm('Realmente desea migrar la información al onedrive?', 'Dialogo de Confirmación HD', null, function(r) {
            if (r == true) {
                $('#btnguardar').css('display', '').html('Volver').attr('name', 'CONFIGURARALMACENAMIENTO');
                $('#contenido').html('Migrando informacion por favor espere..<br><div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

                $.post('modulos/drive/fnDrive.php', { opcion: o, adj: id }, function(data) {

                    $('#contenido').html(data);
                });
            } else {
                return false;
            }
        });

    } else if (o == "ASIGNARFOLDER") {
        var adj = tip; //adjunto
        var idr = id;
        var ven = fil;
        $('#myModal').modal('show');
        $('.modal-dialog').addClass('modal-lg');
        $('#btnguardar').css('display', 'none');
        $('#myModalLabel').html("Asignación Almacenamiento");
        $('#contenido').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: adj, idr: idr, ven: ven }, function(data) {
            $('#contenido').html(data);
        });
    } else if (o == "DETALLEASIGNARFOLDER") {
        var adj = tip; //adjunto
        var idr = id;
        $('#divdetallefolder').html('<div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>');

        $.post('modulos/drive/fnDrive.php', { opcion: o, adj: adj, idr: idr }, function(data) {
            $('#divdetallefolder').html(data);
        });
    } else if (o == "GUARDARASIGNARFOLDER") {
        var adj = tip; //adjunto
        var idr = id; //registro
        var fol = fil; //Folder
        if (adj == "") {
            error("Seleccionar el adjunto al cual le va a asignar el folder");
        } else {
            $.post('modulos/drive/fnDrive.php', { opcion: o, adj: adj, folderid: fol, idr: idr }, function(data) {
                var res = data[0].res;
                var msn = data[0].msn;
                var nam = data[0].nam;

                if (res == "ok") {
                    ok(msn);
                    if (adj == 4) {
                        var table2 = $('#tbPagos').DataTable();
                        table2.row('#rowp_' + idr).draw(false);
                    } else if (adj == 7 || adj == 8 || adj == 9 || adj == 10 || adj == 11) {
                        var table = $('#tbEmpresas').DataTable();
                        table.row('#row_' + idr).draw();
                    }
                    //$('#spanfolder' + id).html(nam);
                    //$('#spanfolder' + id).attr('data-folder', fol); 
                } else {
                    error(msn);
                }

            }, "json");
        }
    }
}

function CARGARARCHIVOS(adj, tipo, id, adjunto, progreso, ruta, img, folderid, fileid, medio) {
    var elem = $('#' + progreso + "Bar");

    var fileSize = $('#' + adjunto)[0].files[0].size / 1024 / 1024;

    if (fileSize > 8) { // 8M
        alert("El archivo que ha intentado adjuntar es mayor de 8 Mb, si desea cambie el tamaño del archivo y vuelva a intentarlo. Tamaño de archivo:" + fileSize + "M");
        elem.css('width', '0%');
    } else {
        $('#' + progreso).css('display', '');
        $("#" + adjunto).upload('modulos/drive/cargararchivo.php', {
                adj: adj,
                tipo: tipo, //1 crear 2 editar
                id: id,
                folderid: folderid,
                fileid: fileid,
                input: adjunto,
                med: medio
            },
            function(respuesta) {
                //Subida finalizada.
                elem.css('width', 0 + '%');
                elem.html(0 * 1 + '%');
                //$("#" + p).val(0);
                var data = $.trim(respuesta);
                if ($.trim(data) == "error1") {
                    //FORMATO ACTA DE VISITA ACLARATORIA
                    error('El archivo a importar no se cargo');
                    elem.css('width', '0%');
                } else if ($.trim(data) == "error2") {
                    error('No ha seleccionado ningun archivo a subir');
                    elem.css('width', '0%');
                } else if ($.trim(data) == "errorcompra1") {
                    error('No hay ningun cliente seleccionado al cual asociar la autorización');
                    elem.css('width', '0%');
                } else if ($.trim(data) == "errorcompra2") {
                    error('No hay ningun proveedor seleccionado al cual asociar proforma');
                    elem.css('width', '0%');
                } else {
                    ok("Archivo cargado correctamente");
                    if (tipo == 1 && folderid == "") {
                        $('#' + ruta).html(data);
                        $('#' + img).attr('src', data);

                    } else if (tipo == 2 && folderid == "") {
                        $('#' + img).attr('src', data);
                        if (adj == "1" && $('#imgperfil').length && $('#idusuario').val() == id) {
                            $('#imgperfil').attr('src', data);
                            $('#imgperfil1').attr('src', data)
                        }
                    } else if (folderid != "") {
                        $('#' + ruta).attr('data-medio', medio);
                        //var f = document.getElementById(img + '2');
                        //f.src = data;
                        /*$('#' + img).attr('src',"modulos/drive/fotodrive.php?fileid="+data);
						if(adj=="1" && $('#imgperfil').length && $('#idusuario').val()==id)
                    	{
                    		$('#imgperfil').attr('src',"modulos/drive/fotodrive.php?fileid="+data);
                    		$('#imgperfil1').attr('src',"modulos/drive/fotodrive.php?fileid="+data)
                    	}*/


                        $('#' + img).attr('src', data);
                        if (adj == "1" && $('#imgperfil').length && $('#idusuario').val() == id) {
                            $('#imgperfil').attr('src', data);
                            $('#imgperfil1').attr('src', data);
                        }
                        //$('#'+img).addClass('hide');
                        //$('#'+img+'2').removeClass('hide');
                    }
                }
            },
            function(progreso, valor) {
                //Barra de progreso.
                if (valor <= 25) {
                    elem.css("background-color", "red");
                } else if (valor > 25 && valor <= 50) {
                    elem.css("background-color", "blue");
                } else if (valor > 50 && valor <= 99) {
                    elem.css("background-color", "orange");
                } else if (valor == 100) {
                    elem.css("background-color", "green");
                }
                elem.css('width', valor + '%');
                elem.html(valor * 1 + '%');
                //console.log(valor);
                //$("#" + p).val(valor);
            });
        // var bar = $('.bar');
        // var percent = $('.percent');
    }
}

function CRUDDRIVE(o, v, tip, id, a, p) //opcion, ventana,tip=nuevo u/o edicion, id, p=progreso a =  adjunto
{
    var folderid = $('#txtfolderid').val();
    var fileid = $('#txtfileid').val();
    var elem = $('#' + p + "Bar");

    var fileSize = $('#' + a)[0].files[0].size / 1024 / 1024;

    if (fileSize > 8) { // 8M
        alert("El archivo que ha intentado adjuntar es mayor de 8 Mb, si desea cambie el tamaño del archivo y vuelva a intentarlo. Tamaño de archivo:" + fileSize + "M");
        elem.css('width', '0%');
    } else {
        $('#' + p).css('display', '');
        $("#" + a).upload('modulos/drive/uploadarchivo.php', {
                opc: o,
                ven: v,
                tip: tip,
                id: id,
                folderid: folderid,
                fileid: fileid

            },
            function(respuesta) {
                //Subida finalizada.
                elem.css('width', 0 + '%');
                elem.html(0 * 1 + '%');
                //$("#" + p).val(0);
                var data = $.trim(respuesta);
                if ($.trim(data) == "error1") {
                    //FORMATO ACTA DE VISITA ACLARATORIA
                    error('El archivo a importar no se cargo');
                    elem.css('width', '0%');

                } else if ($.trim(data) == "error2") {
                    error('No ha seleccionado ningun archivo a subir');
                    elem.css('width', '0%');

                } else {
                    //$("#targetLayer").html(data);
                    //$('#' + p).hide(8000);
                    //RESULTADOADJUNTO(tic);
                    ok(data);
                    setTimeout(function() {
                        CRUDCARPETAS('COLLAPSEFOLDER', folderid, '', '');
                    }, 1000);

                }


            },
            function(progreso, valor) {
                //Barra de progreso.
                if (valor <= 25) {
                    elem.css("background-color", "red");
                } else if (valor > 25 && valor <= 50) {
                    elem.css("background-color", "blue");
                } else if (valor > 50 && valor <= 99) {
                    elem.css("background-color", "orange");
                } else if (valor == 100) {
                    elem.css("background-color", "green");
                }
                elem.css('width', valor + '%');
                elem.html(valor * 1 + '%');
                //console.log(valor);
                //$("#" + p).val(valor);
            });
        // var bar = $('.bar');
        // var percent = $('.percent');
    }

}


function convertTree(table, folder, opc, fileid) {
    console.log("file:" + fileid + " - " + table);

    $("#" + table).treetable({ expandable: true });
    // Highlight selected row
    $("#" + table + " tbody").on("mousedown", "tr", function() {
        //var reg = $('#opcregistro').val();
        $(".selected").not(this).removeClass("selected");
        $(this).toggleClass("selected");
        if ($(this).hasClass('selected')) {
            var parentid = $(this).attr('data-tt-parent-id');
            var folderid = $(this).attr('data-tt-id');
            console.log(folderid);
            //$('#txtfolderid').val(folderid);
            /*if(parentid!="" && parentid!=undefined && parentid!=null)
            {
            	setTimeout(function(){
            		CRUDCARPETAS('UPDFOLDER2',opc,folderid,'')
            	},100);
            }
            if(reg=="1"  && opc=="")
            {
            	
            }*/
            if (opc == "contenido") {
                var idp = $(this).attr('data-parent');
                if (idp != null && idp != undefined && idp != "") {
                    CRUDDRIVE('CONTENIDOFOLDER', idp, '', '')
                }
            }
        } else {
            console.log("Se deselecciono");
            //$('#txtfolderid').val('');
        }
    });

    // Drag & Drop Example Code
    /*$("#"+table+" .file, #"+table+" .folder").draggable({
      helper: "clone",
      opacity: .75,
      refreshPositions: true,
      revert: "invalid",
      revertDuration: 300,
      scroll: true
    });*/
    /*

    $("#"+table+" .folder").each(function() {
      $(this).parents("#"+table+" tr").droppable({
        accept: ".file, .folder",
        drop: function(e, ui) {
          var droppedEl = ui.draggable.parents("tr");
          $("#"+table).treetable("move", droppedEl.data("ttId"), $(this).data("ttId"));
        },
        hoverClass: "accept",
        over: function(e, ui) {
          var droppedEl = ui.draggable.parents("tr");
          if(this != droppedEl[0] && !$(this).is(".expanded")) {
            $("#"+table).treetable("expandNode", $(this).data("ttId"));
          }
        }
      });
    });*/
    if (fileid != "" && fileid != undefined && fileid != null) {
        console.log("file2:" + folder + " - " + table);
        setTimeout(function() {
            $("#" + table).treetable("expandNode", folder);
            $('[data-tt-id="' + fileid + '"]').addClass('selected');
        }, 1000);
    } else
    if (folder != "" && folder != undefined && folder != null) {
        console.log("folder:" + folder + " - " + table);

        setTimeout(function() {

            $("#" + table).treetable("expandNode", folder);

        }, 1000);
    }
    //$("#" + table).treetable("node", "1z02zknI58CZGsatn1NiYztc36WGOF9iZ");

}

function TooltipDrive() {
    $('.toltipfolder').tooltipster({
        content: 'Cargando informacion por favor espere..<br><div id="loader-wrapper"><div id="loader"></div><div class="loader-section section-left"></div><div class="loader-section section-right"></div></div>',
        multiple: true,
        contentAsHTML: true,
        trigger: 'click',
        theme: 'tooltipster-shadow',
        contentCloning: false,
        interactive: true,
        animation: 'slide',
        updateAnimation: 'scale',
        position: 'bottom',
        positionTracker: false,
        distance: 1,
        offsetY: -12,
        arrow: false,
        functionBefore: function(origin, continueTooltip) {
            continueTooltip();
            var id = $(this).attr('data-id');
            var folderid = $(this).attr('data-folder');
            console.log("id" + id);

            $.ajax({
                type: 'POST',
                url: 'modulos/drive/fnDrive.php',
                data: {
                    opcion: "SELFOLDER2",
                    adj: id,
                    folderid: folderid
                },
                success: function(data) {
                    // update our tooltip content with our returned data and cache it
                    /*origin.tooltipster({
                        content: data,
                        multiple: true,
                        contentAsHTML: true,
                        trigger: 'click',
                        theme: 'tooltipster-shadow',
                        contentCloning: false,
                        interactive: true,
                        animation: 'slide',
                        updateAnimation: 'scale',
                        position: 'bottom',
                        positionTracker: false,
                        //multiple:true,  
                        distance: 1,
                        offsetY: -12,
                        arrow: false,
                    });*/

                    origin.tooltipster('update', data).data('ajax', 'cached');
                }
            });

        },
        functionAfter: function(origin) {
            //alert('The tooltip has closed!'); 
            //origin.tooltipster();
        }
    });
}