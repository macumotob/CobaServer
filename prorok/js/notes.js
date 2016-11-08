
var notes = (function () {
  var file_name = null;
  var edit_mode = true;
  var files = [];

  function show_files() {

    save(function () {
      try{
        $("#content").html("");
        var s = "";
        files.forEach(function (item) {
          s += "<div class='note-file' data-method='notes.edit' data-args='" + item + "'>" + item
            + "<span class='glyphicon glyphicon-trash pull-right' data-method='notes.drop' data-args='" + item + "'></span></div>";
        });
        //var file = data[0];
        //get(file);
        file_name = null;
        $("#content").html(s);
        $("#current-file-info").html("File not selected");
      }
      catch (e) {
        alert(e);
      }
    });
  }
  function get_list() {
    
    load_async_json("notes.list?tm=" + new Date().getTime(), function (data) {
      files = data;
      show_files();
    })
  }
  //
  function save(onsuccess) {
    var elem = document.getElementById("note");
    if (file_name === null || !elem) {
      if (onsuccess) onsuccess();
      return;
    }
    var txt = encodeURI($("#note").val());
    post("note.save?", "date=" + file_name + "&txt=" + txt, function (data) {
      $("#current-file-info").html("Файл: " + file_name + " " + data.msg);
      if (onsuccess) onsuccess();
    });
  }
  //
  function get(file) {
    //  <div  id="note-edit" style="text-align:left;visibility:hidden;display:block"><textarea id="note" value=""></textarea></div>
    $("#content").html("Loading...");
    $("#current-file-info").html("File not selected ");
    file_name = null;

    load_async("/notes?date=" + file +"&tm=" + new Date().getTime(), function (data) {
      try{  
        file_name = file;
        $("#current-file-info").html("File: " + file_name);
        if (edit_mode) {
          $("#content").html('<div class="btn btn-success" style="width:50%" data-method="notes.save">Save</div><div  id="note-edit" style="text-align:left;display:block"><textarea id="note" value=""></textarea></div>');
          $("#note").val(data);
        }
        else {
          $("#content").html("<div style='background-color:white;color:black;padding:20px;font-size:1.8em;'>"+ data +"</div>");
        }
      } catch (e) {
        alert(e);
      }
    });
  }
  function view(file) {
    if (file_name === null) {
      alert("File not selected");
      return;
    }
    edit_mode = !edit_mode;
    var ext = mb.get_file_ext(file_name);

    if (ext === "txt") {
      save(function () {
        get(file_name);
      });
    }
    else {
      $("#current_note").html(file);
      id("audio").src = "/notes.wav?file=" + file;
    }

  }
  function edit(file) {
    edit_mode = true;
    var ext = mb.get_file_ext(file);

    if (ext === "txt") {
      save(function () {
        get(file);
      });
    }
    else {
      $("#current_note").html(file);
      id("audio").src = "/notes.wav?file=" + file;
    }

  }
  //
  function drop(file) {
    if (file) {
      if (confirm("Do you real want drop file:\n " + file)) {
        load_async_json("/notes.remove?file=" + file, function (data) {
          get_list();
        });
      }
      return;
    }

    if (file_name == null) {
      alert("File not selected!");
      return;
    }
    if (confirm("Do you real want drop file:\n " + file_name)) {
      load_async_json("/notes.remove?file=" + file_name, function (data) {
        get_list();
      });
    }
  }
  //
  function create_file() {
    file_name = $("#new-file-name").val();
    if (!file_name || file_name.length == 0) {
      file_name = null;
      return;
    }
    post("note.save?", "date=" + file_name + "&txt=new file", function (data) {
      get(file_name);
    });

  }
  function create() {
   
    edit_mode = true;
    save(function () {
      $("#content").html('<table style="width:100%"><tr><td>New file name</td></tr><tr><td><input type="text" id="new-file-name" value="" /></td>' +
      '</tr><tr><td data-method="notes.create_file" class="btn btn-success">Create<span class="glyphicon glyphicon-plus"></span></td></tr></table>');
      });

    /*

    save(function () {
      file_name = current_note_name.value;
      post("note.save?", "date=" + file_name + "&txt=new file", function (data) {
        id("note-save-result").innerText = "Файл: " + file_name + " " + data.msg;
      });
    });
    */
  }
  function view_all() {
    //  alert(files.length);
      var i = 0;
      s = "";
      while (i < files.length) {
          s += "<pre>"+load_sync("/notes?date=" + files[i++] + "&tm=" + new Date().getTime());
          s += "</pre><hr>";
      }
      $("#content").html(s);

  }
  return {
    "list": get_list,
    "edit": edit,
    "create": create,
    "save": save,
    "drop": drop,
    "view": view,
    "show_files": show_files,
    "create_file": create_file,
      "view_all": view_all
  };
})();

//window.addEventListener('click', function () {
//  mb.onclick();
//});

document.addEventListener("keydown", function (e) {
  if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
    e.preventDefault();
    notes.save();
  }
}, false);

$(document).ready(function () {
  notes.list();

  $(window).on("click", function () {
    mb.onclick();
  });
  $(window).on("selstart", function () {
    return false;
  });
});

//alert("loaded js");