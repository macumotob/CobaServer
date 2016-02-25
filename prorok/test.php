<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Test Sqlite3</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta charset="utf-8" />
</script>
</head>

<body>
 <h1>PHP Конспект</h1>
<?

$usedb = true;
require_once("php/mysqldb.php");

$user = $_SERVER['PHP_AUTH_USER'];
$pwd  = $_SERVER['PHP_AUTH_PW'];

if(!$db->is_registered_user($user,$pwd) ){
  $db->authenticate_user();  
}

function show_row($row){
  echo "id: " . $row["id"]. " - Name: " . $row["username"]. " " . $row["password"]. "<br>";
}
echo "<br/>";

$db->foreachRow("select * from prorok_logins","show_row");

echo "user registered :[" . $db->is_registered_user("waswas","1") ."]";


exit();


if (!isset($_SERVER['PHP_AUTH_USER']) || ! isset($_SERVER['PHP_AUTH_PW'])) {   
      header('WWW-Authenticate: Basic Realm="Authentication"'); 
      header("HTTP/1.1 401 Unauthorized");
      print('You must provide the proper credentials!');
      exit;
} 
// If the username and password are set, output their credentials     
else {
  echo "Your supplied username: {$_SERVER['PHP_AUTH_USER']}<br />"; 
  echo "Your password: {$_SERVER['PHP_AUTH_PW']}<br />";     
} 

echo $_REQUEST['name'] . " " . $_GET['name'] . "<br/>";

#echo  phpinfo() . "<br/>";
echo "microtime:" .microtime() . "<br/>";
echo "md5 from microtime :" . md5(microtime()) . "<br/>";

$rnd = substr(md5(microtime()), 0, 5) . "<br/>";


#show_source(__FILE__);    
#show_source("js/loader.js");    


echo $rnd;

print "<p>I love the 
        и всякое разное
    summertime.</p>";
    
printf("<br/>%d bottles of tonic water cost $%f<br/>", 100, 43.20);    
$cost = sprintf("$%.2f", 43.2); 
echo $cost;

class Appliance {
  private $_power;
  function setPower($status) {
    $this->_power = $status;    
  } 
  function show(){
    echo "<br/>Power is :". $this->_power . " type:" . gettype($this->_power) ;
    
  }
}
$blender = new Appliance;
$blender->setPower(56.90);
$blender->show();


#using variables by referensing

$value1 = "Hello";
$value2 =& $value1;    // $value1 and $value2 both equal "Hello" 
$value2 = "Goodbye";   // $value1 and $value2 both equal "Goodbye" 
echo "<br/> $value1  $value2";
#
# global variables
#

$somevar = 15; 
function addit() { 
    global $somevar;
    $somevar++;
    echo "<br/>Somevar is $somevar"; 
  }
addit();
function addit2() { 
    $GLOBALS["somevar"]++; 
}
 addit2(); 
 echo "<br/>Somevar 2 is ".$GLOBALS["somevar"] ."<br/>";
 #
 # STATIC
 #
 function keep_track() { 
   static $count  = 0; 
   $count++;
   echo $count;
   echo "<br />"; 
 }
 keep_track(); keep_track(); keep_track();
 #
 #  SERVER 
 #
 foreach ($_SERVER as $var => $value) {   
   echo "<i>$var</i> : <b>$value</b> <br/>"; 
 }
 #
 #
 #
 $recipe = "spaghetti"; 
 //Interestingly, you can treat the value spaghettias a variable by placing a second dollar sign 
 //in frontof the original variable name and again assigning another value: 
 $$recipe = "&meatballs";
 // This in effect assigns & meatballs to a variable named spaghetti.
 //Therefore, the following two snippets of code produce the same result: 
 echo $recipe , $spaghetti . "<br/>";
 echo $recipe , ${$recipe} . "<br/>";
 
 #
 # CONSTANT
 #
 define("PI", 3.141592); //The constant is subsequently used in the following listing: 
 printf("The value of Pi is %f", PI);
 $pi2 = 2 * PI; 
 printf("Pi doubled equals %f", $pi2);
 
 #
 #  FILE OR 
 #
 $ex = file_exists("filename.txt") OR "File does not exist!";
 echo "file exist: $ex";
 
 #
 # HEREDOC
 #
 $website = "http://www.romatermini.it"; 
 
 echo <<<KUKU
 <p>Rome's central train station, known as
 <a href = "$website">Roma Termini</a>, was built in 1867. 
 Because it had fallen into severe disrepair in the late 20th century, 
 the government knew that considerable resources were required to rehabilitate the station 
 prior to the 50-year <i>Giubileo</i>.</p> 
