using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Media.Imaging;
using System.Windows.Shapes;

namespace Maxbuk.Server.Admin
{
  using Maxbuk.Server.Core;
  public partial class AdvancedWindow : Window
  {
    public AdvancedWindow()
    {
      InitializeComponent();
      _textInfo.Text = MaxbukServerAdmin.ReadLogFile(); 
    }

    private void _buttonLog_Click(object sender, RoutedEventArgs e)
    {
      _textInfo.Text = MaxbukServerAdmin.ReadLogFile(); 
    }

    private void _buttonClear_Click(object sender, RoutedEventArgs e)
    {
      MaxbukServerAdmin.ClearLogFile();
      _textInfo.Text = MaxbukServerAdmin.ReadLogFile(); 
    }

    private void _buttonStop_Click(object sender, RoutedEventArgs e)
    {
      Exception ex;
      string result = MaxbukServerAdmin.StopServer(MaxbukServerAdmin.ServerInfo.Host, MaxbukServerAdmin.ServerInfo.Port,out ex);
      if(ex != null)
      {
        MessageBox.Show(ex.Message);
      }
      else
      {
        MessageBox.Show(result);
      }
    }

    private void _buttonWeb_Click(object sender, RoutedEventArgs e)
    {
      MaxbukServerAdmin.RunSite(MaxbukServerAdmin.ServerInfo.Host, MaxbukServerAdmin.ServerInfo.Port);
    }

    private void _buttonMax_Click(object sender, RoutedEventArgs e)
    {
      MaxbukServerAdmin.RunMaxbuk();
    }

    private void _buttonUnregister_Click(object sender, RoutedEventArgs e)
    {
      MaxbukJsonResult result = MaxbukServerAdmin.UnRegisterServer(MaxbukServerAdmin.ServerInfo.Name);
      MessageBox.Show(result.data);
    }
  }
}
