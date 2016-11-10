<?
// biblio.php

error_reporting(E_ALL);
require_once("sqlite_ext.php");


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
$dbname = 'biblio.sqlite';

function book_delete($rowid){
  global $dbname;
  $db = new mydb($dbname);
  $query = $db->qr("delete from books where id = ?",$rowid);
  $db->close();
}
function book_get_id($db,$file){
  $query = $db->qr("SELECT id FROM books where link = ?",$file);
  $row = $query->fetchArray();
  $rowid = intval( $row['id'] );
  return $rowid;
}
function book_history($title,$author,$file){
  global $dbname;
  
  $title= trim($title);
  $author= trim($author);
  $file = urldecode($file);
  $db = new mydb($dbname);
  
  $rowid = book_get_id($db,$file);
  if($rowid > 0){
    $query = $db->qr("update books set lasttime=datetime() where id =?",$rowid);
    return $rowid;
  }
  // INSERT  
  $query = $db->qr("insert into books (title,author,link,lasttime,status) values(?,?,?,datetime(),0)",$title,$author,$file);
  $rowid = book_get_id($db,$file);
  $db->close();
  return $rowid;
}
function books_list($db,$offset,$sort, $ascdesc,$status){
  if(strlen($sort) == 0){
    $sort ="lasttime";
  }
  if(strlen($ascdesc) == 0) {
    $ascdesc = "desc";
  }
  $where = " where status=$status ";
  $sql = "SELECT * FROM BOOKS" . $where ."order by " . $sort . " " . $ascdesc . "  LIMIT ? , 10";
 // echo $sql;
  $results = $db->qr($sql,$offset);
  
  $fields = array(
    "id" => array("name" =>"Id", "data-method" => "books.sort" , "data-args" => "lasttime"),
    "title" => array("name" =>"Title", "data-method" => "books.sort" , "data-args" => "title"),
    "author" => array("name" =>"Author", "data-method" => "books.sort" , "data-args" => "author"),
    "..." => array("name" =>"&nbsp;", "data-method" => "books.sort" , "data-args" => "author")
  );
  $span = "<span class='glyphicon glyphicon-sort-by-attributes". ($ascdesc === "asc" ? "" :"-alt") . "'></span>";
?>

  
  <div style="width:100%;">
  <table style="width:100%;cursor:pointer;">
    <tr>
      <td data-method="books.first"><span class="glyphicon glyphicon-step-backward"></span></td>
      <td data-method="books.prev"><span class="glyphicon glyphicon-arrow-left"></span></td>
      <td data-method="books.refresh"><span class="glyphicon glyphicon-refresh"></span></td>
      <td data-method="books.next"><span class="glyphicon glyphicon-arrow-right"></td>
      <td data-method="books.last">last</td>
    <tr>
  <table>
   <table class="table table-hover" style="cursor:pointer">
    <tr>
    <?foreach($fields as $key=>$item){
      $x = ($key == $sort ? $span : "");
      ?>
     
      <th data-method='<?=$item["data-method"];?>' data-args='<?=$item["data-args"];?>'><?=$item["name"] . " " .$x;?> </td>
    <?}?>
    <!--
      <th data-method="books.sort" data-args="lasttime">Id</td>
      <th data-method="books.sort" data-args="title">Title</th>
      <th data-method="books.sort" data-args="author">Author</th>
      -->
    </tr>
<?
while ($row = $results->fetchArray()) {
?>
    <tr onclick="open_in_tab('<?=$row["link"];?>')">
    <td><?=$row["id"];?></td>
    <td><?=$row["title"];?></td>
    <td><?=$row["author"];?></td>
    <td onclick="books.edit(<?=$row["id"];?>);" ><span class="glyphicon glyphicon-pencil"></span></td>
    <td onclick="books.delete_book(<?=$row["id"];?>);" ><span class="glyphicon glyphicon-trash"></span></td>
    </tr>
<?}?>    
   </table> 
  <div>  
<?

}
function book_add_quote($db,$bookid,$text,$offset){
  $bookid =intval($bookid );
  //echo $bookid . "</br><p>[" .$text ."]</p>";
  $db->qr("insert into citata (bookid,cit,proc) values(?,?,?)",$bookid,$text,$offset);
  echo "{\"result\":true,\"rowid\":" . $db->lastInsertRowID() ."}"; 
}

function get_status_list($db,$event){
  
  $s ='<select id="sel-status" '. $event .'>';
  $results = $db->qr("SELECT * from status_enum order by id");
  while ($row = $results->fetchArray()) {
    $s .= "<option value='" . $row["id"] ."'>" . $row["stname"] . "</option>";
  }
   $s .= "</select>";
   
  return $s;
}

