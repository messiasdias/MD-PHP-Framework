
function ajax(url, method = 'GET', dataType = 'json', formdata=null, async=true){
	var result;

	$.ajax({
	  method: method,
	  dataType: dataType,
	  url: url,
	  async: async,
	  data: formdata, 
	  success: function(data, status) {
			  result = data;
			  console.log(status);
   	 },
   	 error: function(data, status) {
			result = false;
			console.log(status);
 
   	 	if(status == "parsererror"){
   	 	  console.log("Ajax Error: "+status+",\nO que Pode ser um Erro interno no arquivo chamado pelo Ajax.\nEste Arquivo deve Retornar um Json, Html ou xml (default: json) no Final.");
   	 	}

   	 }

	});

	
	return result;
}




function ajax2(url, method = 'GET',  formdata=null, success=null, error=null){

	$.ajax({
	  method: method.toUpperCase(),
	  url: url.toLowerCase(),
	  async: false,
	  data: formdata, 
	  cache:false,
      contentType: false,
      processData: false,
      async: true,
      success : function(data){

		if( success == null ){
			return JSON.parse(data);
		}

		return success(data);
   	  },
   	  error: function(data) {
		
   	  	if(error == null){
		console.log(data.status);
			if(data.status == "parsererror"){
			  console.log("Ajax Error: "+data.status+",\nO que Pode ser um Erro interno no arquivo chamado pelo Ajax.\nEste Arquivo deve Retornar um Json, Html ou xml (default: json) no Final.");
			}
			return JSON.parse(data);

   	 	}else{
   	 		return error(data);
   	 	}
   	 }	

	});

	
}



function typed(speed,component, text=[], loop=true){
	if ($(component).length) { 
	 var typed = new Typed(component, {
	   strings: text ,
	   typeSpeed: speed,
	   backSpeed: 30,
	   backDelay: 2000,
		loop: loop, // false = infinite
	   loopCount: 100,
	   showCursor: false,
	 }); }

}




function toggle(element){	

    if( $(element).is(':visible') ){
       $(element).hide();
    }else{
		$(element).show();
    }

	console.log("Toggle() --> Element: "+element+" is_visible:"+$(element).is(':visible') );
}


function toggle_menu_dropdown(element){
	$('.menu-list-dropdown').removeClass("active");
	$('.menu-list-dropdown').hide();
	toggle(element);
	$(element).addClass("active");
}


function toggle_menu_btn(){

     if( $(this).scrollTop() >= 100){
        $('#menu-toggle-fixed').show();
    }else{
        $('#menu-toggle-fixed').hide();
    }
}

function fixed_menu_galery(){

	if( ( $(this).scrollTop() > $('#portfolio').offset().top ) & ( $(this).scrollTop() <= ($('#about').offset().top -10 ) ) ){
		$('.galery3-header').addClass("galery3-header-fixed");
	}else{
		$('.galery3-header').removeClass("galery3-header-fixed");
	}
}

function delete_modal(modalid,itemid,action,method="POST", msg="Delete ?"){
	$(modalid+'>div.modal-custom>p.msg').html(msg);
	$(modalid+'>div.modal-custom>form').attr('action', action);
	$(modalid+'>div.modal-custom>form').attr('method', method);
	$(modalid+'>div.modal-custom>form>input#id').attr('value', itemid);
	toggle(modalid);
}


function redirect(url){
	console.log('Redirect to '+url );
	if((window.location.pathname != url) && ( window.location.pathname != "/" ) ){
		history.pushState('data','title',url); 
	}
}


function redirect_location(url){
	return window.location.href = url;
}


function animate_icon_pisca(element){
	setTimeout(function(){ $(element).html('<i class="far fa-smile"></i>'); }, 1000);
	setTimeout(function(){ $(element).html('<i class="far fa-smile-beam"></i>'); }, 600);
	$(element).html('<i class="far fa-smile"></i>');
	
}

function animate_icon_pisca2(element){
	setTimeout(function(){ $(element).html('<i class="far fa-smile"></i>'); }, 900);
	setTimeout(function(){ $(element).html('<i class="far fa-smile-wink"></i>'); }, 600);
	$(element).html('<i class="far fa-smile"></i>');
}

function animate_icon_smile(element){
	setTimeout(function(){ $(element).html('<i class="far fa-grin-wink"></i>'); }, 500);
	$(element).html('<i class="far fa-smile"></i>');

}

function animate_icon(element){
	setTimeout(function(){ animate_icon_pisca(element) }, 200);
	setTimeout(function(){animate_icon_pisca2(element)}, 1800);
	setTimeout(function(){animate_icon_smile(element)}, 1600);
	$(element).html('<i class="far fa-smile"></i>');
	setInterval( function() {animate_icon(element);} , 4000 );
}


/* Jquery onready */

$(document).ready( function(){
 
    /* Jquery onscroll */	
    $(window).scroll(function(){
		//toggle_menu_btn();
		//fixed_menu_galery();
    });
  
  
    /* Click a.menu-item */
    $('a.menu-item').click(
            function () {
				toggle('#menu');
            }
  
		); 
		

	$(".modal-box").click(function(event){
		if( event.target.id === 'mymodal' ){
			toggle('#mymodal');
		}
	});	


	$('.uploadfile_file').change(function(event){

		$(".uploadfile_label").html(event.target.value);
		$(".uploadfile_img").attr("src", $('.uploadfile_file').value );
		$(".uploadfile_submit").prop('disabled', false);

	} );

	/* Disable Input Autocomplete */	
	 $('input').attr('autocomplete', 'off');	


	  typed(20,'.typed-slogan',[" ", '<b id="smile_icon"   ><i class="far fa-smile"></i></b>&nbsp;Hello!' ], false);
	  typed(50,'.typed-slogan2',[" ", "I'm messias dias." ], false);
	  typed(20,'.typed-slogan3',[" ", "Web developer under development" ], false);
	  //animate_icon('#smile_icon');
	  //redirect('{{url}}'); 
	  AOS.init();	
        
   
  
  });


