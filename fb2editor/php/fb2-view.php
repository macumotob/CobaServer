<?php 
$data= false;
$text="";
require_once("fb2.php");
require_once("biblio.php");

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
  
//  if(file_exists($file))
//  {
    //$text = file_get_contents($file);
    $text = fb2_get_content($file);
    $data = fb2_parse($text);
    $xml  = fb2_book_find_tag("?xml",$data);
    //  print_r($xml);
    if($xml["b"] === "?xml"){
      $encoding = $xml["attrs"]["encoding"];
      //if($encoding === "windows-1251") $encoding = "koi8-r";
    }
    else{
      echo "<h1>Invalid fb2 format</h1><br/>";
      echo $text;

      exit;
    }
    $book_title= fb2_book_find_value("book-title",$data);
    $i = fb2_book_find_tag_index("author",$data);
    $first_name = fb2_book_get_value($i+1,"first-name",$data);
    $last_name = fb2_book_get_value($i+4,"last-name",$data);
    $author = $first_name . " " .$last_name;
    $book_id = book_history($book_title,$author,$_SERVER['REQUEST_URI']);
/*  }
  else{
    $text = file_get_contents("http://192.168.0.46:3035/". urlencode($file));
    
    echo "<h2> File not found :" .$file ."</h2>"; 
    echo "<h2> Rename file / Use english only</h2>"; 
    echo $text;
    exit;
  }*/
}
else{
  echo "HELLO";
  exit;
}
 
?>
<!DOCTYPE html>
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
  
  <link rel="stylesheet" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/css/bootstrap-dialog.min.css" />
  <link rel="stylesheet" href="/css/fb2.css" />

  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="/js/loader.js"></script>
  <script type="text/javascript" src="/js/coba_popup.js"></script>
  
</head>

<body onresize="fb2.resize();" onload="fb2.resize();">


 <div id="main-content" onscroll="fb2.onscroll();">
 <div id="book-content" >
<?
  fb2_to_client($data);
?>
  <div class="container-fluid text-center bg-success">END OF FILE</div>
 </div>
 </div>
 
<div id="div-bookmarks" style="overflow:scroll;"></div>
  
<div id="fb2-menu-bottom">
    <table style="width:100%;font-size:1.0em;">
      <tr>
       <td class="fb2-button" onclick="fb2.toogle_description();"><span class="glyphicon glyphicon-book"></span></td>
       <td class="fb2-button" onclick="fb2.zoomin();"><span class="glyphicon glyphicon-zoom-in"></span></td>
       <td class="fb2-button" onclick="fb2.zoomout();"><span class="glyphicon glyphicon-zoom-out"></span></td>
       <td class="fb2-button" onclick="fb2.night();"><span class="glyphicon glyphicon-adjust"></span></td>
       <td class="fb2-button" id="btn-bm-list" ><span class="glyphicon glyphicon-bookmark"></span></td>
       <td class="fb2-button" id="btn-bm-add" ><span class="glyphicon glyphicon-bookmark"></span><span class="glyphicon glyphicon-plus"></span></td>
      </tr>
    </table>      
    <table style="width:100%;font-size:1.0em;">
      <tr>
       <td class="fb2-button"><span id="fb2-bookname"><?=$book_title;?></span></td>
       <td class="fb2-button"><span id="fb2-time">...</span></td>
       <td class="fb2-button"><span id="fb2-info">...</span></td>
       <td class="fb2-button"><span id="fb2-read-time" >...</span></td>
       <td class="fb2-button"><span onclick="rt.reset();" class="glyphicon glyphicon-hourglass"></span></td>
       <td class="fb2-button"><span id="btn-citata" class="glyphicon glyphicon-list-alt"></span></td>
      </tr> 
    </table>
</div>
<!--
<div id="fb2-popup" class="fb2-popup shadow">
  <div class='fb2-popup-header'>
    <span id='fb2-popup-caption'></span>
    <span style='float:right;' id='fb2-popup-close' onclick="fb2.toogle_description();">x</span>
  </div>
  <hr>
  <div>
  <div id="fb2-popup-body" class='fb2-popup-body'></div><hr>
    </div>
  <div class='fb2-popup-footer'></div>
</div>
-->
<div id="dvcitata"></div>
<div id="dv-citata-add"></div>

