<? 
// chat.php
error_reporting(E_ALL);
require_once("sqlite_ext.php");
$dbname="chat.sqlite";
$db = new MyDB($dbname);
//$db->busyTimeout(5000);
//$db->exec('PRAGMA journal_mode = wal;');
$ip = $_SERVER["REMOTE_ADDR"];

function send_result($result,$text){
  echo '{"result":'. ($result ? "true" : false) .',"msg":"' .$text .'"}';
}
function fetch_messages($sql,$whom){
  global $dbname,$db;
 // $whom = $_GET["whom"];
  //$rowid = $_GET["rowid"];
  
  //$db = new MyDB($dbname);
  //$sql ="select * from chat where (who='". $whom ."' and whor=0 )or (whom='".$whom."' and whomr=0)";
  echo '{"data":[';
  if($q = $db->query($sql)){
    $i=0;
    while($row = $q->fetchArray()){
      if($i>0)echo ",";
      echo '{"id":'. $row["id"] .',"message":"' .rawurlencode($row["message"]) .'"';
      //echo ',"who":"'.$row["who"] . '","dt":"' .$row["dt"] .'"}';
      echo ',"who":"'.$row["who"] . '","whom":"' . $row["whom"]. '","dt":"' .$row["dt"] 
          .'","type":'.$row["message_type"].'}';
      $i++;
    }
  }
  echo '],"users":[';
  if($q = $db->query("select * from chatusers")){
    $i=0;
    while($row = $q->fetchArray()){
      if($i>0)echo ",";
      echo '{"id":'.$row["id"] .',"nick":"' . $row["nick"] .'"';
      echo ',"online":'.$row["online"] . '}';
      $i++;
    }
    
  }
  echo '],"count":' .$i .',"result":true}';
  $db->close();
  //unset($db);

}

