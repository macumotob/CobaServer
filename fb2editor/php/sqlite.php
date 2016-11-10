<?
//$dbname = 'enru.sqlite';

require_once("sqlite_ext.php");

//include("sqlite_ext.php");
function create_table_tabs(){
   $table =  $_GET["table"];
   //$dbname =  '../data/' . $_GET["dbname"] ;
   $dbname =  $_GET["dbname"] ;
   $arg =$dbname.','.$table;
    ?>
  
  <table class="table">
    <tr>
    <td>Database : </td>
    <td><b><?=$dbname;?></b></td>
    <td>Table : </td>
    <td><b><?=$table;?></b></td>
    </tr>
  </table>

  
  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#" data-method="show_table_info" data-args="<?=$arg?>">Structure</a></li>
    <li><a data-toggle="tab" href="#" data-method="show_table_data" data-args="<?=$arg;?>" id="show-table-data">Data</a></li>
    <li><a data-toggle="tab" href="#" data-method="show_table_indexes" data-args="<?=$arg;?>">Indexes</a></li>
    <li><a data-toggle="tab" href="#" data-method="show_table_triggers" data-args="<?=$arg;?>">Triggers</a></li>
    <li><a data-toggle="tab" href="#" data-method="show_table_ddl" data-args="<?=$arg;?>">DDL</a></li>
    <li><a data-toggle="tab" href="#" data-method="show_table_sql" data-args="<?=$arg;?>">SQL</a></li>
  </ul>
  <div class="tab-content" id="table-info-content">
  </div>
  <?
}
function create_database_form(){
    ?>
   <form class="form-horizontal" role="form">
    <div class="input-group">
      <input type="text" id="dbname"  class="form-control" placeholder="Database file name" /> 
      <input type="text" id="action" class="form-control"  value="create_db"/> 
    </div>
  </form>
  <?
}
function create_file_form(){
    ?>
   <form class="form-horizontal" role="form">
    <div class="input-group">
      <input type="text" id="filename"  class="form-control" placeholder="Sql file name without ext" /> 
      <input type="text" id="action" class="form-control"  value="create_sql_file"/> 
    </div>
  </form>
  <?
}
function create_database(){
    //$dbname =  '../data/' . $_GET["dbname"] .".sqlite";
    $dbname =  $_GET["dbname"] .".sqlite";
    $db = new MyDB($dbname);
    $db->close();
    echo "created: $dbname";
}
function get_table_info(){
    $dbname =  $_GET["dbname"];
    $table = $_GET["table"];
   
    $db = new MyDB($dbname);
    $s = $db->get_table_info($table);
    $db->close();
    echo $s;
}
/*
*     create_files_list    
*     return all files with sql extention as table html
*/
function create_files_list(){
    $dir = "../data";
    $result = glob( $dir ."/*.sql");
?>
<div>
  <table class="table">
   <thead>
     <tr><th>File</th><th>Size</th></tr>
   </thead>     
   <tbody>
<? 
  foreach ($result as $filename) { 
    echo "<tr data-method='open_sql_file' data-args='$filename'><td>" .$filename . "</td><td>". filesize($filename) ."</td></tr>";
  }
    ?>
    <tbody>
    </table>
  </div>
    <?
    exit;
   
}
if(isset($_GET["action"])){
  $action =$_GET["action"];
 
/*  if($action == "db_list") {
    create_dbs_list();
  }
  else */if($action == "files_list") {
    create_files_list();
  }
  else if($action == "create_form"){
    create_database_form();
  }
  else if($action == "create_file_form"){
    create_file_form();
  }
  else if($action == "create_sql_file"){
    $file = "../data/" .$_GET["filename"] .".sql";
    if(file_exists($file)) {
      echo " error create file :$file . Exists!!!";
    }
    else{
      file_put_contents($file, "/*\r\n SQL File $file \r\n*/\r\n");
    }
    echo $file;
  }
  if($action == "create_db") {
    create_database();
  }
  if($action == "table_tabs"){
    create_table_tabs();
  }
  if($action == "table_info"){
    echo get_table_info();
  }
  exit;
}
/*
*     EXECUTE  FILE
*
*/
if(isset($_GET["exec_file"])){
  //error level 0
  
  
  $db = new MyDB($dbname);
  
  $file = urldecode($_GET["exec_file"]);
  $text = file_get_contents($file);  
  $lines = explode(";", $text);
  
  error_reporting(E_ALL);
  $result = $db->exec($text);
  $error =  $db->lastErrorMsg(); 
  if($error != "not an error") {
     echo "<b>Error in sql :</b> $sql <br/> [". $db->lastErrorMsg()."]\n";
   }
   else{
     echo '{"result":"ok:"}';
   }
  $db->close(); 
  /*
  exit;
  
  foreach ($lines as $sql) { 
   $sql = trim(str_replace ( "\r\n", "" ,  $sql));  
      if(strlen($sql) >0) {
         $result = $db->exec($sql);
         $error =  $db->lastErrorMsg(); 
         if($error != "not an error") {
           echo "<b>Error in sql :</b> $sql <br/> [". $db->lastErrorMsg()."]\n";
         }
         else{
           $count++;
         }
      }
  }
  $db->close();
  echo '{"result":' .$count.'}';
  // error level ALL
  error_reporting(E_ALL);
  */
  exit;
}
if(isset($_POST["exec"])){
  //echo $dbname;
  $sql = urldecode($_POST["exec"]);
  echo "<b>" .$sql ."</b>";
  $db = new MyDB($dbname);
  if($db->query($sql)){
    echo "{\"result\": true,\"msg\":\"". rawurlencode($sql)."\"}";  
  }
  else {
    echo "{\"result\": false,\"msg\":\"" . rawurlencode($db->lastErrorMsg())  ."\"}";  
  }
  $db->close();
  exit;
}
if(isset($_GET["select"])){
  $sql = urldecode($_GET["select"]);
  //$dbname = $_GET["dbname"];
  $db = new MyDB($dbname);
  $result = $db->query($sql) or die("Error in query: <span style='color:red;'>$sql  [$dbname]</span>"); 

  $cols = $result->numColumns(); 
  $n=0;
  echo "[";
  while ($row = $result->fetchArray()) { 
     if($n > 0) echo ",";
     echo "{";
      for ($i = 0; $i < $cols; $i++) { 
          if($i>0) echo ",";
          echo '"' . strtolower($result->columnName($i)) . '":"'; 
          echo rawurlencode($row[$i]) . '"'; 
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
if(isset($_GET["w"])){
  $db = new SQLite3($dbname);
  $word = $_GET["w"];
  $results = $db->query("SELECT * FROM words where word like '$word%' ");
  $i=0;
  $s ="[";
  while ($row = $results->fetchArray()) {
      if($i > 0) $s .= ",";
      $s.= ("{\"id\":" . $row['id'] . ",\"word\":\"" . rawurlencode($row['word']). "\"}");
      $i++;
  }
  $s .= "]";
  echo $s;
  $db->close();
  exit;
}
if(isset($_POST["save_file"])){
  $file = $_POST["save_file"];
  $text = urldecode($_POST["text"]);
  file_put_contents($file, $text);
  echo file_get_contents($file);
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