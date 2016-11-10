<?
//drop_db.php

//require_once("sqlite_ext.php");
$dbname = $_GET["dbname"];

if(isset($_GET["action"])){
  $action = $_GET["action"];
  
  $file = "../data/" . $dbname;
  $newfile = str_replace(".sqlite",".bak",$file);
  if(file_exists($file)){
    //unlink($file);
    rename($file,$newfile);
    echo $action . " file: " .$file ;
  }
  else{
    echo "<h2>file: " .$file . " not found</h2>";
  }
  exit;
}
?>
Drop database <h1><?=$dbname;?></h1>