using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Maxbuk.Server.Core
{
  public class MaxbukDriverInfo
  {
    public string name { get; set; }


    private string _path;
    public string path
    {
      get
      {
        return _path;
      }
      set
      {
        _path = value;
        if (_path != null)
        {
          _path = _path.Replace('\\', '/');
          if (!(_path[_path.Length - 1] == '\\' || _path[_path.Length - 1] == '/'))
          {
            _path += '/';
          }
        }
      }
    }
  }
}
