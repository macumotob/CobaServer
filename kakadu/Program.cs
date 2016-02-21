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

      string fileName = AppDomain.CurrentDomain.BaseDirectory + "\\config\\kakadu.txt";
      if (!File.Exists(fileName))
      {
        Console.WriteLine("File " + fileName + " not found!");
        return;
      }
      List<server_info> servers = new List<server_info>();
      server_info server=null;
      string[] lines = File.ReadAllLines(fileName, Encoding.UTF8);
      foreach(var line in lines)
      {
        var s = line.Trim();
        if(s.Length >0 && s[0] != '#')
        {
          if(s == "server")
          {
             server = new server_info();
            servers.Add(server);
          }
          else
          {
            int i = s.IndexOf(' ');
            string name = s.Substring(0, i).Trim();
            string value = s.Substring(i).Trim();
            switch (name)
            {
              case "host":
                server.host = value;
                break;
              case "port":
                server.port = int.Parse(value);
                break;
              case "root":
                server.site = value;
                break;
              case "php":
                server.php = value;
                break;
            }
            Console.WriteLine(s);
          }
          
        }
      }
      foreach(var srv in servers)
      {
        _run_server_in_thread(srv);
        Thread.Sleep(1000);
      }
      Console.ReadLine();
      return;
      _run_server(@"E:\github\CobaServer\prorok\", "192.168.0.107", 3060);
      _run_server(@"E:\github\CobaServer\site\", "192.168.0.107", 3061);
      Thread.Sleep(300);
      Console.WriteLine("press enter to exit");
      Console.ReadLine();
    }
    private static void _run_server_in_thread(object srvinfo)
    {
      server_info info = (server_info)srvinfo;
      Console.WriteLine("Server {0}:{1} site:{2}", info.host, info.port, info.site);
      CobaServer server = new CobaServer(info.site, info.host, info.port);
      server.PHP_BIN = info.php;
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
