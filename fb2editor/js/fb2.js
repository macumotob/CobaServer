var fb2 = (function () {

  function item(name,parent){
    this.name = name;
    this.items = [];
    this.data = "";

    if (parent) {
      parent.add(this);
    }
    this.add_p = function (data) {
      var i = new item("p", this);
      i.data = data;
      //this.items.push(i);
      return i;
    };

    this.add = function (name) {
      if (typeof (name) === 'object') {
        this.items.push(name);
        return name;
      }
      else {
        var i = new item(name);
        this.items.push(i);
        return i;
      }
    }
    this.header = function () {
      var s = "<" + this.name + ">";
      return s;
    };
    this.footer = function () {
      var s = "</" + this.name + ">";
      return s;
    };
    this.body = function () {
      var s = "";
      this.items.forEach(function (i) {
        s += i.text();
      });
      return s;
    };
    this.text = function () {
      var s = this.header();
      s += this.body();
      s += this.data;
      s += this.footer();
      return s;
    }
  }

  function description() {
    this.__proto__.__proto__.constructor.apply(this, ["description", null]);
    this.title_info = new title_info(this);
    

  }
  function title_info(parent) {
    //this.name = "title-info";
    this.__proto__.__proto__.constructor.apply(this, ["title-info", parent]);
    this.book_title = new item("book-title", this); //this.add("book-title");
    this.genres = [];
    this.add_genre = function (genre_name) {
      var g = new item("genre");
      g.data = genre_name;
      this.genres.push(g);
      return g;
    };
    this.text = function () {
      var s = this.header();
      this.genres.forEach(function (g) { s += g.text(); });
      s += this.body();
      s += this.footer();
      return s;
    };

  }
  function body(parent) {
    this.__proto__.__proto__.constructor.apply(this, ["body", null]);
    this.title = new item("title", this);
    this.epigraphs = [];
    this.add_epigraph = function(){
    };
  }

  body.prototype = new item();
  body.prototype.constructor = item;

  title_info.prototype = new item();
  title_info.prototype.constructor = item;

  description.prototype = new item();      
  description.prototype.constructor = item;

  function book() {
    this.xml = '<?xml version="1.0" encoding="UTF-8"?>';
    this.fiction = '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">';
    this.description = new description("description");
    this.body = new body(this);

    this.text = function () {
      var s = this.xml + this.fiction;
      s += this.description.text();
      s += this.body.text();
      s += "</FictionBook>";
      return s;
    };
  }
  function create() {
    var abook = new book();
    return abook;
  }
  return { "create": create };
})();


/*
<html>
<body>

<table>
	<tr><td>Text to Save:</td></tr>
	<tr>
		<td colspan="3">
			<textarea id="inputTextToSave" style="width:512px;height:256px"></textarea>
		</td>
	</tr>
	<tr>
		<td>Filename to Save As:</td>
		<td><input id="inputFileNameToSaveAs"></input></td>
		<td><button onclick="saveTextAsFile()">Save Text to File</button></td>
	</tr>
	<tr>
		<td>Select a File to Load:</td>
		<td><input type="file" id="fileToLoad"></td>
		<td><button onclick="loadFileAsText()">Load Selected File</button><td>
	</tr>
</table>

<script type='text/javascript'>
*/
function saveTextAsFile()
{
  var textToWrite = document.getElementById("inputTextToSave").value;
  var textFileAsBlob = new Blob([textToWrite], {type:'text/plain'});
  var fileNameToSaveAs = document.getElementById("inputFileNameToSaveAs").value;

  var downloadLink = document.createElement("a");
  downloadLink.download = fileNameToSaveAs;
  downloadLink.innerHTML = "Download File";
  if (window.webkitURL != null)
  {
    // Chrome allows the link to be clicked
    // without actually adding it to the DOM.
    downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
  }
  else
  {
    // Firefox requires the link to be added to the DOM
    // before it can be clicked.
    downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
    downloadLink.onclick = destroyClickedElement;
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
  }

  downloadLink.click();
}

function destroyClickedElement(event)
{
  document.body.removeChild(event.target);
}

function loadFileAsText()
{
  var fileToLoad = document.getElementById("fileToLoad").files[0];

  var fileReader = new FileReader();
  fileReader.onload = function(fileLoadedEvent) 
  {
    var textFromFileLoaded = fileLoadedEvent.target.result;
    document.getElementById("inputTextToSave").value = textFromFileLoaded;
  };
  fileReader.readAsText(fileToLoad, "UTF-8");
}
/*
</script>

</body>
</html>
*/
