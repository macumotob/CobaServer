<?
$encoding = "utf-8";
$book_title="Not found";
$book = array();

function fb2_skip_ws($s,&$i,$n){
  while($i < $n AND ($s[$i] === ' ' OR $s[$i] === '\r' OR $s[$i] === '\n' OR $s[$i] === '\t')) $i++;  
  return $i;
}
function fb2_read_tag_name($s,&$i,$n){
  $name ="";
  while($i < $n AND ($s[$i] !== ' ' AND $s[$i] !== '>' AND $s[$i] !== '\n' AND $s[$i] !== '/') ){
    $name .= $s[$i++];
  } 
  if($name == ""){
    echo "[".$name."]";
    die("<br/><h1>error parse tag name at position " . $i ."</h1> <br/>");
  }
  fb2_skip_ws($s,$i,$n); 
  return $name;
}

function fb2_read_attribute_name($s,&$i,$n){
  $name ="";
  fb2_skip_ws($s,$i,$n);
  while($i < $n AND $s[$i] !== '=') $name .= $s[$i++];
  return $name;
}
function fb2_read_attribute_value($s,&$i,$n){
  $value ="";
  fb2_skip_ws($s,$i,$n);
  if($s[$i] === '=' ) {
    $i++;
    fb2_skip_ws($s,$i,$n);
    $ch = $s[$i] ;
    if($ch === "'" or $ch==='"') {
      if($i < $n AND $s[$i] === $ch){
        $i++;
        while($i < $n AND $s[$i] !== $ch) $value .= $s[$i++];
        if($i < $n AND $s[$i] === $ch){
          $i++;
          fb2_skip_ws($s,$i,$n);
          return $value;
        }
      }
    }
  }
  echo "Invalide attribute value at <b>" .$i . " :[" .$value ."]" . substr($s,$i-50,150) ."]</b><br/>" .$s ;
  exit;
}
function fb2_read_attribute($s,&$i,$n, &$name,&$value){
  if($i + 1 < $n AND $s[$i]==='/' AND $s[$i+1]==='>'){
    return false;
  }
  if($i + 1 < $n AND $s[$i]==='?' AND $s[$i+1]==='>'){
    return false;
  }
  if($i < $n AND $s[$i]==='>'){
    return false;
  }
  $name = fb2_read_attribute_name($s,$i,$n);
  $value = fb2_read_attribute_value($s,$i,$n);
  //echo "<br/>ATTRIBUTE: " .$name . "=" .$value;
  return true;
}
function fb2_add_text(&$res,&$txt){
  $txt = trim($txt);
  if(strlen($txt) > 0){
    array_push($res, array( "t" => 0, "b" => $txt));
    $txt = "";
  }
}
function fb2_read_attributes($s,&$i,$n){
  $attrs = array();
  $name="";
  $value="";
  while(fb2_read_attribute($s,$i,$n,$name,$value)){
    $attrs[$name] = $value;
  }
//  echo "<br>";
//  print_r($attrs);
  return $attrs;
}
function fb2_validate_tag_end($s,&$i,$n){
  # 0 xml 
  if($i + 1< $n AND $s[$i] == '?' AND $s[$i+1] == '>'){
    $i+=2;
    return 4;
  }
  # 3 /> closed
  if($i + 1< $n AND $s[$i] == '/' AND $s[$i+1] == '>'){
    $i+=2;
    return 3;
  }
  # > not closed
  if($i < $n AND $s[$i] == '>'){
    $i++;
    return 1;
  }
  die("<h1>invalid tag end at position :" .$i."</h1>");
}
function fb2_parse($s){
  //echo $s;
  $i=0;
  $n = strlen($s);
  $txt="";
  /*
  while($i < $n){
    if($s[$i] === '<'){
      $i++;
      if($s[$i] === '?'){
        $tag = fb2_read_tag_name($s,$i,$n);
        $attrs = fb2_read_attributes($s,$i,$n);
        $ttype = fb2_validate_tag_end($s,$i,$n);
        
        print_r($attrs);
        echo "[". $tag ."]". $ttype ."<br/>";
      exit;
      }
      break;
    }
    $i++;
  }
  */
  $res = array();


  while($i < $n){
    //check tag's end
    if( $s[$i] === '<' AND $s[$i+1] === '/') {
      $i+=2;
      fb2_add_text($res,$txt);
      $tag = fb2_read_tag_name($s,$i,$n);
      $attrs = fb2_read_attributes($s,$i,$n);
      if($s[$i] !== '>')
      {
        die("<br/><h1>error parse tag name at position " . $i ."</h1> <br/>");
      }
      $i++;
      array_push($res, array( "t" => 2, "b" => $tag , "attrs"=>$attrs));
      continue;
    }

    if($s[$i] === '<') {
      $i++;
      fb2_add_text($res,$txt);
      $tag = fb2_read_tag_name($s,$i,$n);
      $attrs = fb2_read_attributes($s,$i,$n);
      $ttype = fb2_validate_tag_end($s,$i,$n);
      array_push($res,array( "t" => $ttype, "b" => $tag, "attrs" => $attrs));
      continue;
    }
    $txt .= $s[$i];
    $i++;
  }
  return $res;
  #fb2_to_client($res);
  
}
function fb2_print_image($tag,$binaries){
  
  if(count($tag["attrs"]) == 1){
     $attrs = $tag["attrs"];
     $a=false;
     if(array_key_exists("xlink:href",$attrs)){
       $a = substr($attrs["xlink:href"],1);
     }
     else if(array_key_exists("l:href",$attrs)){
       $a = substr($attrs["l:href"],1);
     }
     else {
       echo "IMAGE:";
       print_r($attrs);
     }
     //$a = substr($tag["attrs"]["l:href"],1);//["xlink:href"];
     #echo "<h1>IMAGE : " . $a . "</h1>";
     $binary = $binaries[$a];
     
     echo "<div class='grow pic'>";
     echo "<img src='data:" . $binary["content-type"]  . ";base64," . $binary["data"] 
        . "' class='fb2-image' alt='portrait'/>";
     echo "</div>";
  }
  else{
     echo "<h1>IMAGE ??? </h1>";           
  }
}
function fb2_print_link_close($tag){
  echo "</a>";
}
function fb2_print_link_open($tag){
  if(count($tag["attrs"]) > 0){
     $attrs = $tag["attrs"];
     $a=false;
     if(array_key_exists("xlink:href",$attrs)){
       $a = $attrs["xlink:href"];
     }
     else if(array_key_exists("l:href",$attrs)){
       $a = $attrs["l:href"];
     }
     else {
       echo "LINK:";
       print_r($attrs);
       foreach($attrs as $key => $value){
         echo "KEY:" . $key ." VALUE:".$value;
       }
     }
     $func = "";
     if(substr($a,0,7) === "http://") {
       $func = " target='_blank' data-method='fb2.navigate' data-args='". $a. "'";
     }
     else{
       $func = " data-method='fb2.navigate_local' data-args='". $a. "'";
     }
     echo "<a class='fb2-link' href='" . $a . "'"  . $func . ">";
  }
  else{
     echo "<h1>LINK ??? </h1>";           
  }
  //print_r($tag["attrs"]);
}
function fb2_book_find_value($key,$tags){
  $i=0;
  $n = count($tags);
  while($i < $n - 2){
    if($tags[$i]["b"] === $key AND $tags[$i]["t"]===1){
      return $tags[$i+1]["b"];
    }
    $i++;
  }
  return "Value for key :" .$key ." not found!<br/>"; 
}
function fb2_book_find_tag($key,$tags){
  $i=0;
  $n = count($tags);
  while($i < $n - 2){
    if($tags[$i]["b"] === $key){
      return $tags[$i];
    }
    $i++;
  }
  return "Value for key :" .$key ." not found!<br/>"; 
}
function fb2_book_get_value($i,$key,$tags){
  if($i == -1) return "";
  if($tags[$i]["b"] === $key AND $tags[$i]["t"]===1){
      return $tags[$i+1]["b"];
  }
  return ""; 
}