function get_messages(){
  global $dbname,$db;
  $who  = $_GET["who"];
  $whom = $_GET["whom"];
  $sql="select * from chat where ((who='$who' and whom='$whom') or  ( who='$whom' and whom='$who')) and ((who='$who' and whor=0) or (whom='$who' and whomr=0))";
  $sql="select * from chat where ((who='$who' and whor=0) or (whom='$who' and whomr=0))";
  fetch_messages($sql,$who);
  //"select * from chat where ((who='$who' and whom='$whom') or ( who='$whom' and whom='$who')) and who=",$who);
  
 //$db->close();
 /*exit;
  $db = new MyDB($dbname);
  $sql ="select * from chat where (who='". $whom ."' and whor=0 )or (whom='".$whom."' and whomr=0)";
  echo '{"data":[';
  if($q = $db->query($sql)){
    $i=0;
    while($row = $q->fetchArray()){
      if($i>0)echo ",";
      echo '{"id":'. $row["id"] .',"message":"' .rawurlencode($row["message"]) .'"';
      //echo ',"who":"'.$row["who"] . '","dt":"' .$row["dt"] .'"}';
      echo ',"who":"'.$row["who"] . '","whom":"' . $row["whom"]. '","dt":"' .$row["dt"] .'"}';
      $i++;
    }
  }
  echo '],"users":[';
  if($q = $db->query("select * from chatusers")){
    $i=0;
    while($row = $q->fetchArray()){
      if($i>0)echo ",";
      echo '{"id":'.$row["id"] .',"nick":"' . $row["nick"] .'"';
      echo ',"online":'.$row["online"] . '}';
      $i++;
    }
    
  }
  echo '],"count":' .$i .',"result":true}';
  $db->close();*/
}
function messages_by_date(){
  //global $dbname;
  $who =$_GET["who"];
  $whom =$_GET["whom"];
  
  $dt =$_GET["data"];
  $sql="";
 // $sql="select * from chat where (who='$who' or whom='$nick') and date(dt) between date('now','-1 day') and date('now')";
 if($dt =="today"){
    $sql="select * from chat where ((who='$who' and whom='$whom') or (who='$whom' and whom='$who')) and date(dt) = date('now')"; 
 }
 else if($dt =="all"){
   $sql="select * from chat where (who='$who' or whom='$who')"; 
 }
 else{
   $sql="select * from chat where (who='$who' or whom='$who') and date(dt) = date('now','$dt day')";
 }
 
  //$sql="select * from chat where (who='$nick' or whom='$nick') and date(dt) = date('now')";
  fetch_messages($sql,$who);
}
function message_delete(){
  global $db;
  $rowid=$_GET["rowid"];
  if($db->query("delete from chat where id=$rowid")){
    send_result(true, "deleted row $rowid");
  }
  else{
    send_result(false, "error deleted row $rowid");
  }
  $db->close();
}
function set_readed(){
  global $dbname,$db;
  $ids = $_GET["ids"];
  $nick =$_GET["nick"];
  echo "readed :" .$ids . " db:" .$dbname;
 // $db = new MyDB($dbname);
  $rcount =0;
  $sql ="update chat set whor=1 where id in (".$ids.") and who='".$nick."'";
  
  if($q = $db->query($sql)){
    $rcount =$db->changes();
  }
  $sql ="update chat set whomr=1 where id in (".$ids.") and whom='".$nick."'";
  if($q = $db->query($sql)){
    $rcount += $db->changes();
  }
  echo '{"result":true,"count":' .$rcount .'"}';
  $db->close();
}
function send_message(){
  global $dbname,$db;
  $who = $_POST["who"];
  $whom = $_POST["whom"];
  $message = $_POST["message"];
  if(strlen($message) == 0){
    echo '{"result":false,"msg":"empty message"}';
    exit;
  }
 // $db = new MyDB($dbname);

  $stmt = $db->prepare("insert into chat(who,whom,message) values(?,?,?)");
  $stmt->bindValue(1, $who);
  $stmt->bindValue(2, $whom);
  $stmt->bindValue(3, $message);
  $stmt->execute();
  echo '{"result":true,"msg":"inserted"}';
  $db->close();
  exit;
}
function compress($source, $destination, $quality) { 
  $info = getimagesize($source); 
  if ($info['mime'] == 'image/jpeg')
    $image = imagecreatefromjpeg($source); 
  elseif ($info['mime'] == 'image/gif') 
    $image = imagecreatefromgif($source);
  elseif ($info['mime'] == 'image/png') 
    $image = imagecreatefrompng($source); 
    
  imagejpeg($image, $destination, $quality);
  return $destination; 
  }
//  $source_img = 'source.jpg'; $destination_img = 'destination .jpg'; $d = compress($source_img, $destination_img, 90); 

function send_image(){
  global $dbname,$db;
  $who = $_POST["who"];
  $whom = $_POST["whom"];
  $message = $_POST["message"];
  echo strlen($message) . "  ";
  $file =  $_POST["file"];
  $pth = "../uploads/". $who. "_". $_POST["file"];
  $test = "../uploads/". $who. "_b64_". $_POST["file"];
  //echo strlen($message) . " file: " .$file . " root:" . $_SERVER['DOCUMENT_ROOT'];
  
   if (!$handle = fopen($pth, 'wb')) {
         echo "Cannot open file ($pth)";
         exit;
    }
   //$data = explode(',', $message);
     $value = base64_decode($message);
    // file_put_contents($test,$message);
   // if(count($data) ==2 )
    //{
    //  $value= $data[1];
    //}
    // Write $somecontent to our opened file.
    if (fwrite($handle, $value) === FALSE) {
        echo "Cannot write to file ($pth)";
        exit;
    }

    //echo "Success, wrote to file ($pth)";

    fclose($handle);
 
 // $destination_img = "../uploads/". $who. "_thumb_". $_POST["file"]; 
 // $d = compress($pth, $destination_img, 0); 

//  $db = new MyDB($dbname);

  $stmt = $db->prepare("insert into chat(who,whom,message,message_type) values(?,?,?,1)");
  $stmt->bindValue(1, $who);
  $stmt->bindValue(2, $whom);
  $stmt->bindValue(3, $file);
  $stmt->execute();
  echo '{"result":true,"msg":"inserted"}';
  $db->close();
  exit;
}
/*
  global $dbname;
  
  $db = new MyDB($dbname);
  $db->query("update chatusers set concount=concount+1 where ip='$ip'");
  $db->close();
  */
