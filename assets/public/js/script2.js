
function ajax(url, method = 'GET', dataType = 'json', data=null, async=true){
	var result;

	$.ajax({
	  method: method,
	  dataType: dataType,
	  url: url,
	  async: async,
	  data: data, 
	  success: function(data, status) {
	  		result = data;
   	 },
   	 error: function(data, status) {
   	 	result = false;
 
   	 	if(status == "parsererror"){
   	 	  console.log("Ajax Error: "+status+",\nO que Pode ser um Erro interno no arquivo chamado pelo Ajax.\nEste Arquivo deve Retornar um Json, Html ou xml (default: json) no Final.");
   	 	}

   	 }

	});

	return result;
}



function preview(type, id){

	switch(type){
		 case "mobile":
		 	$(id).css('width', 320);
		 break;

		 case "tablet":
		 	$(id).css('width', 768);
		 break;

		  case "laptop":
		 	$(id).css('width', 1024);
		 break;

		  case "desktop":
		 	$(id).css('width', 1366);
		 break;

		  case "tv":
		 	$(id).css('width', 2500);
		 break;

	} 


}

function typed(speed,component, text=[]){
	   if ($(component).length) { 
	    var typed = new Typed(component, {
	      strings: text ,
	      typeSpeed: speed,
	      backSpeed: 30,
	      backDelay: 2000,
     	  loop: true, // false = infinite
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
}


function toggle_menu(){

    if( $('#menu').is(':visible') ){
        $('.menu-toggle').removeClass('fa-times');
        $('.menu-toggle').addClass('fa-bars');
        $('#menu').css('display', 'none');
        $('#site').css('width', '100vw');
        
        
    }else{
        $('.menu-toggle').removeClass('fa-bars');
        $('.menu-toggle').addClass('fa-times');
        $('#menu').css('display', 'flex');
        $('#site').css('width', '50vw !important');
    }

}


function toggle_menu_btn(){

     if( $(this).scrollTop() >= 100){
        $('#menu-toggle-fixed').show();
    }else{
        $('#menu-toggle-fixed').hide();
    }
}


function delete_modal(modalid,itemid,action,method="POST", msg="Delete ?"){
	$(modalid+'>div.modal>p.msg').html(msg);
	$(modalid+'>div.modal>form').attr('action', action);
	$(modalid+'>div.modal>form').attr('method', method);
	$(modalid+'>div.modal>form>input#id').attr('value', itemid);
	toggle(modalid);
}


function redirect(url){

	console.log(window.location.pathname);
	if(window.location.pathname != url ){
		//console.log(url);
		history.pushState('data','title',url); 
	}
}


/* Jquery onready */

$(document).ready( function(){
 
    /* Jquery onscroll */	
    $(window).scroll(function(){
        toggle_menu_btn();
    });
  
  
    /* Click a.menu-item */
    $('a.menu-item').click(
            function () {
                toggle_menu();
            }
  
        ); 

	/* Disable Input Autocomplete */	
	$('input').attr('autocomplete', 'off');	

        
    /* Jquery typed function */
    AOS.init();
    typed(100,'#typed-slogan',[' ', 
      '<i class="far fa-grin-alt"></i> Hi! <br> this is <br><b class="destak" > MD <i class="fab fa-php" > </i> Framework  </b>',
      '<i class="fab fa-php"></i> <i class="fab fa-js"></i> <br><i class="fab fa-html5"></i> <i class="fab fa-css3"></i> + <i class="fab fa-sass"></i>'
    ]);
  
  
  });