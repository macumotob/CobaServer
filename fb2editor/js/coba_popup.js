/*
 coba_popup.js
*/
//alert("coba_popup.js loaded");

var coba_popup_id = 0;
function fb2popup(div,op){
  
  coba_popup_id++;
  //console.log("coba_popup_id ",coba_popup_id);
  this._visible;//,_content= null; // private member
  this._div = null;
  this._url = op.url;
  this._self = this;
  
  this.bodyid = div.substring(1) + "-body";
  
  var hs = {top:10,middle:75, bottom:15};  
  
  Object.defineProperty(this,"content",{
    get: function() { return  this._body.html(); },
    set: function(value) { 
       //_content = value;  
       //$("#" +this.bodyid).html(value);
       $(this._body).html(value);
    }
  });    
  Object.defineProperty(this,"url",{
    get: function() { return  this._url; },
    set: function(value) { 
       this._url = value;  
    }
  });    
  
  Object.defineProperty(this,"visible",{
    get: function() { return  this._visible; },
    set: function(value) { 
       this._visible = value;  
       this._div.css("display", (this._visible ? "block" : "none"));  
    }
  });    
  function bind_handlers(){
     $( "td[data='delete-quote']").each(function(i,tr){
           var quoteid = $(tr).attr("quoteid");
           console.log(quoteid);
         });
     $( "tr[data='set-quote']").each(function(i,tr){
           var quoteid = $(tr).attr("quoteid");
           console.log(quoteid);
         });
  }
  
  fb2popup.prototype.refresh = function(){
    if(this.url){
       var self = this;
       $.get(this.url, function(result){
         self.content=result;
        // bind_handlers();
       });
    }   
  }
  function stop_events(e){
    e.preventDefault();
    e.stopPropagation();
  }
  function applay_css(ident,css){
      //var dv = $(div);
      css.forEach(function(item,i){
        ident.css(item.n,item.v);
      });
  }
  var self = this;
  function create_button(btn){
    
    var b = $("<span style='cursor:pointer;width:90%;color:#FFFF10;'>"+ btn.caption +"</span>");
    if(btn.action){
      b.click(function(){
        btn.action(self);
      });
    }
    return b;
  }
  this._div = $(div);
  
  applay_css(this._div,[ //defaults
    { n:"position",v:"absolute"},
    { n:"top",v:"25%"},
    { n:"left",v:"25%"},
    { n:"width",v:"45%"},
    { n:"height",v:"40%"},
    //{ n:"padding",v:"5px 0x 0px 5px"},
    { n:"border",v:"1px solid gray"},
    //{ n:"opacity",v:"1"},
    { n:"border-radius",v:"7px 7px 0 0"},
    //{ n:"box-shadow",v:"5px 5px 10px 10px green"},
    { n:"background-color",v:"#000000"},
    { n:"color",v:"white"},
    { n:"display",v:"none"}
   ]); 
   
  if(op.css){
    applay_css(this._div,op.css);
  }
  
  this.cap =null;
  this.button_close = null;
  if(op.caption){
    this.cap  =$("<div>" + op.caption + "</div>");
    this.button_close = $("<span class='glyphicon glyphicon-remove pull-right'></span>");
    this.button_close.css("cursor","pointer"); 
    this.cap.append(this.button_close);
    
    var self =this;
    this.button_close.click(function (){
      //_div.css("display","none");  
      self.visible= false;
    });
    
    applay_css(this.cap,[ 
      {n:"position",v:"relative"},
      {n:"width",v:"100%"},
      {n:"height",v: hs.top + "%"},
      {n:"padding",v:"5px"},
      //{n:"text-align",v:"center"},
      //{n:"vertical-align",v:"middle"},
      //{n:"border",v:"1px solid white"},
      { n:"border-radius",v:"7px 7px 0 0"},
      //{n:"opacity",v:"1.0"},
      //{n:"font-size",v:"1.5em"},
      //{n:"margin",v:"5px"},
      {n:"cursor",v:"move"},
      {n:"color",v:"#D1D120"},
      {n:"background-color",v:"#1F1F20"}
      ]);
    
    this._div.append(this.cap );
    coba_mover.register({"action":"move","control": this.cap,"target": this._div});
  }
  this._body = $("<div id='"+ this.bodyid +"'></div>");
  this._div.append(this._body);
  applay_css(this._body,[ 
      {n:"position",v:"relative"},
      {n:"width",v:"100%"},
      {n:"height",v: hs.middle +"%"},
      //{n:"border",v:"1px solid white"},
     // {n:"background-color",v:"#6A6A00"},
      {n:"color",v:"white"},
      {n:"overflow",v:"scroll"}
      ]);
  this._footer = $("<div></div>");
  this._div.append(this._footer);
  
  applay_css(this._footer,[ 
      {n:"position",v:"relative"},
      {n:"width",v:"100%"},
      {n:"height",v: hs.bottom+"%"},
      {n:"cursor",v:"nw-resize"},
      //{n:"border",v:"2px solid white"},
      {n:"background-color",v:"#1F1F20"}
      ]);
      
  
  var tb = $("<table style='width:100%;height:100%;'></table>");
  this._footer.append(tb);

  var tr = $("<tr></tr>");
  if(op.buttons){
    var me= this;
    var count = op.buttons.length;
    tb.append(tr);
    op.buttons.forEach(function(item){
      var td = $("<td style='text-align:center;cursor:pointer;padding:10px;'></td>");
      tr.append(td);
      $(td).hover(function(){
         $(this).css("background-color","#5F5F20");
       }, function(){
         $(this).css("background-color","#1F1F20");         
       });
    
      var btn = $("<span style='cursor:pointer;width:90%;color:#FFFF10;'>"+ item.caption +"</span>");
      if(item.action){
        td.click(function(){
        item.action(me);
       });
      }
      //var btn = create_button(item);
      td.append(btn);
    });
  }
  this.resizer = $("<td style='text-align:center;width:10px;cursor:nw-resize;'>...</td>");
  tr.append(this.resizer);
  coba_mover.register({"action":"size","control": this.resizer,"target": this._div});

  if(op.url){
  //  this.refresh();
    //$.get(op.url, function(result){
    //  _body.html(result);
    //})
  }

//  return this;
}
/*

         MOVE AND RESIZE DIV WITH MOUSE

*/
var coba_mover = (function(){
  //
  //  move with mouse
  //
  var mouse_x = 0;
  var mouse_y = 0;
  var mause_pressed = false;
  var items = [];

  function stop_events(e){
    e.preventDefault();
    e.stopPropagation();
  }
  
  function register(item){
    item.mousecontrol =(item.action == "move" || item.action == "size");
    items.push(item); 
   // console.log("reg", item);
  }
  
  var active;
  
 $(document).mousedown( function(e){
   active = null;
   mause_pressed = false;
   for(var i = 0 ;i <items.length;i++){
     $(items[i].target).css("z-index","0");
     if(items[i].mousecontrol){
       if(e.target == items[i].control[0]){
         active = items[i];
         $(items[i].target).css("z-index", "1000");
       }
     }
   }
   if(!active) return;
   $(active.target).css("z-index","100");
   mause_pressed = true;
   mouse_x = e.pageX;
   mouse_y = e.pageY;
   stop_events(e);
   //console.log("down :" , e);
 });  
 $(document).mouseup( function(e){
   mause_pressed = false;
   active = null;
   stop_events(e);
   //stop_events(e);
 });  
 $(document).mousemove( function(e){
   if(!mause_pressed) return;
//        console.log("move :" , e);
    var dx =(e.pageX - mouse_x);
    var dy =(e.pageY - mouse_y);
    
    var item = active.target;
    

    if(active.action === "move"){    
      var x = parseInt($(item).css('left')) + dx;
      var y = parseInt($(item).css('top')) + dy;
      $(item).css('left', x + 'px');
      $(item).css('top',  y + 'px');
    }
    else if(active.action === "size"){    
      var x = parseInt($(item).css('width')) + dx;
      var y = parseInt($(item).css('height')) + dy;
      $(item).css('width', x + 'px');
      $(item).css('height',  y + 'px');
    }
    mouse_x = e.pageX;
    mouse_y = e.pageY;
    stop_events(e);
 });  
 
 document.onkeyup = function(e){
  for(var i = 0 ;i <items.length;i++){
    if(!items[i].mousecontrol){
      var item = items[i];
      if(item.target === e.currentTarget
        && item.control 
        && item.control == e.ctrlKey 
        && item.keycode == e.keyCode) {
          item.callback(e);
      }
    }
  }    
 }
 
 return {"register":register};
 
})();