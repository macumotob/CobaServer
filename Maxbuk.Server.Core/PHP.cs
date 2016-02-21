using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;

namespace Maxbuk.Server.Core
{
	public class PHP
	{
    public string php_bin_file = "E:/Develops/php5/php.exe";// @"D:/apache/php5445mt/php.exe";
    public string php_source_folder = @"E:/github/CobaServer/prorok/";

    public PHP ()
		{
       
		}
    private void _parse_argumants(string url, ProcessStartInfo info)
    {
      string s = url.Substring(url.IndexOf('?') + 1);
      string [] ss = s.Split('&');
      foreach(var tok in ss)
      {
        //info.EnvironmentVariables.Add();
      }
    }
		public void Execute(HttpListenerContext context, string fileName){
			//prepare input
			string input = @"name=some stringy input&name2=oiuoiuo";


			string php_options = @"-f";
			string php_file_name = php_source_folder + context.Request.RawUrl;

			Process myProcess = new Process();

			ProcessStartInfo info = new ProcessStartInfo(php_bin_file, "spawn");
      info.UseShellExecute = false;
      info.CreateNoWindow = true;
      info.RedirectStandardOutput = true;
      info.WorkingDirectory = php_source_folder;
      //Provide the other arguments.
      string url = context.Request.RawUrl;
      _parse_argumants(url, info);
      

      info.Arguments = string.Format("{0} {1}", php_options, php_file_name);
      myProcess.StartInfo = info;

			//Execute the process
			myProcess.Start();
			StreamReader myStreamReader = myProcess.StandardOutput;
			string s = null;
			// Read the standard output of the spawned process.
			while((s = myStreamReader.ReadLine ()) != null){
				byte[] data = Encoding.UTF8.GetBytes (s);
				//byte[] data = Encoding.GetEncoding(1252).GetBytes(s);
				context.Response.OutputStream.Write (data, 0, data.Length);
			} while(s != null);
			context.Response.OutputStream.Flush();
			context.Response.OutputStream.Close();
		
			//Console.WriteLine(myString);
		}
	}
}

