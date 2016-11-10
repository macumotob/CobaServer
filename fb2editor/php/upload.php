<?php
error_reporting(E_ALL);
$h =getallheaders();
$s = $h["Coba-File-Info"];
//echo $s;
$data = explode("&", $s);
$action=false;
 $name=false;
 $type = false;
 $size = 0;
 $filesize = 0;
 $start =0;
 $end = 0;

foreach($data as $key=>$value){
  
  $item = explode("=",$value);
  //echo "key: $item[0] => $item[1]";
  switch($item[0]){
    case "action":
     $action = $item[1];
     break;
    case "name":
     $name = $item[1];
     break;
    case "type":
      $type = $item[1];
      break;
    case "size":
      $size = intval($item[1]);
      break;
    case "filesize":
      $filesize = intval($item[1]);
      break;
    case "start":
      $start = intval($item[1]);
      break;
    case "end":
      $end = intval($item[1]);
      break;
  }
}
//  echo "key: $key => $value";
  $filename = "../uploads/QQQ_".$name;
if($action == "close"){
  echo '{"result":false,"offset":' .($end*100/$filesize).', "msg":"'. $action .'"}';
  exit;
}
if($action == 'open'){
  $fp = fopen($filename, 'wb'); 
  fseek($fp, $start,SEEK_SET);
}
else{
  $fp = fopen($filename, 'ab+'); 
  fseek($fp, $start,SEEK_SET);
}
$input = fopen('php://input', 'rb');
stream_copy_to_stream($input, $fp);
fclose($input);
fclose($fp);  
echo '{"result":true,"offset":' .($end*100/$filesize).', "msg":"'. $action .'"}';


exit;

 $name = $_POST["name"]; 
 $type = $_POST["type"];
 $size = $_POST["size:"];
 $filesize = $_POST["filesize"];
 $start = intval($_POST["start"]);
 $end = $_POST["end"];
 $action = $_POST["action"];
 //$blob = base64_decode($_POST["blob"]);
 $blob = $_POST["blob"];
//$blob = file_get_contents('php://input');
$filename = "../uploads/QQQ_".$name;
/*
// the Blob will be in the input stream, so we use php://input
$input = fopen('php://input', 'rb');
*/
if($action == "close"){
  exit;
}
if($action == 'open'){
  $fp = fopen($filename, 'wb'); 
  fseek($fp, 0,SEEK_SET);
}
else{
  $fp = fopen($filename, 'wb+'); 
  fseek($fp, 0,SEEK_END);
}
fwrite($fp,$blob);
// Note: we don't need open and stream to stream, we could've used file_get_contents and file_put_contents
//stream_copy_to_stream($input, $file);
//fclose($input);
fclose($fp);

  //var_dump(file_get_contents('php://input'));
  echo $name . " " . $action . " " . $start*100 ;//. " ->" .$blob;
exit;


function multiple(array $_files, $top = TRUE)
{
    $files = array();
    foreach($_files as $name=>$file){
        if($top) $sub_name = $file['name'];
        else    $sub_name = $name;
        
        if(is_array($sub_name)){
            foreach(array_keys($sub_name) as $key){
                $files[$name][$key] = array(
                    'name'     => $file['name'][$key],
                    'type'     => $file['type'][$key],
                    'tmp_name' => $file['tmp_name'][$key],
                    'error'    => $file['error'][$key],
                    'size'     => $file['size'][$key],
                );
                $files[$name] = multiple($files[$name], FALSE);
            }
        }else{
            $files[$name] = $file;
        }
    }
    return $files;
}

print_r($_FILES);
$files = multiple($_FILES);
echo "<br/>-----------------<br/>";
print_r($files);
echo "<br/>-----------------<br/>";
foreach ($files  as $key => $arr) {
  foreach($arr as $name =>$file){
    $tmp = $file["tmp_name"];
    $fname = $file["name"];
    echo "<h1>name : " . $fname . " => tmp: " . $tmp ."</h1>";

    move_uploaded_file( $tmp, "/uploads/" .$fname);
  }
}
exit;
/*
function diverse_array($vector) { 
    $result = array(); 
    foreach($vector as $key1 => $value1) 
        foreach($value1 as $key2 => $value2) 
            $result[$key2][$key1] = $value2; 
    return $result; 
} 
$upload = diverse_array($_FILES["images"]); 
*/
//var_dump($upload);
var_dump($_FILES);
echo "<br/>SSSSSSSS<br/>";
var_dump($_FILES["images"]);
echo "<br/>-----------------<br/>";
print_r($_FILES);
exit;
 
 if ($_FILES["images"]["error"] > 0)
  {
  echo "Error: " . $_FILES["images"]["error"] . "<br>";
  }
else
  {
  echo "Upload: " . $_FILES["images"]["name"] . "<br>";
  echo "Type: " . $_FILES["images"]["type"] . "<br>";
  echo "Size: " . ($_FILES["images"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["images"]["tmp_name"];
  move_uploaded_file( $_FILES["images"]["tmp_name"][$key], "/uploads/" . $_FILES['images']['name'][$key]);
  }
  
  echo "<br/> hhhhhhhhhhhhhhhhhhh<br/>";
  
foreach ($_FILES["images"]["error"] as $key => $error) {
  echo  " key : $key error: $error<br/>";
  $name = $_FILES["images"]["name"][$key];
    
  if ($error == UPLOAD_ERR_OK) {
    $name = $_FILES["images"]["name"][$key];
    //echo $name .  " key : $key error: $error<br/>";
    move_uploaded_file( $_FILES["images"]["tmp_name"][$key], "/uploads/" . $_FILES['images']['name'][$key]);
  }
}
  
 
echo "<h2>jjjjjjjjjjjjjj</h2>";
/*
if ($_FILES["file"]["error"] > 0)
  {
  echo "Error: " . $_FILES["file"]["error"] . "<br>";
  }
else
  {
  echo "Upload: " . $_FILES["file"]["name"] . "<br>";
  echo "Type: " . $_FILES["file"]["type"] . "<br>";
  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["file"]["tmp_name"];
  }
  */