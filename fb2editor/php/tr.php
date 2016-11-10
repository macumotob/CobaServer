<?
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
$dbname = 'enru.sqlite';

function translate($db,$word){
  $results = $db->query("SELECT * FROM words where word like '$word%' ");
  $i=0;
  $s ="";
  while ($row = $results->fetchArray()) {
   //   if($i > 0) $s .= ",";
//      $s.= ("{\"id\":" . $row['id'] . ",\"word\":\"" . $row['word']. "\"}");
      $s .=  "<p>". $row["word"]."</p>";

      $i++;
  }
//  $s .= "]";
  return array("i"=>$i,"data"=>$s);

}

if(isset($_POST["exec"])){
  $sql = urldecode($_POST["exec"]);
  $db = new SQLite3($dbname);
  //$stmt = $db->prepare($sql);
  //$stmt = $db->query($sql);
  if($db->query($sql)){
    echo "{\"result\": true,\"msg\":\"".$sql."\"}";  
  }
  else {
    echo "{\"result\": false,\"msg\":\"" . SQLite3::lastErrorMsg()  ."\"}";  
  }
  $db->close();
  exit;
}
if(isset($_POST["select"])){
  $sql = urldecode($_POST["select"]);
  
  $db = new SQLite3($dbname);
  $result = $db->query($sql) or die("Error in query: <span style='color:red;'>$sql</span>"); 

  $cols = $result->numColumns(); 
  $n=0;
  echo "[";
  while ($row = $result->fetchArray()) { 
     if($n > 0) echo ",";
     echo "{";
      for ($i = 1; $i < $cols; $i++) { 
          if($i>1) echo ",";
          echo '"' .$result->columnName($i) . '":"'; 
          echo $row[$i] . '"'; 
     } 
     $n++;
     echo "}";
  }
  echo "]";
   
  $db->close();
  exit;
}
if(isset($_POST["id"]) AND isset($_POST["txt"])){
  //if (!empty($_POST)){
  $id= $_POST["id"];
  $txt = urldecode($_POST["txt"]);
  
  $db = new SQLite3($dbname);
  
  $stmt = $db->prepare("UPDATE words SET word=? where id =?");
  $stmt->bindValue(1, $txt);
  $stmt->bindValue(2, $id);
  $stmt->execute();
  if($db->query("UPDATE words SET word='$txt' where id =$id")){
    echo "{\"id\":$id,\"txt\":\"". rawurlencode($txt). "\"}";  
  }
  else{
    echo "{\"id\":$id,\"txt\":\"error\"}";  
  }
  $db->close();
  
  exit;
}
//rawurlencode
if(isset($_GET["w"])){
  $db = new SQLite3($dbname);
  $word = $_GET["w"];
$terms = array(".",",","!",";");
  $word = str_replace($terms,"",$word);
  $results = $db->query("SELECT * FROM words where word like '$word%' ");
  $i=0;
  $s ="[";
  while ($row = $results->fetchArray()) {
      if($i > 0) $s .= ",";
      $s.= ("{\"id\":" . $row['id'] . ",\"word\":\"" . $row['word']. "\"}");
      $i++;
  }
  $s .= "]";
  echo $s;
  $db->close();
  exit;
}

if(isset($_GET["word"])){
  $db = new SQLite3($dbname);
  $word = $_GET["word"];
  $terms = array(".",",","!",";","\"","'");
  $word = str_replace($terms,"",$word);

 $d = translate($db,$word);
 if($d["i"] > 0){
   echo $d["data"];
   $db->close();
   exit;
 }
 $len = strlen($word);
 $begin = substr($word,0,$len-1);
 $end = substr($word,$len-1);

 echo "<b>".$end ."</b> [" . $begin ."]"; 
 $d = translate($db,$begin);
 if($d["i"] > 0){
   echo $d["data"];
   $db->close();
   exit;
 }

 header('Location: https://translate.google.com/#en/ru/'.$word);
 exit;

  $results = $db->query("SELECT * FROM words where word like '$word%' ");
  $i=0;
  
  while ($row = $results->fetchArray()) {
      echo "<pre>". $row["word"]."</pre>";
      //$s.= ("{\"id\":" . $row['id'] . ",\"word\":\"" . $row['word']. "\"}");
      $i++;
  }
  

  echo "<br/>Count : ".$i;
  $db->close();
  exit;
}
          
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>fb2editor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta charset="utf-8" /> 
  <link rel="stylesheet" href="http://134.249.167.212:3000/css/bootstrap.min.css" />
  <link rel="stylesheet" href="http://134.249.167.212:3000/css/bootstrap-dialog.min.css" />

  <script type="text/javascript" src="http://134.249.167.212:3000/js/jquery.min.js"></script>
  <script type="text/javascript" src="http://134.249.167.212:3000/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="http://134.249.167.212:3000/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="http://134.249.167.212:3000/js/loader.js"></script>
  <script>
  
  window.addEventListener('click',function(){	mb.onclick();});
  function edit(id){
    window.location = "/php/tr.php?id=" + id;
  }
  function save(){
    var id = $("#ident").text();
    var txt = encodeURIComponent($("#txt").val());
    var data = {id:id,txt:txt};
    $.post("/php/tr.php", data, function(s){
        var result = JSON.parse(s);
        var t = decodeURIComponent(result.txt);
        edit(result.id);
    });
  }
  function trans(){
    var word = $("#totrans").val();
    $.get("/php/tr.php?w=" + word, function(txt){
        var result = JSON.parse(txt);
        var s= "";
        for(var i = 0; i< result.length;i++){
          var x = result[i];
          s += "<div data-method='edit' data-args='" + x.id +"'>" + x.id +"<pre>" +decodeURIComponent(x.word) +"</pre>";
          s += "<span class='glyphicon glyphicon-user'></span></div></br><hr/>";
        }
        $("#result").html(s);
//        var t = decodeURIComponent(result.txt);
//        edit(result.id);
    });
  }
  if ('speechSynthesis' in window) {
    console.log('Synthesis support. Make your web apps talk!');
  }

  if ('SpeechRecognition' in window) {
    console.log('Speech recognition support. Talk to your apps!');
  }
  </script>
<style>
pre {
    display: block;
    font-family: monospace;
    white-space: pre;
    margin: 1em 0;
} 
</style>
</head>

<body>
<?php
if(isset($_GET["id"])){
  echo "<h1>Перевод- редактирование</h1>";
  $db = new SQLite3($dbname);
  $id = $_GET["id"];
  $results = $db->query("SELECT * FROM words where id =$id");
  while ($row = $results->fetchArray()) {?>
      <div class='row' ><b id='ident'><?=$row['id'];?></b>
      <textarea rows='14' cols='80' id='txt'><?=$row['word'];?></textarea>
      <button class='btn btn-success' data-method='save'>
      <span class='glyphicon glyphicon-save'></span></button>
      </div></br><hr/>
  <?}
  $db->close();
}
else { ?>
  <input type="text" id="totrans"/>
      <button class='btn btn-success' data-method='trans'>
      <span class='glyphicon glyphicon-save'></span></button>
<div id="result"></div>
<?}
?>
</body>
</html>