using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Maxbuk.Server.Core
{
  public class MaxbukJsonResult
  {
    public string result { get; set; }
    public string data { get; set; }
    public bool IsOk
    {
      get
      {
        return this.result == "ok" ? true : false;
      }
    }
  }

}
