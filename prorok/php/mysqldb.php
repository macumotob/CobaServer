<?
#
#  mySQL database class
#
class mdb{
  
 public $server = "192.168.0.46";
 public $dbname = "maxbuk_db2";
 public $user = "root";
 public $password = "root";
 private $cnn;
 private $query;
 
 public function __construct($host, $user, $pass, $base){
	$this->server = $host;
	$this->user   = $user;
	$this->password = $pass;
	$this->dbname =$base;
  
  $this->connect($host, $user, $pass, $base);
   echo "<br/><p>MDB class instance created. <b>" ;
 }
 function __destruct(){
   echo "<br/><p>MDB class instance destroyed.</p>";
 }
 
 	private function connect($host, $user, $pass, $base){
		$this->cnn =   //@mysqli_connect($host, $user, $pass, $base) 
       new mysqli($host, $user, $pass, $base);
    if ($this->cnn->connect_errno) {
      echo "Failed to connect to MySQL: (" . $this->cnn->connect_errno . ") " . $this->cnn->connect_error;
      exit;
    }       
		//   or $this->getError(mysqli_connect_error());
    //$this->_create_database();
	}
  
  
  public function is_registered_user($username,$password){
    $stmt = $this->cnn->prepare("SELECT username, password FROM prorok_logins  WHERE username=? AND password=md5(?)");
    $stmt->bind_param('ss', $username,$password);//$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    $stmt->execute();
    $stmt->store_result(); 
    //echo "ROWS:" . $stmt->num_rows ;
    return ($stmt->num_rows == 0 ? 0 : 1);//         authenticate_user()
  }
  
  public function foreachRow($sql,$rowfunction){
    
    $result = $this->cnn->query($sql);
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        //echo "id: " . $row["id"]. " - Name: " . $row["username"]. " " . $row["password"]. "<br>";
        $rowfunction($row);
      }
    } 
  }
 
  public function query($sql){
    $this->query = mysqli_query($this->cnn, $sql);
  }
  public function fetch_array(){
		return mysql_fetch_assoc($this->query);
	}
  public function authenticate_user() {
    header('WWW-Authenticate: Basic realm="Secret Stash"');
    header("HTTP/1.0 401 Unauthorized");
    print('You must provide the proper credentials!');
    exit;     
  } 
  
  public function validate_user(){
    if(! isset($_SERVER['PHP_AUTH_USER'])) {
      $this->_authenticate_user();
    } 
    else {
      $stmt = $this->cnn->prepare("SELECT username, password FROM prorok_logins WHERE username=? AND password=MD5(?)"); 
      $stmt->bind_param('ss', $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);   
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows == 0)      
         $this->_authenticate_user();
    }
  }
	private function getError($TextError){
		$this->MySQLErrors[] = $TextError;
		die($TextError);
	}

};

echo "<br/><h1>mysqldb included</h1><br/>";

$db = new mdb("localhost:3306","root","root","prorok");
if ($db instanceof mdb) echo "Yes";
//$db->validate_user();

function prorok_read(){
// Open the users.txt file
$users = fopen("php/test.txt", "r");
 // While the EOF hasn't been reached, get next line
 while ($line = fgets($users, 4096)) {
   // use explode() to separate each piece of data.  
   list($name, $occupation, $color) = explode("|", $line);
   // format and output the data      
   printf("Name: %s <br />", $name);      
   printf("Occupation: %s <br />", $occupation);
   printf("Favorite color: %s <br />", $color); 
   }
   fclose($users);
}
?>