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
//using System.Net.WebSockets;


namespace Maxbuk.Server.Core
{
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
		private string _rootDirectory;
		private string _host;
		private HttpListener _listener;
		private int _port;

		public int Port
		{
			get { return _port; }
			private set { }
		}
    public string PHP_BIN;
    public string PHP_SOURSE;

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

    /// <summary>
    /// Construct server with suitable port.
    /// </summary>
    /// <param name="path">Directory path to serve.</param>
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


			_listener = new HttpListener();
			//_listener.Prefixes.Add("http://*:" + _port.ToString() + "/");
			//_listener.Prefixes.Add("http://localhost:" + _port.ToString() + "/");
			//_listener.Prefixes.Add("http://192.168.1.5:" + _port.ToString() + "/");
		//	_listener.Prefixes.Add("http://+:" + _port.ToString() + "/");
			_listener.Prefixes.Add( string.Format("http://{0}:{1}/",_host,_port));
    //  _listener.AuthenticationSchemes = AuthenticationSchemes.Basic;
			_listener.Start();
			_listener.IgnoreWriteExceptions = true;
      Thread thread = new Thread(_server_thread_procedure);
      thread.IsBackground = true;
      thread.Name = "SERVER_THREAD";
      thread.Start();

      /*
      int thread_id = 0;
			Task.Factory.StartNew(() =>
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
            Thread thread = new Thread(_client_thread_procedure);
            thread.IsBackground = true;
            thread.Name = "T" + (thread_id++).ToString();
            thread.Start(context);
          }
          CobaServer.Logger.Log("Server thread stopped.");
          _listener.Stop();
        },TaskCreationOptions.LongRunning);
        */
    }
    public override string ToString(){
			return string.Format ("host {0} port: {1}\nFolder:{2}", _host, _port, _rootDirectory);
		}
		private CobaClient _createClient(){
			return new CobaClient (_rootDirectory , MaxbukServerAdmin.DriversFileName);
		}

    private void _server_thread_procedure()
    {
      int thread_id = 0;
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
        Thread thread = new Thread(_client_thread_procedure);
        thread.IsBackground = true;
        thread.Name = "T" + (thread_id++).ToString();
        thread.Start(context);
      }
      CobaServer.Logger.Log("Server thread stopped.");
      _listener.Stop();

    }
    private void _client_thread_procedure(object data)
    {
      Process((HttpListenerContext)data);
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
      CobaClient client = _createClient();

      if (context.Request.HttpMethod == "POST") {

        if (filename.Equals("/note.save"))
        {
          client.SaveNote(context);
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
            
      }
      //	Console.WriteLine ("client : " + context.Request.RemoteEndPoint.ToString ());
      if (filename.Equals ("/get.folder")) {
				client.Execute (context, filename);
				return;
			}

			if (filename.Equals ("/mkdir")) {
				client.CreateFolder (context);
				return;
			}
      if (filename.Equals("/file.create"))
      {
        client.CreateFileInNoteFolder(context);
        return;
      }

      if (filename.Equals("/textview"))
      {
        client.SendTextViewPage(context);
        return;
      }
      if (filename.Equals("/notes"))
      {
        client.SendNotes(context);
        return;
      }
      if (filename.Equals("/notes.list"))
      {
        client.SendNotesList(context);
        return;
      }

      if (filename.Equals ("/mouse")) {
				client.ExecuteMouse (context);
				return;
			}
			if (filename.Length >= 2 && filename[0] == '/' && filename[1] == '~') {
				client.Send(context, filename);
				return;
			}
			string url = context.Request.Url.ToString();
	//		Console.WriteLine(filename + " url:" + url);
			filename = filename.Substring(1);

			if (filename.IndexOf (".php") != -1) {
				PHP php = new PHP ();
				php.Execute (context, filename);
				return;
			}

			if (string.IsNullOrEmpty(filename))
			{
				foreach (string indexFile in _indexFiles)
				{
					if (File.Exists(Path.Combine(_rootDirectory, indexFile)))
					{
						filename = indexFile;
						break;
					}
				}
			}

			filename = Path.Combine(_rootDirectory, filename);
      string ext = Path.GetExtension(filename).ToLower();
      CobaServer.SendFile(context, filename);

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
			_rootDirectory = path;
			_port = port;
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

