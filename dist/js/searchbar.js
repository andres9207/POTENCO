// getting all required elements
const searchWrapper = document.querySelector(".search-input");
const inputBox = searchWrapper.querySelector("input");
var inputSearch = $('#txtbusqueda'); 
const suggBox = searchWrapper.querySelector(".autocom-box");
const icon = searchWrapper.querySelector(".icon");
let linkTag = searchWrapper.querySelector("a");
let webLink;

// if user press any key and release
inputBox.onkeyup = (e)=>{
    let userData = e.target.value; //user enetered data
    
    if(userData){
        icon.onclick = ()=>{
            webLink = "https://www.google.com/search?q=" + userData;
            linkTag.setAttribute("href", webLink);
            console.log(webLink);
            linkTag.click();
        }
        
         $.post('search_radicado.php', { query: userData }, function (data) 
		{	
				//return process(objects);
				data = $.parseJSON(data);
				//console.log(data);
                //return data;
                
				refresh(data);
        });
        /*console.log(emptyArray)
        suggestions.filter((data)=>{
            //filtering array value and user characters to lowercase and return only those words which are start with user enetered chars
            return data.toLocaleLowerCase().startsWith(userData.toLocaleLowerCase()); 
        });
        emptyArray = emptyArray.map((data)=>{
            // passing return data inside li tag
            return data = '<li>'+ data.id +'</li>';
        });
        searchWrapper.classList.add("active"); //show autocomplete box
        showSuggestions(emptyArray);
        let allList = suggBox.querySelectorAll("li");
        for (let i = 0; i < allList.length; i++) {
            //adding onclick attribute in all li tag
            allList[i].setAttribute("onclick", "select(this)");
        }*/
    }else{
        searchWrapper.classList.remove("active"); //hide autocomplete box
    }
}
inputBox.onblur = (e)=>{
    if($('.search-input.active .autocom-box').length<=0)
    {
        //searchWrapper.classList.remove("active");
    }
}

inputBox.onfocus = (e)=>{
    let userData = e.target.value; //user enetered data
    
    if(userData){
        icon.onclick = ()=>{
            webLink = "https://www.google.com/search?q=" + userData;
            linkTag.setAttribute("href", webLink);
            console.log(webLink);
            linkTag.click();
        }
        
         $.post('search_radicado.php', { query: userData }, function (data) 
		{	
				//return process(objects);
				data = $.parseJSON(data);
				//console.log(data);
                //return data;
                
				refresh(data);
        });
        /*console.log(emptyArray)
        suggestions.filter((data)=>{
            //filtering array value and user characters to lowercase and return only those words which are start with user enetered chars
            return data.toLocaleLowerCase().startsWith(userData.toLocaleLowerCase()); 
        });
        emptyArray = emptyArray.map((data)=>{
            // passing return data inside li tag
            return data = '<li>'+ data.id +'</li>';
        });
        searchWrapper.classList.add("active"); //show autocomplete box
        showSuggestions(emptyArray);
        let allList = suggBox.querySelectorAll("li");
        for (let i = 0; i < allList.length; i++) {
            //adding onclick attribute in all li tag
            allList[i].setAttribute("onclick", "select(this)");
        }*/
    }else{
        searchWrapper.classList.remove("active"); //hide autocomplete box
    }
}


function refresh( datos)
{
    let emptyArray = [];
    emptyArray = datos.map((data)=>{
        // passing return data inside li tag
        return data = '<li><a href="#/Documentos/Preview?ID='+data.id+'">'+ data.radicado +'</a></li>';
    })
    searchWrapper.classList.add("active"); //show autocomplete box
    showSuggestions(emptyArray);
    let allList = suggBox.querySelectorAll("li");
    for (let i = 0; i < allList.length; i++) {
        //adding onclick attribute in all li tag
        allList[i].setAttribute("onclick", "select(this)");
    }
}

function select(element){
    //let selectData = element.textContent;
    inputBox.value = "";//selectData;
    /*
    icon.onclick = ()=>{
        webLink = "https://www.google.com/search?q=" + selectData;
        linkTag.setAttribute("href", webLink);
        linkTag.click();
    }*/
    searchWrapper.classList.remove("active");
}

function showSuggestions(list){
    let listData;
    if(!list.length){
        userValue = inputBox.value;
        listData = '<li>No se encontro datos</li>';
    }else{
        listData = list.join('');
    }
    suggBox.innerHTML = listData;
}


$(document)
    .bind( 'click', function(event){
        inputSearch
        .removeClass( 'event-outside' )
        .children( '.event-target' );//       .text( ' ' );
        console.log("Prueba");

    })
    .trigger( 'click' );
  
  // Bind the 'clickoutside' event to each test element.
  inputSearch.bind( 'clickoutside', function(event){
    console.log("Prueba2");
    var elem = $(this),
      target = $(event.target);
      
      // Update the text to reference the event.target element.
     /*text = 'Clicked: ' + target[0].tagName.toLowerCase()
        + ( target.attr('id') ? '#' + target.attr('id')
          : target.attr('class') ? '.' + target.attr('class').replace( / /g, '.' )
          : ' ' );*/
    
    // Highlight this element and set its text.
    elem
      .addClass( 'event-outside' )
      .children( '.event-target' );//   .text( text );

    searchWrapper.classList.remove("active");
  });