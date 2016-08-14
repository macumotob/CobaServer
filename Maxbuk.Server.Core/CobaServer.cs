using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Net.Sockets;
using System.Net;
using System.IO;
using System.Threading;
using System.Diagnostics;
using System.Threading.Tasks;
using System.Web;
//using System.Net.WebSockets;


namespace Maxbuk.Server.Core
{
  //using SQLite;
	public class CobaServer
	{
		private readonly string[] _indexFiles = { 
			"index.html", 
			"index.htm", 
			"default.html", 
			"default.htm" 
		};

		internal static IDictionary<string, string> _mimeTypeMappings = new Dictionary<string, string>(StringComparer.InvariantCultureIgnoreCase) {
			#region extension to MIME type list
			{".asf", "video/x-ms-asf"},
			{".asx", "video/x-ms-asf"},
			{".avi", "video/x-msvideo"},
			{".bin", "application/octet-stream"},
			{".cco", "application/x-cocoa"},
			{".crt", "application/x-x509-ca-cert"},
			{".css", "text/css"},
			{".deb", "application/octet-stream"},
			{".der", "application/x-x509-ca-cert"},
			{".dll", "application/octet-stream"},
			{".dmg", "application/octet-stream"},
			{".ear", "application/java-archive"},
			{".eot", "application/octet-stream"},
			{".exe", "application/octet-stream"},
			{".flv", "video/x-flv"},
			{".gif", "image/gif"},
			{".hqx", "application/mac-binhex40"},
			{".htc", "text/x-component"},
			{".htm", "text/html"},
			{".html", "text/html"},
			{".ico", "image/x-icon"},
			{".img", "application/octet-stream"},
			{".iso", "application/octet-stream"},
			{".jar", "application/java-archive"},
			{".jardiff", "application/x-java-archive-diff"},
			{".jng", "image/x-jng"},
			{".jnlp", "application/x-java-jnlp-file"},
			{".jpeg", "image/jpeg"},
			{".jpg", "image/jpeg"},
			{".js", "application/x-javascript"},
			{".mml", "text/mathml"},
			{".mng", "video/x-mng"},
			{".mov", "video/quicktime"},
			{".mp3", "audio/mpeg"},
			{".mpeg", "video/mpeg"},
			{".mpg", "video/mpeg"},
			{".msi", "application/octet-stream"},
			{".msm", "application/octet-stream"},
			{".msp", "application/octet-stream"},
			{".pdb", "application/x-pilot"},
			{".pdf", "application/pdf"},
			{".pem", "application/x-x509-ca-cert"},
			{".pl", "application/x-perl"},
			{".pm", "application/x-perl"},
			{".png", "image/png"},
			{".prc", "application/x-pilot"},
			{".ra", "audio/x-realaudio"},
			{".rar", "application/x-rar-compressed"},
			{".rpm", "application/x-redhat-package-manager"},
			{".rss", "text/xml"},
			{".run", "application/x-makeself"},
			{".sea", "application/x-sea"},
			{".shtml", "text/html"},
			{".sit", "application/x-stuffit"},
			{".swf", "application/x-shockwave-flash"},
			{".tcl", "application/x-tcl"},
			{".tk", "application/x-tcl"},
			{".txt", "text/plain"},
			{".war", "application/java-archive"},
			{".wbmp", "image/vnd.wap.wbmp"},
			{".wmv", "video/x-ms-wmv"},
			{".xml", "text/xml"},
			{".xpi", "application/x-xpinstall"},
			{".zip", "application/zip"},
			#endregion
		};
    private volatile static coba.Logger _logger;
    public static coba.Logger  Logger
    {
      get
      {
        if(_logger == null)
        {
          _logger = new coba.Logger("mb", CobaServer.LogFolder);
        }
        return _logger;
      }
    }
		private Thread _serverThread;
		public string RootDirectory;
		private string _host;
		private HttpListener _listener;
		private int _port;
    public int HttpsPort = 0;
    public int Port
		{
			get { return _port; }
			set {
        _port = value;
      }
		}
    public string PHP_BIN;
    //public string PHP_SOURSE;

