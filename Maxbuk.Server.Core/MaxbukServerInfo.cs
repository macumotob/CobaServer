using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Maxbuk.Server.Core
{
  public class MaxbukServerInfo
  {

    public MaxbukServerInfo()
    {
      this.Port = 3035;
      this.Host = Environment.MachineName;
    //  this.LogFile = "coba_server.log";
      this.Name = Environment.MachineName;
    }
    public string Host { get; set; }
    public string Name { get; set; }
    public int Port { get; set; }

    public string Site 
    {
      get
      {
        return MaxbukServerAdmin.SiteFolderName;
      }
      }
    public string VirtualFolders
    {
      get
      {
        return MaxbukServerAdmin.DriversFileName;
      }
    }

    public void Parse(string name, string value)
    {
      name = name.Trim().ToLower();
      value = value.Trim();
      switch (name)
      {
        case "host":
          this.Host = value;
          break;
        case "port":
          int port;
          int.TryParse(value, out port);
          this.Port = port;
          break;
        case "site":
          //this.Site = value;
          break;
        case "folders":
          //this.VirtualFolders = value;
          break;
        case "log":
          //this.LogFile = value;
          break;
        case "name":
          this.Name = value == null ? Environment.MachineName : value;
          break;
        default:
          break;
      }
    }
    public void SaveSettings()
    {
      string file = MaxbukServerAdmin.ServerSettingFileName;
      System.IO.File.WriteAllText(file, this.ToString());
    }

    public override string ToString()
    {
      string s = string.Format("host :{0}\r\nport :{1}\r\nsite :{2}\r\nfolders :{3}\r\nname :{4}\r\n",
        this.Host, this.Port, this.Site, this.VirtualFolders, this.Name);
      return s;
    }
  }
}