if(isset($_GET["action"])){
  $action =$_GET["action"];
  switch($action){
   /* case "login":
      login();
    break;
    case"register":
     register();
    break;*/
    case "messages":
     get_messages();
    break;
    case "set_readed":
    set_readed();
    break;
    case "bydate":
      messages_by_date();
    break;
    case "delete":
     message_delete();
    break;
  }
  exit;
}

if(isset($_POST["action"])){
  $action = $_POST["action"];

  switch($action){
    case"send":
      send_message();
    break;
    case "image":
      send_image();
    break;
  }
    
  exit;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Chat.Maxbuk.com</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta charset="utf-8" /> 

   
  <link rel="stylesheet" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/css/bootstrap-dialog.min.css" />
  <link rel="stylesheet" href="/css/sqlite.css" />
  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="/js/loader.js"></script>
  <script type="text/javascript" src="/js/table.js"></script>
  
  <style>
  body{ 
    background-color: black; 
    color:#fa9f00;
  }
  #main-content{
    color:white;
  }
  .msg-1{
    color:#00AA00;
  }
  .my-message{
    color: gray;
    background-color:#000000;
    margin-bottom:30px;
    
  }
  .not-my-message{
    color: yellow;
    background-color:#131313;
    margin-bottom:30px;
  }
  </style>
<script>

 var nick;
 var whom;
 var rowid=0;
 var message_count=0;
 var message_total=0;
 var users_for_message =[];
 var in_message_loop = false;
 
 function linkify(inputText) {
    var replacedText, replacePattern1, replacePattern2, replacePattern3;

    //URLs starting with http://, https://, or ftp://
    replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
    replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');

    //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
    replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');

    //Change email addresses to mailto:: links.
    replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
    replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

    return replacedText;
}
function parse_json(s){
  try{
    var data =JSON.parse(s);
    return data;
  }
  catch(e){
    alert("parse json error in :" + s);
  }
}
function scroll_to_last(){
  $("#messages").scrollTop($("#messages")[0].scrollHeight);
} 
function delete_message(rowid){
 if(confirm("delete message: " +rowid)){
   $.get("/php/chat.php?action=delete&rowid=" +rowid,function(data){
     var result=JSON.parse(data);
     if(result.result){
       $("#msgdv"+rowid).empty();
       speak_message("message deleted");
     }
     else{
       alert(result.msg);
     }
   });
  
 }
}

function show_message(item){
  
  var pname = item.who == nick ? "my-message" : "not-my-message";
  var text = decodeURIComponent(item.message);
  var dvmain = $("<div id='msgdv" + item.id+"' class='" + pname +"'></div>");
  var dv = $("<div><span>" + item.id +" </span><span> " + item.who + "</span> --&gt;<span> " + item.whom + "</span></div>");
    
  $(dv).append("<span class='pull-right'>&nbsp;" + item.dt +"</span>");
  $(dv).append("<span style='cursor:pointer;' class='pull-right glyphicon glyphicon-trash' data-method='delete_message'"
  + " data-args='"+item.id+"'></span>");
  
  $(dvmain).append(dv);
  if(item.type == 1){
    dv = $("<div><span>" +text +"</span><br/></div>");
    var img= $("<img style='width:50%;margin-left:25%;position:relative;'/>").attr('src',"/uploads/"
        + item.who +"_" +item.message);
    var scale = 0.2;
    $(dv).append(img);
    $(dvmain).append(dv);
   /* $(img).css({
      width: img.width*scale, 
      height: img.height*scale,
    });*/
  //  $("#messages").append(img);
/*
var url ="/uploads/" + nick +"_" +item.message;
$.ajax({ 
    url : url, 
    cache: true,
    processData : false,
    }).always(function(){
      var scale = 0.2;
     // var img = document.createElement('img');
     var img =$("<img width='90%'/>").attr("src", url);
        $("#messages").append(img.fadeIn());
       img.css({
               width: img.width*scale, 
               height: img.height*scale
              });
   });   
*/
  }
  else{
   // var text = decodeURIComponent(item.message);
    text =linkify(text);
    var dv =$("<div><pre class='" + pname + "'>"+ text+"</pre></div>");
    $(dvmain).append(dv);
    //speak_message(text);
  }
   $("#messages").append(dvmain);
  message_count++;
  message_total++;
}
function show_messages(data,ids){
 
  if(message_count>1000){
    message_count =0;
    $("#messages").empty();
  }
  if(data.result){
    for(var i=0;i<data.data.length;i++){
      var item = data.data[i];
      if(ids) ids.push(item.id);
      show_message(item);
    }
    
    if(data.users){
      show_users(data.users);
    }
    
    if(ids && ids.length > 0) {
      //$("#messages").scrollTop($("#messages")[0].scrollHeight);
      scroll_to_last();
      send_readed(ids.join(","));
    }
  }
}

