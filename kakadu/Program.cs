using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.IO;
namespace kakadu
{
  using Maxbuk.Server.Core;
  using System.Threading;

  class Program
  {
    //static int thread_id = 0;
    static void Main(string[] args)
    {

//      _encoding_folder();      return;
      Console.WriteLine(Environment.MachineName);
      
      string fileName = string.Format("{0}\\config\\{1}.txt",
        AppDomain.CurrentDomain.BaseDirectory,Environment.MachineName);
      if (!File.Exists(fileName))
      {
        Console.WriteLine("File " + fileName + " not found!");
        return;
      }
      //List<server_info> servers = new List<server_info>();

      List<CobaServer> servers  = new List<CobaServer>();
      CobaServer server = null;
      string[] lines = File.ReadAllLines(fileName, Encoding.UTF8);
      for(int n = 0; n < lines.Length;n++)
      {
        var s = lines[n].Trim();
        if(s.Length >0 && s[0] != '#')
        {
          if(s == "server")
          {
            server = new CobaServer();
            servers.Add(server);
          }
          else if(s == "folders")
          {

            n++;
            while(n < lines.Length)
            {
              s = lines[n].Trim();
              if (s == "end") break;
              server.ParseFolderInfo(s);
              n++;
            }
          }
          else
          {
            int i = s.IndexOf(' ');
            string name = s.Substring(0, i).Trim();
            string value = s.Substring(i).Trim();
            switch (name)
            {
              case "host":
                server.Host = value;
                break;
              case "port":
                server.Port = int.Parse(value);
                break;
              case "https_port":
                server.HttpsPort = int.Parse(value);
                break;
              case "root":
                server.RootDirectory = value;
                break;
              case "php":
                server.PHP_BIN = value;
                break;
            }
          //  Console.WriteLine(s);
          }
          
        }
      }
      foreach(var srv in servers)
      {
        _run_server_in_thread(srv);
      }
      Console.ReadLine();
      
    }
    private static void _run_server_in_thread(object srv)
    {
      CobaServer server = (CobaServer)srv;
      Console.WriteLine("Server {0}:{1} https:{3} site:{2}", server.Host, server.Port, server.RootDirectory,server.HttpsPort);
      server.Start();
      Thread.Sleep(1000);
      if (!server.IsWorking)
      {
        Console.WriteLine(server.ToString () + "---------->ERROR see log file ");
      }
    }
    private static void _encoding_folder()
    {
      string folder = @"E:\Books\012\";
      string[] files = Directory.GetFiles(folder, "*.txt");
      foreach(var file in files)
      {
        _change_encoding(file);
      }
      
    }
    private static void _change_encoding( string file)
    {
      try {
        string s = File.ReadAllText(file, Encoding.GetEncoding(1251));
        File.WriteAllText(file, s, Encoding.UTF8);
        string[] lines = File.ReadAllLines(file, Encoding.UTF8);
        string folder = Path.GetDirectoryName(file) + "\\" + lines[0].Trim() + " " + lines[1].Trim() + "\\";

        string newFile = lines[2].Replace("?", "").Replace("\"", "").Replace(":", " ") + ".txt";

        if (!Directory.Exists(folder))
        {
          Directory.CreateDirectory(folder);
        }
        File.Move(file, folder + newFile);
      }
      catch(Exception ex)
      {
        Console.WriteLine("File :{0}",file);
        Console.WriteLine(ex.ToString());
      }
    }
  }
}
