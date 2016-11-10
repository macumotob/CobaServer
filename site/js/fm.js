
alert("FM BEGIN ...");

HTMLElement.prototype.removeClass = function (remove) {
  var newClassName = "";
  var i;
  var classes = this.className.split(" ");
  for (i = 0; i < classes.length; i++) {
  //  console.log(classes[i]);
    if (classes[i] !== remove) {
      newClassName += classes[i] + " ";
    }
  }
  this.className = newClassName;
}

function fm_viewer(ident) {
  this.id = ident;
  this.files = [];
  this.index = 0;
  this.reset = function () {
    this.files = [];
    this.index = 0;
  };
  this.reg = function (file) {
    this.files.push(file);
  };
  this.play = function (i) {
    var tr = id("tr" + this.index);
    if (tr) {
      //tr.className = "text-default";
      tr.removeClass("success");
    }
    var elem = id(this.id);
    //elem.src = 'get.file?file=' + fm.join_path() + this.files[i].name;
    elem.src = fm.join_path() + this.files[i].name;
    elem.play();
    this.index = i;
    tr = id("tr" + this.index);
    if (tr) {
      //tr.className = "text-primary";
      tr.className = "success";
    }
  };
  this.hl = function () {
    var tr = id("tr" + this.index);
    if (tr) {
      tr.className = "text-primary";
    }
  };
  this.sort = function () {
    this.files.sort(function (a, b) {
      if (a.name.toLowerCase() < b.name.toLowerCase())
        return -1;
      if (a.name.toLowerCase() > b.name.toLowerCase())
        return 1;
      return 0;
    });
  };
  this.next = function(){
    var i = this.index+1;
    if (i > this.files.length) {
      i = 0;
    }
    this.play(i);
  }
  this.play_by_name = function (name) {
    for(var i = 0; i < this.files.length;i++)  {
      if (this.files[i].name === name) {
        this.play(i);
        return;
      }
    }
    this.play(0);
  }
}

var lng = {
  ef: {
    worning: "Предупреждение",
    folder: "Папка",
    is_empty: "пустая.",
    can: "Вы можете ",
    create: "Создать подпапку",
    upload: "Выгрузить файла на сервер",
    or: "или",
    goback:"Выйти из папки"
  }
};