function fb2_book_find_tag_index($key,$tags){
  $i=0;
  $n = count($tags);
  while($i < $n - 2){
    if($tags[$i]["b"] === $key){
      return $i;
    }
    $i++;
  }
  return -1; 
}

function fb2_to_client($t){ // array of  tags from fb2_parse
  //print_r($t);
  
  $i=0;
  $n = count($t);
  $binaries=array();
  while($i < $n - 2){
    if($t[$i]["b"] === "binary" AND $t[$i]["t"]===1){
      $binary = $t[$i]["attrs"];
      $binaries[$binary["id"]] = array("content-type" => $binary["content-type"] , "data" => $t[$i+1]["b"]);
      //echo "<img src='data:" . $binary["content-type"]  . ";base64," . $t[$i+1]["b"] . "' id='" . $binary["id"] . "'/>";
      
      //$binary = fb2_parse_binary($t[$i+1]["b"], $id);
      //echo "<img src='data:" . $binary["type"]  . ";base64," . $binary["data"] . "' id='" .$id . "'/>";
    }
    $i++;
  }
  //print_r($binaries);
    $dic = array(
    "genre"    => array("b" => "<div class='fb2-genre'>Жанр : " , "end" => "</div>"),
    "emphasis" => array("b" => "<i class='fb2-amphasis'>", "end" =>"</i>"),
    "p"        => array("b" => "<p class='fb2-p'>", "end" =>"</p>"),
    "strong"   => array("b" => "<b class='fb2-strong'>", "end" =>"</b>"),
    "author"     => array("b" => "<div class='fb2-author'>Автор : ", "end" =>"</div>"),
    "description" => array("b" => "<div id='fb2-description' class='fb2-description'>", "end" =>"</div>"),
    "first-name"  => array("b" => "<b class='fb2-first-name'>", "end" =>"</b>"),
    "email"       => array("b" => "<b class='fb2-email'> email : ", "end" =>"</b>"),
    "history"     => array("b" => "<b class='fb2-history'> history : ", "end" =>"</b>"),
    "nickname"  => array("b" => "<b class='fb2-nickname'>", "end" =>"</b>"),
    "middle-name" => array("b" => "<b class='fb2-middle-name'>", "end" =>"</b>"),
    "last-name"   => array("b" => "<b class='fb2-last-name'>", "end" =>"</b>"),
    "text-author"   => array("b" => "<b class='fb2-text-author'>", "end" =>"</b>"),
    "book-title"  => array("b" => "<div id='fb2-book-title' class='fb2-book-title'>Название : <b>", "end" =>"</b></div>"),
    "book-name"   => array("b" => "<div class='fb2-book-name'>Название : <b>", "end" =>"</b></div>"),
    "id"          => array("b" => "<div class='fb2-id'>ID : <b>", "end" =>"</b></div>"),
    "version"     => array("b" => "<div class='fb2-version'>Версия : <b>", "end" =>"</b></div>"),
    "publisher"   => array("b" => "<div class='fb2-publisher'>Издательство : <b>", "end" =>"</b></div>"),
    "city"        => array("b" => "<div class='fb2-city'>Город : <b>", "end" =>"</b></div>"),
    "year"        => array("b" => "<div class='fb2-year'>Год : <b>", "end" =>"</b></div>"),
    "isbn"        => array("b" => "<div class='fb2-isbn'>ISBN : <b>", "end" =>"</b></div>"),
    "publish-info"=> array("b" => "<div class='fb2-publish-info'>Информация об издательстве</div><div>", "end" =>"</div>"),
    "document-info"=> array("b" => "<div class='fb2-document-info'>Информация о документе</div><div>", "end" =>"</div>"),
    
    "lang"        => array("b" => "<div class='fb2-lang'>Язык : <b>", "end" =>"</b></div>"),
    "date"        => array("b" => "<div class='fb2-date'>Дата : <b>", "end" =>"</b></div>"),
    "src-lang"    => array("b" => "<div class='fb2-src-lang'>Язык оригинала : <b>", "end" =>"</b></div>"),
    "title"       => array("b" => "<div class='fb2-title'>", "end" =>"</div>"),
    "subtitle"       => array("b" => "<div class='fb2-subtitle'>", "end" =>"</div>"),
    "section"     => array("b" => "<section class='fb2-section'>", "end" =>"</section>"),
    "myheader"     => array("b" => "<section class='fb2-myheader'>", "end" =>"</section>"),
    "myfooter"     => array("b" => "<section class='fb2-myfooter'>", "end" =>"</section>"),
    "coverpage"   => array("b" => "<div class='fb2-coverpage'>", "end" =>"</div>"),
    "translator"  => array("b" => "<div class='fb2-translator'>Переводчик : <b>", "end" =>"</b></div>"),
    "program-used"=> array("b" => "<div class='fb2-program-used'>Программа : <b>", "end" =>"</b></div>"),
    "custom-info" => array("b" => "<div class='fb2-custom-info'>Доп.инфо : <i>", "end" =>"</i></div>"),
    "src-url"     => array("b" => "<div class='fb2-custom-info'>Источник : <a>", "end" =>"</a></div>"),
    "title-info"  => array("b" => "<div class='fb2-title-info'>", "end" =>"</div>"),
    "epigraph"    => array("b" => "<div class='fb2-epigraph'>", "end" =>"</div>"),
    "cite"        => array("b" => "<div class='fb2-cite'>", "end" =>"</div>"),
    "poem"        => array("b" => "<div class='fb2-poem'>", "end" =>"</div>"),
    "stanza"      => array("b" => "<div class='fb2-stanza'>", "end" =>"</div>"),
    "v"           => array("b" => "<div class='fb2-v'>", "end" =>"</div>"),
    "src-title-info" => array("b" => "<div class='fb2-src-title-info'>Информация о переводе</div><div>", "end" =>"</div>"),
    "annotation"  => array("b" => "<div class='fb2-annotation'>Аннотация : <i>", "end" =>"</i></div>"),
    "a"           => array("b" => "<a class='fb2-a'>", "end" =>"</a>"),
    "sup"         => array("b" => "<span class='fb2-sup'><b>", "end" =>"</b></span>"),
    "sub"         => array("b" => "<span class='fb2-sub'><b>", "end" =>"</b></span>"),
    "src-ocr"     => array("b" => "<span class='fb2-src-ocr'><b>", "end" =>"</b></span>"),
    "table"       => array("b" => "<table class='fb2-table'>", "end" =>"</table>"),
    "tr"          => array("b" => "<tr>", "end" =>"</tr>"),
    "td"          => array("b" => "<td>", "end" =>"</td>"),
    "home-page"   => array("b" => "<div class='fb2-home-page'>Home Page :<a href='HREF'> ", "end" =>"</a></div>"),
    
    # 3
    "empty-line/"  => array("b" => "<hr class='fb2-empty-line' />", "end" =>""),
    "empty-line"  => array("b" => "<hr class='fb2-empty-line' />" , "end" =>"")
    );
  
  $i=0;
  # echo "count:" .count($t);
  
  $need_echo = true;
  
  $body_count=0;
  while($i < count($t) - 2){
    $tag = $t[$i]["b"];
   // echo "<h1 style='color:red'>$tag $i ".  $t[$i]["t"] ."</h1>";
    switch($t[$i]["t"]){
      case 0:
        if( $need_echo AND !empty($t[$i]["b"])){
          echo  " " . $tag . " ";  
        }
        else{
          echo $t[$i]["b"];
        }
        break;
      case 1:
        $need_echo = ($tag !== "binary"); 
        if($tag === "body") {
          $body_count++;
          //echo "BEGIN BODY COUNT: " .$body_count ;
        }
        if($tag === "binary") {
          echo "BEGIN BINARY " ;
          return;
        }
        
        if($tag === "binary" || $tag === "body" || $tag === "FictionBook" ) {
          break;
        }
        if($tag === "a") {
          fb2_print_link_open($t[$i]);
          break;
        }
        if($tag === "image") {
          fb2_print_image($t[$i],$binaries);
          break;
        }
        if(array_key_exists($tag,$dic)){
          
          if($t[$i]["attrs"]){
            $x = $dic[$tag]["b"];
            $attrs = $t[$i]["attrs"];
            if($tag ==="section" or $tag ==="p"){
              echo substr($x,0,strlen($x)-1);  
              foreach ($attrs as $key => $value){
                echo  " " .$key . "='" .$value ."'";
              }
              echo ">";
              if($tag === "p") {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;";
              }
              // echo "<h1 style='color:green;'>" .  $tag . " ". print_r($attrs) ."</h1>";
            }
            else{
             echo $x;  
             echo "<h1 style='color:green;'>" .  $tag . " ". print_r($attrs) ."</h1>";
            }
          }
          else{
            if($tag === "home-page"){
              //echo str_replace("HREF", $dic[$tag]["b"] ;  
              echo $dic[$tag]["b"] ;  
            }
            else {
              echo $dic[$tag]["b"] ;  
            }
          }
        }
        else echo  "<br/>1 class='fb2-". $t[$i]["b"] ."'" ;  
        break;
      case 2:
        if($tag === "body" ){ //or $tag === "description") {
          $body_count--;
        }

        $need_echo = ($tag === "binary"); 
        if($tag === "body" || $tag === "FictionBook" ) {
          break;
        }
        if($tag === "a") {
          fb2_print_link_close($t[$i]);
          break;
        }
        if($tag === "binary" OR $tag === "image") {
          break;
        }
        if(array_key_exists($tag,$dic)){
          echo $dic[$tag]["end"];
        }
        else echo  "<br/>2 class='fb2-". $t[$i]["b"] ."'" ;  
        break;
      case 3:
        if($tag === "body") {
          echo "RETURN 3";
          return;
        }

        if($tag === "image") {
          fb2_print_image($t[$i],$binaries);
          break;
        }
        if(array_key_exists($tag,$dic)){
            
          $attrs = $t[$i]["attrs"];
          
          
          //echo $dic[$tag]["b"];
          if(count($attrs) > 0){
            if($tag === "a") {
              fb2_print_link_open($t[$i]);
              echo "...";
              fb2_print_link_close($t[$i]);
            }
            else{ 
              echo $dic[$tag]["b"];
              echo "<h1 style='color:red;'>XX" . print_r($attrs) ."</h1>";
              echo $dic[$tag]["end"];
            }
          }
          else{
            echo $dic[$tag]["b"] . $dic[$tag]["end"];
          }
        }
        else 
          echo  "<br/>3". $t[$i]["b"];          
        break;
        
        case 4: // xml
          if($tag === "?xml") break;

          echo  "<br/>4". $t[$i]["b"];       
          print_r($t[$i]["attrs"]);
        break;
      default:
        
        die( "Неизветный тэг");
    }
    $i++;
  }

}

#
#
#
/*
header('Powered: maxbuk'); 
header('Content-Type: text/html; charset=utf-8');

if(isset($_GET["file"])){
  $file_name = $_GET["file"];
  
  ///books/ E:/Books/fb2/
  $file = str_replace("/books/", "E:/Books/fb2/", $file_name);
  echo "<h1>".$file_name."</h1><br/>";
  echo "<h1>".$file."</h1>";
  if(file_exists($file))
  {
    $text = file_get_contents($file);
    fb2_parse($text);
    exit;
  }
  echo "File not found!";
  exit;
}
echo "HELLO";
*/
?>