    public static string ApplicationDataFolder
    {
      get
      {
        string folder = Environment.GetFolderPath( Environment.SpecialFolder.ApplicationData) + "/CobaServer/";
        return folder;
      }
    }
    public static string NotesFolder
    {
      get
      {
        return CobaServer.ApplicationDataFolder + "Notes/";
      }
    }
    public static string LogFolder
    {
      get
      {
        return CobaServer.ApplicationDataFolder + "Log/";
      }
    }
    /// <summary>
    /// Construct server with given port.
    /// </summary>
    /// <param name="path">Directory path to serve.</param>
    /// <param name="port">Port of the server.</param>
    public CobaServer(string path, string host, int port)
		{
			_host = host;
			this.Initialize(path, port);

      if (!Directory.Exists(CobaServer.ApplicationDataFolder))
      {
        Directory.CreateDirectory(CobaServer.ApplicationDataFolder);
      }
      if (!Directory.Exists(CobaServer.NotesFolder))
      {
        Directory.CreateDirectory(CobaServer.NotesFolder);
      }
      if (!Directory.Exists(CobaServer.LogFolder))
      {
        Directory.CreateDirectory(CobaServer.LogFolder);
      }
    }
    public CobaServer()
    {
      
    }


    public CobaServer(string path)
		{
			//get an empty port
			TcpListener l = new TcpListener(IPAddress.Loopback, 0);
			l.Start();
			int port = ((IPEndPoint)l.LocalEndpoint).Port;
			l.Stop();
			this.Initialize(path, port);
		}

    /// <summary>
    /// Stop server and dispose all functions.
    /// </summary>
    /// 
    private object _locker = new object();
    private volatile bool _is_working = false;
    public  bool IsWorking
    {
      get
      {
        lock (_locker)
        {
          return _is_working;
        }
      }
      set
      {
        lock (_locker)
        {
          _is_working = value;
        }
      }
    }

    public string Host
    {
      get
      {
        return _host;
      }

      set
      {
        _host = value;
      }
    }


    /*
public void Stop()
{
 is_working = false;
 Thread.Sleep(200);
 _serverThread.Abort();
 _listener.Stop();
}
*/
    private void Listen()
		{
      try
      {
        _listener = new HttpListener();
        _listener.Prefixes.Add(string.Format("http://{0}:{1}/", _host, _port));
        if (HttpsPort > 0)
        {
          _listener.Prefixes.Add(string.Format("https:/:{0}/", HttpsPort));
          ServicePointManager.ServerCertificateValidationCallback = delegate { return true; };
        }
        //  _listener.AuthenticationSchemes = AuthenticationSchemes.Basic;

        _listener.Start();
        //_listener.IgnoreWriteExceptions = true;

        Thread thread = new Thread(_server_thread_procedure);
        thread.IsBackground = true;
        thread.Name = "SERVER_THREAD";
        thread.Start();

        thread = new Thread(_server_process_client_query);
        thread.IsBackground = true;
        thread.Name = "SERVER_THREAD 2";
        thread.Start();

      }
      catch (Exception ex)
      {
        IsWorking = false;
        _logger.Log(ex, "Listen function host {0} port {1}", _host, _port);
      }
    }
    public override string ToString()
    {
      return string.Format("host {0} port: {1}\nFolder:{2}", _host, _port, RootDirectory);
    }
    int _client_thread_id = 0;

    private void _server_thread_procedure()
    {

      IsWorking = true;
      CobaServer.Logger.Log("Server thread running.");
      while (IsWorking)
      {
        HttpListenerContext context = _listener.GetContext();
        if (!IsWorking) break;
        string url = context.Request.Url.AbsolutePath;
        if (url.Equals("/-stop_coba-server-"))
        {
          IsWorking = false;
          CobaServer.SendText(context, "server stopped");
          break;
        }
        if(_queries.Count == 0)
        {
          _run_client_thread(context);
        }
        else
        {
          _queries.Add(context);
        }
        //_run_client_thread(context);
      }
      CobaServer.Logger.Log("Server thread stopped.");
      _listener.Stop();
    }

    List<HttpListenerContext> _queries = new List<HttpListenerContext>();
    private void _server_process_client_query()
    {
      while (IsWorking)
      {
        while (_queries.Count > 0)
        {
          HttpListenerContext data = _queries[0];
          _queries.RemoveAt(0);
          _run_client_thread(data);
          Debug.Print("Queries: {0}", _queries.Count);
          Thread.Sleep(10);
        }
        Thread.Sleep(100);
      }
    }

