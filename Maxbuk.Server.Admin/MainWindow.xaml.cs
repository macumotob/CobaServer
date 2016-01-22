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
      col.DisplayMemberBinding = new Binding("Name");
      grid.Columns.Add(col);

      col = new GridViewColumn();
      col.Header = "Folder";
      col.Width = 200;
      col.DisplayMemberBinding = new Binding("Folder");
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

      _listIp = MaxbukServerAdmin.GetIPAddress();
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
      List<MaxbukDriverInfo> list = MaxbukServerAdmin.LoadDriversList();
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
    private MaxbukDriverInfo _selectedDriverInfo
    {
      get
      {
        if(_listFolders.SelectedItem != null)
        {
          return (MaxbukDriverInfo) _listFolders.SelectedItem;
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
      MaxbukJsonResult result = MaxbukServerAdmin.RegisterServer(_name, _port.ToString());
      if(result.IsOk)
      {
        //int index = _listIp.IndexOf(result.data);
        //if(index == -1)
        //{
        //  _listIp.Add(result.data);
        //}
        _saveSettings();
        string message = string.Format("Server IP : {0} registered!", result.data);
        _showMessage(message);
      }
      else
      {
        _showMessage("Error : " + result.data);
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
        string text = string.Format("Delete {0}?{1}{2}", _selectedDriverInfo.Name, Environment.NewLine, _selectedDriverInfo.Folder);
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

    private void _buttonSave_Click(object sender, RoutedEventArgs e)
    {
      _saveSettings();
      _showMessage("Saved.");
    }

    private void _buttonRun_Click(object sender, RoutedEventArgs e)
    {
      _updateData();

      if(MaxbukServerAdmin.IsServerRunning(_host, _port))
      {
        _showMessage("Server is running");
        _buttonRun.IsEnabled = false;
        return;
      }
      string result = MaxbukServerAdmin.RunServer(_host, _port);
      if (result.IndexOf("Error:") == -1)
      {
        _buttonRun.IsEnabled = MaxbukServerAdmin.IsServerRunning(_host, _port);
      }
      else
      {
        _showMessage(result);
      }
    }

    private void _buttonAdvanced_Click(object sender, RoutedEventArgs e)
    {
      AdvancedWindow wnd = new AdvancedWindow();
      wnd.ShowDialog();
    }
   
  }
}
