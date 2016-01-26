using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net;
using System.Runtime.Serialization.Json;
using System.Text;

namespace Maxbuk.Server.Core
{
  using System.Net.Sockets;
  using System.Threading;
  using xsrv;

  public class MaxbukServerAdmin
  {
    static public MaxbukServerInfo ServerInfo;
    static public string ServerSettingFileName
    {
      get
      {
        return AppDomain.CurrentDomain.BaseDirectory + "server.txt";
      }
    }
    static public string DriversFileName
    {
      get
      {
        return AppDomain.CurrentDomain.BaseDirectory + "virtual_drivers.txt";
      }
    }
    static public string SiteFolderName
    {
      get
      {
        return AppDomain.CurrentDomain.BaseDirectory + "\\..\\Site\\";//index.html";
      }
    }
    static private MaxbukJsonResult _getJsonResult(string response)
    {
      response = response.Replace('\'', '\"');
      MaxbukJsonResult result = null;
      byte[] data = Encoding.Unicode.GetBytes(response);

      DataContractJsonSerializer ser = new DataContractJsonSerializer(typeof(MaxbukJsonResult));

      using (MemoryStream ms = new MemoryStream(data))
      {
        result = (MaxbukJsonResult)ser.ReadObject(ms);
        ms.Close();
      }
      return result;
    }
    static public MaxbukJsonResult RegisterServer(string name, string port)
    {
      MaxbukJsonResult result = null;
      using (WebClient client = new WebClient())
      {
        string response = client.DownloadString(string.Format("http://maxbuk.com/regsrv.php?name={0}&port={1}", name, port));
        result = _getJsonResult(response);
        client.Dispose();
      }
      return result;
    }
    static public MaxbukJsonResult UnRegisterServer(string name)
    {
      MaxbukJsonResult result = null;
      using (WebClient client = new WebClient())
      {
        string response = client.DownloadString(string.Format("http://maxbuk.com/delsrv.php?name={0}", name));
        result = _getJsonResult(response);
        client.Dispose();
      }
      return result;
    }


    static public MaxbukServerInfo LoadSetting()
    {
      string file = MaxbukServerAdmin.ServerSettingFileName;
      MaxbukServerInfo info;
      if (!System.IO.File.Exists(file))
      {
        info = new MaxbukServerInfo();
        info.SaveSettings();
      }
      else
      {
        string text = System.IO.File.ReadAllText(file, Encoding.UTF8);
        info = MaxbukServerAdmin.ParseServerInfo(text);
      }
      return info;
    }

    static private MaxbukServerInfo ParseServerInfo(string text)
    {
      MaxbukServerInfo info = new MaxbukServerInfo();

      string[] lines = text.Split('\n');
      foreach (var line in lines)
      {
        var s = line.Replace('\r', ' ').Trim();
        if (s.Length > 0)
        {
          if (s[0] == '#')
          {

          }
          else
          {
            int i = s.IndexOf(':');
            string name = s.Substring(0, i - 1);
            string value = s.Substring(i + 1);
            info.Parse(name, value);
          }
        }
      }
      return info;
    }

    #region Work with Drivers

    static public List<FileFolderInfo> Drivers;
    static public FileFolderInfo CurrentDriver;

    public static void DriverInfoDelete(FileFolderInfo driverInfo)
    {
      if (MaxbukServerAdmin.Drivers != null)
      {
        MaxbukServerAdmin.Drivers.Remove(driverInfo);
        MaxbukServerAdmin.SaveDriversList();
      }
    }
    public static void DriverInfoAdd(FileFolderInfo driverInfo)
    {
      if (MaxbukServerAdmin.Drivers != null)
      {
        MaxbukServerAdmin.Drivers.Add(driverInfo);
        MaxbukServerAdmin.SaveDriversList();
      }
    }

