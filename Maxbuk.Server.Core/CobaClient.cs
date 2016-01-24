using System;
using System.Net;
using System.IO;
using System.Text;
using System.Collections.Generic;
using System.Web;

namespace xsrv
{
	public class FileFolderInfo
	{
    //todo path check
		public string name { get; set; }
		public string path { get; set; }
		public int d { get; set; }
	}

	public class CobaClient
	{
		private List<FileFolderInfo> _disks;
		private string _workingFolder;
    private string _drivers_info_filename;
		public CobaClient (string workingFolder, string drivers_info_filename)
		{
      _drivers_info_filename = drivers_info_filename;
      _workingFolder = workingFolder;
		}
		private void _printRequestHeaders(HttpListenerContext context){
			Console.WriteLine ("***");
			foreach (var item in context.Request.Headers.AllKeys) {
				Console.WriteLine (item.ToString () + ":" + context.Request.Headers [item]);
			}

		}
		public void Execute(HttpListenerContext context,string command){
			try
			{
				switch(command){
				case "/get.folder":
					this.SendFolderContent(context);
					return;
				case "/mkdir":
					return;
				default:
					Console.WriteLine("unsupported : " + command);
					break;
				}
			}
			catch (Exception ex)
			{
				Console.WriteLine ("exception: " + ex.ToString ());
				context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
			}

		}
    public void ExecutePost(HttpListenerContext context){
			//_printRequestHeaders (context);

			try {
				string s = context.Request.Headers ["coba-file-info"];
				//s = System.Web.HttpUtility.UrlDecode (s);
				string name = System.Web.HttpUtility.ParseQueryString (s).Get ("name");
				string ssize = System.Web.HttpUtility.ParseQueryString (s).Get ("size");
				string sfilesize = System.Web.HttpUtility.ParseQueryString (s).Get ("filesize");
				string sstart = System.Web.HttpUtility.ParseQueryString (s).Get ("start");
				string send = System.Web.HttpUtility.ParseQueryString (s).Get ("end");
				string action = System.Web.HttpUtility.ParseQueryString (s).Get ("action");
                   
				long start = long.Parse (sstart);
				long end = long.Parse (send);
				long filesize = long.Parse (sfilesize);
				long size = long.Parse (ssize);

				_load_public_folders ();
				name = _redirect (name);

//				if(System.IO.File.Exists(name)){
//					this.SendJson (context, "{result:'ok',msg:'close',offset:" + filesize.ToString() + "}");
//					return;
//				}
				System.IO.Stream body = context.Request.InputStream;


				FileMode fm = (action == "open" ? FileMode.CreateNew : FileMode.Append);
        long total_readed = 0;
				using (FileStream fs = File.Open (name, fm)) {
					//fs.Seek(start,SeekOrigin.Begin);
					using (BinaryWriter writer = new BinaryWriter (fs)) {
						byte[] data = new byte[1024 * 64];
						while (size > 0) {
							int read = body.Read (data, 0, data.Length);
              total_readed += read;
              size -= read;
							if (read > 0) {
								writer.Write (data, 0, read);
							}
						}
						body.Close ();
						writer.Close ();
						writer.Dispose ();
					}
					fs.Close ();
					fs.Dispose ();
				}
				if (end >= filesize){
					action = "close";
				}
        float precent = (start + total_readed) * 100 / filesize;
        //	this.SendJson (context, "{result:'ok',msg:'" + action + "',offset:" + end.ToString() + "}");
        this.SendJson(context, "{result:'ok',msg:'" + action + "',offset:" + precent.ToString("F") + "}");
      } catch (Exception ex) {
				Console.WriteLine ("exception: " + ex.ToString ());
        this.SendJson(context, "{result:'error',msg:'" + ex.ToString().Replace("\r\n"," ").Replace('\'',' ') + "',offset: -1}");
       // context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
			}
		}

