var map;
var service;
var infowindow;
var placeSearch, autocomplete,geocoder;
var countryRestrict = {'country': 'CO'};

function permisoUbicacion()
{
    geocoder = new google.maps.Geocoder;
    infowindow = new google.maps.InfoWindow();
    if (navigator.geolocation) {
        console.log("Entro por aca posicion actual 2");
        navigator.geolocation.getCurrentPosition(function(position) {
          var pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            geometry:{
              location:{
                lat: position.coords.latitude,
                lng: position.coords.longitude,
              }
            }              
          };
          
          //infowindow.setPosition(pos);
          console.log(position);
          //infowindow.setContent('Location found.' + position);
          //infowindow.open(map);
          //map.setCenter(pos);

          //clearMarkers(); 
          //setTimeout(dropMarker(0),  100);
          //createMarker(pos);
          //geocodeLatLng(geocoder,/*map,infowindow,*/pos.lat,pos.lng)
          
        }, function() {
          handleLocationError(true, infowindow, map.getCenter());
        });
      } 
      else 
      {
        console.log("entro por aca 2");
        // Browser doesn't support Geolocation
        handleLocationError(false, infowindow, map.getCenter());
      }
}

function setUbicacion(o,id)
{
    geocoder = new google.maps.Geocoder;
    infowindow = new google.maps.InfoWindow();

    if (navigator.geolocation) {
        console.log("Entro por aca posicion actual 2");
        navigator.geolocation.getCurrentPosition(function(position) {
          var pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            geometry:{
              location:{
                lat: position.coords.latitude,
                lng: position.coords.longitude,
              }
            }              
          };
          
          //infowindow.setPosition(pos);
          console.log(position);
          //infowindow.setContent('Location found.' + position);
          //infowindow.open(map);
          //map.setCenter(pos);

          //clearMarkers(); 
          //setTimeout(dropMarker(0),  100);
          //createMarker(pos);
          geocodeLatLng(geocoder,/*map,infowindow,*/pos.lat,pos.lng,o,id)
          
        }, function() {
          handleLocationError(true, infowindow, map.getCenter());
        });
      } 
      else 
      {
        console.log("entro por aca 2");
        // Browser doesn't support Geolocation
        handleLocationError(false, infowindow, map.getCenter());
      }
}

function geocodeLatLng(geocoder,/* map, infowindow,*/lat,lng,o,id) {
    //var input = document.getElementById('latlng').value;
    //var latlngStr = input.split(',', 2);
    var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};
  
    //document.getElementById("latitud").value = lat;
    //document.getElementById("longitud").value = lng;  
   
      geocoder.geocode({'location': latlng}, function(results, status) {
      if (status === 'OK') {
          if (results[0]) 
          {
            var ciudad = "";
            var barrio = "";
            var sector = "";
            var pais = "";
            var corta = "";
            var larga = "";
            var ciudades = [];
            var paises = [];
            var barrios = [];
  
            for (var i = 0; i < results.length; i++) 
            {
              console.log("Formated address: " + results[i].formatted_address);
              console.log(results[i].address_components[0].short_name + " tipo:" + results[i].types[0]);
                if (results[i].types[0] === "locality"  || results[i].types[0]=="administrative_area_level_2") 
                {
                  ciudades.push(results[i].address_components[0].short_name);
                  paises.push(results[i].address_components[2].short_name);
  
                  ciudad = results[i].address_components[0].short_name;
                  pais = results[i].address_components[2].short_name;
                  //$("input[name='location']").val(city + ", " + state);
                }
                else if(results[i].types[0]=="political" || results[i].types[0]=="neighborhood")
                {
                  barrio = results[i].address_components[0].short_name;
                  barrios.push(results[i].address_components[0].short_name);
                }
            }
  
            /*map.setZoom(11);
            var marker = new google.maps.Marker({
              position: latlng,
              map: map
            });*/
            //infowindow.setContent(results[0].formatted_address);
            //infowindow.open(map);
            console.log("Ciudad:" + ciudad);
            console.log("Barrio:" + barrio);
            console.log("Sector:" + sector);
            console.log("Ciudades:" + ciudades);
            console.log("Barrios:" + barrios);
            console.log("Sector:" + sector);
            $('.onload').hide('slow');
            
            /*setTimeout(function(){
              selectDatosDireccion(ciudad,barrio,sector,ciudades,barrios);
            },1000);*/
            if(o==1)
            {
              console.log("Entro por direccion seleccionada");
            }
            else
            {
                localStora('dirpedido',results[0].formatted_address);
                localStora('latpedido',lat);
                localStora('lngpedido',lng);
                if(o==2)
                {                
                  CRUDCORRESPONDENCIA('EMITIRORDEN');
                  $('#spanubicacion').html(results[0].formatted_address + "<br>Coordenadas:" +lat + "-"+lng );
                }
                else if(o==3)
                {
                  CERRARVISITA(id);
                }
                else if(o==4)
                {
                  CRUDVISITA('GENERARORDEN',id)
                }
                //$('#spanlatitud').html(lat);
                //$('#spanlongitud').html(lng);
              //$('#geoubicacion').val(results[0].formatted_address);
              //$('#txtdireccion').val(results[0].formatted_address);
            }
          } 
          else 
          {
            window.alert('No se encontraron resultados');
          }
        } else {
          window.alert('Geocoder failed due to: ' + status);
        }
      });
  
  }