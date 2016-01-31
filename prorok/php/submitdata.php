<?php  
   function sanitize_data($value, $key) {  
     echo "<br/>1.value : " . $value . " key :" .$key;
     $value = strip_tags($value);     
     echo "<br/>2value : " . $value . " key :" .$key;
   
   }
   array_walk($_POST['keyword'],"sanitize_data"); 
   ?>