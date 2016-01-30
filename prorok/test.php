<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Prorok</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta charset="utf-8" />
</script>
</head>

<body>
 <h1>PHP Конспект</h1>
<?
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
   echo "<i>$var</i> : <b>$value</b> <br />"; 
 }
?>

<?="<br/><h1>md5 from user pwd :" . md5("waswas:Maximka1234Ivan") . "</h1><br/>";?>
</body>
</html>