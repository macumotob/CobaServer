<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Sqlite DBA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta charset="utf-8" /> 

   
  <link rel="stylesheet" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/css/bootstrap-dialog.min.css" />
  <link rel="stylesheet" href="/css/sqlite.css" />
  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="/js/loader.js"></script>
  <script type="text/javascript" src="/js/table.js"></script>
  
  <style>
  body{ 
    background-color: #f8f8f8; 
  }
  </style>


<script type="text/javascript">
    
 var dbname= null;
 var dialog,current_table,current_table_info,tb_offset=0;
 function show_sqlite(){
 
   $.get("sqlite.html?dbname="+dbname + "&tm="+ new Date().getTime() ,function(data){
      $("#main-content").html(data);
   });
 }
 function show_message(text, caption){
   dialog = BootstrapDialog.show({
            title:  caption || 'Error',
            message: text,
            buttons: [
                {
                label: 'Close',
                action: function(dialog) {
                    dialog.close();
                    $('#command').focus();
                }
            }]            
        });
 }
 function databases_refresh(){
   //$.get('php/sqlite.php?action=db_list',function(data){
  $.get('/php/db_list.php',function(data){
      $("#left-panel").html(data);
    	$('.treeview').each(function () {
        var tree = $(this);
        tree.treeview();
      });

   });
 }
 
 
 function execute_select(sql,onsuccess){
    var s = sql || $("#command").val();
    if(s.length >0)    {
    $("#result").text("wait...")  ;
    var data = {"select":s ,"dbname":dbname };     
    $.get("/php/sqlite.php", data, function(s){
      try{        
        var result = JSON.parse(s);
      } 
      catch(e){
        alert(e);
      }
        if(onsuccess) onsuccess(result);
        //var t = decodeURIComponent(result.msg);
        //console.log(result);
        //$("#result").text(s)  ;
    });

    }
    else {
      $("#result").text("command is empty")  ;
    }
 }
 function show_create_sql_file(){
      dialog =    BootstrapDialog.show({
          title: 'Create SQL file',
          message: $('<div></div>').load('php/sqlite.php?action=create_file_form'),
          buttons: [
            {
              label: 'Close',
              action: function(dlg){
                dlg.close();
              }
            },
            {
              icon: 'glyphicon glyphicon-ok',
              label: 'Create',
              cssClass: 'btn-primary',
              action: function(dlg){
                   var name = $("#filename").val();
                   if(name.length == 0){
                     $("#filename").focus();
                     return;
                   }
                   var action = $("#action").val();
                  $.ajax({
                      type: "GET",
                      url: "php/sqlite.php",
                      data: "filename=" + name + "&action=" + action,
                      success : function(text){
                          alert(text);
                          dlg.close();         
                      }
                     });
              }
            }
            ]
        });
  }
 function show_drop_database(){
   if(!dbname){
     show_message("Select database to drop!");
     return;
   }
   dialog =    BootstrapDialog.show({
    title: 'Drop Database',
    message: $('<div></div>').load('php/drop_db.php?dbname='+dbname),
    buttons: [
      {
        label: 'Close',
        action: function(dlg){
           dlg.close();
        }
      },
      {
        icon: 'glyphicon glyphicon-ok',
        label: 'Drop',
        cssClass: 'btn-primary',
        id : 'drop-button',
        action: function(dlg){
            var $button = this; 
            $.ajax({
                type: "GET",
                url: "php/drop_db.php",
                data: "dbname=" + dbname + "&action=drop",
                success : function(text){
                  dialog.setMessage(text);
                  databases_refresh();
                  $button.disable();
                }
               });
        }
      }
      ]
  });
  }
   
 function show_create_database(){
      dialog =    BootstrapDialog.show({
          title: 'Create Database',
          message: $('<div></div>').load('php/sqlite.php?action=create_form'),
          buttons: [
            {
              label: 'Close',
              action: function(dlg){
                dlg.close();
              }
            },
            {
              icon: 'glyphicon glyphicon-ok',
              label: 'Create',
              cssClass: 'btn-primary',
              action: function(dlg){
                   var name = $("#dbname").val();
                   if(name.length == 0){
                     $("#dbname").focus();
                     return;
                   }
                   var action = $("#action").val();
                  $.ajax({
                      type: "GET",
                       url: "php/sqlite.php",
                      //url: "/sqlite",
                      data: "dbname=" + name + "&action=" + action,
                      success : function(text){
                          databases_refresh();
                          dlg.close();         
                          
                      }
                     });
              }
            }
            ]
        });
  }
 function show_table_info(name,table){

  var sql = "PRAGMA table_info(" + table +")";
  execute_select(sql, function(a){
     current_table_info = a;
    var options ={
      div : "#table-info-content",
      data : a,  
      convert :  function(colname,value){
        if(colname == "notnull" || colname == "pk"){
         return (value == "1" ? "<span class='glyphicon glyphicon-ok'></span>" :"");
        }
        return value;
      }
    };
    $("#table-info-content").empty();
    new sqlite_table(options);
  });
  
  $("#table-info-content").html("Load Table Info...");
/*  return;
  
    $.ajax({
    type: "GET",
    url: "php/sqlite.php",
    data: "dbname=" + name + "&action=table_info&table="+table,
    success : function(text){
       var data = JSON.parse(text);
       current_table_info = data;
          
       var s ="<table class='table'>";
       s+="<thead><tr><th>CID</th><th>NAME</th><th>TYPE</th><th>NOT NULL</th><th>DEFAULT</th><th>PK</th></tr></thead><tbody>";
       for(var i =0; i< data.length;i++){
         var col = data[i];
         s += "<tr>";
         s += "<td>" + col["cid"] + "</td>";
         s += "<td>" + col["name"] + "</td>";
         s += "<td>" + col["type"] + "</td>";
         s += "<td>" + (col["notnull"] == "1" ? "<span class='glyphicon glyphicon-ok'></span>" :"") + "</td>";
         s += "<td>" + col["dflt_value"] + "</td>";
         s += "<td>" + (col["pk"] == "1" ? "<span class='glyphicon glyphicon-flash'></span>" :"") + "</td>";
         s += "</tr>";
       }
       s += "</tbody></table>";
       $("#table-info-content").html(s);
    }
    });
    */
 }
 function show_next_page(){
   tb_offset += 20;
   show_table_data(dbname,current_table);
 }
 function show_previous_page(){
   tb_offset -= 20;
   if(tb_offset < 0) tb_offset=0;
   show_table_data(dbname,current_table);
 }
