<?

function prepare_statment()
{
    $sql = "";
    $ParameterNumber = 0;

    if (func_num_args() && $sql = func_get_arg($ParameterNumber++))
    {
        while ($ParameterNumber < func_num_args())
        {
            $NextParameter = func_get_arg($ParameterNumber++);
            $PlaceToInsertParameter = strpos($sql, '?');
            if ($PlaceToInsertParameter !== false)
            {
                $s = '';

                if (is_bool($NextParameter))
                {
                    $s = $NextParameter ? 'TRUE' : 'FALSE';
                }
                else if (is_float($NextParameter) || is_int($NextParameter))
                {
                    $s = $NextParameter;
                }
                else if (is_null($NextParameter))
                {
                    $s = 'NULL';
                }
                else
                {
                   // $s = "'" . mysql_real_escape_string($NextParameter) . "'";
                    $s = "'" . addslashes($NextParameter) . "'";
                }

                $sql = substr_replace($sql, $s, $PlaceToInsertParameter, 1);
            }
        }
    }
    return $sql;
    //mysqli->query(prepare("SELECT District FROM City WHERE Name=?", $city))
}
function prepare_statment_with_array($ar)
{
    $sql = "";
    $ParameterNumber = 0;
    $sql = $ar[0];
    
    $i = 1;
        while ($i < count($ar))
        {
            $NextParameter = $ar[$i++];
            $PlaceToInsertParameter = strpos($sql, '?');
            if ($PlaceToInsertParameter !== false)
            {
                $s = '';

                if (is_bool($NextParameter))
                {
                    $s = $NextParameter ? 'TRUE' : 'FALSE';
                }
                else if (is_float($NextParameter) || is_int($NextParameter))
                {
                    $s = $NextParameter;
                }
                else if (is_null($NextParameter))
                {
                    $s = 'NULL';
                }
                else
                {
                   // $s = "'" . mysql_real_escape_string($NextParameter) . "'";
                    $s = "'" . addslashes($NextParameter) . "'";
                }

                $sql = substr_replace($sql, $s, $PlaceToInsertParameter, 1);
            }

          }
    
    return $sql;
    //mysqli->query(prepare("SELECT District FROM City WHERE Name=?", $city))
}

class MyDB extends SQLite3
{
  var $name ='';
  function __construct($name)
  {
    $name = str_replace("../data/","",$name);
    $this->name = $name;
    $this->open("../data/". $name);
    $this->busyTimeout(5000);
    $this->exec('PRAGMA journal_mode = wal;');

  }
  public function each($sql ,$callback){
    $result = $this->query($sql) or die("Error in query: <span style='color:red;'>$sql</span>"); 
    $i=0;
    while ($row = $result->fetchArray()) { 
      $callback($this,$result, $row,$i);
      $i++;
    }
  }
  public function qexec(){
    $sql =  call_user_func_array('prepare_statment_with_array', func_get_args()); 
           
    return $this->query($sql);
  }

  public function qr(){
    $sql =  call_user_func_array('prepare_statment', func_get_args()); 
   // echo $sql;
    return $this->query($sql);
  }
  public function get_table_info($tbname){
    $s="[";
    $result = $this->query("PRAGMA table_info(" .$tbname .");") or die("Error in query: <span style='color:red;'>$tbname</span>"); 
    $arr = array("cid", "name","type","notnull","dflt_value","pk");
    $n=0;
    while ($row = $result->fetchArray()) { 
      if($n > 0) $s.=",{";
      else $s.= "{";
      
      $j=0;
      foreach ($arr as &$item) {
        if($j > 0) $s.=",";
        $s.= '"'.$item.'":"' .$row[$item] . '"';
        $j++;
      }
      $s.="}";
      $n++;  
    }
    $s.="]";
    return $s;
  }
  public function table_info($tbname){
    $a = array();
    $result = $this->query("PRAGMA table_info(" .$tbname .");") or die("Error in query: <span style='color:red;'>$tbname</span>"); 
    $arr = array("cid", "name","type","notnull","dflt_value","pk");
    while ($row = $result->fetchArray()) { 
      $ar = array(); 
      foreach ($arr as &$item) {
        $ar[$item] =$row[$item] ;
      }
      $a[$row["name"]] = $ar;
    }
    return $a;
  }

} // end of class
$dbname =null;
if(isset($_GET["dbname"])){
  $dbname = $_GET["dbname"];
}
if(isset($_POST["dbname"])){
  $dbname = $_POST["dbname"];
}

?>