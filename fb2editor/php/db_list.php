<?php
/*
select a.id,a.who,a.whom, a.dt, a.message ,a.message_type,b.status from chat as a,chatstate as b where a.id=b.chatid

delete from chatstate;

insert into chatstate(chatid,nick)
select id,who from chat;

insert into chatstate(chatid,nick)
select id,whom from chat;

*/
// db_list.php
require_once("sqlite_ext.php");
?>

<nav class="navbar navbar-inverse" style="width:95%">
    <ul class="nav navbar-nav">
      <li>
        <a data-method="show_create_database" title="Creat Database">
          <span class="glyphicon glyphicon-plus"></span>
        </a>
      </li>
      <li>
        <a data-method="databases_refresh" title="Refresh Databases">
          <span class="glyphicon glyphicon-refresh"></span>
        </a>
      </li>
    </ul>    
    <ul class="nav navbar-nav navbar-right">
      <li style="padding-right:10px;">
        <a data-method="show_drop_database" title="Drop Database">
          <span class="glyphicon glyphicon-trash"></span>
        </a>
      </li>
    </ul> 
</nav>

<?
function create_column_info($db, $result, $row){

  echo "<li><a href='#'>".$row["name"]. "</a></li>";
  
  /*$cols = $result->numColumns(); 
  $n=0;
  for ($i = 1; $i < $cols; $i++) { 
          if($i>1) echo ",";
          echo '"' .$result->columnName($i) . '":"'; 
          echo $row[$i] . '"'; 
     } 
   $n++;
*/
}

function create_table_info($db,$result, $row){
  //echo $row["name"];
  //echo '<ul class="treeview"><li><a href="#">'.$row["name"].'</a></li>';
  echo '<li><a href="#" data-method="show_table_tabs" data-args="'.$db->name.','.$row["name"].'">'.$row["name"].'</a><ul>';

  $db->each("PRAGMA table_info(" .$row["name"] .")","create_column_info");
  echo "</ul></li>";
}

function create_dbs_list(){
    $dir = "../data/";
    $files = glob( $dir ."*.sqlite");
    echo "<table class='table'>";
  foreach ($files as $filename) { 
    $filename = str_replace($dir,"",$filename);
?>
        <ul class="treeview">
          <li>
            <span class="glyphicon glyphicon-th-list"></span>
            <a href="" data-method='open_database' data-args='<?=$filename;?>,true'><?=$filename;?></a>
          </li>
          <ul>
<!--        echo "<tr><td data-method='open_database' data-args='$filename'>$filename</td></tr>";-->
<?
  $db = new MyDB('sqlite_master') or die("error open data base");
  echo '<li><a href="" data-method="show_table_tabs" data-args="'.$filename.',sqlite_master">sqlite_master</a><ul>';

  $db->each("PRAGMA table_info('sqlite_master')","create_column_info");
  echo "</ul></li>";

    $db = new MyDB($filename);
    $db->each("select * from sqlite_master","create_table_info");
    $db->close();
?>
          </ul>
         </ul>
      <?

  }
    echo "</table>";    
    exit;
}
create_dbs_list();
?>
