

function id(name) {
  var item = document.getElementById(name);
  if (!item) {
    alert('error in function id ' + name);
  }
  return item;
 //return document.getElementById(name);
}
var mb = (function () {

  function file_delete(name) {
    if (!confirm("Delete file " + name)) return;
    load_async_json("file.delete?name=" + name, function (data) {
      if (data.result) {
        alert(data.msg);
      }
      else {
        alert(data.msg);
      }
    });
  }
  function get_file_ext(file) {
    var ext = "";
    for (var i = file.length - 1; i > -1; i--) {
      if (file[i] === '.') break;
      ext = file[i] + ext;
    }
    return ext.toLowerCase();
  }

  function sqlite(sql) {
    alert(sql);
    load_async_json("sqlite?" + encodeURI(sql), function (data) {
      if (data.result) {
        alert(data.msg);
      }
      else {
        alert(data.msg);
      }
    });
  }

  function find_data_method(elem) {
    var method = null;

    while (!method) {
      method = elem.getAttribute("data-method");
      if (method) {
        return { elem: elem, method: method, args: elem.getAttribute("data-args") };
      }
      elem = elem.parentElement;
      if (elem == null) return method;
    }
  }
  function fm_prevent_events(e) {
    if (typeof e.preventDefault === 'function') {
      e.preventDefault();
      e.stopPropagation();
    } else {
      e.returnValue = false;
      e.cancelBubble = true;
    }
  }

  function fm_on_click(e) {

    e = e || window.event;
    var target = e.target || e.srcElement;

    var x = find_data_method(target);
    if (!x) {
      return true;
    }
    if (x.method) {
      if (x.args) {
        x.args = x.args.split(',');
        x.args.push(target);
      }
      x.method = x.method.split('.');
      if (x.method.length === 1) {
        window[x.method[0]].apply(this, x.args);
      }
      else if (x.method.length === 2) {
        var obj = eval(x.method[0]);
        window[x.method[0]][x.method[1]].apply(obj, x.args);
      }
      else {
        var obj = eval(x.method[0] + "." + x.method[1]);
        window[x.method[0]][x.method[1]][x.method[2]].apply(obj, x.args);
      }
      fm_prevent_events(e);
    }
  }

  return {
    "file_delete": file_delete,
    "onclick": fm_on_click,
    "sql": sqlite,
    "get_file_ext": get_file_ext
  };

})();



  function toggleFullScreen() {
  if (!document.fullscreenElement &&    // alternative standard method
      !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) {  // current working methods
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
}

function load_async(file,onsuccess) {
  var xhr = new XMLHttpRequest();
  xhr.open("get", file, true,"waswas","pwd");
  xhr.setRequestHeader("Access-Control-Allow-Origin", "*");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status >= 200 && xhr.status < 300 || xhr.status == 304) {
        var text = xhr.responseText;
      //  var heders = xhr.getResponseHeader("content-type");
      if(onsuccess){
        	onsuccess(text);
        }
      }
    }
  };
  xhr.send(null);
}
function load_sync(file) {
    var xhr = new XMLHttpRequest();
    xhr.open("get", file, false, "waswas", "pwd");
    xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
    xhr.send(null);
    if (xhr.status == 200) {
        return xhr.responseText;
    }
    return "";
}

function post(url,data,cb) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 // http.setRequestHeader("Content-length", data.length);
 // http.setRequestHeader("Connection", "close");

  xhr.onreadystatechange = function () {//Call a function when the state changes.
    if (xhr.readyState == 4) {
      if (xhr.status >= 200 && xhr.status < 300 || xhr.status == 304) {
        var text = xhr.responseText;
        cb( eval( "(" + text + ")"));
      }
    }
  };
  xhr.send(data);
}
function load_async_(file, onsuccess) {
  var xhr = new XMLHttpRequest();
  xhr.open("get", file, true);
  xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
 // xhr.setRequestHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36');
  xhr.setRequestHeader('Accept', '*/*');
  //xhr.setRequestHeader("Host", "xyz.com:8000");
  xhr.setRequestHeader("Accept", "*/*");
  xhr.setRequestHeader("Accept-Language", "en-US,en;q=0.5");
  //xhr.setRequestHeader("Accept-Encoding", "gzip, deflate");
  //xhr.setRequestHeader("DNT", "1");
  //xhr.setRequestHeader("Referer", "http://localhost:8000/test/");
  //xhr.setRequestHeader("Origin", "http://localhost:8000");
  //xhr.setRequestHeader("Connection", "keep-alive");
  xhr.setRequestHeader("Pragma", "no-cache");
  xhr.setRequestHeader("Cache-Control", "no-cache");
  //xhr.setRequestHeader("Content-Length", 0);

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status >= 200 && xhr.status < 300 || xhr.status == 304) {
        var text = xhr.responseText;
        //  var heders = xhr.getResponseHeader("content-type");
        onsuccess(text);
      }
    }
  };
  xhr.send(null);
}





