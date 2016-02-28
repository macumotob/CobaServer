using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Media.Imaging;
using System.Windows.Navigation;
using System.Windows.Shapes;

namespace Maxbuk.Server.Admin
{
  using Maxbuk.Server.Core;
  using System.Threading;
  //using xsrv;


  public partial class MainWindow : Window
  {
   
    List<string> _listIp;
    int _ipIndex;
    public MainWindow()
    {
      InitializeComponent();

      _createListViewHeaders();
      _updateControls();
    }
    private string _host
    {
      get
      {
        return MaxbukServerAdmin.ServerInfo.Host;
      }
      set
      {
        MaxbukServerAdmin.ServerInfo.Host = value;
      }
    }
    private int _port
    {
      get
      {
        return MaxbukServerAdmin.ServerInfo.Port;
      }
      set
      {
        MaxbukServerAdmin.ServerInfo.Port = value;
      }
    }
    private string _name
    {
      get
      {
        return MaxbukServerAdmin.ServerInfo.Name;
      }
      set
      {
        MaxbukServerAdmin.ServerInfo.Name = value;
      }
    }
    private void _createListViewHeaders()
    {
      GridView grid = new GridView();
      GridViewColumn col = new GridViewColumn();
      col.Header = "Name";
      col.Width = 200;
      col.DisplayMemberBinding = new Binding("name");
      grid.Columns.Add(col);

      col = new GridViewColumn();
      col.Header = "Folder";
      col.Width = 200;
      col.DisplayMemberBinding = new Binding("path");
      grid.Columns.Add(col);
      _listFolders.View = grid;
    }

    private void _updateControls()
    {
      MaxbukServerAdmin.ServerInfo = MaxbukServerAdmin.LoadSetting();

      _textHost.Text = _host;
      _textPort.Text = _port.ToString();
      _textName.Text = _name;

      _refreshDrivers();

      _listIp = CobaServer.GetIPAddress();
   //   _listIp.Add(Environment.MachineName);
      _ipIndex = _listIp.IndexOf(_host) + 1;
      if (_ipIndex > _listIp.Count - 1) _ipIndex = 0;
    }
    private void _updateData()
    {
      _host = _textHost.Text;
      _name = _textName.Text;
      int port;
      int.TryParse(_textPort.Text, out port);
      _port = port;

    }
    private void _refreshDrivers()
    {
      List<FileFolderInfo> list = MaxbukServerAdmin.LoadDriversList();
      _listFolders.Items.Clear();
      foreach (var item in list)
      {
        _listFolders.Items.Add(item);
      }
      _listFolders.Focus();
    }
    private bool _IsDriverSelected
    {
      get
      {
        return (_listFolders.SelectedItem != null);
      }
    }
    private FileFolderInfo _selectedDriverInfo
    {
      get
      {
        if(_listFolders.SelectedItem != null)
        {
          return (FileFolderInfo) _listFolders.SelectedItem;
        }
        return null;
      }
    }

    private void _showMessage(string text)
    {
      MessageBox.Show(this, text, "Warning", MessageBoxButton.OK, MessageBoxImage.Warning);
    }
    private void _saveSettings()
    {
      _updateData();
      MaxbukServerAdmin.ServerInfo.SaveSettings();
    }
    private void _buttonRegister_Click(object sender, RoutedEventArgs e)
    {
      this.Cursor = Cursors.Wait;
      _saveSettings();
      JsonResult result = MaxbukServerAdmin.RegisterServer(_name, _port.ToString());
      if(result.IsOk)
      {
        //int index = _listIp.IndexOf(result.data);
        //if(index == -1)
        //{
        //  _listIp.Add(result.data);
        //}
        _saveSettings();
        string message = string.Format("Server IP : {0} registered!", result.msg);
        _showMessage(message);
      }
      else
      {
        _showMessage("Error : " + result.msg);
      }
      this.Cursor = Cursors.Arrow;
    }

    private void _buttonPort_Click(object sender, RoutedEventArgs e)
    {
      _textPort.Text = MaxbukAdmin.FindFreePort().ToString();
    }

    private void _buttonMachine_Click(object sender, RoutedEventArgs e)
    {
      _textHost.Text = _listIp[_ipIndex];
      _ipIndex++;
      if(_ipIndex > _listIp.Count - 1)
      {
        _ipIndex = 0;
      }
    }

    private void _showAddFolderWindow()
    {
      AddFolderWindow wnd = new AddFolderWindow();
      wnd.DriverInfo = _selectedDriverInfo;
      wnd.ShowDialog();
      _refreshDrivers();
    }
    private void _buttonAdd_Click(object sender, RoutedEventArgs e)
    {
      _showAddFolderWindow();
    }

    private void _buttonDelete_Click(object sender, RoutedEventArgs e)
    {
      if(_IsDriverSelected)
      {
        string text = string.Format("Delete {0}?{1}{2}", _selectedDriverInfo.name, Environment.NewLine, _selectedDriverInfo.path);
        if (MessageBox.Show(this,text, "Warning", MessageBoxButton.OKCancel, MessageBoxImage.Warning ) == MessageBoxResult.OK)
        {
          MaxbukServerAdmin.DriverInfoDelete(_selectedDriverInfo);
          _refreshDrivers();
        }
      }
      else
      {
        _showMessage("No folder selected.\nSelect folder to delete.");
      }
    }

    private void _buttonEdit_Click(object sender, RoutedEventArgs e)
    {
      if (_IsDriverSelected)
      {
        _showAddFolderWindow();
      }
      else
      {
        _showMessage("No folder selected.\nSelect folder to edit.");
      }
    }

    private void _listFolders_MouseDoubleClick(object sender, MouseButtonEventArgs e)
    {
      _showAddFolderWindow();
    }
    private void _buttonSite_Click(object sender, RoutedEventArgs e)
    {
      MaxbukServerAdmin.RunSite(MaxbukServerAdmin.ServerInfo.Host, MaxbukServerAdmin.ServerInfo.Port);
    }

    private void _buttonSave_Click(object sender, RoutedEventArgs e)
    {
      _saveSettings();
      _showMessage("Saved.");
    }

    private void _buttonRun_Click(object sender, RoutedEventArgs e)
    {
      _updateData();

      if(MaxbukServerAdmin.IsServerRunning())
      {
        MaxbukServerAdmin.StopServer();
        //_buttonRun.IsEnabled = false;
        _labelServerStatus.Content = "Run Server";
        return;
      }
      MaxbukServerAdmin.RunServer(_host, _port);
      Thread.Sleep(200);
      if (MaxbukServerAdmin.IsServerRunning())
      {
        _labelServerStatus.Content = "Stop Server";
      }
      else
      {
        _labelServerStatus.Content = "Run Server";
      }
    }

    private void _buttonAdvanced_Click(object sender, RoutedEventArgs e)
    {
      AdvancedWindow wnd = new AdvancedWindow();
      wnd.ShowDialog();
    }
   
  }
}
