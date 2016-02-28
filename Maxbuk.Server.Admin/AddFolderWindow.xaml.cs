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
  using System.Windows.Forms;
  //using xsrv;

  public partial class AddFolderWindow : Window
  {
    FileFolderInfo _driverInfo;
    private bool _isNewFolder;
    public AddFolderWindow()
    {
      InitializeComponent();
    }
    public FileFolderInfo DriverInfo
    {
      set
      {
        _driverInfo = value;
        _isNewFolder = value == null;
        if(_isNewFolder)
        {
          _driverInfo = new FileFolderInfo();
        }
        _updateControls();
      }
    }
    private void _updateControls()
    {
      _textFolder.Text = _driverInfo.path;
      _textName.Text = _driverInfo.name;
    }
    private void _updateData()
    {
      _driverInfo.path = _textFolder.Text;
      _driverInfo.name = _textName.Text;
    }
    private bool _validateInputs()
    {
      if(string.IsNullOrEmpty(_textName.Text))
      {
        MessageBox.Show("Name is empty", "Warning", MessageBoxButtons.OK , MessageBoxIcon.Warning );
        _textName.Focus();
        return false;
      }
      if (string.IsNullOrEmpty(_textFolder.Text))
      {
        MessageBox.Show("Folder is empty", "Warning", MessageBoxButtons.OK, MessageBoxIcon.Warning);
        _textFolder.Focus();
        return false;
      }
      if (!System.IO.Directory.Exists(_textFolder.Text))
      {
        MessageBox.Show("Folder not exists.", "Warning", MessageBoxButtons.OK, MessageBoxIcon.Warning);
        _textFolder.Focus();
        return false;
      }
      return true;
    }
    private void _buttonSave_Click(object sender, RoutedEventArgs e)
    {
      if (!_validateInputs()) return;

      _updateData();
      if (_isNewFolder)
      {
        MaxbukServerAdmin.DriverInfoAdd(_driverInfo);
      }
      else
      {
        MaxbukServerAdmin.SaveDriversList();
      }
      this.Close();
    }
    private void _selectFolder()
    {
      using (FolderBrowserDialog dialog = new FolderBrowserDialog())
      {
        dialog.ShowDialog();
        if (string.IsNullOrEmpty(dialog.SelectedPath)) return;

        _driverInfo.path = dialog.SelectedPath;
        _updateControls();
      }
    }

    private void _buttonSelectFolder_Click(object sender, RoutedEventArgs e)
    {
      _selectFolder();
    }
  }
}