function get_status_list2($db,$active){
 
  $results = $db->qr("SELECT * from status_enum order by id");
  while ($row = $results->fetchArray()) {
    $s = ($active == $row["id"] ? " selected='selected' " :"");
    echo "<option value='" . $row["id"] . $s ."'>" . $row["stname"] . "</option>";
  }
}

         
function make_edit_form($db,$rowid){
    $rowid = intval($rowid);
    $query = $db->qr("SELECT * FROM books where id=?",$rowid);
    $row = $query->fetchArray();
    ?>
    <table id="tb-book-edit" style="width:100%;padding:10px;" cellpadding="20px" border="0px">
    <tr>
      <td class="tb-book-edit-td" >Id</td>
      <td data-bind="id"><?=$row["id"];?></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td" >Title</td>
      <td><input type="text" data-bind="title" value='<?=$row["title"];?>' /></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td" >Author</td>
      <td><input type="text" data-bind="author" value='<?=$row["author"];?>' /></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td" >Genre</td>
      <td><input type="text" data-bind="genre" value='<?=$row["genre"];?>' /></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td">Location</td>
      <td><?=$row["link"];?></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td">Last time</td>
      <td><?=$row["lasttime"];?></td>
    </tr>
    <tr>
      <td class="tb-book-edit-td">Status</td>
      <td>
      <select data-bind="status">
      <?get_status_list2($db,$row["status"]);
       /*
       $status = array("None","Reading","Readed","Not Interested");       
       foreach($status as $k=>$v){
         $s = ($k == $row["status"] ? " selected='selected' " :"");
         echo "<option value='$k' $s>$v</option>";
       }*/
      ?>
      </select>
      </td>
    </tr>
    
    </table>
    <?
}

function book_quote_list($db,$bookid){
  $bookid = intval($bookid);
  
  $results = $db->qr("SELECT id,cit,proc FROM citata where bookid=?",$bookid);
?><table style="cursor:pointer;width:100%;"><?
while ($row = $results->fetchArray()) {
?>
    <tr style="border-top:1px solid gray;" data-method="fb2.bookmark_set" data-args="<?=$row["proc"];?>">
    <td style="padding:5px;vertical-align: text-top;"><?=$row["id"];?></td>
    <td style="padding:5px;"><?=$row["cit"];?></td>
    <td style="padding:5px;vertical-align: text-top;"><?=$row["proc"];?></td>
    <td style="padding:5px;vertical-align: text-top;" data-method="fb2.delete_quote" data-args="<?=$row["id"];?>">
      <span class="glyphicon glyphicon-trash"></span>
    </td>
    </tr>
<?}?></table><?
}

function book_update_info($db){
  $sql = "";
  $where = "";
  $i=0;
  $data = array();
  foreach($_GET as $k=>$v){
    switch($k){
      case "action":
       break;
      case "table":
       $sql = "update " . $v ." set "; 
       break;
      case "id":
        $where = " where id=" . $v;
        break;
      default:
        if($i >0) $sql .= ",";
        $sql .=  $k . "=?";
        array_push($data,$v);
        $i++;
        break;
    }
  }
  $sql .= $where;
  array_unshift($data,$sql);
  $db->qexec($data);
   
  echo '{"result":' . $db->changes() ."}";

}

function book_quote_delete($db,$quoteid){
  $db->qr("delete from citata where id = ?",$quoteid);
  echo 'Number of rows modified: ', $db->changes();
}


if(isset($_GET["action"])){
 $db = new mydb($dbname);
 $action =   $_GET["action"];
 switch($action){
   case "books-list":
    $offset = $_GET["offset"];
    $sort ="";
    $ascdesc ="";
    if(isset($_GET["sort"])){
      $sort =$_GET["sort"];
    }
    if(isset($_GET["ascdesc"])){
      $ascdesc =$_GET["ascdesc"];
    }
    $status = 0;
    if(isset($_GET["status"])){
      $status =$_GET["status"];
      
    }
    books_list($db,$offset,$sort,$ascdesc,$status);
    break;
   case "book-delete":
    $rowid = $_GET["rowid"];
    $query = $db->qr("delete from books where id = ?",$rowid);
    break;
   case "citata-list" :
    $bookid = $_GET["bookid"];
    book_quote_list($db,$bookid);
    break;
    case "book-add-quote":
    $bookid = $_GET["bookid"];
    $text = $_GET["text"];
    $offset = $_GET["offset"];
    book_add_quote($db,$bookid,$text,$offset);
    break;
    
    case "book-quote-delete":
    $quoteid = $_GET["quoteid"];
    book_quote_delete($db,$quoteid);
    break;
    
   case "book-edit":
    $rowid = $_GET["rowid"];
    make_edit_form($db,$rowid);
    break; 
    
    case "book-update-info":
     book_update_info($db);
    break;
    case "status-list":
     echo get_status_list($db,' onchange="books.filter(this.value);" ');
     break;
   default:
    echo "action is :" . $action;
    break;
 }
 $db->close();

}
?>