    public static void SaveDriversList()
    {
      string fileName = MaxbukServerAdmin.DriversFileName;

      string xjson = new System.Web.Script.Serialization.JavaScriptSerializer().Serialize(MaxbukServerAdmin.Drivers);
      System.IO.File.WriteAllText(fileName, xjson, Encoding.UTF8);

      //DataContractJsonSerializer ser = new DataContractJsonSerializer(typeof(List<FileFolderInfo>));
      //ser.
      //using (StreamWriter sw = new StreamWriter(fileName, false, Encoding.UTF8))
      //{
      //  ser.WriteObject(sw.BaseStream, MaxbukServerAdmin.Drivers);
      //  sw.Close();
      //  sw.Dispose();
      //}
      /*
      return;

      List<FileFolderInfo> list = MaxbukServerAdmin.Drivers;



      StringBuilder sb = new StringBuilder("[");

      for (int i = 0; i < list.Count; i++)
      {
        FileFolderInfo driver = list[i];

        if (i > 0)
        {
          sb.Append("," + Environment.NewLine);
        }
        sb.Append("{");
        sb.AppendFormat("\"Name\":\"{0}\",\"Folder\":\"{1}\"", driver.name, driver.path);
        sb.Append("}");
      }
      sb.Append("]");
      using (StreamWriter sw = new StreamWriter(fileName, false, Encoding.UTF8))
      {
        sw.Write(sb.ToString());
        sw.Close();
      }*/
    }


    static public List<FileFolderInfo> LoadDriversList()
    {


      string fileName = MaxbukServerAdmin.DriversFileName;
      if (!System.IO.File.Exists(fileName))
      {
        MaxbukServerAdmin.Drivers = new List<FileFolderInfo>();
        MaxbukServerAdmin.Drivers.Add(new FileFolderInfo() { path = "C:/Program Files/", name = "Shared Folder" });
        MaxbukServerAdmin.SaveDriversList();
      }
      else
      {
        string json = System.IO.File.ReadAllText(fileName, Encoding.UTF8);
        var ser = new System.Web.Script.Serialization.JavaScriptSerializer();
        MaxbukServerAdmin.Drivers = ser.Deserialize<List<FileFolderInfo>>(json);
      }
      return MaxbukServerAdmin.Drivers;

      //byte[] buffer = Encoding.UTF8.GetBytes(json);

      //List<FileFolderInfo> list = null;
      //DataContractJsonSerializer ser2 = new DataContractJsonSerializer(typeof(List<FileFolderInfo>));

      //using (MemoryStream ms = new MemoryStream(buffer))
      //{
      //  list = (List<FileFolderInfo>)ser2.ReadObject(ms);
      //  ms.Close();
      //}
      //MaxbukServerAdmin.Drivers = list;
      //return list;
    }
    #endregion

    //static private Process _findRunningServer(string caption)
    //{
    //  var pps = System.Diagnostics.Process.GetProcessesByName("CobaServer");
    //  if (pps.Length > 0)
    //  {
    //    foreach (var p in pps)
    //    {
    //      try
    //      {
    //        string title = p.MainWindowTitle;
    //        if(caption == title)
    //        {
    //          return p;
    //        }
    //      }
    //      catch(Exception ex)
    //      {

    //      }

    //    }
    //  }
    //  return null;
    //}
    //static private string _getSeverTitle(string host, int port)
    //{
    //  return string.Format("CobaServer (c) 0.1 {0}:{1}", host, port);

    //}
    static public bool IsServerRunning()
    {
      if(_server != null)
      {
        return _server.IsWorking;
      }
      return false;
    }