    public void SendTextViewPage(HttpListenerContext context)
    {
      try
      {
        _load_public_folders();

        const string marker = "/textview?";
        string url = context.Request.Url.ToString();

        url = url.Substring(url.IndexOf(marker) + marker.Length);
        //url = System.Web.HttpUtility.UrlDecode (url);
        string file = System.Web.HttpUtility.ParseQueryString(url).Get("file");
        string real_file = _redirect(file);

        string ext = System.IO.Path.GetExtension(real_file).ToLower();
        if (ext == ".txt")
        {
          _send_text_file(context, real_file,file);
        }
        else
        {
          CobaServer.SendFile(context, real_file);
        }
      }
      catch (Exception ex)
      {
        Console.WriteLine("exception: " + ex.ToString());
        context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
      }
    }
    public void SaveNote(HttpListenerContext context)
    {
      string url = context.Request.Url.ToString();
      var request = context.Request;
      string text;
      using (var reader = new StreamReader(request.InputStream,
                                           request.ContentEncoding))
      {
        text = HttpUtility.UrlDecode(reader.ReadToEnd());
      }
      text = text.Substring(text.IndexOf("txt=") + 4);
      string date = System.Web.HttpUtility.ParseQueryString(url).Get("date");
      
      string name = DateTime.Now.Day.ToString() + "-" + DateTime.Now.Month.ToString() + "-" + DateTime.Now.Year.ToString() + ".txt";

      string file = CobaServer.NotesFolder + name;
      File.WriteAllText(file, text, Encoding.UTF8);

      SendJson(context, "{'result': true}");
    }

    public void SendNotes(HttpListenerContext context)
    {
      try
      {
        const string marker = "/notes?";
        string url = context.Request.Url.ToString();

        url = url.Substring(url.IndexOf(marker) + marker.Length);
        //url = System.Web.HttpUtility.UrlDecode (url);
        string date = System.Web.HttpUtility.ParseQueryString(url).Get("date");
        string name = DateTime.Now.Day.ToString() + "-" + DateTime.Now.Month.ToString() + "-" + DateTime.Now.Year.ToString() + ".txt";

        string file = CobaServer.NotesFolder + name;
        if (!File.Exists(file))
        {
          File.WriteAllText(file, "New File", Encoding.UTF8);
        }
        string text = File.ReadAllText(file, Encoding.UTF8);
        if(date == "0")
        {

        }
        else
        {

        }
        _send_text(context, text,"text/plain");
      }
      catch (Exception ex)
      {
        Console.WriteLine("exception: " + ex.ToString());
        context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
      }
    }

