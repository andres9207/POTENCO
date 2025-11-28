const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
  });

function ok(msn){   
    Toast.fire({
        type: 'success',
        title: msn
    })
}
function error(msn){
    Toast.fire({
        type: 'error',
        title: msn
    })
}

function ok2(msn)
{
	  swal({
		position: 'center',
		type: 'success',
		title: msn,
		showConfirmButton: true,
		confirmButtonText:"Aceptar"
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

function setpreview(rut,inp,formu,tmp = 'tmp') // creamos la función
 { 
	 var datos = new FormData();   
	  
     datos.append('ruta',rut);
     datos.append('input',$( '#'+inp )[0].files[0]);
	 datos.append('formu',formu);
	 datos.append('tmp',tmp);
 
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
            async:false,       
            contentType: false,
            cache: false,
            processData:false,
            dataType:"json",
            crossDomain : true,
            }).                               
    done( function(data)
    {
        console.log(data);
        var res = data.res;
        var url = data.url;
        var msn = data.msn;
        console.log(msn);
        
        if($.trim(res)=="error")
        {           
            error("No se cargo el archivo");
            ///$("#loadMe").modal("hide");
        }                
        else if(jQuery.trim(res)=="ok")
        {
            $('#'+rut).html(url);
            //ok("Archivo cargado");
            //$("#loadMe").modal("hide");
        }
    })
    .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
         result = true;
         //$("#loadMe").modal("hide");
     }
    });
}

function INICIARSESIONNUEVO(email, pass) {
	var datos = new FormData();
	datos.append("loginmail", email);
	datos.append("loginpass", pass);

	$.ajax({
		url: "data/validarUsuarios.php",
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
        var msn = data.msn;
        var url = data.url;
        console.log("res=" + res);
        if (jQuery.trim(res) == "error") {

            jQuery('#btnlogin').button('reset');
            error(msn);
            num = 1;
            result = false;
        } else if (jQuery.trim(res) == "ok") {
            //localStorage.removeItem('notapedido');
            num = 1;
            window.location.href = url;
            //window.location.reload();
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        if (console && console.log) {
            console.log("La solicitud a fallado: " + textStatus);
            result = true;
        }
    });
}

function INICIARSESION() {
	//var formData = new FormData(jQuery("#formlogin"));
	//var codcat = grecaptcha.getResponse();
    //console.log("codigo captcha" + codcat);
    
    $('#frmlogin').parsley().validate();
    if ($('#frmlogin').parsley().isValid())
    {
        var email =  $('#loginmail').val();
        var pass = $('#loginpass').val();
        var datos = new FormData();
        datos.append("loginmail", email);
        datos.append("loginpass", pass);


        var num = 0;

        $("#frmlogin").on('submit', (function (e) {
            e.preventDefault();
            if (num == 0) {
                $.ajax({
                    url: "data/validarUsuarios.php",
                    type: "POST",
                    data: datos,//new FormData(this),
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
                    var msn = data.msn;
                    var url = data.url;
                    console.log("res=" + res);
                    if (jQuery.trim(res) == "error") {
                        //grecaptcha.reset();
                        //jQuery('#btnlogin').button('reset');
                        error(msn);
                        num = 1;
                        result = false;
                    } else if (jQuery.trim(res) == "ok") {
                        num = 1;
                        window.location.href = url; 
                        //window.location.reload();
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    if (console && console.log) {
                        console.log("La solicitud a fallado: " + textStatus);
                        result = true;
                    }
                });
            }
        }));
    }
}

function RECUPERARCONTRASENA()
{
	var datos = new FormData();
	var email = $('#txtrecuperar').val();
	datos.append("opcion", 'RECUPERARCONTRASENA');
	datos.append("email", email);
	var num = 0;

	$("#formrecuperar").on('submit',(function(e)
    {	
		e.preventDefault();
		if(num==0)
		{
			jQuery.ajax({
	        url: "funciones/usuarios/fnUsuarios.php",
	        type: "POST",
	       	data: datos,     
	        async:false,       
	        contentType: false,
	        cache: false,
	        processData:false,
	        dataType:"json",
	        crossDomain : true,
	        }).                               
	        done( function(data)
	        {
	        	var res = data[0].res;
				var msn  =data[0].msn;
				if(res=="ok")
				{
					ok(msn);
					setTimeout(function(){
                        window.location.href="index.php";
						//CRUDUSUARIOS('LOGIN','','');
					},3000);
				}
				else
				{
					error2(msn);
				}             
	        })
	       .fail(function( jqXHR, textStatus, errorThrown ) {
			     if ( console && console.log ) {
			         console.log( "La solicitud a fallado: " +  textStatus);
	                 result = true;
			     }
			});
		}
	}));
}

function RESTABLECER(cod)
{
	var con1 = $("#contrasena1").val();
	var con2 = $("#contrasena2").val();
	if(con1=="")
	{
		error("Ingresar la nueva contraseña");
	}
	else if(con2=="")
	{
		error("Ingresar la contraseña de verificacion");
	}
	else if(con1!=con2)
	{
		error("Las contraseñas no coinciden");
	}
	else
	{
		$.post('funciones/usuarios/fnUsuarios.php', {opcion: 'RESTABLECER',con1:con1,con2:con2,cod:cod}, function(data, textStatus, xhr) {
			var res = data[0].res;
            var msn = data[0].msn;
            var ema = data[0].ema;
            
			if(res=="ok")
			{
				ok(msn);
                setTimeout(function(){
                    INICIARSESIONNUEVO(ema,con1)
                },4000);
                /*swal({
				title: msn,
				type: 'info',
				html:"<a onclick=INICIARSESIONNUEVO('"+ema+"','"+con1+"')>Iniciar sesión</a>",
				showCloseButton: true,
				showCancelButton: false,
				focusConfirm: false,
				confirmButtonText:
				'<i class="fa fa-thumbs-up"></i> Ok!',				
				})*/
			}
			else
			{
				error(msn);
			}
			/*optional stuff to do after success */
		},"json");
	}

	
	
}