KUKU;
 
 #
 # FOREACH
 #
 $links = array("www.apress.com","www.php.net","www.apache.org");
 echo "<b>Online Resources</b>:<br/>";
 foreach($links as $link) {
   echo "<b>$link</b><br/>";
   echo "<a href=\"http://$link\">$link</a><br />";
 }
 
 $links = array(
   "The Apache Web Server" => "www.apache.org",
   "Apress" => "www.apress.com",
   "The PHP Scripting Language" => "www.php.net"); 
   //Each array item consists of both a key and a corresponding value. 
   //The foreach statement can easily peel each key/value pair from the array, 
   //like this: echo "<b>Online Resources</b>:<br />"; 
   foreach($links as $title => $link) {  
   echo "<a href=\"http://$link\" target=\"_blank\">$title</a><br />"; 
  }
  
  
  #
  #  GOTO
  #
  for ($count = 0; $count < 10; $count++) {     
    $randomNumber = rand(1,50);     
    if ($randomNumber < 10)  goto less;
    else 
      echo "Number greater than 10: $randomNumber<br />"; 
    }
  less:   
    echo "Number less than 10: $randomNumber<br />";
    
#
# include require
#

if ($usedb) {
  //include("php/mysqldb.php");
  require_once("php/mysqldb.php");
  include "http://www.wjgilmore.com/index.html?background=blue";
}
else 
  include ('another_filename');

#
# functions
#
function generateFooter() {
  echo "Copyright 2016 V.Bukshovan<br/>"; 
}
 //Once defined, you can call this function like so: 

 generateFooter(); 
 
 
function retrieveUserProfile()   {
  $user[] = "Jason Gilmore";  
  $user[] = "jason@example.com"; 
  $user[] = "English";
  return $user;     
}
  list($name, $email, $language) = retrieveUserProfile();
  echo "Name: $name, email: $email, language: $language"; 
  
#
# array
#  
$states = array(
 "OH" => "Ohio", 
 "PA" => "Pennsylvania", 
 "NY" => "New York"); // You could then reference Ohio like this: 
 echo $states["OH"];
 // It’s also possible to create arrays of arrays, known as multidimensional arrays. 
 //For example, you could use a multidimensional array to store U.S. state information. 
 //Using PHP syntax, it might look like this: 
 $states = array (  
 "Ohio" => array("population" => "11,353,140", "capital" => "Columbus"), 
 "Nebraska" => array("population" => "1,711,263", "capital" => "Omaha") );
// You could then reference Ohio’s population:
echo  $states["Ohio"]["population"]; 

prorok_read();

$customers = array(); 
$customers[] = array("Jason Gilmore", "jason@example.com", "614-999-9999"); 
$customers[] = array("Jesse James", "jesse@example.net", "818-999-9999"); 
$customers[] = array("Donald Duck", "donald@example.org", "212-999-9999"); 
foreach ($customers AS $customer) {   
 vprintf("<p>Name: %s<br />E-mail: %s<br />Phone: %s</p>", $customer);
}
echo "<br/> print array";
print_r($customers);

$states = array("Ohio", "New York"); 
array_unshift($states, "California", "Texas");
print_r($states);
array_push($states,"Москва","Киев");
print_r($states);
$state = array_shift($states);
print_r($state);
$state = array_pop($state);
print_r($state);

$state = "Ohio"; 
$states = array("California", "Hawaii", "Ohio", "New York"); 
if(in_array($state, $states)) echo "Not to worry, $state is smoke-free!";

$st["Delaware"] = "December 7, 1787";
$st["Pennsylvania"] = "December 12, 1787"; 
$st["Ohio"] = "March 1, 1803"; 
if (array_key_exists("Ohio", $st))
  printf("Ohio joined the Union on %s", $st["Ohio"]);

$xstate["Ohio"] = "March 1"; 
$xstate["Delaware"] = "December 7"; 
$xstate["Pennsylvania"] = "December 12"; 
$founded = array_search("December 7", $xstate); 
if ($founded) printf("%s was founded on %s.", $founded, $xstate[$founded]);


#
#  LOG
#

//define_syslog_variables();
#openlog("CHP8", LOG_PID, LOG_USER);
#syslog(LOG_WARNING,"Chapter 8 example warning.");
#closelog();

try { 
    $fh = fopen("contacts.txt", "r"); 
    if(!$fh) {
      throw new Exception("Could not open the file!");     
    } 
  }
  catch (Exception $e) {
    echo "Error (File: ".$e->getFile().", line ". $e->getLine()."): ".$e->getMessage(); 
   
  }
?>


<?="<br/><h1>md5 from user pwd :" . md5("waswas:Maximka1234Ivan") . "</h1><br/>";?>

<form action="php/submitdata.php" method="post">   
  <p>     Provide up to six keywords that you believe best describe the state in
  which you live:     </p>
  <p>Keyword 1:<br /><input type="text" name="keyword[]" size="20" maxlength="20" value="sdfsd" /></p>
  <p>Keyword 2:<br />www.it-ebooks.info
  <input type="text" name="keyword[]" size="20" maxlength="20" value="апвап а" /></p>
  <p>Keyword 3:<br /><input type="text" name="keyword[]" size="20" maxlength="20" value="мисчми" /></p> 
  <p>Keyword 4:<br /><input type="text" name="keyword[]" size="20" maxlength="20" value="мисчмисм" /></p> 
  <p>Keyword 5:<br /><input type="text" name="keyword[]" size="20" maxlength="20" value="ячичмичям" /></p>
  <p>Keyword 6:<br /><input type="text" name="keyword[]" size="20" maxlength="20" value="ячмичмиячмтичм" /></p>
  <p><input type="submit" value="Submit!"></p>
  </form>
  
</body>
</html>