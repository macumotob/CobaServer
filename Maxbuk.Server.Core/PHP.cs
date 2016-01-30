using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;

namespace Maxbuk.Server.Core
{
	public class PHP
	{
		public PHP ()
		{
		}
		public void Execute(HttpListenerContext context, string fileName){
			//prepare input
			string input = @"name=some stringy input&name2=oiuoiuo";

			//NOTE: change path according to your own PHP.exe file, if you have the proper environment variables setup, then you can just call PHP.exe directly without the path
			string call = @"D:/apache/php5445mt/php.exe";

			//To execute the PHP file.
			string php_options = @"-f";
			string phpFolder = @"D:/github/CobaServer/prorok/";
			//the PHP wrapper class file location. NOTE: remember to enclose in " (quotes) if there is a space in the directory structure. 
			string php_file_name = phpFolder + fileName;

			Process myProcess = new Process();

			// Start a new instance of this program but specify the 'spawned' version. using the PHP.exe file location as the first argument.
			ProcessStartInfo myProcessStartInfo = new ProcessStartInfo(call, "spawn");
			myProcessStartInfo.UseShellExecute = false;
      myProcessStartInfo.CreateNoWindow = true;
      myProcessStartInfo.RedirectStandardOutput = true;
      myProcessStartInfo.WorkingDirectory = @"D:/github/CobaServer/prorok/";
      //Provide the other arguments.
      myProcessStartInfo.Arguments = string.Format("{0} {1}", php_options,fileName);
			myProcess.StartInfo = myProcessStartInfo;

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

