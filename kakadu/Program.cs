using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace kakadu
{
  using Maxbuk.Server.Core;
  using System.Threading;

  class Program
  {
    static int thread_id = 0;
    static void Main(string[] args)
    {

      _run_server(@"E:\github\CobaServer\prorok\", "192.168.0.107", 3060);
      _run_server(@"E:\github\CobaServer\site\", "192.168.0.107", 3061);
      Thread.Sleep(300);
      Console.WriteLine("press enter to exit");
      Console.ReadLine();
    }
    class server_info
    {
      public string site;
      public string host = "192.168.0.107";
      public int port;
    }
    private static void _run_server_in_thread(object srvinfo)
    {
      server_info info = (server_info)srvinfo;
      Console.WriteLine("Server {0}:{1} site:{2}", info.host, info.port, info.site);
      CobaServer server = new CobaServer(info.site, info.host, info.port);
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
