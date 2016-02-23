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
    class server_info
    {
      public string site;
      public string host = "192.168.0.107";
      public int port;
      public string php;
    }

    static int thread_id = 0;
    static void Main(string[] args)
    {
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
              case "root":
                server.RootDirectory = value;
                break;
              case "php":
                server.PHP_BIN = value;
                break;
            }
            Console.WriteLine(s);
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
      Console.WriteLine("Server {0}:{1} site:{2}", server.Host, server.Port, server.RootDirectory);
      //CobaServer server = new CobaServer(info.site, info.host, info.port);
      //server.PHP_BIN = info.php;
      server.Start();
      Thread.Sleep(1000);
      if (!server.IsWorking)
      {
        Console.WriteLine(server.ToString () + "---------->ERROR see log file ");
      }
    }
    private static void _run_server(string site,string host,int port)
    {
      Thread.Sleep(100);
      Thread thread = new Thread(_run_server_in_thread);
      thread.IsBackground = true;
      thread.Name = "T" + (thread_id++).ToString();
      thread.Start( new server_info() { port = port, site = site, host=host });

    }
  }
}