    private void _run_client_thread(object context)
    {
      Thread thread = new Thread(_client_thread_procedure);
      thread.IsBackground = true;
      thread.Name = "T" + (_client_thread_id++).ToString();
      thread.Start(context);
    }
    private void _client_thread_procedure(object data)
    {
      Thread.Sleep(100);
      Process((HttpListenerContext)data);
    }
    private List<FileFolderInfo> _disks = new List<FileFolderInfo>();
    public void AddFolderInfo(string folderName,string path)
    {
      FileFolderInfo fi = new FileFolderInfo() { name = folderName, path = path };
      _disks.Add(fi);
    }
    public void ParseFolderInfo(string s)
    {
      s = s.Trim();
      if (s.Length == 0) return;
      if (s[0] == '#') return;

      string[] data = s.Split('=');
      string name = data[0].Trim(); ;
      string path = data[1].Trim(); 
      FileFolderInfo fi = new FileFolderInfo() { name = name, path = path };
      _disks.Add(fi);
    }
    private int _findPosition(string name, string folder)
    {
      if (folder == "~" + name)
        return 0;

      int i = folder.IndexOf("~" + name + "\\");
      if (i != -1)
        return i;
      return folder.IndexOf("~" + name + "/");

    }

    private string _redirect(string folder)
    {
      for (int i = 0; i < _disks.Count; i++)
      {
        FileFolderInfo item = _disks[i];
        int n = _findPosition(item.name, folder);
        if (n != -1)
        {
          folder = item.path + folder.Substring(n + ("~" + item.name).Length);
          return folder;
        }
      }
      return folder;
    }