    public void CreateFolder(HttpListenerContext context)
    {
      try
      {
        _load_public_folders();

        const string marker = "/mkdir?";
        string url = context.Request.Url.ToString();

        url = url.Substring(url.IndexOf(marker) + marker.Length);
        //url = System.Web.HttpUtility.UrlDecode (url);
        string folder = System.Web.HttpUtility.ParseQueryString(url).Get("folder");
        folder = _redirect(folder);

        string subfolder = System.Web.HttpUtility.ParseQueryString(url).Get("name");

        //Console.WriteLine ("Create folder : " + subfolder + " in " + folder);
        System.IO.Directory.CreateDirectory(folder + subfolder);
        this.SendJson(context, "{result:true,msg:'" + subfolder + "'}");

      }
      catch (Exception ex)
      {
        Console.WriteLine("exception: " + ex.ToString());
        context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
      }
    }
		private void prepareNSPlayerHeader(HttpListenerResponse response,
			long start,long end,long chunkSize,long fileSize)
		{
			if(end == -1){
				end = fileSize -1;
				chunkSize = end - start + 1;
			}

			string range ="bytes " + start.ToString () + "-" + end.ToString () 
				+ "/" + fileSize.ToString ();
			response.StatusCode = (int)HttpStatusCode.PartialContent;
			response.Headers ["TransferMode.DLNA.ORG"] = "Streaming";
			response.Headers ["Access-Control-Allow-Origin"] = "*";

			response.Headers ["File-Size"] =  fileSize.ToString();
			response.Headers ["Content-Range"] = range;

			response.Headers ["Accept-Ranges"] = "bytes";
			response.ContentLength64 = chunkSize;
			response.SendChunked = true;
			response.KeepAlive = true;
			response.Headers ["Content-Type"] = "video/mp4";
			//Console.WriteLine (range);
		}
		private void prepareIPhoneHeader(HttpListenerResponse response,
			long start,long end,long chunksize,long filesize)
		{
			if (end == -1) {
				end = filesize - 1;
				chunksize = end - start + 1;
			}
			response.StatusCode = (int)HttpStatusCode.PartialContent;

			response.Headers ["Content-Range"] = string.Format("bytes {0}-{1}/{2}",	start,end,filesize);
			response.Headers ["Accept-Ranges"] = "bytes";
			response.ContentLength64 = chunksize;
			response.SendChunked = true;
			response.KeepAlive = true;
			response.Headers ["Content-Type"] = "video/mp4";
			//Console.WriteLine (start.ToString () + "-" + end.ToString () + "/" + filesize.ToString ());

		}
    private void _send_text_file(HttpListenerContext context, string fileName,string relativeName)
    {
      if (File.Exists(fileName))
      {
        string maket = _workingFolder + "text_view.html";
        string s = System.IO.File.ReadAllText(maket,Encoding.UTF8);
        string text = File.ReadAllText(fileName, Encoding.UTF8);
        s= s.Replace("{{NAME}}", relativeName);
        s = s.Replace("{{TEXT}}", text);
        _send_text(context, s, "text/html");
      }
      else
      {
        _send_text(context, "File not found","text/plain");
      }
    }
		public void Send(HttpListenerContext context, string url)
    {
			_load_public_folders ();
			url = System.Web.HttpUtility.UrlDecode (url);
			string filename = _redirect (url);

			if (File.Exists (filename)) {
				long start = 0, chunksize = 0;
				long end = -1;
				var range = context.Request.Headers ["Range"];					

				try {
					using (FileStream fs = new FileStream (filename,FileMode.Open,
						FileAccess.Read, FileShare.Read)) {

						if (range != null && range.Length > 0) {
							string[] positions = range.Replace ("bytes=", "").Split ('-');
							start = long.Parse (positions [0]);
							end = positions [1].Length == 0 ? fs.Length - 1 : long.Parse (positions [1]);
							if(end == -1)
							{
								end = fs.Length -1;
							}
							chunksize = end - start + 1;
							string srange ="bytes " + start.ToString () + "-" + end.ToString () 
								+ "/" + fs.Length.ToString ();
							//Console.WriteLine ("_________" + srange + " ch:" + chunksize.ToString());
							fs.Seek (start, SeekOrigin.Begin);
						}

						string ext = System.IO.Path.GetExtension(filename);

						var response = context.Response;
						string userAgent = context.Request.Headers ["User-Agent"];

						if (userAgent.IndexOf ("NSPlayer/") != -1) {
							this.prepareNSPlayerHeader (response, start, end, chunksize, fs.Length);
						} 
						else if (userAgent.IndexOf ("iPhone;") != -1) {
							this.prepareIPhoneHeader (response, start, end, chunksize, fs.Length);
						} else {
							if (end == -1) {
								end = fs.Length-1;
								chunksize = end-start+1;

								response.StatusCode = (int)HttpStatusCode.OK;
								response.StatusDescription = "OK";
								response.ContentLength64 = fs.Length;
								response.SendChunked = true;
								response.KeepAlive = false;
								if(ext == ".txt"){
									response.ContentType = "text/plain";
									response.ContentEncoding= Encoding.UTF8;// Encoding.GetEncoding(1251);
								}
									
								response.ContentType = System.Net.Mime.MediaTypeNames.Application.Octet;

							} else {
								response.StatusCode = (int)HttpStatusCode.PartialContent;

								response.Headers ["Content-Range"] = "bytes " +
								start.ToString () + "-" + end.ToString () + "/" + fs.Length.ToString ();

								response.Headers ["Accept-Ranges"] = "bytes";
								response.ContentLength64 = chunksize;
								response.SendChunked = true;
								response.KeepAlive = true;
								response.Headers ["Content-Type"] = "video/mp4";
								//Console.WriteLine (start.ToString () + "-" + end.ToString () + "/" + fs.Length.ToString ());
							}
						}

						if (end == -1) {
							end = fs.Length-1;
							chunksize = end-start+1;
						}
						byte[] buffer = new byte[128 * 1024];
						int read;
						using (BinaryWriter bw = new BinaryWriter (response.OutputStream)) {
							try {
								//int i = 0;
								while (chunksize >= 0 && (read = fs.Read (buffer, 0, buffer.Length)) > 0) {
									if(chunksize < read){
										bw.Write (buffer, 0,(int) chunksize);
									}
									else{
										bw.Write (buffer, 0, read);
									}
									chunksize -= read;
									//i++;
								}
							} catch (Exception ex) {
								Console.WriteLine ("exception :\r\n" + ex.ToString ());
							}
							bw.Close ();
							response.OutputStream.Flush ();
							response.OutputStream.Close ();
						}
					
					}
				} catch (Exception ex) {
					//Console.WriteLine ("exception: " + ex.ToString ());
					context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
				}

			} else {
				context.Response.StatusCode = (int)HttpStatusCode.NotFound;
			}
			//response.OutputStream.Close();
			context.Response.OutputStream.Close ();
			//Console.WriteLine ("client closed : " + context.Request.UserHostAddress.ToString ());
		}
		private void SendJson(HttpListenerContext context,string text)
		{
			byte [] data = Encoding.UTF8.GetBytes(text);

			context.Response.StatusCode = (int)HttpStatusCode.OK;
			context.Response.ContentType = "application/json";
			context.Response.ContentLength64 = data.Length;
			context.Response.AddHeader("Date", DateTime.Now.ToString("r"));
			//context.Response.KeepAlive = true;
			context.Response.OutputStream.Write(data, 0, data.Length);
			context.Response.OutputStream.Flush();
			context.Response.OutputStream.Close();
		}

