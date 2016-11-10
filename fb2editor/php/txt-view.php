<?php 
$data= false;
$text="";
$encoding ="windows-1251";
function fb2_get_content($file){
  $file = $_GET["file"];
 
  if(file_exists($file))
  {
    $text = file_get_contents($file);
  }
  else {
    $text = file_get_contents("http://192.168.0.46:3035/". urlencode($file));
  }
  return $text;
}

if(isset($_GET["file"])){
  $file_path = $_GET["file"];
  $file = $file_path;
  $text = fb2_get_content($file);
}
else{
  echo "HELLO";
  exit;
}
 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?= $file;?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta name="mobile-web-app-capable" content="yes">
  <meta http-equiv="Content-Type" content="text/html;charset=<?=$encoding;?>"/>
  <meta charset="<?=$encoding;?>" /> 
  
  <link rel="stylesheet" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/css/bootstrap-dialog.min.css" />
  <link rel="stylesheet" href="/css/fb2.css" />

  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="/js/loader.js"></script>

</head>
<body onresize="fb2.resize();" onload="fb2.resize();">

  <div id="fb2-menu-top" class="fb2-container" >...</div>
  
  <div  id="cont" class="fb2-container"
  style="
   height:220px;
   top:0px;
   overflow-y: scroll;
   " onscroll="fb2.onscroll();"
   >
   
    <div id="main-content" style="
    width:100%;
    background-color:white;
    
    top:0px;
    left:0px;
    z-index:1000;
    text-align:center
    " >
    <pre style="display: inline-block;text-align: left;"><?=$text;?></pre>
<!--
  <div style="width:100%;text-align:center;">
    <b><?=$book_title;?></b>
    <i> charset=<?=$encoding;?></i>
  </div>
  <div 
    id="main-content2" style="width:100%;background-color:rgb(254,254,250);">
  -->
  

<?
//  fb2_to_client($data);
?>

  <div class="container-fluid text-center bg-success">END OF FILE</div>
<!--  
  <div style="width:100%;height:250px;background-color:white"></div>
  <div id='control-panel' class="row-fluid navbar-inverse navbar-fixed-bottom" 
       style="height:70px;opacity:0.9;margin:0;padding:0;display:none;">
    <table style="color:white;width:100%">
      <tr>
        <td onclick="fb2_full_screen();" style="text-align:center;">
          <button type="button" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span></button>
        </td>
        <td onclick="fb2_toogle_description();" style="text-align:center;">
          <button type="button" class="btn btn-primary"><span class="glyphicon glyphicon-book"></span></button>
        </td>
        <td onclick="window.scrollTo(0,(scroll.y += 200));" style="text-align:center;">
          <button type="button" class="btn btn-primary"><span class="glyphicon glyphicon-adjust"></span></button>
        </td>
        <td onclick="window.scrollTo(0,(scroll.y -= 200));" style="text-align:center;">
          <button type="button" class="btn btn-primary"><span class="glyphicon glyphicon-adjust"></span></button>
        </td>
      </tr>
      <tr>
        <td colspan="4" style="text-align:center;font-size:small" id='box1'><?=$book_title;?></td>
      </tr>
      
     </table>
  </div>
  </div>
  </div>
  -->
  </div>
  
<div id="fb2-menu-bottom" class="fb2-container">
    <table style="width:100%;font-size:.9em;">
     <tr>
       <td id="fb2-time" class="fb2-text-center">...</td>
       <td id="fb2-info" class="fb2-text-center">...</td>
       <td id="fb2-read-time" class="fb2-text-center">...</td>
       <td class="fb2-button" onclick="rt.reset();">reset</td>
     </tr>
     <tr>
     <td id="fb2-bookname" colspan="4" class="fb2-text-center"><?=$file;?></td>
    </table>
</div>

<div id="fb2-popup" class="fb2-popup shadow">
  <div class='fb2-popup-header'>
    <span id='fb2-popup-caption'></span>
    <span style='float:right;' id='fb2-popup-close' onclick="fb2_show_popup('none');">x</span>
  </div>
  <hr>
  <div>
  <div id="fb2-popup-body" class='fb2-popup-body'></div><hr>
    </div>
  <div class='fb2-popup-footer'></div>
