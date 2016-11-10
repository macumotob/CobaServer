<?
// db_info.php
require_once("sqlite_ext.php");


function db_info_refresh(){
  global $dbname;
  echo "<h1>Refresh</h1>";
  //$db = new MyDB($dbname);
  //  $columns = $db->table_info("sqlite_master");
  //$db->close();
}

if( isset($_GET["refresh"]) ){
  db_info_refresh();
  exit;
}
?>
<nav class="navbar navbar-inverse" style="width:97%">
    <ul class="nav navbar-nav">
      <li>
        <a data-method="show_create_table" title="Creat Table">
          <span class="glyphicon glyphicon-plus"></span>
        </a>
      </li>
      <li>
        <a data-method="refresh_database_info" title="Refresh Database Info">
          <span class="glyphicon glyphicon-refresh"></span>
        </a>
      </li>
      <li><a><?=$dbname;?></a></li>
    </ul>    
    <ul class="nav navbar-nav navbar-right">
      <li style="padding-right:10px;">
        <a data-method="show_drop_database" title="Drop Database">
          <span class="glyphicon glyphicon-trash"></span>
        </a>
      </li>
    </ul> 
</nav>
<div id="db-info-content">
  database objects
</div>

<script>

 function refresh_database_info(){
   $.get("/php/db_info.php?dbname=" +dbname +"&refresh",function(html){
     
    $("#db-info-content").html(html);
    execute_select("SELECT rowid,* FROM sqlite_master", function(a){
      var options ={
      div : "#db-info-content",
      data : a ,
      pk :"rowid",
      dbname : dbname,
      table : "sqlite_master",
      caption : "database  objects",
      pager: false,
      editable:false,
      onrow : function(tr){
        var el = event.target || event.srcElement;
        var x = el.parentNode;
        var tbname = x.childNodes[3].innerText;
        show_table_tabs(dbname,tbname);
        console.log(x.childNodes[0].innerText,x.childNodes[3].innerText,x);
      }
     };
      $("#db-info-content").empty();
      new sqlite_table(options);
     });
  });
 }
 function show_create_table(){
   
 }
 refresh_database_info();
</script>