var app = angular.module('myApp', [
    'ui.router',
    //'ui.bootstrap',
    'oc.lazyLoad'
    //,
    //'angularFileUpload'

])

app.config(['$stateProvider', '$urlRouterProvider', '$ocLazyLoadProvider', 'JS_REQUIRES', function($stateProvider, $urlRouterProvider, $ocLazyLoadProvider, jsRequires) {

    // LAZY MODULES
    $ocLazyLoadProvider.config({
        debug: false,
        events: true,
        modules: jsRequires.modules
    });

    $urlRouterProvider.otherwise("/Inicio");

    $stateProvider
        .state('Inicio', {
            url: '/Inicio?Idvisita&Idpedido',
            templateUrl: 'modulos/home/home.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("DASHBOARD");
                $('#titlemodulo2').html("Home");
                $('#titlemodulo3').html("<a href='#/Inicio'>Dashboard</a>");
                $('#btnnuevo').hide();
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                //$("body").PushMenu('toggle');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }

                CRUDINFORMES('CONTADORHOME');
                INICIALIZARCONTENIDO();
                setTimeout(function() {
                    CRUDINFORMES('GRAFICO');
                }, 1000);
            }
        })
        .state('InformesContabilidad', {
            url: '/Informes/Contabilidad',
            templateUrl: 'modulos/informes/informes.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("Informe Contabilidad");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Informes");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDINFORMES('FILTROSCONTABILIDAD', '');
                // CRUDINFORMES($stateParams.Informe);
            }
        })
        .state('InformesHoras', {
            url: '/Informes/Horas',
            templateUrl: 'modulos/informes/informes.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("Informe Horas");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Informes");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDINFORMES('FILTROSHORAS', '');
                // CRUDINFORMES($stateParams.Informe);
            }
        })
        .state('Perfiles', {
            url: '/Admin/Perfiles',
            templateUrl: 'modulos/perfiles/perfiles.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN PERFILES");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Perfiles");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDPERFIL('NUEVO')");
                localStora('idvisita', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDPERFIL('FILTROS', '');

            }
        })
        .state('Usuarios', {
            url: '/Admin/Usuarios',
            templateUrl: 'modulos/usuarios/usuarios.php',
            controller: function($scope, $stateParams) {
                localStora('lstipousuario', 1);
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN USUARIOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Usuarios");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDUSUARIOS('NUEVO')");
                localStora('idvisita', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDUSUARIOS('FILTROS', '');
                setTimeout(function() {
                    CRUDUSUARIOS('VALORESACTUAL', '');
                }, 500);
            }
        })
        .state('Cencos', {
            url: '/Admin/Cencos',
            templateUrl: 'modulos/cencos/cencos.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN CENTRO DE COSTOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Centro de Costos");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDCENCOS('NUEVO')");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDCENCOS('FILTROS', '');
            }
        })
        .state('Obras', {
            url: '/Admin/Obras',
            templateUrl: 'modulos/obras/obras.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN OBRAS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Obras");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDOBRAS('NUEVO')");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDOBRAS('FILTROS', '');
            }
        })
        .state('Horarios', {
            url: '/Admin/Horarios',
            templateUrl: 'modulos/horarios/horarios.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN HORARIOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Horarios");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDHORARIOS('NUEVO')");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDHORARIOS('FILTROS', '');
            }
        })
        .state('Empleados', {
            url: '/Admin/Empleados',
            templateUrl: 'modulos/usuarios/usuarios.php',
            controller: function($scope, $stateParams) {
                localStora('lstipousuario', 2);
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN EMPLEADOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Empleados");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDUSUARIOS('NUEVO')");
                localStora('idvisita', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                //CRUDUSUARIOS('LISTAUSUARIOS','');
                CRUDUSUARIOS('FILTROS', 'Todos');
                setTimeout(function() {
                    //CRUDUSUARIOS('VALORESACTUAL','');
                }, 500);
            }
        })
        .state('Motivos', {
            url: '/Admin/Motivos',
            templateUrl: 'modulos/motivos/motivos.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN MOTIVOS RECHAZO");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Motivos");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDMOTIVOS('NUEVO')");
                localStora('idvisita', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDMOTIVOS('LISTAMOTIVOS', '');

            }
        })
        .state('TipoDocumentos', {
            url: '/Admin/TipoDocumentos',
            templateUrl: 'modulos/documentos/tipodocumentos.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN TIPO DOCUMENTOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Tipo Documentos");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDTIPODOCUMENTO('NUEVO')");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDTIPODOCUMENTO('LISTATIPODOCUMENTO', '');
            }
        })
        .state('Planillas', {
            url: '/Jornada/Planillas?TIP&TIT',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("CONSULTA PLANILLAS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html($stateParams.TIT);
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDJORNADA('FILTROS', '');

            }
        })
        
        .state('NewJornada', {
            url: '/Jornada/New',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {

                localStorage.setItem('lstiporegistro', "1");
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>REGISTRO TIEMPO LABORAL</strong>");
                $('#titlemodulo2').html("Registro");
                $('#titlemodulo3').html("Jornada");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDJORNADA('NUEVO');
            }
        })
        .state('ApprovedJornada', {
            url: '/Jornada/Approved?ID',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {

                localStorage.setItem('lstiporegistro', "1");
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>APROBACIÓN TIEMPO LABORAL</strong>");
                $('#titlemodulo2').html("Aprobación");
                $('#titlemodulo3').html("Jornada");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDJORNADA('APPROVED', $stateParams.ID);
            }
        })
        .state('EditJornada', {
            url: '/Jornada/Edit?ID',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                //localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>EDICIÓN JORNADA</strong>");
                $('#titlemodulo2').html("Edición");
                $('#titlemodulo3').html("Jornada");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDJORNADA('EDITAR', $stateParams.ID);

            }
        })

        //LIQUIDACION

        .state('NewSettle', {
            url: '/Liquidar/NewSettle',
            templateUrl: 'modulos/jornada/liquidar.php',
            controller: function($scope, $stateParams) {

                localStorage.setItem('lstiporegistro', "1");
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>LIQUIDADOR</strong>");
                $('#titlemodulo2').html("Nueva");
                $('#titlemodulo3').html("Liquidación");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDLIQUIDAR('NUEVALIQUIDACION');

            }
        })
        .state('Liquidaciones', {
            url: '/Liquidar/List?TIP&TIT',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("");
                $('#titlemodulo2').html("Consulta");
                $('#titlemodulo3').html($stateParams.TIT);
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDLIQUIDAR('FILTROS', '');

            }
        })
        .state('LiquidarObras', {
            url: '/Liquidar/LiquidarObras?TIP&TIT',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("");
                $('#titlemodulo2').html("Consulta");
                $('#titlemodulo3').html($stateParams.TIT);
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDLIQUIDAR('FILTROSLIQUIDAROBRA', '');

            }
        })
        .state('Compensatorios', {
            url: '/Liquidar/Compensatorios',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>COMPENSATORIOS</strong>");
                $('#titlemodulo2').html("Compensatorios");
                $('#titlemodulo3').html($stateParams.TIT);
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDINFORMES('FILTROSCOMPENSATORIOS', '');
            }
        })
        .state('AlertaLimite', {
            url: '/Alertas/AlertaLimite',
            templateUrl: 'modulos/jornada/jornada.php',
            controller: function($scope, $stateParams) {
                localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>USUARIO QUE EXCEDEN LIMITE HORAS SEMANA</strong>");
                $('#titlemodulo2').html("Alertas");
                $('#titlemodulo3').html($stateParams.TIT);
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDINFORMES('FILTROSLIMITE', '');
            }
        })
        .state('ApprovedSettle', {
            url: '/Liquidar/ApprovedSettle?ID',
            templateUrl: 'modulos/jornada/liquidar.php',
            controller: function($scope, $stateParams) {

                localStorage.setItem('lstiporegistro', "1");
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>APROBACIÓN LIQUIDACIÓN</strong>");
                $('#titlemodulo2').html("Aprobación");
                $('#titlemodulo3').html("Liquidación");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDLIQUIDAR('APPROVED', $stateParams.ID);
            }
        })
        .state('EditSettle', {
            url: '/Liquidar/EditSettle?ID',
            templateUrl: 'modulos/jornada/liquidar.php',
            controller: function($scope, $stateParams) {
                //localStorage.setItem('lstipoconsulta', $stateParams.TIP);
                $('#divfooter').html("");
                $('#titlemodulo1').html("<strong>EDICIÓN LIQUIDACIÓN</strong>");
                $('#titlemodulo2').html("Edición");
                $('#titlemodulo3').html("Liquidación");
                $('#btnnuevo').hide();
                $('#btnnuevo').attr('onclick', "");
                $('#divfiltros').html("");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDLIQUIDAR('EDITAR', $stateParams.ID);

            }
        })
        .state('cambiocontrasena', {
            url: '/InformacionPerfil',
            templateUrl: 'modulos/cambiarcontrasena/cambiocontrasena.php'
        })
        .state('Festivos', {
            url: '/Admin/Festivos',
            templateUrl: 'modulos/festivos/festivos.php',
            controller: function($scope, $stateParams) {
                $('#divfooter').html("");
                $('#titlemodulo1').html("GESTIÓN FESTIVOS");
                $('#titlemodulo2').html("Admin");
                $('#titlemodulo3').html("Festivos");
                $('#btnnuevo').show();
                $('#btnnuevo').attr('onclick', "CRUDFESTIVOS('NUEVO')");
                //$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                if (!$("body").hasClass('sidebar-collapse')) {
                    $("body").PushMenu('toggle');
                }
                CRUDFESTIVOS('FILTROS', '');
            }
        })
        .state('Ajustes', {
            url: '/acount/Ajustes?Op',
            templateUrl: 'modulos/cuenta/ajuste.php',
            controller: function($scope, $stateParams) {
                var $op = $stateParams.Op;
                if ($op == "Cue") {
                    console.log($op);
                    $('#licuenta').addClass('active');
                    $('#lidireccion').removeClass('active');
                    CRUDCUENTA('AJUSTECUENTA', '')
                }
            }
        });

    // Generates a resolve object previously configured in constant.JS_REQUIRES (config.constant.js)
    function loadSequence() {
        var _args = arguments;
        return {
            deps: ['$ocLazyLoad', '$q',
                function($ocLL, $q) {
                    var promise = $q.when(1);
                    for (var i = 0, len = _args.length; i < len; i++) {
                        promise = promiseThen(_args[i]);
                    }
                    return promise;

                    function promiseThen(_arg) {
                        if (typeof _arg == 'function')
                            return promise.then(_arg);
                        else
                            return promise.then(function() {
                                var nowLoad = requiredData(_arg);
                                if (!nowLoad)
                                    return $.error('Route resolve: Bad resource name [' + _arg + ']');
                                return $ocLL.load(nowLoad);
                            });
                    }

                    function requiredData(name) {
                        if (jsRequires.modules)
                            for (var m in jsRequires.modules)
                                if (jsRequires.modules[m].name && jsRequires.modules[m].name === name)
                                    return jsRequires.modules[m];
                        return jsRequires.scripts && jsRequires.scripts[name];
                    }
                }
            ]
        };
    }
}]);

