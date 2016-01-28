using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml;
using System.Data;
using System.Reflection;
using System.Web.Script.Serialization;
using System.Xml;
using System.Xml.Serialization;


namespace Maxbuk.Server.Core
{
  
  using System.IO;

  public static class Extender
  {

    static public string ToJson(this object x)
    {
      JavaScriptSerializer serializer = new JavaScriptSerializer();
      return serializer.Serialize(x);
    }

    /*ww
    static public string SerializeObject<T>(this T toSerialize)
    {
      XmlSerializer xmlSerializer = new XmlSerializer(typeof(T));
      StringWriter textWriter = new StringWriter();

      // And now...this will throw and Exception!
      // Changing new XmlSerializer(typeof(T)) to new XmlSerializer(subInstance.GetType()); 
      // solves the problem
      xmlSerializer.Serialize(textWriter, toSerialize);
      return textWriter.ToString();
    }
    */
    public delegate void EventForEachItem<T>(T item);
    public delegate void EventForEachItemCheckLast<T>(T item,bool last);

    public delegate void EventForEachDictionaryItem<T>(string name,T item);
    public delegate bool CompareExtender<T> (string name, T x);

    public delegate void HandlerForEachRow(DataRow row); 
    public static void ForEach(this DataTable tb, HandlerForEachRow handler)
    {
      for (int i = 0; i < tb.Rows.Count; i++)
      {
        DataRow row = tb.Rows[i];
        handler(row);
      }
    }

