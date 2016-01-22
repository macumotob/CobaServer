using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Sockets;
using System.Text;

namespace Maxbuk.Server.Admin
{
  using Maxbuk.Server.Core;
  using System.IO;
  using System.Runtime.Serialization.Json;
  using System.Threading.Tasks;

  public class MaxbukAdmin
  {

    public static Exception LastException;
    public static int FindFreePort()
    {
      try
      {
        LastException = null;
        TcpListener l = new TcpListener(IPAddress.Loopback, 0);
        l.Start();
        int port = ((IPEndPoint)l.LocalEndpoint).Port;
        l.Stop();
        return port;
      }
      catch(Exception ex)
      {
        LastException = ex;
      }
      return -1;
    }
    //public static MaxbukJsonResult RegisterServer(string name, string port)
    //{
    //  MaxbukJsonResult result = null;

    //  using (WebClient client = new WebClient())
    //  {
    //    string query = string.Format("http://maxbuk.com/regsrv.php?name={0}&port={1}", name, port);
    //    string response = client.DownloadString(query);

    //    byte[] data = Encoding.Unicode.GetBytes(response);

    //    DataContractJsonSerializer ser = new DataContractJsonSerializer(typeof(MaxbukJsonResult));

    //    using (MemoryStream ms = new MemoryStream(data))
    //    {
    //      result = (MaxbukJsonResult)ser.ReadObject(ms);
    //      ms.Close();
    //    }
    //  }
    //  return result;
    //}
  }
}
