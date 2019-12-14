


  window.fbAsyncInit = function() {
    FB.init({
      appId            : '751614811971217',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v5.0'
    });

    FB.AppEvents.logPageView();   

    FB.getLoginStatus( function(response) { 
      App.facebook_setData(response);
    } ); 

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/pt_BR/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));


  }
  


  