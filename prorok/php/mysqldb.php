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
 private static $visitors = 0;
 
 public function __construct($host, $user, $pass, $base){
	$this->server = $host;
	$this->user   = $user;
	$this->password = $pass;
	$this->dbname =$base;
  
  $this->connect($host, $user, $pass, $base);
  self::$visitors++;
  echo "<br/><p>MDB class instance created. <b>" . self::$visitors . "</b></p>";
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
    $this->_create_database();
	} 
  private function _authenticate_user() {
    header('WWW-Authenticate: Basic realm="Secret Stash"');
    header("HTTP/1.0 401 Unauthorized");
    print('You must provide the proper credentials!');
    exit;     
  } 
  
  private function _create_database(){
    
    $file = file_get_contents('./create_db.sql', true);
    //echo $file;
    $lines = explode(";", $file);
    foreach($lines as $line){
      echo $line;
      if ( !$this->cnn->query($line)) {
        echo "Table creation failed: (" . $this->cnn->errno . ") " . $this->cnn->error;
      }
    }
    return;
    if ( !$this->cnn->query("DROP TABLE IF EXISTS test") 
      || !$this->cnn->query("CREATE TABLE test(id INT)")) {
      echo "Table creation failed: (" . $this->cnn->errno . ") " . $this->cnn->error;
      }
    else{
      echo "Table created";
      /* Prepared statement, stage 1: prepare */
      if (!($stmt = $this->cnn->prepare("INSERT INTO test(id) VALUES (?)"))) {
         echo "Prepare failed: (" . $this->cnn->errno . ") " . $this->cnn->error;
      }
      else
      {
        $id = 1;
        if (!$stmt->bind_param("i", $id)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        for($i=1; $i<10;$i++)
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
      }
    }
  }
 
  public function validate_user(){
    if(! isset($_SERVER['PHP_AUTH_USER'])) {
      $this->_authenticate_user();
    } 
    else {
      $stmt = $this->cnn->prepare("SELECT username, pswd FROM logins WHERE username=? AND pswd=MD5(?)"); 
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

$db = new mdb("localhost:3306","root","root","test");
if ($db instanceof mdb) echo "Yes";
$db->validate_user();

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