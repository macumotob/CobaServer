function sqlite_table(options){

/*
  options
   div, data, convert
*/
  var columns= [];
  
  function crtb(){
    var t = document.createElement("table");
    t.appendChild(document.createElement("thead"));
    t.appendChild(document.createElement("tbody"));
    $(t).addClass("table table-hover");
    if(options.caption){
      $(t).append("<caption>" + options.caption +"</caption>");
    }

    return t;
  }  
  function crtr(tb){
    var tr= document.createElement("tr");
    if(tb){
      tb.appendChild(tr);
    }
    return tr;
  }
  function crth(tr,caption){
    var th = document.createElement("th");
    if(caption){
      th.innerText = caption;
    }
    if(tr){
      tr.appendChild(th);
    }
    return th;
  }
  function crtd(tr,caption){
    var td = document.createElement("td");
    if(caption){
      td.innerHTML = caption;
    }
    if(tr){
      tr.appendChild(td);
    }
    return td;
  }
  function delete_row(){
    alert("delete row");
  }
  function edit_row(ident){
    $(options.div).empty();
    $.get("php/edit_row.php?dbname=" + options.dbname + "&table=" + options.table + "&id=" +ident ,function(data){
      $(options.div).html(data);
      $("#row-go-back").click( function() {
        $(options.div).empty();
        populate();
        
        //show_table_tabs(options.dbname, options.table );
        //$("#show-table-data").click();
        //show_table_data(name,table);
        });
    });
    /*
    return;
    var div = $("<div class='container' >back to data</div>");
    var tb = crtb();

    if(options.data && options.data.length > 0){
      
      var item = options.data[ident];
      Object.keys(item).forEach(function(key) {
         tr = crtr(tb.tBodies[0]);
         crtd(tr,key);
         var td = crtd(tr);//,decodeURIComponent(item[key]));
         var input = $("<input type='text' value=''/>");
         input.css("width","100%");
         input.val(decodeURIComponent(item[key]));
         $(td).append(input);
      });
    }
    div[0].onclick = function(){
      $(options.div).empty();
      populate();
    }
    $(div).append(tb);
    $(options.div).append(div);
    */
  }
  function populate() {
    var tb = this.tb = crtb();
    var tr = crtr(this.tb.tHead);
    
    if(options.data && options.data.length > 0){
      var item = options.data[0];
      Object.keys(item).forEach(function(key) {
         crth(tr,key);
         columns.push(key);
      });
      
      options.data.forEach(function(item){
        tr = crtr(tb.tBodies[0]);
        if(options.pk){
          tr.id = "row" + item[options.pk];
        }
        if(options.editable) {
          tr.onclick = function(){
          edit_row(item[options.pk]);
          }
        }
        if(options.onrow) {
          tr.onclick = function(){
          options.onrow(tr);
          }
        }
        
        Object.keys(item).forEach(function(key) {
          var v = decodeURIComponent(item[key]);
           if(options.convert){
             v = options.convert(key,v);
           }
           crtd(tr,v);
        });
        
      });
    }
    if(options.pager) {
    var s ='<ul class="pager">';
    s+='<li class="previous" data-method="show_previous_page"><a href="#">Previous</a></li>';
    s+='<li class="next" data-method="show_next_page"><a href="#">Next</a></li></ul>';
    $(options.div).append(s);
    }
    $(options.div).append(this.tb);
  }
  populate() ;
}
/*
--------------------------------------------
*/
function fm_table(name) {

  this.name = name;
  this.offset = 0;

  this.make_prms = function () {
    var attr = $("[fm-name]");
    var s = "tm=" + new Date().getTime();
    for (var i = 0; i < attr.length; i++) {
      s += "&" + attr[i].getAttribute("fm-name");
      s += "=" + encodeURIComponent(attr[i].value);
    }
    return s;
  }
  this.show = function () {
    var count = 10;

    var self = this;
    var url = "/" + this.name + ".page?offset=" + this.offset + "&count=" + count + "&tm=" + new Date().getTime();
    load_async_json(url, function (data) {
      if (data.result) {
        var info = id("#info");
        if (data.msg.length == 0) {
          if (info) {
            info.innerHTML = "last records";
          }
          return;// self.show_add_note();
        }
        fm_set_main_content(generator.gen(data.msg, name));
        if (info) {
          info.innerHTML = "records:" + self.offset;
        }
        fm.dropdown_hide();
      }
      else {
        alert(data.msg);
      }
    });
  }
  this.edit = function (ident) {
    var self = this;
    load_async_json("/" + name + ".edit?tm=" + new Date().getTime() + "&id=" + ident, function (data) {
      if (data.result) {
        fm_set_main_content(generator.gen(data.msg[0], name + "-edit"));
      }
      else {
        alert(data.msg);
      }
    });
  };
  this.show_add = function () {
    fm_set_main_content(generator.gen(null, name +"-add"));
  }
  this.show_offset = function (delta) {
    this.offset += parseInt(delta);
    if (this.offset < 0) this.offset = 0;
    this.show();
  };
  this.add = function (ident) {
    
    var self = this;
    post(name + ".add?",  this.make_prms(), function (data) {
      if (data.result) {
        self.show();
      }
      else {
        alert(data.msg);
      }
    });
  };
  this.update = function (ident) {
    var self = this;
    post(name + ".update?", "id=" + ident + this.make_prms(), function (data) {
      if (data.result) {
        self.show();
      }
      else {
        alert(data.msg);
      }
    });
  };
  this.delete = function (ident) {
    var self = this;
    load_async_json("/" + name + ".delete?id=" + ident, function (data) {
      if (data.result) {
        self.show();
      }
      else {
        alert(data.msg);
      }
    
    });
  }
}// end of class