    private void _send_text(HttpListenerContext context, string text, string content_type)
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

    private void _load_public_folders()
		{
			//string filename = _workingFolder + @"data\folders.json";
			string s = File.ReadAllText( _drivers_info_filename ,System.Text.Encoding.UTF8);
			var ser = new System.Web.Script.Serialization.JavaScriptSerializer ();
			_disks = ser.Deserialize<List<FileFolderInfo>> (s);
		}
		private int _findPosition(string name,string folder){
			if (folder == "~" + name)
				return 0;
				
			int i = folder.IndexOf ("~" + name + "\\");
			if (i != -1)
				return i;
			return folder.IndexOf ("~" + name + "/");

		}
		private string _redirect(string folder)
		{
			for (int i = 0; i < _disks.Count; i++) {
				FileFolderInfo item = _disks [i];
			    int n = _findPosition (item.name, folder);
				if (n != -1 ) {
					folder = item.path + folder.Substring (n+ ("~"+item.name).Length);
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
        _load_public_folders();
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
        this.SendJson(context, result);
      }
      catch (Exception ex)
      {
        Console.WriteLine("exception : " + ex.ToString());
        context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
      }

    }

		//--------------------------------------------------------------------
		//  mouse
		//--------------------------------------------------------------------
		private bool _parseMouseCoordinats(string url, ref int x,ref int y){
			string sx = System.Web.HttpUtility.ParseQueryString (url).Get ("x");
			string sy = System.Web.HttpUtility.ParseQueryString (url).Get ("y");
			try {
				x = int.Parse (sx);
				y = int.Parse (sy);
				return true;
			} catch (Exception ex) {
				return false;
			}

		}
		public void ExecuteMouse(HttpListenerContext context){

			try {
				string url = context.Request.Url.ToString ();
				string marker = "mouse";
				url = url.Substring (url.IndexOf (marker) + marker.Length);
				string action = System.Web.HttpUtility.ParseQueryString (url).Get ("action");

				if(action == null){
					Console.WriteLine("mouse action is null!");
					SendJson(context,"{rsult:false,msg:'invalide mouse action'}");
					return;
				}
					
			//	Console.WriteLine("mouse action : " + action);
				int x =0 ,y = 0;
				switch(action){
				case "move":
					if(_parseMouseCoordinats(url,ref x,ref y))
					{
						Simulator.MouseCursorMove(x,y);
						string s = NativeMethods.CaptureScreen(true,150,80);
						this.SendJson (context, "{result:true, msg:'" + s + "'}");
					}
					else
					{
						this.SendJson (context, "{result:false}");
					}
					return;
				case "click":
					Simulator.LeftClick();
					this.SendJson (context, "{result:true}");
					return;
				case "rclick":
					Simulator.RightClick();
					this.SendJson (context, "{result:true}");
					return;
				case "scroll":
					if(_parseMouseCoordinats(url,ref x,ref y))
					{
						Simulator.ScrollWheel(y);
						this.SendJson (context, "{result:true}");
					}
					else
					{
						this.SendJson (context, "{result:false}");
					}
					return;
				}
				this.SendJson (context, "{result:false}");

			} catch (Exception ex) {
				Console.WriteLine ("exception : " + ex.ToString ());
				context.Response.StatusCode = (int)HttpStatusCode.InternalServerError;
			}
		}
	}//
}