function show_table_indexes(name,table){
  var sql = "PRAGMA index_list(" + table +")";
  execute_select(sql, function(a){
    var options ={
      div : "#table-info-content",
      data : a,  
      convert : function(colname,value){
      if(colname == "unique" || colname == "partial"){
       return (value == "1" ? "<span class='glyphicon glyphicon-ok'></span>" :"");
      }
      return value;
      }
    };
    $("#table-info-content").empty();
    new sqlite_table(options);
  });
  $("#table-info-content").html("indexes loading!");
}  
function show_table_triggers(name,table){
  $("#table-info-content").html("triggers todo!");
}  
function show_table_ddl(name,table){
  var sql = "SELECT * FROM sqlite_master WHERE name='" + table +"'";
    execute_select(sql, function(a){
    var options ={
      div : "#table-info-content",
      data : a,  
      convert : function(colname,value){
      if(colname == "unique" || colname == "partial"){
       return (value == "1" ? "<span class='glyphicon glyphicon-ok'></span>" :"");
      }
      return value;
      }
    };
    $("#table-info-content").empty();
    new sqlite_table(options);
  });
  $("#table-info-content").html("indexes loading!");

  $("#table-info-content").html("ddl todo!");
}  
function show_table_sql(name,table){
  var target = "#table-info-content";
   if(!name){
     target = "#main-content";
   }
   $.get("php/sql_form.php?dbname="+dbname + "&table=" + table + "&tm="+ new Date().getTime() ,function(data){
        $(target).html(data);
   });

  $(target).html("sql");
}  

 function show_table_data(name,table){
   
  var sql = "SELECT * FROM " + table +" LIMIT 20 OFFSET " +tb_offset ;
  execute_select(sql, function(a){
    var options ={
      div : "#table-info-content",
      data : a  ,
      pk :"id",
      dbname : name,
      table : table,
      pager: true,
      editable:true
    };
    $("#table-info-content").empty();
    new sqlite_table(options);
  });
  $("#table-info-content").html("Table data load...");
 }
 function show_database_tables(){
  $.get("php/db_info.php?dbname=" + dbname, function(html){
    $("#main-content").html(html);
  }); 
/*  return;
  var sql = "SELECT * FROM sqlite_master";
  execute_select(sql, function(a){
     var s = "<table class='table table-striped' style='width:100%'>";
     s +=  "<thead><tr><th>name</th><th>rootpage</th><th>tbl_name</th></tr></thead><tbody>";
    for(var i=0;i<a.length;i++){
      s+= "<tr data-method='show_table_tabs' data-args='" + dbname + "," + a[i].name + "'>";
      s+= "<td>" + a[i].name +"</td>"; 
      s+= "<td>" + a[i].rootpage + "</td>"; 
      s+= "<td>" + a[i].tbl_name + "</td>"; 
      s+= "</tr>";
      s+= "<tr data-method='show_table_tabs' data-args='" + dbname + "," + a[i].name + "'>";
      s+= "<td colspan='3'><pre>" + decodeURIComponent(a[i].sql) + "</pre></td></tr>"; 
    }
    s+="</tbody></table>";
    $("#main-content").html(s);
  });*/
 }
 

 function show_table_tabs(name,table){
    if(table != current_table) tb_offset = 0;
    current_table =table;
    open_database(name);
    $.ajax({
    type: "GET",
    url: "php/sqlite.php",
    data: "dbname=" + dbname + "&action=table_tabs&table="+table,
    success : function(text){
      $("#main-content").html(text);
      show_table_info(name,table);
    }
   });
}
function open_database(name,showtables) {
  dbname=name;
  $("#current_db").text(dbname);
  if(showtables)  show_database_tables();
}

