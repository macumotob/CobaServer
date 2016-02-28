using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Maxbuk.Server.Core
{
  public class JsonResult
  {
    public string result { get; set; }
    public string msg { get; set; }
    public bool IsOk
    {
      get
      {
        return this.result == "ok" ? true : false;
      }
    }
    public override string ToString()
    {
      return string.Format("{'result':{0},'msg':'{1}'}", result, msg); 
    }
  }

}