function show_users(users){
 // $("#users").empty();
  $("#users-list").empty();
  for(var i = 0;i < users.length;i++){
    var user =users[i];
    var checked = users_for_message.indexOf(user.id) !==-1;
    
   // var s ="<div>"+user.nick+"<input type='checkbox' data-method='add_user_to_list' data-args='"
   // + user.id+"'"+ (checked ? "checked " : "")+"/></div>";
   // $("#users").append($(s));
    if(user.nick !== nick){
      var style = "style='cursor:pointer;color:" + (user.nick == whom ? "yellow" : "blue") + "'";
     var s = "<li data-method='add_user_to_list' data-args='" + user.nick +"'><a " + style+ ">" + user.nick +"</a></li>";
      $("#users-list").append($(s));
    }
  }
}
function show_by_date(dt){
  $("#messages").empty();
  $.get("/php/chat.php?action=bydate&who=" + nick +"&whom="+whom+"&data="+dt,function(result){
    var data=JSON.parse(result);
    show_messages(data,null);
    scroll_to_last();
    //$("#messages").scrollTop($("#messages")[0].scrollHeight);
  });
}
function add_user_to_list(user){
  whom =user;
  $("#user-whom").html(whom);
  show_by_date('today');
  //users_for_message.push(parseInt(userid));
}
function check_messages(){
  if(in_message_loop){
    return;
  }
  in_message_loop =true;
  var ids =[];
  var url ="/php/chat.php?action=messages&who=" + nick+"&whom="+whom;
  $.get(url,function(data){
    data= parse_json(data);
    show_messages(data,ids);
    in_message_loop=false;
    if(data.data.length > 0){
      speak_message("you have a messages");
    }
    //console.log(data);
  });
}
function send_readed(ids){
  //console.log(ids);
  var url ="/php/chat.php?action=set_readed&ids="+ids+"&nick="+nick;
  $.get(url,function(result){
    //console.log(result);
  });
}
function message_loop(){
  var tm = setInterval( function(){
   check_messages();
  },1000);
}
function send_message(){
  if(!nick){
    alert("need login");
    return;
  }
  if(!whom){
    alert("selct whom!");
    return;
    
  }
  var msg = $("#msg").val();
  if(msg.length == 0){
    $("#msg").focus();
    return;
  }
  //var whom = "coba";
  var data = {"action" : "send", "who" : nick, "message" : msg, "whom" : whom};
  $.post("/php/chat.php", data, function ( result){
    $("#msg").val("");
    $("#msg").focus();
    check_messages();
  });
}

function send_image(img,file){

  var data = {"action" : "image", "who" : nick, "message" : img, "whom" : whom, "type":1,"file":file};
  
  $.post("/php/chat.php", data, function ( result){
    check_messages();
  });
  return;
/*  
   formdata = new FormData();
   
formdata.append("action", "img");
formdata.append("who", nick);
formdata.append("message" ,img);
formdata.append("whom" ,whom);
 */         
   $.ajax({
            url: "/php/chat.php",
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (res) {
              console.log("upload:",res);
            },
            error: function (response, status, e) {                                                                     
               }
           });  

}
function show_login(){
  $.get("/php/login.php",function(html){
    $("#messages").html(html);
  });
}
window.addEventListener('click',function(){	mb.onclick();});

