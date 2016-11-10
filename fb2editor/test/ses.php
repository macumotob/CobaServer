<?

class mdb{
  
 private $cnn;
 private $query;
 
 private  $host = "maxbuk.mysql.ukraine.com.ua";
 private $dbname = "maxbuk_db";
 private $user = "maxbuk_db";
 private $password = "bukshovan2009";

 public function __construct(){
  $this->connect();
  echo "<br/><p>MDB class instance created." ;
 }
 function __destruct(){
   mysql_close($this->cnn);
   echo "<br/><p>MDB class instance destroyed.</p>";
 }
 
 private function connect(){
    
    $this->cnn = mysql_connect($this->host, $this->user, $this->password) or die('Error connecting to mysql');
    mysql_select_db($this->dbname);
 }
 public function users(){
 
 $query  = "SELECT * FROM ser13_users";
  $result = mysql_query($query,$this->cnn);


while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
 echo $row['email'] . $row['pwd']. $row['id']. "<br/>";
} 

 mysql_free_result($result);

 
  }
}
$db = new mdb();
$db->users();

  session_start();   
  echo "Your session identification number is " . session_id(); 
  $_SESSION['username'] = "Jason";
  printf("Your username is %s.", $_SESSION['username']); 
  if(isset($_SESSION['x'])){
  $x =$_SESSION['x'];
  $_SESSION['x'] = ++$x;
 }
else{
  $x =1;
  $_SESSION['x']=1; 
}
 echo "<br/> counter :$x";
?>