    public static Dictionary<string, object> ToParams(this object x)
    {
      Dictionary<string, object> prms = new Dictionary<string, object>();

      Type t = x.GetType();
      PropertyInfo [] props = t.GetProperties();
      props.ForEach(p =>
        {
          prms[p.Name] = p.GetValue(x, null);
        });


      return prms;
    }
    public static string ToUrlParams(this object x)
    {
      StringBuilder sb = new StringBuilder("?");
      Type t = x.GetType();
      PropertyInfo[] props = t.GetProperties();

      int i = 0;
      props.ForEach(p =>
      {
        object value = p.GetValue(x, null);
        string s;
        if (value != null && value is bool)
        {
          s = value.ToString().ToLower();
        }
        else
        {
          s = (value == null ? null : value.ToString());
        }
        sb.AppendFormat("{2}{0}={1}", p.Name, s, (i == 0 ? "" : "&"));
        i++;
      });
      return sb.ToString();
    }
    public static void FromParams(this object x, Dictionary<string, object> prms)
    {
      Type t = x.GetType();
      PropertyInfo[] props = t.GetProperties();
      props.ForEach(p =>
      {
        string name = p.Name.ToUpper();
        if (p.PropertyType.Name == "Boolean")
        {
          bool value = bool.Parse(prms[name].ToString());
          p.SetValue(x, value, null);
        }
        else
        {
          p.SetValue(x, prms[name], null);
        }
      });
    }
    public static void ForEach<T>(this Dictionary<string, T> items, EventForEachDictionaryItem<T> handler)
    {
      foreach (string key in items.Keys)
      {
        handler(key,items[key]);
      }
    }
    /*
    public static T Find<T>(this Dictionary<string, T> items,string name, CompareExtender<T> compare )
    {
      foreach (string key in items.Keys)
      {
        if (compare(name , items[key] ))
        {
          return items[key];
        }
      }
      return default(T);
    }
    */
    static public void ForEach<T>(this List<T> items, EventForEachItem<T> handler)
    {
      for (int i = 0; i < items.Count; i++)
      {
        handler(items[i]);
      }
    }
    static public void ForEach<T>(this List<T> items, EventForEachItemCheckLast<T> handler)
    {
      
      for (int i = 0; items != null && i < items.Count; i++)
      {
        handler(items[i], i == items.Count - 1);
      }
    }
    static public void ForEach<T>(this T[] items, EventForEachItem<T> handler)
    {
      for (int i = 0; items != null && i < items.Length; i++)
      {
        handler(items[i]);
      }
    }
    static public void ForEach<T>(this T[] items, EventForEachItemCheckLast<T> handler)
    {
      for (int i = 0; items != null && i < items.Length; i++)
      {
        handler(items[i], i == items.Length - 1);
      }
    }

/*
    static public string JSon(this string[] values)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("data = {");
      if (values != null)
      {
        for (int i = 0; i < values.Length; i += 2)
        {
          sb.AppendFormat("{0} : {1}", values[i], (values[i + 1] is string ? "'" + values[i + 1] + "'" : values[i + 1]));
          if (i < values.Length - 2)
          {
            sb.Append(',');
          }
        }
      }
      sb.Append('}');
      return sb.ToString();
    }
    static public string JSon(this Dictionary<string, object> prms)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("data = {");
      int i = 0;
      foreach (string key in prms.Keys)
      {
        sb.AppendFormat("{0} : {1}", key.ToLower(), (prms[key] is string ? "'" + prms[key].ToString() + "'" : prms[key]));
        if (i < prms.Count - 1)
        {
          sb.Append(',');
        }
        i++;
      }
      sb.Append('}');
      return sb.ToString();
    }
 */
    static public string Xml(this Dictionary<string, object> prms)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
      sb.Append("<data>");
      foreach (string key in prms.Keys)
      {
          sb.AppendFormat("<{0}>{1}</{0}>", key, 
            (prms[key] == null ? "" : prms[key].ToString()));
      }
      sb.Append("</data>");
      return sb.ToString();
    }
    static public string XmlWithCData(this Dictionary<string, object> prms)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
      sb.Append("<data>");
      foreach (string key in prms.Keys)
      {
        sb.AppendFormat("<{0}><![CDATA[{1}]]></{0}>", key, 
          (prms[key] == null ? "" : prms[key].ToString()));
      }
      sb.Append("</data>");
      return sb.ToString();
    }

    static public string Xml(this string[] values)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
      sb.Append("<data>");
      if (values != null)
      {
        for (int i = 0; i < values.Length; i += 2)
        {
          sb.AppendFormat("<{0}>{1}</{0}>", values[i], values[i + 1]);
        }
      }
      sb.Append("</data>");
      return sb.ToString();
    }
    static public string XmlWithCData(this string[] values)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
      sb.Append("<data>");
      if (values != null)
      {
        for (int i = 0; i < values.Length; i += 2)
        {
          sb.AppendFormat("<{0}><![CDATA[{1}]]></{0}>", values[i], values[i + 1]);
        }
      }
      sb.Append("</data>");
      return sb.ToString();
    }
    /*ww
    static public string Xml(this BaybakData data)
    {
      StringBuilder sb = new StringBuilder();
      sb.Append("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
      sb.AppendFormat("<{0}>",data.Name);

      sb.AppendFormat("<columns count =\"{0}\">", data.ColumnsCount);
      for (int col = 0; col < data.ColumnsCount; col++)
      {
        sb.AppendFormat("<{0}><![CDATA[{1}]]></{0}>", "column", data.Columns[col]);
      }
      sb.Append("</columns>");
      sb.AppendFormat("<rows count =\"{0}\">", data.RowCount);
      for (int row = 0; row < data.RowCount; row++)
      {
        sb.Append("<row>");
        for (int col = 0; col < data.ColumnsCount; col++)
        {
          object value = data[row, col];
          sb.AppendFormat("<{0}><![CDATA[{1}]]></{0}>", data.Columns[col],
            (value == null ? "" : value.ToString()));
        }
        sb.Append("</row>");
      }
      sb.Append("</rows>");
      sb.AppendFormat("</{0}>", data.Name);
      return sb.ToString();

    }
    */
    static public int IndexOf(this string[] values,string name)
    {
      for (int i = 0; i < values.Length; i++)
      {
        if (string.Compare(values[i], name, true) == 0)
        {
          return i;
        }
      }
      return -1;
    }
    static public int EndOfHeader(this byte[] data)
    {
      int i = 0;
      while (i < data.Length)
      {
        if (data[i] == '\r' && data[i + 1] == '\n' && data[i + 2] == '\r' && data[i + 3] == '\n')
        {
          return i + 4;
        }
        i++;
      }
      return -1;
    }

  }
}
