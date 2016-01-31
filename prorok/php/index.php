<?

if(isset($_GET["user-ip"])){
  echo $_SERVER["REMOTE_ADDR"];
  exit;
}

#
# registration
#

if(isset($_POST["act"])){
  $action =$_POST["act"];
  if($action == "register"){
    $email = $_POST["email"];
    $pwd = $_POST["pwd"];
    
    echo "pwd :" .$pwd . " email:" .$email;
    $usedb = true;
    require_once("mysqldb.php");
    $db->register_user($email,$pwd);
    
    exit;
  }
}



?>