var img = {
  images: [],
  index : 0,
  image: null,
  elem_index: null,
  elem_count: null,
  elem_caption: null,
  path : null
, reg: function (file) {
  this.images.push(file);
}
, find: function () {
  if (this.image == null) {
    this.image = id("img-view");
    this.elem_index = id("img-index");
    this.elem_count = id("img-count");
    this.elem_caption = id("img-caption");
  }
}
, reset: function (path) {
  this.path = path;
  this.index = 0;
  this.images = [];
  this.image = null;
}
, first: function () {
  this.find();
  this.index = 0;
  this.refresh();
}
, last: function () {
  this.find();
  this.index = this.images.length - 1;
  this.refresh();
}
, next: function () {
  this.find();
  this.index++;
  if (this.index > this.images.length - 1) {
    this.index = 0;
  }
  this.refresh();
}
, prev: function () {
  this.find();
  this.index--;
  if (this.index < 0) {
    this.index = this.images.length - 1;
  }
  this.refresh();
}
,get_current : function(){
  var name = this.images[this.index];
  return fm.join_path() + name;

}
, refresh: function () {
  var name = this.images[this.index];
  var path = fm.join_path() + name;
  this.image.src = path;
  this.image.alt = this.images[this.index];
  this.elem_index.innerHTML = (this.index + 1);
  this.elem_count.innerHTML = this.images.length;
  this.elem_caption.innerHTML = name;

}
, refresh_by_name : function(name){
  for(var i = 0, count=this.images.length; i < count; i++){
    if(this.images[i] == name){
      this.index = i;
      this.refresh();
      return;
    }
  }
}
, show_modal : function(filename){
  var d =  document.getElementById("dd");
  generator.gen([],"i", "dd");
  img.find();
  img.refresh_by_name(decodeURIComponent(filename));
  //d.requestFullScreen();
}
,scale : 1
, zoomin:function(){
  //var d = document.getElementById("dd");
  //d.clientWidth += d.clientWidth *0.1;

  this.image.clientWidth += this.image.clientWidth *0.2;
}
,zoomout : function(){
//  var d = document.getElementById("dd");
//  d.clientWidth -= d.clientWidth *0.1;
  this.scale -= 0.5;
  this.image.style = "transform:scale(" + this.scale + ")";

 }
};
//         -----------------------------------
//                 fm
//-----------------------------------------------
var fm = {
  stack: [],
  is_popup_visible: false,
  consts: {
    main_content: "fm-main-content"
  },
  state: {
    current: 0,
    upload: 1,
    create_folder: 2,
    navigator: 3
  },
  file_type: {
    unknown: 0,
    video: 1,
    audio: 2,
    document: 3,
    image: 4,
    html : 5
  }
, audio: new fm_viewer("audio")
, video: new fm_viewer("video")
, links: new fm_table("links")
, encode: function (s) {
  return encodeURIComponent(s);
}

, downloadAll: function (files){  
  if(files.length == 0) return;  
  file = files.pop();
  this.download_file(file.name);
  /*
  var href =  fm.join_path() + encodeURIComponent(file.name)
  var theAnchor = $('<a />')  
      .attr('href', file[1])  
      .attr('download',file[0]);  
  theAnchor[0].click();   
  theAnchor.remove();  
  */
  this.downloadAll(files);  
}  
  /*
            DOWNLOAD  F O L D E R
  */
, download_folder: function (name) {
  
  this.downloadAll(this.audio.files);
  return;

  if (name[0] === '~') {
    BootstrapDialog.show({
      title : "Предупреждение",
       message: $('<div></div>').load('data/error_download_drive.html'),
       
       buttons: [{
         label: 'Закрыть',
         action: function(dialogRef){
           dialogRef.close();
         }
       }],
      onshown: function (dialogRef) {
        var dr = id("drive");
        dr.innerText = name;
      },
    });
    return;
  }
  BootstrapDialog.show({
    title: "Скачать папку",
    message: $('<div></div>').load('data/folder_download.html'),

    buttons: [
      {
        id:'button-download-folder',
        label: 'Скачать',
        cssClass: 'btn-success',
        action: function (dialogRef) {
          zip_folder_zip(name);
        }},
      {
        label: 'Закрыть',
        action: function (dialogRef) {
          dialogRef.close();
        }
      }
     ],
    onshown: function (dialogRef) {
      zip_folder_show(name);
    },
  });
  
}
,pokupki : function(){
  fm_set_main_content(generator.gen(null, "geo", null));

 //setInterval(function () { fm.get_geo();}, 500);

  this.get_geo();
}
,get_geo : function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(this.show_geo_position);
  } else {
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
}
,show_geo_position : function(position) {
  var x = document.getElementById("geo");
  x.innerHTML = "Latitude: " + position.coords.latitude + 
  "<br>Longitude: " + position.coords.longitude; 
}
  ,is_video(ext){
    return  ("mp4;mov;3gp;ogg;avi;mkv;vob;m4v;".indexOf(ext + ';') >= 0);
  }
  , is_picture(ext) {
    return ("jpg;png;jpeg;bmp;".indexOf(ext + ';') >= 0);
  }
  , is_mobile() {
    
      if (navigator.userAgent.match(/Android/i)
      || navigator.userAgent.match(/webOS/i)
      || navigator.userAgent.match(/iPhone/i)
      || navigator.userAgent.match(/iPad/i)
      || navigator.userAgent.match(/iPod/i)
      || navigator.userAgent.match(/BlackBerry/i)
      || navigator.userAgent.match(/Windows Phone/i)
      ) {
        return true;
      }
      else {
        return false;
      }
    
  }