    private void SendFolderContent(HttpListenerContext context)
    {
      //	string mime;
      try
      {
        
        string x = context.Request.RawUrl.Substring("/get.folder?".Length);
        string u = System.Web.HttpUtility.UrlDecode(x);
        string folder = System.Web.HttpUtility.ParseQueryString(u).Get("folder");

        string result = "{'folders':[";
        if (folder == "root")
        {
          for (int i = 0; i < _disks.Count; i++)
          {
            FileFolderInfo item = _disks[i];
            result += (i == 0 ? "" : ",") + "{\"name\": \"~" + item.name + "\",d:1}";
          }
          result += "],'files':[]}";
        }
        else
        {
          string dir = _redirect(folder + "/");

          string[] dirs = System.IO.Directory.GetDirectories(dir);
          for (int i = 0; i < dirs.Length; i++)
          {
            string item = dirs[i].Replace('\\', '/');
            item = item.Substring(dir.Length);
            result += (i == 0 ? "" : ",") + "{\"name\":\"" + item + "\",d:1,size:0}";
          }
          result += "], 'files' :[";
          string[] files = System.IO.Directory.GetFiles(dir);

          for (int i = 0; i < files.Length; i++)
          {
            string item = files[i].Replace('\\', '/');
            long size = new System.IO.FileInfo(item).Length;
            item = item.Substring(dir.Length);
            result += (i == 0 ? "" : ",") + "{\"name\":\"" + item + "\",d:0,size:" + size.ToString() + "}";
          }
          result += "]}";
        }
        CobaServer.SendJson(context, result);
      }
      catch (Exception ex)
      {
        Console.WriteLine("exception : " + ex.ToString());
        context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
      }

    }
    public static void SendJson(HttpListenerContext context, string text)
    {
      try
      {
        byte[] data = Encoding.UTF8.GetBytes(text);
        context.Response.StatusCode = (int)HttpStatusCode.OK;
        context.Response.ContentType = "application/json";
        context.Response.ContentLength64 = data.Length;
        context.Response.AddHeader("Date", DateTime.Now.ToString("r"));
        //context.Response.KeepAlive = true;
        context.Response.OutputStream.Write(data, 0, data.Length);
      }
      catch (Exception ex)
      {
        CobaServer.Logger.Log(ex, "exception send json{0}", text);
      }
      context.Response.OutputStream.Flush();
      context.Response.OutputStream.Close();
    }
    private static Dictionary<string, string> _parse_query_string(string s)
    {
      Dictionary<string, string> dic = new Dictionary<string, string>();
      string[] qparams = s.Split('&');
      foreach(var qparam in qparams)
      {
        string[] data = qparam.Split('=');
        dic[data[0].ToLower()] = data[1];
      }
      return dic;
    }
    public static Dictionary<string, string> ParseQueryString(HttpListenerContext context, string command)
    {
      //const string marker = "/upload.php?";
      string cmd = context.Request.Url.ToString();
      cmd = cmd.Substring(cmd.IndexOf(command) + command.Length);
      return _parse_query_string(cmd);
    }
    private void Process(HttpListenerContext context)
		{
      //HttpListenerBasicIdentity identity = (HttpListenerBasicIdentity)context.User.Identity;
     // string user_name = identity.Name;
      //string user_password = identity.Password;

      string filename = context.Request.Url.AbsolutePath;
      //      if (context.Request.IsWebSocketRequest) {
      //        SocketClient.CreateSocketThread (context);
      ////				SocketClient client = new SocketClient ();
      ////				client.Execute (context);
      //        return;
      //      }
      CobaClient client = new CobaClient(RootDirectory); // _createClient();
      client._disks = _disks;
      if (context.Request.HttpMethod == "POST") {

        if (filename.Equals("/note.save"))
        {
          client.SaveNote(context);
          return;
        }
        if (filename.Equals("/upload.php"))
        {
       //   const string marker = "/upload.php?";
       //   string cmd = context.Request.Url.ToString();
        //  cmd = cmd.Substring(cmd.IndexOf(marker) + marker.Length);
         // Dictionary<string, string> p = _parse_query_string(cmd);

          Dictionary<string, string> p = CobaServer.ParseQueryString(context, filename + "?");
          string fileName = CobaServer.NotesFolder + coba.Logger.GetDateTimeFileName("vr") + ".wav";
          CobaClient.SaveWavFile(context.Request.ContentEncoding, CobaClient.GetBoundary(context.Request.ContentType), context.Request.InputStream, fileName);


          return;
        }
        client.ExecutePost (context);
				return;
			}

      switch (filename)
      {
        case "/file.delete":
          client.DeleteFile(context);
          return;
        case "/file.rename":
          client.RenameFile(context);
          return;
        case "/file.info":
          client.SendFileInfo(context);
          return;
        case "/folder.zip":
          client.ZipFolder(context);
          return;
        case "/textview":
          client.SendTextViewPage(context);
          return;
        case "/sqlite":
          {
            Dictionary<string, string> p = CobaServer.ParseQueryString(context, filename + "?");
            SQLiteManager.Instance.RootDirectory = this.RootDirectory;
            string result = SQLiteManager.Instance.Execute(p);
            CobaServer.SendJson(context, result == null? "{result:true,msg:'SQL'}": result);
          }
          break;

        case "/notes":
          client.SendNotes(context);
          return;
        case "/notes.wav":
          {
            Dictionary<string, string> p = CobaServer.ParseQueryString(context, filename + "?");
            string file= CobaServer.NotesFolder + p["file"];
            CobaServer.SendFile(context, file);
            return;
          }
        case "/notes.list":
          client.SendNotesList(context);
          return;
        case "/notes.remove":
          {
            Dictionary<string, string> p = CobaServer.ParseQueryString(context, filename + "?");
            string file = CobaServer.NotesFolder + p["file"];

            if (System.IO.File.Exists(file))
            {
              System.IO.File.Delete(file);
              CobaServer.SendJson(context, "{result:true,msg:'file removed :" + file + "'}");
            }
            else
            {
              CobaServer.SendJson(context, "{result:false,msg:'file not found :" + file + "'}");
            }
          }
          return;
        case "/get.folder":
          {
            //client.Execute (context, filename);
            this.SendFolderContent(context);
            return;
          }
        case "/mkdir":
            client.CreateFolder(context);
            return;
        case "/file.create":
            client.CreateFileInNoteFolder(context);
            return;
        case "/mouse":
            client.ExecuteMouse(context);
            return;
      }
      //	Console.WriteLine ("client : " + context.Request.RemoteEndPoint.ToString ());



      if (filename.Length >= 2 && filename[0] == '/' && filename[1] == '~') {
				client.Send(context, filename);
				return;
			}
			string url = context.Request.Url.ToString();
	//		Console.WriteLine(filename + " url:" + url);
			filename = filename.Substring(1);

			if (filename.IndexOf (".php") != -1) {
				PHP php = new PHP ();
        php.php_bin_file = PHP_BIN;
        php.php_source_folder = RootDirectory;
				php.Execute (context, filename);
				return;
			}

			if (string.IsNullOrEmpty(filename))
			{
				foreach (string indexFile in _indexFiles)
				{
          string path = Path.Combine(RootDirectory, indexFile);
          if (File.Exists(path))
					{
						filename = indexFile;
						break;
					}
				}
			}
      string absolute_file_name = HttpUtility.UrlDecode(url).Replace("http://" + _host + ":" + _port + "/", "");
      if (absolute_file_name.Length > 2 && absolute_file_name[1] == ':' && absolute_file_name[2]=='/' &&  File.Exists(absolute_file_name))
      {
        string text = File.ReadAllText(absolute_file_name, Encoding.UTF8);
        CobaServer.SendText(context, text, "text/plain");
      }
      else
      {
        if (filename == "index") filename = "index.html";
        filename = Path.Combine(RootDirectory, filename);
        string ext = Path.GetExtension(filename).ToLower();
        CobaServer.SendFile(context, filename);
      }
			context.Response.OutputStream.Close();
		}
    public static void SendText(HttpListenerContext context, string text, string content_type = "text/plain")
    {
      byte[] data = Encoding.UTF8.GetBytes(text);

      context.Response.StatusCode = (int)HttpStatusCode.OK;
      context.Response.ContentType = content_type;//"application/json";
      context.Response.ContentLength64 = data.Length;
      context.Response.AddHeader("Date", DateTime.Now.ToString("r"));
      //context.Response.KeepAlive = true;
      context.Response.OutputStream.Write(data, 0, data.Length);
      context.Response.OutputStream.Flush();
      context.Response.OutputStream.Close();
    }


