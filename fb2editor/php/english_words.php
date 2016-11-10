<?

// english_words.php
error_reporting(E_ALL);
require_once("sqlite_ext.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$dbname = 'biblio.sqlite';
$db = new mydb($dbname);

if(isset($_GET["action"])){
  $action =$_GET["action"];
  
  $query = $db->qr("SELECT memo FROM memo where id=?",9);
  $row = $query->fetchArray();
  echo str_replace(array("\\"), array(""), $row["memo"]);
  //echo $row["memo"];
  $db->close();
  
}
else if(isset($_POST["action"])){
  $action = $_POST["action"];
  $data = $_POST["data"];
  echo "action POST " . $action;
  $db->qr("update memo set memo=? where id=9",$data);
  $db->close();  
}
else{
  echo "ok";
}
?>