$(function() {
  $('#msg').keydown(function (e) {
   // if (e.ctrlKey && e.keyCode == 13) {
    if (e.keyCode == 13) {
      send_message();
      e.preventDefault();
      e.stopPropagation();

    }
});
 show_login();
});
/*
//Author James Harrington 2014
function base64(file, callback){
  var coolFile = {};
  function readerOnload(e){
    var base64 = btoa(e.target.result);
    coolFile.base64 = base64;
    callback(coolFile)
  };

  var reader = new FileReader();
  reader.onload = readerOnload;

  var file = file[0].files[0];
  coolFile.filetype = file.type;
  coolFile.size = file.size;
  coolFile.filename = file.name;
  reader.readAsBinaryString(file);
}*/
//base64( $('input[type="file"]'), function(data){
//  console.log(data.base64)
//})
function encodeImageFileAsURL(){

    var filesSelected = document.getElementById("inputFileToLoad").files;
    if (filesSelected.length > 0)
    {
        var fileToLoad = filesSelected[0];

        var reader = new FileReader();

        reader.onload = function(fileLoadedEvent) {
            var srcData = fileLoadedEvent.target.result; // <--- data: base64
            srcData= srcData.substr(srcData.indexOf(',')+1);
            //console.log(srcData.length);
            send_image(srcData,fileToLoad.name);
        }
        reader.onerror = function(event) {
          alert("File could not be read! Code " + event.target.error.code);
        };
        reader.readAsDataURL(fileToLoad);
        //reader.readAsBinaryString(fileToLoad);
    }
}
//var voices = window.speechSynthesis.getVoices();

function speak_message(text){
  var msg = new SpeechSynthesisUtterance();
 // var voices = window.speechSynthesis.getVoices();
 // msg.voice = voices[1]; // Note: some voices don't support altering params
  msg.voiceURI = 'native';
  msg.volume = 1; // 0 to 1
  msg.rate = 1; // 0.1 to 10
  msg.pitch = 2; //0 to 2
  msg.text = text;
  msg.lang = 'en-US';

  msg.onend = function(e) {
    console.log('Finished in ' + event.elapsedTime + ' seconds.');
  };

  speechSynthesis.speak(msg);
}
</script
<body>
<input id="inputFileToLoad" type="file" style="visibility:hidden;" onchange="encodeImageFileAsURL();" />
<!--
 <form method="post" enctype="multipart/form-data"  action="/php/upload.php">
      <input type="file" name="images[]" id="images" multiple />
      <button type="submit" id="btn">Upload Files!</button>
    </form>
-->
  <div class="container-fluid">
    <div class="fill_height" style="left:0%;width:80%;position:fixed" id="main-content">
    <div id="messages" class="pre-scrollable fill_height" style="width:100%"></div>
    
    
  <nav class="navbar navbar-inverse navbar-fixed-bottom">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#"><?=$ip;?></a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a id="user-nick">Home</a></li>
      <li class="active"><a id="user-whom">Whom</a></li>
      <li data-method="show_by_date" data-args="0"><a>today</a></li>
      <li data-method="show_by_date" data-args="-1"><a>-1 day</a></li>
      <li data-method="show_by_date" data-args="all"><a>all</a></li>
      <li>
      <textarea rows="3" cols="80" style="color:yellow;background-color:black;" id="msg"></textarea>
      </li>
      <li>  
        <span class="glyphicon glyphicon-send" style="cursor:pointer;font-size:2em;" data-method="send_message"> </span>
        <span class="glyphicon glyphicon-download-alt" style="cursor:pointer;font-size:2em;" data-method="inputFileToLoad.click"> </span>
       <!-- <button class="btn btn-success visible" onclick="inputFileToLoad.click();" id="select-button">Select files</button>-->
      </li> 

    </ul>
    </div>
  </nav>
  </div>
    <div style="left:80%;width:20%;position:fixed;" id="right-panel">
      <div>Maxbuk.com chat ip:<?=$ip;?></div>
    <div>
    <nav>
      <ul class="nav" id="users-list"></ul>
    </nav>
  </div>

</body>
</html>