    //static private Process _serverProcess;
    static private CobaServer _server;
    static public string StopServer()
    {
      if(_server != null)
      {
        string result = _server.Stop();
        Thread.Sleep(200);
        return result;
      }
      return "server not running";
    }
    static public string RunServer(string host,int port)
    {
      string result = null;

      try
      {
        string wdir = MaxbukServerAdmin.SiteFolderName;
       // wdir = @"E:\github\MyDrives\site\";
        _server = new CobaServer(wdir,host,port);//@"E:\github\MyDrives\site\", host, port);
        return "OK";
/*
        bool createNoWindow = true;
        _serverProcess = _findRunningServer(_getSeverTitle(host,port));
        if(_serverProcess != null)
        {
          result = string.Format("Server started!{0}Process: {1}{0}", Environment.NewLine, _serverProcess.Id);
          return result;
        }
        _serverProcess = new Process();

        _serverProcess.StartInfo.FileName = AppDomain.CurrentDomain.BaseDirectory + "/CobaServer.exe";
        _serverProcess.StartInfo.CreateNoWindow = createNoWindow;
        _serverProcess.EnableRaisingEvents = true;
        _serverProcess.StartInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Normal;
        _serverProcess.StartInfo.Verb = "runas";
        _serverProcess.StartInfo.UseShellExecute = false;
        //server.StartInfo.RedirectStandardOutput = true;
        //server.StartInfo.RedirectStandardInput = true;
        //server.StartInfo.RedirectStandardError = true;
        //server.StartInfo.Arguments = "-service=true";

        if (_serverProcess.Start())
        {
          bool stoped = _serverProcess.WaitForExit(1000);
          if (stoped)
          {
            result = "Error: start server!\r\nSee log file " + MaxbukServerAdmin.LogFileName;
          }
          else
          {
            result = string.Format("Server started!{0}Process: {1}{0}", Environment.NewLine, _serverProcess.Id);
          }
        }
        else
        {
          result = "Error start server!";
        }*/
      }
      catch (Exception ex)
      {
        result = "Exception " + ex.Message;
      }
      return result;
      
    }
    /*
    static public string RunServer_Original(string host, int port)
    {
      string result = null;

      try
      {
        bool createNoWindow = true;
        _serverProcess = _findRunningServer(_getSeverTitle(host, port));
        if (_serverProcess != null)
        {
          result = string.Format("Server started!{0}Process: {1}{0}", Environment.NewLine, _serverProcess.Id);
          return result;
        }
        _serverProcess = new Process();

        _serverProcess.StartInfo.FileName = AppDomain.CurrentDomain.BaseDirectory + "/CobaServer.exe";
        _serverProcess.StartInfo.CreateNoWindow = createNoWindow;
        _serverProcess.EnableRaisingEvents = true;
        _serverProcess.StartInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Normal;
        _serverProcess.StartInfo.Verb = "runas";
        _serverProcess.StartInfo.UseShellExecute = false;
        //server.StartInfo.RedirectStandardOutput = true;
        //server.StartInfo.RedirectStandardInput = true;
        //server.StartInfo.RedirectStandardError = true;
        //server.StartInfo.Arguments = "-service=true";

        if (_serverProcess.Start())
        {
          bool stoped = _serverProcess.WaitForExit(1000);
          if (stoped)
          {
            result = "Error: start server!\r\nSee log file " + MaxbukServerAdmin.LogFileName;
          }
          else
          {
            result = string.Format("Server started!{0}Process: {1}{0}", Environment.NewLine, _serverProcess.Id);
          }
        }
        else
        {
          result = "Error start server!";
        }
      }
      catch (Exception ex)
      {
        result = "Exception " + ex.Message;
      }
      return result;
    }
    */
    static public string RunSite(string host, int port)
    {
      string result = null;
      try
      {
        Process process = new Process();

        process.StartInfo.FileName = @"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe";
        process.EnableRaisingEvents = true;
        process.StartInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Normal;
        process.StartInfo.UseShellExecute = false;
        process.StartInfo.Arguments = string.Format("http://{0}:{1}/", host, port);
        if (process.Start())
        {
          bool stoped = process.WaitForExit(1000);
          if (stoped)
          {
            result = "Error: start chrome!\r\n";
          }
          else
          {
            result = string.Format("Chrome started!{0}Process: {1}{0}", Environment.NewLine, process.Id);
          }
        }
        else
        {
          result = "Error start chrome!";
        }
      }
      catch (Exception ex)
      {
        result = "Exception " + ex.Message;
      }
      return result;
    }

    static public string RunMaxbuk()
    {
      string result = null;
      try
      {
        Process process = new Process();

        process.StartInfo.FileName = @"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe";
        //process.EnableRaisingEvents = true;
        process.StartInfo.WindowStyle = System.Diagnostics.ProcessWindowStyle.Normal;
        process.StartInfo.UseShellExecute = false;
        process.StartInfo.Arguments = string.Format("www.maxbuk.com");
        if (process.Start())
        {
          bool stoped = process.WaitForExit(1000);
          if (stoped)
          {
            result = "Error: start chrome!\r\nSee log file";
          }
          else
          {
            result = string.Format("Chrome started!{0}Process: {1}{0}", Environment.NewLine, process.Id);
          }
        }
        else
        {
          result = "Error start chrome!";
        }
      }
      catch (Exception ex)
      {
        result = "Exception " + ex.Message;
      }
      return result;
    }
    

  }
}
