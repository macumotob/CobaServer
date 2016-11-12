using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Linq;
using System.ServiceProcess;
using System.Text;
using System.Threading.Tasks;
using coba;
using Maxbuk.Server.Core;
using System.IO;
using System.Threading;

namespace KakaduService
{
    //https://msdn.microsoft.com/ru-ru/library/vstudio/zt39148a%28v=vs.110%29.aspx
    //installutil.exe Service.exe

    public partial class KakaduService : ServiceBase
    {
        public static string LogFolder
        {
            get
            {
                //string dir = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData) + "/Kakadu/";
                string dir = AppDomain.CurrentDomain.BaseDirectory + "\\Log\\";
                if (!System.IO.Directory.Exists(dir))
                {
                    System.IO.Directory.CreateDirectory(dir);
                }
                return dir; 
            }
        }

        private volatile static coba.Logger _logger;
        public static coba.Logger logger
        {
            get
            {
                if (_logger == null)
                {
                    _logger = new coba.Logger("kakadu", LogFolder);
                }
                return _logger;
            }
        }

        public KakaduService()
        {
            InitializeComponent();
        }

        protected override void OnStart(string[] args)
        {
            logger.Log("Kakadu service started.");
            _run_servers();
        }

        protected override void OnStop()
        {
            logger.Log("Kakadu service stopped.");
        }

        protected override void OnPause()
        {
            logger.Log("Kakadu service started.");
        }

        protected override void OnContinue()
        {
            logger.Log("Kakadu service started.");
        }
        private void _run_servers()
        {
            var x = CobaServer.GetIPAddress();
            x.ForEach(item =>
            {
                logger.Log(item);
            });

            logger.Log( "Mashine name : " +Environment.MachineName);
            
            string fileName = string.Format("{0}\\config\\{1}.txt", AppDomain.CurrentDomain.BaseDirectory, Environment.MachineName);
            if (!File.Exists(fileName))
            {
                Console.WriteLine("File " + fileName + " not found!");
                return;
            }
            Console.WriteLine("Log file location :" + CobaServer.LogFolder + "\r\n");
            //List<server_info> servers = new List<server_info>();

            List<CobaServer> servers = new List<CobaServer>();
            CobaServer server = null;
            string[] lines = File.ReadAllLines(fileName, Encoding.UTF8);
            for (int n = 0; n < lines.Length; n++)
            {
                var s = lines[n].Trim();
                if (s.Length > 0 && s[0] != '#')
                {
                    if (s == "server")
                    {
                        server = new CobaServer();
                        servers.Add(server);
                    }
                    else if (s == "folders")
                    {

                        n++;
                        while (n < lines.Length)
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
            foreach (var srv in servers)
            {
                _run_server_in_thread(srv);
            }

            Console.WriteLine("Press Q to exit");

        }
        private static void _run_server_in_thread(object srv)
        {
            CobaServer server = (CobaServer)srv;
            logger.Log("Server {0}:{1} https:{3} site:{2}", server.Host, server.Port, server.RootDirectory, server.HttpsPort);
            server.Start();
            Thread.Sleep(2000);
            if (!server.IsWorking)
            {
                logger.Log(server.ToString() + "---------->ERROR see log file ");
            }
        }

    }
}