function load_sync(file) {
  var xhr = new XMLHttpRequest();
  xhr.open("get", file, false);
  var result;
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status >= 200 && xhr.status < 300 || xhr.status == 304) {
        var text = xhr.responseText;
        result = text;
      }
    }
  };
  xhr.send(null);
  return result;
}

function load_async_json(file, onsuccess) {
  load_async(file, function (text) {
    try{
      var x = eval('(' + text + ')');
      onsuccess(x);
    }
    catch (err) {
      alert("Error eval json:" + err + "\n" + text);
    }
  });
}
function load_async_script(file) {
  load_async(file, function (text) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.text = text;
    document.body.appendChild(script);
  });
}
function load_script(url,callback) {

  var script = document.createElement("script")
  script.type = "text/javascript";
  if (script.readyState) { //IE
    script.onreadystatechange = function () {
      if (script.readyState == "loaded" || script.readyState == "complete") {
        script.onreadystatechange = null;
        callback();
      }
    };
  } else { //Others
    script.onload = function () {
      callback();
    };
  }
  script.src = url;
  document.getElementsByTagName("head")[0].appendChild(script);
}
function load_style(url, callback) {

  var style = document.createElement("link")
  style.type = "text/css";
  style.rel = 'stylesheet';

  if (style.readyState) { //IE
    style.onreadystatechange = function () {
      if (style.readyState == "loaded" || style.readyState == "complete") {
        style.onreadystatechange = null;
        callback();
      }
    };
  } else { //Others
    style.onload = function () {
      callback();
    };
  }
  style.href = url;
  document.getElementsByTagName("head")[0].appendChild(style);
}
//--------------------------------------------------------------------------


//XMLHttpRequest.prototype.setRequestHeader = function (header, value) {
//  alert(header + ": " + value);
//  this.xhr.setRequestHeader(header, value);

//};

//var open = XMLHttpRequest.prototype.open;

//XMLHttpRequest.prototype.open = function (method, uri, async, user, pass) {

// // this.setRequestHeader('range', 'bytes 0-');
//  this.addEventListener("readystatechange", function (event) {
//    if (this.readyState == 4) {
//      var self = this;
//      var response = {
//        method: method,
//        uri: uri,
//        responseText: self.responseText
//      };
//      console.log(response);
//    } else if (this.readyState === 2) {
//      var self = this;
//    //  self.setRequestHeader("accept-range", "bytes");
//      this.onloadstart = function () {
//        self.setRequestHeader("accept-range", "bytes");
//      };
//    } else {
//      console.log(this.readyState);
//    }
//  }, false);
//  var self = this;
//  this.onloadstart = function () {
//    self.setRequestHeader("accept-range", "bytes");
//  };

//  return open.apply(this, arguments);
////  open.call(this, method, uri, async, user, pass);
//};
  //XMLHttpRequest.prototype.open = function (method, url, async, user, password)
  //{
  //  alert(url);
  //  if (this.__proto__ && this.__proto__.open) {
  //    return this.__proto__.open(method, url, async, user, password); //send it on
  //  }
  //  if (this.open) {
  ////    return this.open(method, url, async, user, password); 
  //  }
//}
//var send = XMLHttpRequest.prototype.send;

//XMLHttpRequest.prototype.send = function (data) {
//  this.setRequestHeader('range','bytes 0-');

//  console.log('send');

//  // send.call(this, data);

// return send.apply(this, arguments);
//}