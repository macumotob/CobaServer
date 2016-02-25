using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;

namespace Maxbuk.Server.Core
{
	public class PHP
	{
    public string php_bin_file = "E:/Develops/php5/php.exe";// @"D:/apache/php5445mt/php.exe";
    public string php_source_folder = @"E:/github/CobaServer/prorok/";

    public PHP ()
		{
       
		}
    
    private string _parse_argumants(string url, ProcessStartInfo info,out string code)
    {
      
      int i = url.IndexOf('?');
      string file = url.Substring(0, i);

      string s = url.Substring(i + 1);
      code = " ";
      string [] ss = s.Split('&');
      foreach(var tok in ss)
      {
        string [] toks = tok.Split('=');
        string name = toks[0].Trim();//.ToUpper();
        if (toks.Length == 1)
        {
         // info.EnvironmentVariables[name]=" ";
          code +=  "$_GET[\"" +name + "\"]=\"\";";
        }
        else
        {
          //info.EnvironmentVariables[name] = toks[1];
          
          code += "$_GET[\"" + name + "\"]=" + toks[1] +";";
        }
      }
      return file;
    }
    private string _make_get(string url,out string file)
    {
      int i = url.IndexOf('?');
      if (i == -1)
      {
        file = url;
        return null;
      }
      file = url.Substring(0, i);

      string s = url.Substring(i + 1);
      StringBuilder sb = new StringBuilder();
      string[] ss = s.Split('&');
      foreach (var tok in ss)
      {
        string[] toks = tok.Split('=');
        string name = toks[0].Trim();//.ToUpper();
        if (toks.Length == 1)
        {
          sb.AppendFormat("$_GET[\\\"{0}\\\"]=\\\"\\\";",name);
        }
        else
        {
          sb.AppendFormat("$_GET[\\\"{0}\\\"]=\\\"{1}\\\";", name, toks[1]);
        }
      }
      return sb.ToString();

    }
    StreamReader _reader;
    private void _process_thread(object obj)
    {
      string s = "";
      HttpListenerContext context = (HttpListenerContext)obj;
      try
      {
        while (s != null)
        {
          s = _reader.ReadLine();
          if (s == null) break;
          byte[] data = Encoding.UTF8.GetBytes(s);
          //byte[] data = Encoding.GetEncoding(1252).GetBytes(s);
          context.Response.OutputStream.Write(data, 0, data.Length);
        }
      }
      catch (Exception ex)
      {
        
      }
      context.Response.OutputStream.Flush();
      context.Response.OutputStream.Close();

    }
    public void Execute(HttpListenerContext context, string fileName){

			ProcessStartInfo info = new ProcessStartInfo(php_bin_file, "spawn");
      info.UseShellExecute = false;
      info.CreateNoWindow = true;
      info.RedirectStandardOutput = true;
      info.RedirectStandardError = true;
      info.RedirectStandardInput = true;

      info.WorkingDirectory = php_source_folder;

      info.EnvironmentVariables["REMOTE_ADDR"] = context.Request.RemoteEndPoint.ToString();


      string url = context.Request.RawUrl;

      string php_file_name;

      string code = _make_get(url, out php_file_name);
      php_file_name = php_source_folder + php_file_name;
      if (code == null)
      {
        code = string.Format("-c -f \"{0}\" ", php_file_name);
        php_file_name = php_source_folder + php_file_name;
      }
      else
      {
        //php_file_name = php_source_folder + php_file_name;
        code = String.Format("-r \"{0} require_once \\\"{1}\\\";\"", code, php_file_name);
      }
      //info.Arguments = string.Format("-c -f \"{0}\" ", php_file_name);
      info.Arguments = code;
      Process process = new Process();
      process.StartInfo = info;
			process.Start();
  //    process.StandardInput.WriteLine(code);
  //    process.StandardInput.Flush();

      _reader = process.StandardOutput;

      Thread thread = new Thread(_process_thread);
      thread.IsBackground = true;
      //thread.Name = "T" + (thread_id++).ToString();
      thread.Start(context);
     

      bool result = process.WaitForExit(5000);
      string error = process.StandardError.ReadToEnd();
      context.Response.OutputStream.Flush();
			context.Response.OutputStream.Close();
		
			//Console.WriteLine(myString);
		}
	}
}

