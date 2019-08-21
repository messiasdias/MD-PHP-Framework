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
	    }); }

}

function toggle(element , target=null){
	if ($(element).is(':visible')) {
		
		$(element).hide('slow');

		 if(target != null){
		 	window.location.href = target;
		 }

	}else{
		$(element).show('slow');

		if(target != null){
		 	window.location.href = target;
		 }
		}
}


function dropdown(element){
		 	

	if ($(element).is(':visible')) {

		$('.drop-item').css('display', 'none');
		$(element).css('height', 0);
		$(element).css('display', 'none');

	}else{
		$('.drop-item').css('height', 0);	
		$('.drop-item').css('display', 'none');
		$(element).css('display', 'flex');
		$(element).css('height', 100);

		}
}



function top_btn() {
	if ( $(window).scrollTop() > 100 ){
		$('#top-btn').show('fade');
	}else{
		$('#top-btn').hide('slow');
			}
}

function reload_frame(element){
	$(element).attr('src', $(element).attr('src'));
}


function toogle_modal(element){

	if( $(element).is(':visible') ){
		 $(element).hide('fast');
	}else{
		$(element).show('fast');
	}

}


function delete_modal(id,action,method="POST", msg="Delete ?"){

	$('.delete_modal>p.msg').html(msg);
	$('.delete_modal>form').attr('action', action);
	$('.delete_modal>form').attr('method', method);
	$('.delete_modal>form>input#id').attr('value', id);
	toogle_modal('.delete_modal');
}


/* Jquery onready */

$(document).ready( function(){
 
  /* Jquery onscroll */	
  $(window).scroll(function(){
  	top_btn();
  });


  /* Click .drop-item */
  $('.drop-item-link').click(
  		function () {
  			toggle('.drop-item');
  			toggle('.title-bar-nav ');
  		}

  	); 








  /* Jquery typed function */
  AOS.init();
  typed(300,'#typed-slogan',[' ',' Web Developer ',' Freelancer ']);
  //typed(300,'#loading',[' ','...']);


});