    public static void SendFile(HttpListenerContext context, string filename)
    {
      if (File.Exists(filename))
      {
        //Console.WriteLine ("File :" + filename);
        try
        {
          Stream input = new FileStream(filename, FileMode.Open, FileAccess.Read, FileShare.Read);

          string mime;
          context.Response.StatusCode = (int)HttpStatusCode.OK;
          context.Response.ContentType = _mimeTypeMappings.TryGetValue(Path.GetExtension(filename), out mime) ? mime : "application/octet-stream";
          context.Response.ContentLength64 = input.Length;
          context.Response.AddHeader("Date", DateTime.Now.ToString("r"));
          context.Response.AddHeader("Last-Modified", System.IO.File.GetLastWriteTime(filename).ToString("r"));
          context.Response.AddHeader("Access-Control-Allow-Origin","*");
          byte[] buffer = new byte[1024 * 64];
          int nbytes;
          while ((nbytes = input.Read(buffer, 0, buffer.Length)) > 0)
            context.Response.OutputStream.Write(buffer, 0, nbytes);
          input.Close();
          context.Response.OutputStream.Flush();

        }
        catch (Exception ex)
        {
          //          Console.WriteLine("exception: " + ex.ToString());
          context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
        }
      }
    }
    private void Initialize(string path, int port)
		{
      RootDirectory = path;
			_port = port;
			_serverThread = new Thread(this.Listen);
			_serverThread.Start();
		}
    public void Start()
    {
      _serverThread = new Thread(this.Listen);
      _serverThread.Start();
    }
    public string Stop()
    {
      if (!IsWorking)
      {
        return "server not running";
      }
      WebClient client = null;
      string response = null;
      try
      {
        client = new WebClient();
        string query = string.Format("http://{0}:{1}/-stop_coba-server-", _host, _port);
        response = client.DownloadString(query);
      }
      catch (Exception ex)
      {
        response = ex.ToString();
      }
      finally
      {
        if (client != null)
        {
          client.Dispose();
        }
      }
      return response;
    }

    private static bool _IsValidIP(string ip)
    {
      string[] items = ip.Split('.');

      return items.Length == 4;
    }
    static public List<string> GetIPAddress()
    {
      List<string> list = new List<string>();

      string localIP = "";

      IPHostEntry host = Dns.GetHostEntry(Dns.GetHostName());

      foreach (IPAddress ip in host.AddressList)
      {
        localIP = ip.ToString();
        if (_IsValidIP(localIP))
        {
          list.Add(localIP);
        }
      }
      return list;
    }

  }//end of class

}

