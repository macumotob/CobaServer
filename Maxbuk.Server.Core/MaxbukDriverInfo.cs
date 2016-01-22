using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Maxbuk.Server.Core
{
  public class MaxbukDriverInfo
  {
    public string Name { get; set; }


    private string _Folder;
    public string Folder
    {
      get
      {
        return _Folder;
      }
      set
      {
        _Folder = value;
        if (_Folder != null)
        {
          _Folder = _Folder.Replace('\\', '/');
          if (!(_Folder[_Folder.Length - 1] == '\\' || _Folder[_Folder.Length - 1] == '/'))
          {
            _Folder += '/';
          }
        }
      }
    }
  }
}