, get_file_type: function (ext) {
  if ("pdf;doc;mobi;fb2;txt;epub;rtf;doc;zip;rar;muse;tex;".indexOf(ext + ';') >= 0) return this.file_type.document;
  if (this.is_video(ext) ) return this.file_type.video;
  if (this.is_picture(ext)) return this.file_type.image;
  if ("mp3;3gpp;".indexOf(ext + ';') >= 0) return this.file_type.audio;
  if ("html;".indexOf(ext + ';') >= 0) return this.file_type.html;
  return this.file_type.unknown;
}
, errors: {
  create_folder: "specify the folder in which you want to create a subfolder",
  upload_select_folder: "specify the folder to which you want to upload files"
},
  foreach: function (obj, callback) {
    for (var i = 0, max = obj.length; i < max; i++) {
      var item = obj[i];
      callback(item, i);
    }
  }
, can_goback: function () {
  return (this.stack.length > 0);
}

, get_main_content: function () { return id(this.consts.main_content); }
, set_folder: function (folder) {
  if (folder !== "root") {
    if (folder === "..") {
      this.stack.pop();
    }
    else {
      this.stack.push(folder);
    }
    folder = this.stack.join('/');
    if (folder.length === 0) {
      folder = "root";
    }
  }
  else {
    this.stack = [];
  }
  this.is_popup_visible = false;
  return folder;
}
, join_path: function (decode) {
  var s = "";
  for (var i = 0, max = this.stack.length ; i < max; i++) {
    var x = this.stack[i];
    x = (x[x.length - 1] == '/') ? x.substr(0, x.length - 1) : x;
    s += (decode ? decodeURIComponent(x) : x) + '/';
  }
  return s;
}
, open_file: function (file) {

  //var link = document.createElement("a");
  //link.href = decodeURI(fm.join_path() + file);
  //link.target = "_blank";
  //link.click();\
  var url = decodeURI(fm.join_path() + file);
  url = unescape(decodeURIComponent(fm.join_path() + file));
  //alert(url);
  var ext = get_file_ext(file);
  if (ext === "fb2") {
    //http://192.168.0.46:14094/books/1/American%20Gods.fb2
    var urls = [
      { url: "~Книги/", target: "http://192.168.0.46:14094/b/" },
      { url: "~Книги fb2/", target: "http://192.168.0.46:14094/b/fb2/" }
    ];
    for (var i = 0 ; i < urls.length; i++) {
      var x = urls[i];
      if (url.indexOf(x.url) != -1) {
        url = url.replace(x.url, x.target);
      }
    }
    $("<a href='" + url + "' target='_blank'></a>")[0].click();
    return;
  }
  if (ext === "doc") {
    //alert(url);
    var link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.click();
    return;
  }
  if (ext === "zip" || ext === "rar") {
    load_async_json("/textview?file=" + url, function (data) {
      
      if (data.result) {
        init_document(data.msg);
      }
      else {
        alert(data.msg);
      }
    });
    return;
  }
  else {
    window.open(window.location.href + "/textview?file=" + url, '_blank', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes');
  }
}
, open_site: function (url) {
  var link = document.createElement("a");
  link.href = "http://" + url;
  link.target = "_blank";
  link.click();
}
, download_file: function (file) {

  var link = document.createElement("a");
  link.download = decodeURI(file);
  link.href = decodeURIComponent(fm.join_path() + file);
  link.target = "_blank";
  link.click();
  //  fm.refresh();
}
, refresh_current: function () {

  switch (this.state.current) {
    case this.state.upload:
      if (this.stack.length === 0) {
        fm.refresh();
      }
      else {
        create_upload_form();
      }
      break;
    case this.state.create_folder:
      create_folder_form();
      break;
    case this.state.navigator:
      fm.refresh();
      break;
    default:
      break;
  }
}
, recreate_stack: function (max) {
  var new_stack = [];
  for (var i = 0; i < max; i++) {
    new_stack.push(this.stack[i]);
  }
  var folder = this.stack[max];
  this.stack = new_stack;
  return folder;
}
, show_ext_menu: function () {
  this.reset_notes();
  fm_set_main_content(generator.generate_one(null, "fm-ext-menu", null));
}
, show_servers: function () {
  var self = this;
  load_async_json("/get.servers?tm=" + new Date().getTime(), function (data) {
    if (data.result) {
      fm_set_main_content(generator.gen(data, "servers"));
    }
    else {
      fm_set_main_content(generator.generate_one(data.msg, "fm-mysql-error"));
      $("myModal").modal();
      self.show_ext_menu();
    }
  });
}
, dropdown_hide: function (dropdown_name) {
  var drop = id(dropdown_name);//"dropdown-left");
  if (drop) {
    drop.removeClass("open");
  }
}
,hide_left_popup : function(){
  this.dropdown_hide("dropdown-left");
}
, hide_right_popup: function () {
  //this.dropdown_hide("dropdown-right");
}
, offset: 0
, last_notes: false
, reset_notes: function () {
  this.offset = 0;
  this.last_notes = false;
}/*
, show_notes_offset: function (delta) {

  
  this.offset += parseInt(delta);
  if (this.offset < 0) this.offset = 0;
  this.show_notes();
}
, show_notes: function () {
  var count = 10;

  
  var self = this;
  var url = "/notes.get?offset=" + this.offset + "&count=" + count + "&tm="+ new Date().getTime();
  load_async_json(url, function (data) {

    if (data.result) {
      var info = id("notes-info");
      if (data.msg.length == 0) {
        if (info) {
          self.last_notes = true;
          info.innerHTML = "last records";
        }
        return;// self.show_add_note();
      }
      fm_set_main_content(generator.gen(data.msg, "notes"));
      self.last_notes = false;
      if (info) {
        info.innerHTML = "records:" + self.offset;
      }
      self.dropdown_hide("dropdown-left");
    } else {
      fm_set_main_content(generator.generate_one(data.msg, "fm-mysql-error"));
      $("myModal").modal();
    }

  });
}
, delete_note: function (ident) {
  var self = this;
  load_async_json("/notes.delete?id=" + ident, function (data) {
    if (data.result) {
      self.show_notes();
    }
    else {
      alert(data.msg);
    }
    
  });
}
, save_new_note: function (txt) {
    var elem = id("txt");
    if (elem.value.length == 0) {
      elem.value = "Введите текст...";
      return;
    }
    var self = this;
    post("note.add?", "txt=" + encodeURI(elem.value), function (data) {
      if (data.result) {
        self.show_notes();
      }
      else {
        elem.value = data.msg + "\n\n" +elem.value;
      }
    });
}
, show_add_note: function () {
  fm_set_main_content(generator.gen(null, "add-note"));
}
, show_edit_note: function (ident) {
  var self = this;
  load_async_json("/notes.note?tm="+ new Date().getTime() + "&id=" + ident, function (data) {
    if (data.result) {
      fm_set_main_content(generator.gen(data.msg[0], "edit-note"));
    }
    else {
      alert(data.msg);
    }
  });
}
, update_note: function (ident) {
  var elem = id("txt");
  var self = this;
  post("note.update?", "id=" + ident + "&txt=" + encodeURI(elem.value), function (data) {
    if (data.result) {
      self.show_notes();
    }
    else {
      elem.value = data.msg + "\n\n" + elem.value;
    }
  });
}*/,
create_new_file: function () {
  
  //id("current-note").innerText = name == "0" ? "Today" : name;
  var name = id("new-file-name").value;
  
  load_async_json("file.create?file=" + encodeURI(name), function (data) {
    if (data.result) {
      fm.get_notes_list();
    }
    else {
      alert(data.msg);
    }
  });
},
delete_file : function(name,onsuccess){
  var url = decodeURIComponent(fm.join_path() + name);
  if(!confirm("DELETE FILE " + url))  return;

  load_async_json("file.delete?name=" + url, function (data) {
    if (data.result) {
      if (onsuccess) onsuccess(data);
      else {
        alert(data.msg);
      }
    }
    else {
      alert(data.msg);
    }
  });
/*  $.ajax({
    dataType: "json",
    url: "file.delete",
    data: "name=" + name,
    success: function (x,y,z,q) {
      alert('success');
    }*//*,
    error: function (x,y,z,q) {
      alert('error');
    }
  });*/
},
  // new notes on local disk, without using database
save_note :function(name){
  var elem = id("notes");
  //id("current-note").innerText = name == "0" ? "Today" : name;
  name = name ? name : id("current-note").innerText;
  name = name == "Today" ? "0" : name;
  post("note.save?", "date=" + name +"&txt=" + encodeURI(elem.value), function (data) {
    id("note-save-result").innerText = data.msg;
  });
},
get_notes_list: function () {
  load_async_json("notes.list?tm=" + new Date().getTime(), function (data) {
    fm_set_main_content(generator.gen(data, "notes"));
  });
},
show_notes_page: function (name) {
  load_async("/notes_edit_page.html?tm="+ new Date().getTime(), function (data) {
    fm_set_main_content(data);
    name = name ? name : "0";
    load_async("/notes?date=" + name, function (data) {
      id("current-note").innerText = name == "0" ? "Today" : name;
      id("notes").value = data;
    });
  });
}
};


