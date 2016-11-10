<?php
$dbname = null;
$columns = null;

require_once("sqlite_ext.php");

function make_update_statment($tbname,$id,&$p){
   $s = "UPDATE $tbname set ";
   $first = true;
    foreach ($_POST as $key => $value) {
    $key = strtolower($key);
     if($key == "action" OR $key == "dbname" OR $key == "id" OR $key == "tbname") {
       //echo "<b>". $key . "</b>" . $value ."<br/>";  
     }
     else{
       if(!$first) $s .= ",";
       $pname = ":". $key;
       $s .= "'" .$key  . "'=". $pname;
       $p[$pname] = $value;
       $first= false;
     }
  }
  $s .= " WHERE ID= " .$id;
  return $s;
}
function make_insert_statment($tbname,$id,&$p){
  $s = "INSERT INTO  $tbname (";
  $s2 = "VALUES(";
   
  $first = true;
  foreach ($_POST as $key => $value) {
    $key = strtolower($key);
     if($key == "action" OR $key == "dbname" OR $key == "id" OR $key == "tbname") {
       //echo "<b>". $key . "</b>" . $value ."<br/>";  
     }
     else{
       if(!$first) {
         $s .= ",";
         $s2 .= ",";
       } 
       $pname = ":". $key;
       $s .= "'" .$key  . "'";
       $s2 .= $pname; 
       $p[$pname] = $value;
       $first= false;
     }
  }
  $s .= ")";
  $s2 .= ")";
  return $s .$s2;
}

if($_POST){
  
  $p = array();
  $tbname= $_POST["tbname"];
  $dbname = $_POST["dbname"];
  $id = $_POST["id"];
  $action = $_POST["action"];
  
  $db = new MyDB($dbname);

  if($action == "delete"){
    $s ="DELETE FROM $tbname WHERE id=$id";;
    $db->exec($s);
  }
  else{
    $s = $id == -1 ? make_insert_statment($tbname,$id,$p) : make_update_statment($tbname,$id,$p);
    
    $stmt = $db->prepare($s);
    foreach ($p as $pname => $value) {
      $stmt->bindValue($pname, $value);//, PDO::PARAM_STR);
    }
    $stmt->execute();
  }
  echo "Record affected:" . $db->changes();
  $db->close();
  exit;
}

$dbname = $_GET["dbname"];
$table = $_GET["table"];
$id = $_GET["id"];
//echo "database : " .$dbname . " table : " . $table . "  id :" .$id;

function create_row_edit($db,$result,$row){
   global $columns;
   $cols = $result->numColumns(); 

  for ($i = 0; $i < $cols; $i++) { 
   $cname = $result->columnName($i);
   $value = $row[$i];
   $tp = strtolower($columns[$cname]["type"]);
   if($tp == "text"){
?>
   <div class="form-group">
    <label class="control-label col-sm-2" for="<?=$cname;?>"><?=$cname;?>:</label>
    <div class="col-sm-10"> 
      <textarea  class="form-control" id="<?=$cname;?>" placeholder="Enter <?=$cname;?>"><?=$value;?></textarea>
    </div>
  </div>
<?    
   }
   else {
?>
   <div class="form-group">
    <label class="control-label col-sm-2" for="<?=$cname;?>"><?=$cname;?>:</label>
    <div class="col-sm-10"> 
      <input type="text" class="form-control" id="<?=$cname;?>" placeholder="Enter <?=$cname;?>" value="<?=$value;?>"/>
    </div>
  </div>
<?    
   }
  } 
}
//class="navbar-brand"
?>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
    <ul class="nav navbar-nav" >
      <li><a title="Go back" id='row-go-back'>
          <span class="glyphicon glyphicon-arrow-left"></span>
      </a></li>
      <li><a title="Add record" onclick="add_row();">
        <span class="glyphicon glyphicon-plus"></span>
      </a></li>
      <li><a title="Save record" onclick="save_row();">
       <span class="glyphicon glyphicon-floppy-save"></span>
       </a></li>
    </ul>    
    </div>
    <ul class="nav navbar-nav navbar-right">
      <li><a style="color: #cc9900">db:<?=$dbname;?></a></li>
      <li><a style="color: #cc9900">table:<?=$table;?></a></li>
      <li>
        <a data-method="delete_row" title="Delete record">
          <span class="glyphicon glyphicon-trash"></span>
        </a>
      </li>
    </ul>    
  </div>
</nav>
 <div class="pre-scrollable">

<form class="form-horizontal" role="form" style="width:90%;" id="form-edit-row">
<?
  $db = new MyDB($dbname);
  $columns = $db->table_info($table);
  //print_r($columns);
  $db->each("select * from " .$table . " WHERE id=" .$id,"create_row_edit")
?>
</form>
  

<nav class="navbar navbar-inverse">
    <ul class="nav navbar-nav" >
      <li><a id='row-result'>Result</a></li>
    </ul>  
</nav>  
</div>
<script>
 var rowid = <?=$id;?>;
 var dbname = '<?=$dbname;?>';
 var tbname = '<?=$table;?>';
 
 function add_row(){
  rowid = -1;
  var term = $("#form-edit-row").find( "input, textarea" );
  term.each(function (i,item) {
    if(item.id == "id")   item.value =rowid;
    else   item.value ="";
     });
/*     
  term = $("#form-edit-row").find( "textarea" );
  term.each(function (i,item) {
    if(item.id == "id")   item.value =rowid;
    else   item.value ="";
     });
  */   
 }
 function save_row(){

     var data =[];  
     data.push({"name" : "action", "value" : "update"});
     data.push({"name" : "dbname", "value" : dbname});
     data.push({"name" : "tbname", "value" : tbname});
     data.push({"name" : "id"    , "value": rowid});
     
     var term = $("#form-edit-row").find( "input" );
     term.each(function (i,item) {
       data.push({"name": item.id, "value": item.value});
     });
     
     term = $("#form-edit-row").find( "textarea" );
     term.each(function (i,item) {
       data.push({"name": item.id, "value": item.value});
     });
     //console.log(data);
     
     $.post("/php/edit_row.php",data,function (result){
       console.log(result);
       $("#row-result").html(result);
     });
 }
  function delete_row(){
  var rowid = <?=$id;?>;
  alert(rowid);
  var data =[];  
  data.push({"name" : "action", "value" : "delete"});
  data.push({"name" : "dbname", "value" : dbname});
  data.push({"name" : "tbname", "value" : tbname});
  data.push({"name" : "id"    , "value": rowid});
  $.post("/php/edit_row.php",data,function (result){
    $("#row-result").html(result);
  });

 }

</script>