app.constant('JS_REQUIRES', {
    //*** Scripts
    scripts: {
        //*** Javascript Plugins
        /*'multiselect': [
        	'dist/js/prettify.js',
        	'dist/css/bootstrap-multiselect.css',
        	'dist/js/bootstrap-multiselect.js',
        	'http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'],*/
        /*'multiselect': ['dist/css/checklist/jquery.multiselect.css',
        'dist/css/checklist/jquery.multiselect.filter.css',
        'dist/css/checklist/styleselect.css',
        'dist/css/checklist/prettify.css',
        'dist/css/checklist/jquery-ui.css',
        'http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',
        'dist/js/checklist/jquery.multiselect.js',
        'dist/js/checklist/jquery.multiselect.filter.js',
        'dist/js/checklist/prettify.js']*/
    }
});

app.controller('TabsDemoCtrl', function($scope, $window) {
    $scope.tabs = [
        { title: 'Dynamic Title 1', content: 'Dynamic content 1' },
        { title: 'Dynamic Title 2', content: 'Dynamic content 2', disabled: true }
    ];
    $scope.alertSector = function(g) {

    };
});
/*
 app.controller('AppController', ['$scope', 'FileUploader', function($scope, FileUploader) {
        var uploader = $scope.uploader = new FileUploader({
            url: 'modulos/actividades/upload.php'
        });

        // FILTERS

        uploader.filters.push({
            name: 'customFilter',
            fn: function(item , options) {
                return this.queue.length < 10;
            }
        });

        // CALLBACKS

        uploader.onWhenAddingFileFailed = function(item , filter, options) {
            console.info('onWhenAddingFileFailed', item, filter, options);
			$
        };
        uploader.onAfterAddingFile = function(fileItem) {
            console.info('onAfterAddingFile', fileItem);
			
        };
        uploader.onAfterAddingAll = function(addedFileItems) {
            console.info('onAfterAddingAll', addedFileItems);
			
        };
        uploader.onBeforeUploadItem = function(item) {
            console.info('onBeforeUploadItem', item);
			
        };
        uploader.onProgressItem = function(fileItem, progress) {
            console.info('onProgressItem', fileItem, progress);
			
        };
        uploader.onProgressAll = function(progress) {
            console.info('onProgressAll', progress);
			
        };
        uploader.onSuccessItem = function(fileItem, response, status, headers) {
            console.info('onSuccessItem', fileItem,response, status, headers);
			$('#archivo').replaceWith( $('#archivo').val('').clone( true ) );
        };
        uploader.onErrorItem = function(fileItem, response, status, headers) {
            console.info('onErrorItem', fileItem, response, status, headers);
			
        };
        uploader.onCancelItem = function(fileItem, response, status, headers) {
            console.info('onCancelItem', fileItem, response, status, headers);
			$('file').replaceWith( $('file').val('').clone( true ) );
        };
        uploader.onCompleteItem = function(fileItem, response, status, headers) {
            console.info('onCompleteItem', fileItem, response, status, headers);
			$('file').replaceWith( $('file').val('').clone( true ) );
        };
        uploader.onCompleteAll = function() {
            console.info('onCompleteAll');
			$('file').replaceWith( $('file').val('').clone( true ) );
        };

        console.info('uploader', uploader);
    }]);
*/



app.controller('ProgressCtrl', function($scope) {

    $scope.max = 100;

    $scope.cargar = function(v) {
        var value = v; // Math.floor(Math.random() * 100 + 1);
        var type;

        if (value < 25) {
            type = 'success';
        } else if (value < 50) {
            type = 'info';
        } else if (value < 75) {
            type = 'warning';
        } else {
            type = 'danger';
        }

        $scope.showWarning = type === 'danger' || type === 'warning';

        $scope.dynamic = value;
        $scope.type = type;
    };


});