function get_file_ext(file) {
  var ext = "";
  for (var i = file.length - 1; i > -1; i--) {
    if (file[i] === '.') break;
    ext = file[i] + ext;
  }
  return ext.toLowerCase();
}
function create_image_view(parent, filename) {
 return img.show_modal(filename);

  fm_set_main_content(generator.gen(img.images, "images"));
  img.find();
  img.refresh_by_name(decodeURIComponent(filename));
//  return;
//  load_async("/img_view.html", function (text) {
//    parent.innerHTML = text;
//    var elem = id("img-view");
//    elem.onload = function () {
//      var k = this.height / this.width;
//    }
//    img.find();
//    img.refresh();
//});
}

function create_mp3_player(parent, filename) {

 fm_set_main_content(
generator.generate_one(fm.audio.files, "fm-audio-header", 0)
+ generator.generate(fm.audio.files, "fm-audio-body", 0)
+ generator.generate_one(fm.audio.files, "fm-audio-footer")
  );
 fm.audio.play_by_name(filename);
}

function create_vidio_view(parent, filename) {

 fm_set_main_content(
    generator.generate_one(fm.video.files, "fm-video-header",0)
    + generator.generate(fm.video.files, "fm-video-body", 0)
    + generator.generate_one(fm.video.files, "fm-video-footer")
  );
  fm.video.play_by_name(filename);
}
function create_upload_form() {

  fm.state.current = fm.state.upload;

  if (fm.stack.length == 0 ){
    fm_show_error(fm.errors.upload_select_folder) 
  }
  fm_set_main_content(generator.generate_one(null, "fm-upload-form"));
  
  fm.hide_right_popup();
  //control_upload_files.click();
}
function create_folder_form() {

  if (fm.stack.length == 0) {
    fm_show_error(fm.errors.create_folder);
  }
  else {
    fm_set_main_content(generator.generate_one(null, "fm-create-folder"));
  }
  fm.hide_right_popup();
}
function fm_get_file(file) {

  var elem = fm.get_main_content();
  var ext = get_file_ext(file);

  var command = "get.file?file=" + fm.join_path() + file;

  var type = fm.get_file_type(ext);
  var fname = decodeURIComponent(file);

  switch (type) {
    case fm.file_type.document:
      return fm.open_file(file);
    case fm.file_type.video:
      return create_vidio_view(elem, fname);
    case fm.file_type.image:
      return create_image_view(elem, fname);
    case fm.file_type.audio:
      return create_mp3_player(elem, fname);
    case fm.file_type.html:
      return fm.open_file(file);
    default:
      return fm.open_file(file);
      break;
  }
  
}
function fm_set_main_content(html) {
  var elem = fm.get_main_content();
  elem.innerHTML = html;
}
function fm_show_error(text) {
  load_async("/error.html", function (data) {
    fm_set_main_content(data);
    id("error-text").innerHTML = text;
  });
}
function fm_create_folder() {

  var elem = id("input-folder-name");
  fm.state.current = fm.state.create_folder;

  if (elem.value.length < 1) {
    fm_show_error("folder name is empty!");
    return;
  }
  var folder_name = encodeURI(elem.value);
  load_async_json("/mkdir?folder=" + encodeURI(fm.join_path()) + "&name=" + folder_name, function (data) {
    if (data.result) {
      fm.refresh();
    }
    else {
      alert(data.name + "\n" + data.msg);
    }
  });
  fm.get_main_content().innerHTML = "working...";
}
function fm_refresh_by_index(index) {
  var folder = fm.recreate_stack( parseInt(index));
  init_document(folder);
}

