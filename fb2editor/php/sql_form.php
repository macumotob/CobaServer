<?
 $dbname = $_GET["dbname"];
 $tbname = $_GET["table"];
 //echo $dbname . '  ' .$tbname;
?>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#" onclick="clear_form();">
        <span class="glyphicon glyphicon-trash"></span>
      </a>
      <a class="navbar-brand" href="#" onclick="execute();">
        <span class="glyphicon glyphicon-play"></span>
      </a>
      <a class="navbar-brand" href="#" data-toggle="tooltip" title="Explain query plan" onclick="explain();">
        <span class="glyphicon glyphicon-question-sign"></span>
      </a>
      <a class="navbar-brand" href="#" onclick="open_file();">
        <span class="glyphicon glyphicon-open"></span>
      </a>
      <a class="navbar-brand" href="#" id="file-name">
      </a>
      <a class="navbar-brand" href="#" title="Save current sql file" onclick="save_current_file();">
      <span class="glyphicon glyphicon-floppy-save"></span>
      </a>
    </div>
  </div>
</nav>

<div class="row" style="width:90%" >
   <div class="col-xs-12">
    <!--<button class="btn btn-success" data-method="execute_select">Select</button>-->
     <textarea rows="10" cols="50" style="width:100%;font-size:1.5em;font-family:Comic Sans MS;" id="command"></textarea>
   </div>
</div>

<div id="result" style='color:red;'>result</div>

<script type="text/javascript">
  var sql_file_name;
  
  function clear_form(){
    $('#command').val('');
    $('#command').focus();
    sql_file_name=null;
    $("#file-name").html("");
  }
  function explain(){
    // EXPLAIN QUERY PLAN
    var s = $("#command").val();
    if(s.length == 0 ){
      show_message("Enter sql statement or select file to execute!");
      return;
    }
    execute_select("EXPLAIN QUERY PLAN " + encodeURIComponent(s), function(a){
      var options ={
        div : "#result",
        data : a  
      };
      $("#result").empty();
      new sqlite_table(options);
    });
  } 
  
 
 function save_current_file(){
   if(sql_file_name){
    var txt = encodeURIComponent($("#command").val());
    var data = {"save_file":sql_file_name , "text" : txt};     
    $.post("/php/sqlite.php", data, function(s){
        $("#command").val(s)  ;
    });
   }
   else{
     show_message("File not loaded!");
   }
 }
 function open_sql_file(file){
   
    if(dialog) dialog.close();
    $.get(file,function(data){
      $("#command").val(data);
      $("#file-name").html(file);
      sql_file_name = file;
    });
 }
 function open_file(){
      $.get('php/sqlite.php?action=files_list',function(data){
         show_message(data, "Select file to open");
      });
 }
 /*
 function execute_statment(commands){
    if(commands.length >0)    {
      var s = commands[0];
      var data = {"exec":s,"dbname":dbname};     
      
      $.post("/php/sqlite.php", data, function(s){
        var result = JSON.parse(s);
        commands.shift();  
        execute_statment(commands);
      });
    }
    else {
      show_message("Done!");
    }
  }*/
 function execute(){
    $("#result").html("wait..."); 
    if(!dbname){
        show_message("Select database !");
        return;
    }
    if(sql_file_name)    {
      var data = {"exec_file":sql_file_name,"dbname":dbname};     
      $.get("/php/sqlite.php", data, function(s){
          $("#result").html(s);
        });
    }
    else{
      var s = $("#command").val();
      if(s.length == 0 ){
        show_message("Enter sql statement or select file to execute!");
      }
      else{
        if(s.indexOf('select ') == 0) {
          //execute_select(encodeURIComponent(s), function(a){
          execute_select(escape(s), function(a){
          var options ={
            div : "#result",
            data : a  
          };
          $("#result").empty();
          new sqlite_table(options);
          });
          return;
        } 
        
        var data = {"exec":s,"dbname":dbname};     
        $.post("/php/sqlite.php", data, function(s){
          $("#result").html(s);
        });
      }
    }
 }

</script>