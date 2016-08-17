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
  
  function add(en, rus) {

    if (en.length == 0) return;

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
  var prev_word_index = -1;

  function check(en, ru) {
    var i;
    if ((i = find(en)) === -1) return null;
    
    var word = words[i];
    word.count++;
    var result = word.ru.indexOf(ru) !== -1;
    if (result) {
      word.succ++;
    }
    save();
    return result;
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

  function speak(text) {
    
      var msg = new SpeechSynthesisUtterance();
      // var voices = window.speechSynthesis.getVoices();
      // msg.voice = voices[1]; // Note: some voices don't support altering params
      msg.voiceURI = 'native';
      msg.volume = 1; // 0 to 1
      msg.rate = 1; // 0.1 to 10
      msg.pitch = 2; //0 to 2
      msg.text = text;
      msg.lang = 'en-US';

      msg.onend = function (e) {
       // console.log('Finished in ' + event.elapsedTime + ' seconds.');
      };
      speechSynthesis.speak(msg);
  }
  function random() {
    if (words.length == 0) return;
    var i = -1;
    var try_count = 5;
    while(try_count > 0 ) {
      try_count--;
      i = Math.floor((Math.random() * words.length));
      if (prev_word_index !== i) break;
    }
    prev_word_index = i;
    return words[i];
  }

  load();

  return {
    "add": add,
    "check": check,
    "remove": remove,
    "each": each,
    "speak": speak,
    "random" :random
  };

})();
/*
ewords.add("acquire", "приобретать,плучать,овладевать");
ewords.check("acquire", "приобретать");

ewords.each(function (i, w) {
  console.log(w);
});

//ewords.speak("acquire");

ewords.remove("acquire");
*/
$(document).ready(function () {

  $("#btn-delete").click(function () {
    console.log("delete");
  });

  $("#btn-save").click(function () {
    ewords.add($("#en-word").val(), $("#ru-word").val());
  });

  $("#btn-speak").click(function () {
    ewords.speak($("#en-word").val());
  });

  $("#btn-random").click(function () {
    var w = ewords.random();
    $("#en-word").val(w.en);
    $("#ru-word").val("");
    $("#ru-word").focus();
  });

  $("#btn-list").click(function () {
    $("#dv-words").html("");
    

    var html = "<table class='table table-hover'>";
    
     ewords.each(function (i, w) {
       html += "<tr onclick=\"$('#en-word').val('" + w.en + "');$('#ru-word').val('" + w.ru + "');\">";
       html += "<td>" + w.en + "</td>";
       html += "<td>" + w.ru + "</td>";
       html += "<td>" + w.count + "</td>";
       html += "<td>" + w.succ + "</td>";
       html += "</tr>";
     });
     html += "</table>";
     $("#dv-words").html(html);
  });

  $("#ru-word").keydown(function (event) {
    if (event.which === 13) {
     var result =  ewords.check( $("#en-word").val(), $("#ru-word").val());
     if (result) {
       $("#dv-error").css('visibility', 'hidden')
     }
     else {
       $("#dv-error").css('visibility', 'visible')
     }
    }
  });
});