<script type="text/javascript">
   var url = '<?=urlencode($file);?>';
   var url = '<?=$file;?>';
   var book_rowid = <?=$book_id;?>;
    var scroll= {y:0, proc:0 ,bookmarks: []};  
 
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
                + " " + month[d.getMonth()]  + " " 
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
    var view = id("book-content");
    //var popup = id("fb2-popup");
    var div_bookmarks = $("#div-bookmarks");
    //popup.style.display = "none";
    
    var toogle_count=0;      
      
    function show_info(){
      scroll.proc =(parseInt((scroll.y *10000)/view.clientHeight)/100);
      info.innerText = scroll.proc + "%" ;
    }    
    function noevents(){
      var e = window.event; //e || 
      e.stopPropagation();
      e.preventDefault();
    }
    function onscroll() {
        //scroll.y = window.pageYOffset || document.documentElement.scrollTop;
        scroll.y = main.scrollTop;
        //var data =  //{'scroll': scroll.y};
        var name = window.location.pathname;
        localStorage.setItem(name, JSON.stringify(scroll));
        show_info();
            //console.log(scrolled + 'px');
    };
    function restore(){
     var name = window.location.pathname;
     var data = localStorage.getItem(name);
     if(data){
       data =  JSON.parse(data);
       if(!data.scroll){
         scroll = data;
       }
       if( !scroll.bookmarks){
         scroll.bookmarks = [];
       }
       //main.scrollTop =scroll.y ;
       main.scrollTop =(scroll.proc * view.clientHeight)/100;
     }
     else {
       //fb2_show_popup('block');
     }
     bookmarklist();
    }

    function resize(){
      //var w =  document.documentElement.clientWidth;
      //var h = document.documentElement.clientHeight;
   
    //  view.style.width = w + "px";
    //  view.style.height = (h) + "px";
      show_info();
    }
    //
    //
    id("btn-bm-list").onclick = bookmarks_toggle;
    id("btn-bm-add").onclick = bookmark_add;

        

    function bookmarks_toggle(){
      if(div_bookmarks.css("display") == "block"){
        div_bookmarks.css("display","none");
      }
      else{
        div_bookmarks.css("display","block");
      }
    }        
    
    function bookmarklist(){

      div_bookmarks.empty();
      if(scroll.bookmarks.length ==0){
        div_bookmarks.html("<h1>NO BOOKMARKS</h1>");
        return;
      }
      
      var tb =$('<table class="table">');
     
      scroll.bookmarks .forEach( function(item,i){
        var tr =$('<tr class="bookmark"><td><span class="glyphicon glyphicon-bookmark"></span></td><td>'+item+'</td></tr>');
        tr.click(function(){
          bookmark_set(item);
        });
        var td = $('<td><span class="glyphicon glyphicon-trash" style="width:30%;"></span><td>');
        td.click(function(e){
          scroll.bookmarks.splice(i,1); 
          bookmarklist();
          noevents();
        });
        tr.append(td);
        tb.append(tr);
      });
      div_bookmarks.append(tb);
      div_bookmarks.css("display","block");
    }
    function bookmark_set(item){
      main.scrollTop =(item * view.clientHeight)/100;
    }
    function bookmark_add(){
      //var range = document.caretRangeFromPoint(0,0);
      var proc =(parseInt((scroll.y *10000)/view.clientHeight)/100);
      scroll.bookmarks.push(proc);
      bookmarklist();
    }
    var zoom ={ 
       "step": 5,
       mesure : "%",
       "main": {
         left : 25,
         width:50
       },
       "popup" :{
         left : 20,
         width:60
       }};
    function zoomit(){
      main.style.left = zoom.main.left + zoom.mesure;
      main.style.width = zoom.main.width  + zoom.mesure;
      popup.style.left = zoom.popup.left + zoom.mesure;
      popup.style.width = zoom.popup.width  + zoom.mesure;
      //onscroll();
    }
    function zoomin(){
      zoom.main.left -= zoom.step;
      zoom.main.width += zoom.step *2;
      
      zoom.popup.left -= zoom.step;
      zoom.popup.width += zoom.step *2;
      zoomit();
    }
    function zoomout(){
      zoom.main.left += zoom.step;
      zoom.main.width -= zoom.step*2;
      zoom.popup.left += zoom.step;
      zoom.popup.width -= zoom.step *2;

      zoomit();
    }
    function full_screen(e){
      if(e.changedTouches[0].clientY < 100 && toogle_count % 2 === 0){
        toggleFullScreen();
        noevets();
      }
      toogle_count++;
    }
    var mode_night = true;
    function night(){
      main.style.backgroundColor = (mode_night ? "#f1f1f1" : "#000000");
      main.style.color = (mode_night ? "#000000" : "#fafafa");
      
      //popup.style.backgroundColor = (mode_night ? "#f1f1f1" : "#000000");
      //popup.style.color = (mode_night ? "#000000" : "#fafafa");
      
      mode_night = !mode_night;
    }
    
    var popup_desc = null;
    function toogle_description() {
      if(!popup_desc){
        var dv = $("<div id='dv-book-info'></div>");
        $(document.body).append(dv);
      
        popup_desc = new fb2popup("#dv-book-info",
        { "caption":"Document info",
          "buttons":[
          {
           "caption":"close",
           "action" : function(pp){
            pp.visible= false;
            }
          }
        ]
        });
        popup_desc.content = htm_book_info;
      }
      popup_desc.visible=true;
  
       //popup.style.display = popup.style.display ==="block" ? "none" : "block";
       noevents();
     }
    function current_offset(){
      var proc =(parseInt((scroll.y *10000)/view.clientHeight)/100);
      return proc;
    }
    function book_delete_quote(quoteid){
      $.get("/php/biblio.php?action=book-quote-delete&quoteid=" + quoteid,function(result){
        alert(result);
        dvcit.refresh();  
      });
    }
    function navigate(url){
      var win = window.open(url, '_blank');
      win.focus();
    }
    function navigate_local(url){
      if(url.indexOf("#http:") == 0){
        navigate(url.substring(1));
      }
      else{
        bookmark_add();
        location.href = url;
      }
    }
    
    var popup_quote = null;
    function create_quote_popup(){
     popup_quote = new fb2popup("#dv-citata-add",{
     "caption":"Save quote",
     "buttons":[
      {
       "caption":"save quote",
       "action" : function(pp){
          $.get("/php/biblio.php?action=book-add-quote&bookid=" + book_rowid 
          + "&offset=" + fb2.current_offset() 
          + "&text="+ encodeURIComponent(pp.content),
          function(result){
            try{
              var x =JSON.parse(result);
              if( x && x.result) {
                pp.visible= false;  
              }
              else {
                alert(result);
              }
            }
            catch(e){
             alert(e + "\n" + result) ;   
            }
           
          });
       }
     },
     {
       "caption":"close",
       "action" : function(pp){
          pp.visible= false;
      }
     }
     ]
   });
    }
    function show_quote(){
      
      var text = document.getSelection().toString();
      if(text && text.length >0){
        popup_quote.visible= true;
        popup_quote.content = text;
      }
    }
    
    create_quote_popup();
    //
    //
    //
    return {
      show_info:show_info,
      onscroll:onscroll,
      resize:resize,
      full_screen:full_screen, 
      restore:restore,
      toogle_description:toogle_description,
      zoomout:zoomout,
      zoomin:zoomin,
      "night":night,
      "current_offset":current_offset,
      "bookmark_set":bookmark_set,
      "delete_quote":book_delete_quote,
      "navigate_local":navigate_local,
      "navigate" :navigate,
      "show_quote":show_quote
      };
    })();
    
/*

*/
var dvcit;    
var htm_book_info;  
 
 window.addEventListener('load', function(){
   $(document).keyup(function(e){
    if(e.ctrlKey && e.keyCode == 81) { // ctrl  Q
      fb2.show_quote();
      }
   });
   dvcit = new fb2popup("#dvcitata",
   { "caption":"Book quotes",
    "url":"/php/biblio.php?action=citata-list&bookid=" +book_rowid,
     "buttons":[
     {
       "caption":"close",
       "action" : function(pp){
          pp.visible= false;
      }
     }
     ]
   });
    
   
   $("#btn-citata").click(function(){
      dvcit.visible=!dvcit.visible;
      if(dvcit.visible){
        dvcit.refresh();
      }
   });
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
     //--------------------------------
     htm_book_info = $("#fb2-description").html() + "<hr><div style='height:30px'></div>";
     $("#fb2-description").remove();
     //$("#fb2-popup-body").html(htm);
   
     var title = $("#fb2-book-title").text();
     //id("fb2-popup-caption").innerText = title;
     fb2.restore();
    
 });

 </script>
</body>
</html>
