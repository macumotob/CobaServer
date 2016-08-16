var ewords = (function () {

  var words = null;
  var STORE_NAME = "ww_english_words";
  function load() {
    if (typeof (Storage) !== "undefined") {
      // Code for localStorage/sessionStorage.
      words = localStorage.getItem(STORE_NAME);
      if (!words) {
        words = [];
        save();
      }
      else {
        words = JSON.parse(words);
      }
    } else {
      alert( "Sorry! No Web Storage support..");
    }
  }
  function save() {
    var json = JSON.stringify(words);
    localStorage.setItem(STORE_NAME, json);
  }
  
  function add(en,rus) {
    var word = { "en": en, "ru": rus, "count": 0, "succ": 0 };
    var i;
    if ((i = find(en)) !== -1) {
      words[i].ru = rus;
    }
    else {
      words.push(word);
    }
    save();
  }
  function find(en) {
    for (var i = 0; i < words.length; i++) {
      if (words[i].en === en) return i;
    }
    return -1;
  }
  function check(en, ru) {
    var i;
    if ((i=find(en)) === -1) return;
    var word = words[i];
    word.count++;
    if (word.ru.indexOf(ru) !== -1) {
      word.succ++;
    }
    save();
    return word.ru;
  }
  function remove(en) {
    var i = find(en);
    if (i !== -1) {
      words.splice(i, 1);
      save();
    }
  }
  function each(callback) {
    for (var i = 0; i < words.length; i++) {
      callback(i, words[i]);
    }
  }
  load();
  return {"add":add,"check":check,"remove":remove,"each":each};

})();

ewords.add("acquire", "приобретать,плучать,овладевать");
ewords.check("acquire", "приобретать");

ewords.each(function (i, w) {
  console.log(w);
});

ewords.remove("acquire");

alert("e w loaded");