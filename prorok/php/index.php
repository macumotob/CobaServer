<?

if(isset($_GET["user-ip"])){
  echo $_SERVER["REMOTE_ADDR"];
  exit;
}
else {
  echo "invalide param";
}
$usedb = true;
require_once("mysqldb.php");


?>