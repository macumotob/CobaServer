<?php
//login.php

error_reporting(E_ALL);
require_once("sqlite_ext.php");
$dbname="chat.sqlite";
$db = new MyDB($dbname);
$ip = $_SERVER["REMOTE_ADDR"];

function register(){
  
  global $dbname,$db,$ip;
  
  $nick = $_GET["nick"];
  $pwd = $_GET["pwd"];
  
  if(strlen($nick) == 0){
    echo '{"result":false,"msg":"username is empty","error":1}';
    $db->close();
    exit;
  }
 $q = $db->query("select count(*) as c from chatusers where nick='$nick'");
 $row = $q->fetchArray();
if($row["c"] == 0){
  if(strlen($pwd) == 0){
    echo '{"result":false,"msg":"password is empty","error":2}';
    $db->close();
    exit;
  }
  
  $sql = "insert into chatusers(ip,nick,pwd) values('$ip','$nick','$pwd')";  
  if($db->query($sql)){
  echo '{"result":true,"msg":"registered : ' .$ip.'","ip":"'.$ip.'"}';
}
else
{
  echo '{"result":false,"msg":"db error"}';
}
}
else{
  echo '{"result":false,"msg":"exists"}';
}
$db->close();
}

function login(){
  global $dbname,$db,$ip;
  $nick = $_GET["nick"];
  $pwd  = $_GET["pwd"];
  
  if(strlen($nick) == 0){
    echo '{"result":false,"msg":" empty username ","error":1}';
    $db->close();
    exit;
  }
  if(strlen($pwd) == 0){
    echo '{"result":false,"msg":" empty password","error":2}';
    $db->close();
    exit;
  }
$q = $db->query("select count(*) as c from chatusers where nick='$nick' and pwd='$pwd'");
$row = $q->fetchArray();
if($row["c"] == 0){
  echo '{"result":false,"msg":" not registered or invalid para ","error":3}';
}
else{
  $sql = "update chatusers set online=1 where nick='$nick' and pwd='$pwd'";  
  if($db->query($sql)){
    echo '{"result":true,"msg":"log : ' .$ip.'","ip":"'.$ip.'","error":0}';
  }
  else
  {
    echo '{"result":false,"msg":"db error","error":4}';
  }
}
$db->close();
exit;
}

if(isset($_GET["action"])){
  $action =$_GET["action"];
  switch($action){
    case "login":
      login();
    break;
    case"register":
     register();
    break;
  }
  exit;
}


?>
   <div class="form-group">
    <label class="control-label col-sm-2" for="nick">Nick:</label>
    <div class="col-sm-10"> 
      <input type="text" class="form-control chat-input" id="nick" placeholder="Enter nickname" value="waswas"/>
    </div>
    <label class="control-label col-sm-2" for="pwd">Password:</label>
    <div class="col-sm-10"> 
      <input type="password" class="form-control chat-input" id="pwd" placeholder="Enter password" value=""/>
    </div>
   </div>
  </div>
  <div class="row">
    <div class="col-xs-3"></div>
    <button class="col-xs-3 btn btn-success" data-method="register">register</button>
    <button class="col-xs-3 btn btn-success" data-method="login">login</button>
    <div class="col-xs-3"></div>
 </div>
 <script>
 //var nick;
 function init_messages(data){
  if(data.result){
    $("#messages").empty();
    $("#user-nick").html(nick);
    show_by_date(0);
    
    message_loop();
  }
  else{
      alert(data.msg);
    }
 }
 
 function register(){
  nick = $("#nick").val();
  var pwd = $("#pwd").val();
  
  $.get("/php/login.php?action=register&nick=" + nick +"&pwd="+pwd,function(data){
    data=JSON.parse(data);
    init_messages(data);
  });
}

function login(){
  nick = $("#nick").val();
 var pwd = $("#pwd").val();

  $.get("/php/login.php?action=login&nick=" + nick+"&pwd="+pwd,function(data){
    data=JSON.parse(data);
    init_messages(data);
  });
}


 </script>