



var App = new Vue( {
  
  //delimiters
  delimiters: ['{', '}'],
  
  //element
  el: '#site',
  
  
  //data
  data : {

    //app
      location : 'home',
      assets: '/assets/', 
      search:'',
      search_input: '',
      modal : {
        visible: false,
        title: 'Modal Teste Titulo',
      },


      //galery
      galery: { 
        show: 6,
        name: 'all',
        orderby: '',
        itens : '',
        single: {
          item: false,
          preview: {
            dimensions: ['1440px','1088px'],
            layout: 'desktop',
            rotate: false
          },
        },
      },


      //facebook
      facebook: {
        status: false ,
        response : null,
      } ,


      /* Data comming from Twig template engine PHP */
      user: false, //object 
      token: false, //string
      scheme: location.protocol , //string http|https
      host: window.location.host , //string|url
      api: '', // this.setApi() method set
      assets:'', //assets - public path img, css, js ...
      url:'', //Present Url
    }
 ,  

 created: function(){
  this.api_set();
  this.user_load();
  this.galery_load('all');
  this.galery_single();

 }
 ,

 mounted: function(){
  if(this.galery.single.item != false){
    this.galery_single_preview(this.galery.single.preview.layout,this.galery.single.preview.rotate);
  }
 }
,
 //methods
  methods:{


    //App
    api_set : function(){
      this.api = this.scheme+"//api."+this.host+"/";
    },

    modal_toggle : function ($event){
      event.preventDefault();
      this.modal.visible = this.modal.visible ? false : true;
    },
    app_redirect : function(url){
      window.location.href = url ;
    },

    //Galery methods
    galery_load (galery){
      var vue = this;
      this.galery.name = galery;
      this.search = undefined;
      axios.get(this.api+'galery/'+galery)
      .then(function(response) { 
        keys = Object.keys(response.data);
        for( var i=0; i < keys.length; i++) {
            vue.galery[keys[i]] = response.data[keys[i]];
        }
        this.token = response.data.token ? response.data.token : false;
       } );
    },

    galery_select: function(event, galery){
      event.preventDefault();
      this.galery.name = galery;
      if( this.search_input != '' ){
        this.galery_search(event);
      }else{
        this.galery_load(galery);
      }
      window.location.href = '#portfolio' ;
    },

    galery_single: function(){
      if(galery_single != false) {
        this.galery.single.item = galery_single;
        return true;
      }
      return false;
    },
    
    galery_single_preview: function(layout,rotate=false){
      this.galery.single.preview.dimensions=[];
      switch( layout.toLowerCase()  ){
        case 'phone':
            this.galery.single.preview.dimensions = [ '360px',  '640px'];
        break;
        case 'tablet':
            this.galery.single.preview.dimensions = [ '768px',  '1024px'];
        break;
        default: 
        case 'desktop':
            this.galery.single.preview.dimensions = [ '1088px',  '1088px']; // [ '1440px',  '1088px']
        break;
      } 
      if(rotate ) {
        this.galery_single_preview_rotate();
      }
      this.galery.single.preview.rotate = rotate;
      this.galery.single.preview.layout =  layout.toLowerCase();
      this.galery_single_preview_setIcon(rotate);
      window.location.href = '#portfolio' ;
    },

    galery_single_preview_rotate: function(){
      if(  this.galery.single.preview.rotate   ){
        this.galery.single.preview.rotate = false;
      }else{
        this.galery.single.preview.rotate = true;
      }
      this.galery_single_preview_setIcon( this.galery.single.preview.rotate);
      this.galery.single.preview.dimensions.reverse();
    },

    galery_single_preview_setIcon: function(rotate=false){
      var icon = '';
      switch( this.galery.single.preview.layout){
        case 'phone':
            icon = 'mobile';
        break;
        case 'tablet':
            icon = 'tablet';
        break;
        default: 
        case 'desktop':
            icon = 'sync-alt';
        break;
      } 
      if( rotate ){
        document.getElementById('preview_rotation').innerHTML = '<i id="view_format" class="fa fa-'+icon+' fa-rotate-90"></i>' ;
      }else{
        document.getElementById('preview_rotation').innerHTML = '<i id="view_format" class="fa fa-'+icon+'"></i>' ;
      }
    },

   galery_search : function(event) {
      event.preventDefault();
      var vue = this;
      var formdata =  new FormData();
      formdata.append('search',this.search_input );
      axios.post(this.api+'galery/search/'+this.galery.name, formdata ).then(
        function(response) { 
          vue.galery.itens = response.data.itens;
          vue.token = response.data.token ? response.data.token : false;
       }); 
       this.search = this.search_input;
       window.location.href = '#portfolio' ;
   },

   galery_setLike : function(event,itemIndex){
    event.preventDefault();
    alert('Like item '+itemIndex+"  "+this.galery.itens[itemIndex].title+"!" );
   },

   galery_comments_openBox : function (event,itemIndex) {
    event.preventDefault();
    alert('Galery_comments_openBox item '+itemIndex+"  "+this.galery.itens[itemIndex].title+"!" );
   },

   galery_share_openBox : function (event,itemIndex) {
    event.preventDefault();
    alert('Galery_share_openBox item '+itemIndex+"  "+this.galery.itens[itemIndex].title+"!" );
   },

   //end Galery methods


   //User methods

   user_load: function(){
   
      if( localStorage.getItem('token')  ) {
        var vue = this;
        myform = new FormData();
        myform.append("token", window.localStorage.token);
        axios.post(this.api+'user', myform )
        .then(function(response) { 
            vue.user = response.status ? response.data.user : false ;
            vue.token = response.data.token ? response.data.token : false;
            if(vue.token){
              localStorage.setItem('token' , vue.token ) ;
            }
        } );
     }else{
      this.user_logout();
     }
    
   },

   user_logout : function () {
   
    localStorage.removeItem('token');
    this.user = false;
    if(!this.user){
      return true;
    }

    return false;

   },

   user_getRolName : function (lower=false){

    var rol_name;
   
    if (this.user )  {

        switch(this.user.rol){
          case 1:
            rol_name = 'Admin'
            break;

            case 2:
                rol_name = 'Manager'
            break;

            case 3:
                rol_name = 'User'
                break;
            case 4:
                    rol_name = 'Facebook'
            break;

            case 5:
                rol_name = 'Google'
            break;

            case 6:
                rol_name = 'Instagram'
            break;

            default:
                    rol_name = 'Guest'
            break;

        } 

      }else{
        rol_name = 'Guest'
      } 

    return lower ? rol_name.toLowerCase() : rol_name;

   },

   user_middleware : function(middelwares){

      if( Array.isArray(middelwares)  ){
        var keys = Object.keys(middelwares);
        for( var i=0; i<keys.length; i++ ){
          if( middelwares[i].toLowerCase() == this.user_getRolName(true) ){
            return true;
          }
        }
      }

      if(  (typeof middelwares === 'string' ) && ( ( middelwares.toLowerCase() == this.user_getRolName(true) | ( this.user && (  middelwares.toLowerCase() == 'auth' ) ) ) ) ){
        return true;
      }

      return false;

   },

   //end User methods


  ////Facebook methods

  facebook_setData : function(response){
    this.facebook.status = response.status == 'connected' ? true : false ;
    this.facebook.response = response.authResponse; 
  },

  facebook_addUser: function (){
    alert('Facebook Add User!');
  },


  fbloginteste: function (){
  
    myform = new FormData();
    myform.append('username','@messiasdias');
    myform.append('email','messiasdias.ti@gmail.com');
    var vue = this;
    axios.post(this.api+'/social/login', myform )
    .then(function(response) { 
      vue.user = response.status ? response.data.user : false ;
      vue.token = response.data.token ? response.data.token : false;
        if(vue.token){
          localStorage.setItem('token' , vue.token ) ;
          console.log(this.token);
        }
     } ); 

     console.log(this.user);

  },

   facebook_login: function (){

    var vue = this;

    return FB.login(function(response) {

      App.facebook_setData(response);

      if (response.authResponse) {
          FB.api('/me?fields=id,email,name', function(me) {
            
            form = new FormData();
            form.set('username', me.id);
            form.set('email', me.email);

            axios.post(this.api+'social/login', {
              username: me.id,
              email:me.email,
            } )
            .then(function(response) { 
              vue.user = response.status ? response.data.user : false ;
              vue.token = response.data.token ? response.data.token : false;
             } );


          });
      } else {
        console.log('Login cancelado por não autorização!');
      }
       });

    
   },
   /// end Facebokk methods



  },// end methods()


  
} );

if(view_data.token){
  localStorage.setItem('token' , view_data.token );
}


