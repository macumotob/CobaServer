
<?
 # /php/full-screen.php
 $book_title ="DEMO";
 $encoding = "utf-8";
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?= $book_title;?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta name="mobile-web-app-capable" content="yes">
  <meta http-equiv="Content-Type" content="text/html;charset=<?=$encoding;?>"/>
  <meta charset="<?=$encoding;?>" /> 
  <style>
  @menu_height:50px;
  @menu_bgcolor: rgb(100,100,100);
  
   html,body {
     margin:0;
     padding:0;
   }

  .fb2-container{
    left:0px;
    width:100%;
    position: fixed;
    background-color: rgb(100,100,100);
    text-align:center;
   }
   
  #fb2-menu-top {
    top: 0;
    height: 50px;
  }
  #fb2-menu-bottom {
    bottom: 0;
    height: 50px;
  }

   
  </style>  
  <script type="text/javascript" src="/js/loader.js"></script>

</head>
<body onresize="fb2_resize();" onload="fb2_resize();">

<div id="fb2-menu-top" class="fb2-container" onclick="toggle_full_screen();">...</div>


<div  id="cont" class="fb2-container"
  style="
   height:220px;
   top:50px;
   background-color:red;
   color:white;
   text-align:center;
   overflow: scroll;
   "
   >
    <div id="main-content" style="
    width:100%;
    height:2000px;
    background-color:black;
    top:0px;
    left:0px;
    z-index:1000;
    ">
     .............
     </div>

   </div>
   
  <div id="fb2-menu-bottom" class="fb2-container" onclick="toggle_full_screen();">...</div>
  
<script >
var is_full_screen_mode = false;
  function toggle_full_screen() {
    
  if (!document.fullscreenElement &&    // alternative standard method
      !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) {  // current working methods
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
  is_full_screen_mode=!is_full_screen_mode;
  fb2_resize();
}
function fb2_resize(){
  var w =  get_actual_width();//document.documentElement.clientWidth;
  var h = get_actual_height();
  
  id("cont").style.width = w + "px";
  id("cont").style.height = (h - 100) + "px";
  id("main-content").innerText = get_actual_width() + " Y:" + document.documentElement.clientHeight
  + "Y2:" + window.screen.height + " { " + id("fb2-menu-top").clientWidth;


}
function get_actual_width() {
    var actualWidth = window.innerWidth ||
                      document.documentElement.clientWidth ||
                      document.body.clientWidth ||
                      document.body.offsetWidth;

  return document.documentElement.clientWidth;                      
    return actualWidth;
}
function get_actual_height() {
    var actualHeight = window.innerHeight ||
                      document.documentElement.clientHeight ||
                      document.body.clientHeight ||
                      document.body.offsetHeight;

  return document.documentElement.clientHeight;
                          return actualHeight;
}

</script>
</body>
</html>