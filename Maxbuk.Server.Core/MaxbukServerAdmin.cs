﻿using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net;
using System.Runtime.Serialization.Json;
using System.Text;

namespace Maxbuk.Server.Core
{
  
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
    static public string LogFileName
    {
      get
      {
        return AppDomain.CurrentDomain.BaseDirectory + "server_log.txt";
      }
    }

    static public string SiteFolderName
    {
      get
      {
        return AppDomain.CurrentDomain.BaseDirectory + "\\..\\Site\\";//index.html";
      }
    }
    static public string ReadLogFile()
    {
      if (System.IO.File.Exists(MaxbukServerAdmin.LogFileName))
      {
        string text = System.IO.File.ReadAllText(MaxbukServerAdmin.LogFileName, Encoding.UTF8);
        return text;
      }
      return "Log File Not Exists!";
    }
    static public void ClearLogFile()
    {
      if (System.IO.File.Exists(MaxbukServerAdmin.LogFileName))
      {
        System.IO.File.Delete(MaxbukServerAdmin.LogFileName);
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

    static public string StopServer(string host,int port,out Exception exception)
    {
      exception = null;
      WebClient client = null;
      string response = null;
      try
      {
        client = new WebClient();
        string query = string.Format("http://{0}:{1}/CobaSoft.StopServer.0\n\n", host, port);
        response = client.DownloadString(query);
      }
      catch (Exception ex)
      {
        exception = ex;
      }
      finally
      {
        if(client != null)
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

    static public List<MaxbukDriverInfo> Drivers;
    static public MaxbukDriverInfo CurrentDriver;

    public static void DriverInfoDelete(MaxbukDriverInfo driverInfo)
    {
      if (MaxbukServerAdmin.Drivers != null)
      {
        MaxbukServerAdmin.Drivers.Remove(driverInfo);
        MaxbukServerAdmin.SaveDriversList();
      }
    }
    public static void DriverInfoAdd(MaxbukDriverInfo driverInfo)
    {
      if (MaxbukServerAdmin.Drivers != null)
      {
        MaxbukServerAdmin.Drivers.Add(driverInfo);
        MaxbukServerAdmin.SaveDriversList();
      }
    }

    public static void SaveDriversList()
    {

      List<MaxbukDriverInfo> list = MaxbukServerAdmin.Drivers;
      string fileName = MaxbukServerAdmin.DriversFileName;

      StringBuilder sb = new StringBuilder("[");

      for (int i = 0; i < list.Count; i++)
      {
        MaxbukDriverInfo driver = list[i];

        if (i > 0)
        {
          sb.Append("," + Environment.NewLine);
        }
        sb.Append("{");
        sb.AppendFormat("\"Name\":\"{0}\",\"Folder\":\"{1}\"", driver.Name, driver.Folder);
        sb.Append("}");
      }
      sb.Append("]");
      using (StreamWriter sw = new StreamWriter(fileName, false, Encoding.UTF8))
      {
        sw.Write(sb.ToString());
        sw.Close();
      }
    }


    static public List<MaxbukDriverInfo> LoadDriversList()
    {

      string fileName = MaxbukServerAdmin.DriversFileName;
      if(!System.IO.File.Exists(fileName))
      {
        MaxbukServerAdmin.Drivers = new List<MaxbukDriverInfo>();
        MaxbukServerAdmin.Drivers.Add(new MaxbukDriverInfo() { Folder = "C:/Program Files/", Name = "Shared Folder" });
        MaxbukServerAdmin.SaveDriversList();
        return MaxbukServerAdmin.Drivers;
      }
      string json = System.IO.File.ReadAllText(fileName, Encoding.UTF8);
      byte[] buffer = Encoding.UTF8.GetBytes(json);

      List<MaxbukDriverInfo> list = null;
      DataContractJsonSerializer ser = new DataContractJsonSerializer(typeof(List<MaxbukDriverInfo>));

      using (MemoryStream ms = new MemoryStream(buffer))
      {
        list = (List<MaxbukDriverInfo>)ser.ReadObject(ms);
        ms.Close();
      }
      MaxbukServerAdmin.Drivers = list;
      return list;
    }
    #endregion

    static private Process _findRunningServer(string caption)
    {
      var pps = System.Diagnostics.Process.GetProcessesByName("CobaServer");
      if (pps.Length > 0)
      {
        foreach (var p in pps)
        {
          try
          {
            string title = p.MainWindowTitle;
            if(caption == title)
            {
              return p;
            }
          }
          catch(Exception ex)
          {

          }

        }
      }
      return null;
    }
    static private string _getSeverTitle(string host, int port)
    {
      return string.Format("CobaServer (c) 0.1 {0}:{1}", host, port);

    }
    static public bool IsServerRunning(string host,int port)
    {
      Process p = _findRunningServer(_getSeverTitle(host, port));
      return p != null;
    }

    static private Process _serverProcess;
    static private CobaServer _server;
    static public string RunServer(string host,int port)
    {
      string result = null;

      try
      {
        string wdir = MaxbukServerAdmin.SiteFolderName;
       // wdir = @"E:\github\MyDrives\site\";
        _server = new CobaServer(wdir,host,port);//@"E:\github\MyDrives\site\", host, port);
        return "OK";

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
        }
      }
      catch (Exception ex)
      {
        result = "Exception " + ex.Message;
      }
      return result;
    }
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
            result = "Error: start chrome!\r\nSee log file " + MaxbukServerAdmin.LogFileName;
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
            result = "Error: start chrome!\r\nSee log file " + MaxbukServerAdmin.LogFileName;
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