$(function() {
  databases_refresh();
});

window.addEventListener('click',function(){	mb.onclick();});

$(document).on('keydown', function(e){
    if(e.ctrlKey && e.which === 83){ 
        e.preventDefault();
        e.stopPropagation();
        if(window.save_row){
          window.save_row();
        }
        return false;
    }
});

$.fn.extend({
	treeview:	function() {
		return this.each(function() {
			// Initialize the top levels;
			var tree = $(this);
			
			tree.addClass('treeview-tree');
			tree.find('li').each(function() {
				var stick = $(this);
			});
			tree.find('li').has("ul").each(function () {
				var branch = $(this); //li with children ul
				
				branch.prepend("<i class='tree-indicator glyphicon glyphicon-chevron-right'></i>");
				branch.addClass('tree-branch');
				branch.on('click', function (e) {
					if (this == e.target) {
						var icon = $(this).children('i:first');
						
						icon.toggleClass("glyphicon-chevron-down glyphicon-chevron-right");
						$(this).children().children().toggle();
					}
				})
				branch.children().children().toggle();
				
				/**
				 *	The following snippet of code enables the treeview to
				 *	function when a button, indicator or anchor is clicked.
				 *
				 *	It also prevents the default function of an anchor and
				 *	a button from firing.
				 */
				branch.children('.tree-indicator, button, a').click(function(e) {
					branch.click();
					
					e.preventDefault();
				});
			});
		});
	}
});

</script>

</head>

<body>

  <nav class="navbar navbar-inverse">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Sqlite</a>
      </div>
      <ul class="nav navbar-nav">
         <li class="dropdown active">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Databases<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a data-method="show_create_database">Create</a></li>
            <li><a data-method="show_drop_database">Drop Database</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#" data-method="show_create_sql_file">Create sql file</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#" data-method="databases_refresh">Refresh</a></li>
          </ul>
        </li>
        <li><a href="#" data-method="show_table_sql" >SQL</a></li>
        <li class="dropdown active">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Help<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="http://www.sqlite.org/lang.html" target="_blank">Sqlite documentation</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="http://php.net/manual/en/book.sqlite3.php" target="_blank">Sqlite3 php documentation</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="https://nakupanda.github.io/bootstrap3-dialog/#examples" target="_blank">Bootstrap dialog</a></li>
            <li><a href="https://getbootstrap.com/components/" target="_blank">Bootstrap components</a></li>
          </ul>
        </li>
        <li><a >Database  <b style="color: #cc6600" id="current_db"></b></a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a data-method="show_drop_database"><span class="glyphicon glyphicon-trash"></span></a></li>
      </ul>  
    </div>
  </nav>  


    <!-- pre-scrollable -->
  <div class="container-fluid">
    <div class="pre-scrollable fill_height" style="left:0%;width:20%;position:fixed;" id="left-panel">
    </div>
    <div class="pre-scrollable fill_height" style="left:20%;width:60%;position:fixed">
       <div id="main-content" style="width:100%;background-color:rgb(254,254,250);">
         <!--                 MAIN CONTENT                           -->
              <center><h1>No selected items</h1></center>
       </div>
    </div>
    <div style="left:80%;width:20%;position:fixed;">
  </div>

</body>
</html>

<?
 # index.php

?>