</div>

  <script type="text/javascript">
    var scroll= {y:0};  
 
 /*
    read time
  */
  rt = (function(){
    var sec=0,min=0,hour=0;
    var month = ["January","February","March",
                 "April","May","June","July","August","September",
                 "October","November","December"];

    function reset(){
      sec=min=hour=0;
    }             
    function format(n) {
       return (n < 10 ? "0" + n : n );
    }
    function date(){                 
      var d = new Date();
      var s =  format(d.getDate()) 
                +  month[d.getMonth()]  + " " 
                + d.getFullYear() + " "  
                + format(d.getHours()) + ":"  
                + format(d.getMinutes()) + ":" 
                + format(d.getSeconds());
                return s;
    }
    function tick(){
      sec++;
      if(sec >59){
        sec=0;
        min++;
        if(min>59){
          min=0;
          hour++;
        }
      }
    }
    function text(){
      return (hour < 10 ? "0" + hour:hour) 
        + ":" + (min < 10 ? "0" + min:min)
        + ":" + (sec < 10 ? "0" + sec:sec);
    }
    return { tick:tick,text:text,date:date,reset:reset};
  })();
 
 /*
       fb2
 
 */
  var fb2 = (function (){
      
    var main =id("main-content");
    var info =id("fb2-info");
    var view =id("cont");
    var toogle_count=0;      
      
    function show_info(){
       info.innerText = (parseInt((scroll.y *10000)/main.clientHeight)/100) + "%" ;
    }    
    function onscroll() {
        //scroll.y = window.pageYOffset || document.documentElement.scrollTop;
        scroll.y = view.scrollTop;
        var data = {'scroll': scroll.y};
        var name = window.location.pathname;
        localStorage.setItem(name, JSON.stringify(data));
        show_info();
            //console.log(scrolled + 'px');
      };
    function resize(){
      var w =  document.documentElement.clientWidth;
      var h = document.documentElement.clientHeight;
   
      view.style.width = w + "px";
      view.style.height = (h) + "px";
      show_info();
    }

    function full_screen(e){
      if(e.changedTouches[0].clientY < 100 && toogle_count % 2 === 0){
        toggleFullScreen();
        e.stopPropagation();
        e.preventDefault();
      }
      toogle_count++;
    }
      
    //
    return {show_info:show_info,onscroll:onscroll,resize:resize,full_screen:full_screen};
    })();
    
/*

*/    
 window.addEventListener('load', function(){

  function show_time(){
    rt.tick();
    id("fb2-time").innerText = rt.date();
    id("fb2-read-time").innerText = rt.text();
  } 
  show_time();
  
  window.setInterval(function(){
    show_time();
  },1000);
    
//    var toogle_count=0;
    
    document.body.addEventListener('touchend', function(e){
      fb2.full_screen(e);
        /*
        if(e.changedTouches[0].clientY < 100 && toogle_count % 2 === 0){
          fb2.full_screen();
          e.stopPropagation();
          e.preventDefault();
        }
        toogle_count++;
        */
    }, false);
    
     //--------------------------------
     var htm = $("#fb2-description").html() + "<hr><div style='height:30px'></div>";
     $("#fb2-description").remove();
     $("#fb2-popup-body").html(htm);
   
     var title = $("#fb2-book-title").text();
     id("fb2-popup-caption").innerText = title;
     
     var name = window.location.pathname;
     var data = localStorage.getItem(name);
     if(data){
       data =  JSON.parse(data);
       scroll.y = data.scroll;
       id("cont").scrollTop =scroll.y ;
     }
     else {
       //fb2_show_popup('block');
     }
     
 });
//var is_book_info_visible = false;
/*
var mouse_x =0;
var mouse_y =0;
var mouse_pressed=false;
*/
/*
function fb2_on_mouse_down(event){
  mouse_pressed = true;
  mouse_y =  event.y;
  mouse_x =  event.x;
  event.stopPropagation();
  event.preventDefault();
  return false;
}
function fb2_on_mouse_move(event){
 // if(mouse_pressed) {
    var st = id('box1');
    scroll.y += ((mouse_y - event.y) >0 ? 20:-20);
    mouse_y =  event.y;
    mouse_x =  event.x;
  
    //window.scrollTo(0,scroll.y);
    st.innerText = "3 x:" + event.x + " y:" + scroll.y;
    //document.body.scrollTop = scroll.y;
    //document.getElementById('header').scrollIntoView();
   // $('#main-content').animate({ scrollTop: elementOffset }, 200);
    event.stopPropagation();
    event.preventDefault();
    return false;
  //}
}
function fb2_on_mouse_up(event){
  mouse_pressed = false;
  event.stopPropagation();
  event.preventDefault();
  return false;

}
*/
function fb2_toogle_description() {
  var show = is_book_info_visible ? "none" : "block";
  fb2_show_popup(show);
  is_book_info_visible = !is_book_info_visible;
}
 function fb2_show_popup(show) {
   id("fb2-popup").style.display = show;
   event.stopPropagation();
   return false;
//   event.preventDefault();
 }
 </script>
  
</body></html>