function make_popup() {

  if (fm.is_popup_visible) {
    fm.is_popup_visible = false;
    fm.refresh();
    return;
  }
  fm.is_popup_visible = true;
  var elem = fm.get_main_content();

  var html = generator.generate_one(fm.stack, "fm-popup-header", 0);
  html += generator.generate(fm.stack, "fm-popup-body");
  html += generator.generate_one(fm.stack, "fm-popup-footer",0);
  elem.innerHTML = html;
 // fm.dropdown_hide("dropdown-right");
}
/*
function make_breadcrumbs() {

  var html = generator.generate_one(fm.stack, "fm-bread-header")
   + generator.generate(fm.stack, "fm-bread-body")
   + generator.generate_one(fm.stack, "fm-bread-footer");

  id("td-path").innerHTML = html;

}
*/
function fm_delete_file(file) {
  confirm("delete " + file);
}

function fm_append_images() {

  var x = document.getElementsByTagName("p");

  for (var i = 0; i < x.length; i++) {
    var item = x[i];
    var ext = $(item).attr("fm-ext");
    if (fm.is_picture(ext)) {
      var url = $(item).attr("fm-path")
      $(item).append("<img src='" + url + "' width='100' height='100' async/>");
    }
    else if ( fm.is_video(ext)  ) {
      var url = $(item).attr("fm-path")
      $(item).append("<video src='" + url + "' width='100' height='100' preload='metadata'/>");
    }
    //   break;
  }


}
function init_document(folder) {

  alert("init begin " + folder);
  try {

    history.pushState(null, null, '/');

    fm.state.current = fm.state.navigator;
    folder = fm.set_folder(folder);
    //make_breadcrumbs();
    $("#fm-current-folder").text(folder + " ...");

    fm.video.reset();
    fm.audio.reset();
    
    load_async_json("get.folder?folder=" + encodeURIComponent(folder) + "&tm=" +(new Date).getTime(), function (data) {
      
      if (data.length === 0) {
        return fm_set_main_content(generator.generate_one(decodeURIComponent(folder), "fm-empty-folder", null));
      }
    
      $("#fm-current-folder").text(folder + " " + data.folders.length + "/" + data.files.length);
      
      img.reset(fm.join_path());
      var html = generator.generate_one(null, "fm-list-header");

      fm.foreach(data.folders, function (item, i) {
        html += generator.generate_one(item, "fm-list-folder-body", i);
      });

      fm.foreach(data.files, function (item, i) {

        html += generator.generate_one(item, "fm-list-file-body", i);

        var filename = item.name;
        var ext = get_file_ext(filename);
        var type = fm.get_file_type(ext);
        switch (type) {
          case fm.file_type.document:
            break;
          case fm.file_type.video:
            fm.video.reg(item);
            break;
          case fm.file_type.image:
            img.reg(filename);
            break;
          case fm.file_type.audio:
            fm.audio.reg(item);
            break;
          default:
            break;
        }
      });

      html += generator.generate_one(null, "fm-list-footer");
      fm.get_main_content().innerHTML = html;

      if (!fm.is_mobile()) {
          fm_append_images();
      }
      alert("init end");
   });
  }
  catch (err) {
    alert(err);
  }
}
alert("FM END");