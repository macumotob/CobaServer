<?
class mdb{
  
 public $host = "192.168.0.46";
 public $dbname = "prorok";
 public $user = "root";
 public $password = "root";
 private $cnn;
 private $query;
 
 //public function __construct($host, $user, $pass, $base){
  public function __construct(){
/*	$this->server = $host;
	$this->user   = $user;
	$this->password = $pass;
	$this->dbname =$base;
  */
  $this->connect();
//   echo "<br/><p>MDB class instance created. <b>" ;
 }
 function __destruct(){
   mysql_close($this->cnn);
  // echo "<br/><p>MDB class instance destroyed.</p>";
 }
 
 	private function connect(){
    
    $this->cnn = mysql_connect($this->host, $this->user, $this->password) or die('Error connecting to mysql');
    mysql_select_db($this->dbname);
    /*
$query  = "SELECT * FROM users";
$result = mysql_query($query);
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<MessageXML>";
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
 echo "<Data>"."<Email>{$row['email']}</Email>"."<Name>{$row['name']}</Name>"."<Password>{$row['password']}</Password>"."<Question>{$row['question']}</Question>"."<Answer>{$row['answer']}</Answer>"."</Data>";
} 
echo "</MessageXML>";
mysql_close($conn);
	}
  */
 }
}
$db = new mdb();

header('Powered: maxbuk'); 
header('Content-Type: text/html; charset=utf-8');

$arr = array('title' => 'Привет товарищи!', 
     'b' => array('Автор' => 'Кум Петрович', 'Дата' =>' 21-12-1998'),
     'c' => 3, 'd' => 4, 'e' => 5);

//echo '('.json_encode($arr).')';

function fb2_parse($s){
  //echo $s;
  $i=0;
  $n = strlen($s);
  $txt="";
  
  $res = array();

  while($i < $n){
    //check tag's end
    if( $s[$i] === '<' AND $s[$i+1] === '/') {
      $tag ="";
      $i+=2;
      if(strlen($txt) > 0){
        array_push($res, array( "t" => 0, "b" => $txt));
        $txt = "";
      }
      while($i < $n AND $s[$i] !== '>') {
        $tag .= $s[$i]; $i++;
      }
      if($s[$i] === '>')
      {
        
      }
      else{
        echo "<br/>XXXXXXXXXXXXXXXX<br/>";
      }
      $i++;
      array_push($res, array( "t" => 2, "b" => $tag));
      continue;
    }

    if($s[$i] === '<') {
      $i++;
      $tag ="";
      if(strlen($txt) > 0){
        array_push($res, array( "t" => 0, "b" => $txt));
        $txt = "";
      }
      // read tag name
      while($i < $n AND $s[$i] !== ' ' AND $s[$i] !== '>') {
        $tag .= $s[$i++]; //$i++;
      }
      
      //skip white space
      if($i < $n AND $s[$i] === ' '){
        while($i < $n AND $s[$i] === ' ') $i++;
      }
      else{
        $i++;
      }
      if($i + 1 < $n AND $s[$i]==='/' AND $s[$i+1]==='>'){
        $i+=2;
        array_push($res, array( "t" => 3, "b" => $tag));
      }
      else{
        array_push($res,array( "t" => 1, "b" => $tag));
      }

      continue;
    }
    $txt .= $s[$i];
    $i++;
  }
  fb2_parse_p($res);
  
}
function fb2_parse_p($t){ // array of  tags from fb2_parse
 // print_r($t);
  $i=0;
  echo "count:" .count($t);
  while($i < count($t) - 2){
    if( $t[$i]["t"] === 1 AND $t[$i+2]["t"] === 2 AND $t[$i]["b"] === $t[$i+2]["b"]){
      if($t[$i]["b"] === "p"){
        echo "<p>" . $t[$i+1]["b"] ."</p>";
      }
      else if($t[$i]["b"] === "strong"){
        echo "<b>" . $t[$i+1]["b"] ."</b>";
      }
      else if($t[$i]["b"] === "emphasis"){
        echo "<i class='fb2-" . $t[$i]["b"] ."'>" . $t[$i+1]["b"] ."</i>";
      }
      else{
        echo "<br/>" . $t[$i]["b"] ."&nbsp;" . $t[$i+1]["b"] . "&nbsp;<i>" . $t[$i+2]["b"] ."</i>";  
      }
      $i+=3;
      continue;
    }
    if( $t[$i]["t"] === 1){
      if($t[$i]["b"] === "emphasis"){
        echo "<i class='fb2-" . $t[$i]["b"] ."'>";  
      }
      else{
        echo "<div class='fb2-" . $t[$i]["b"] ."'>";  
      }
      $i++;
      continue;
    } 
    if( $t[$i]["t"] === 2){
      if($t[$i]["b"] === "emphasis"){
        echo "</i>";  
      }
      else{
        echo "</div>";  
      }
      $i++;
      continue;
    } 
    echo  $t[$i]["b"];  
    $i++;

  }
}
function readfile_chunked($filename,$retbytes=true) {
    $chunksize = 1024; // how many bytes per chunk
    $buffer = '';
    $cnt =0;
    // $handle = fopen($filename, 'rb');
    $handle = fopen($filename, 'r');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, $chunksize);
        if ($retbytes) {
          fb2_parse($buffer);
          $cnt += strlen($buffer);
        }
    }
        $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    } 
    return $status;
}
$file = $_SERVER['DOCUMENT_ROOT'] . "book/demo/x.fb2";
$file = "./../books/demo/x.fb2";
//echo file_get_contents($file);
//$file = "/books/demo/100_receptov_оolivjeп.fb2";
//echo "<b>" . $_SERVER['DOCUMENT_ROOT'] ."</b><br/>" .$file ."<br/>";
//echo realpath($file);
//readfile_chunked($file);
 $text = file_get_contents($file);
 fb2_parse($text);

?>