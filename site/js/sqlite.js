var sqlite = (function () {

  //var server = "https://192.168.0.46:8443";
  var db = "server.sqlite";
  function open(dbname) {
    db = dbname;
  }

  function get(sql, onsuccess) {
    load_async_json("sqlite?db=" + db + "&select=" + sql, function (data) {
      if(onsuccess) onsuccess(data);
      console.log(data);
    });
  }
  function schema(tbname, onsuccess) {
    load_async_json("sqlite?db=" + db + "&schema=" + tbname, function (data) {
      if (onsuccess) onsuccess(data);
      console.log(data);
    });
  }
  return {
    "open": open,
    "select": get,
    "schema":schema
  };
})();