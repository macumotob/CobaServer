using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml;

namespace Maxbuk.Server.Core
{

  public enum TagStatus
  {
    Begin,
    End,
    Complite
  }

  public class Fb2Binary
  {
    public string Id;
    public string Data;
    public string ContentType;
  }

  public class Fb2Tag
  {
    public Fb2Tag(string name,int depth,bool isEmpty)
    {
      this.Name = name;
      this.IsEmpty = isEmpty;
      this.Depth = depth;
      if (this.IsEmpty )
      {
        if (this.Status == TagStatus.Begin)
        {
          this.Status = TagStatus.Complite;
        }
        else
        {
          throw new Exception("erroro");
        }
      }
    }
    public Fb2Tag(string name,string value)
    {
      this.Name = name;
      this.Value = value;
    }
    public Fb2Tag this[string name]
    {
      get
      {
        if (this._Attributes != null && this._Attributes.Count > 0)
        {
          Fb2Tag attr = null;
          this._Attributes.ForEach(a =>
            {
              if (a.Name == name)
              {
                attr = a;
              }
            });
          return attr;
        }
        return null;
      }
    }
    public string Name;
    public string Value;
    public string Tail;
    public bool HasChildren
    {
      get
      {
        return (_Childs != null && _Childs.Count > 0);
      }
    }
    public TagStatus Status = TagStatus.Begin;
    private List<Fb2Tag> _Attributes;
    internal string Attributes2Html()
    {
      if (this._Attributes != null && this._Attributes.Count > 0)
      {
        StringBuilder s = new StringBuilder();
        this._Attributes.ForEach(a =>
          {
            s.AppendFormat(" {0}=\"{1}\" ", a.Name.Replace("l:href","href"), a.Value);
          });
        return s.ToString();

      }
      return string.Empty;
    }
    public List<Fb2Tag> Attributes
    {
      get
      {
        if (_Attributes == null)
        {
          _Attributes = new List<Fb2Tag>();
        }
        return _Attributes; 
      }
      set { _Attributes = value; }
    }
    private List<Fb2Tag> _Childs;

    public List<Fb2Tag> Childs
    {
      get 
      {
        if (this._Childs == null)
        {
          this._Childs = new List<Fb2Tag>();
        }
        return _Childs; 
      }
      set { _Childs = value; }
    }
    public int Depth;
    public bool IsEmpty;

    public override string ToString()
    {
      return string.Format("{2}.{3} {0} : {1} T:{4}", this.Name, this.Value,this.Depth,this.Status,this.Tail);
    }
  }

  public class Fb2Book
  {
    public Fb2Book()
    {
    }
    public Dictionary<string, Fb2Binary> Binaries = new Dictionary<string, Fb2Binary>();
    public List<Fb2Tag> Attributes;
    public Fb2Tag Root;
    private StringBuilder sb = new StringBuilder();

    public string Convert2Html()
    {
      this.Root.Childs.ForEach(t =>
        {
          this.Tag2Html(t);
        });
      return sb.ToString();
    }
    private void Tag2Html(Fb2Tag tag, string open, string close)
    {
      sb.Append(open);
      tag.Childs.ForEach(t =>
      {
        Tag2Html(t);
      });
      sb.Append(close);
    }

    static Dictionary<string, string> dicSimple = new Dictionary<string, string>();
    static Dictionary<string, string[]> dicComplex = new Dictionary<string, string[]>();

    static internal void LoadDictionary(string templ)
    {
      string marker = "<!--CONTAINER";

      int start = templ.IndexOf(marker) + marker.Length;
      int end = templ.IndexOf("-->", start);
      string s = templ.Substring(start, end - start);
      string [] items = s.Split('\n');
      
      items.ForEach(item =>
        {
          if (!string.IsNullOrWhiteSpace(item))
          {
            string[] data = item.Split(':');
            string name = data[0].Trim();
            dicComplex[name] = data[1].Split(',');
            dicComplex[name][0] = dicComplex[name][0].Trim();
            dicComplex[name][1] = dicComplex[name][1].Replace("\r","").Trim();
          }
          else
          {
          }
        });
      marker = "<!--SIMPLE";

      start = templ.IndexOf(marker , end+3) + marker.Length;
      end = templ.IndexOf("-->", start);
      s = templ.Substring(start, end - start);

      items = s.Split('\n');
      items.ForEach(item =>
      {
        if (!string.IsNullOrWhiteSpace(item))
        {
          int i = item.IndexOf(':');
          string name = item.Substring(0,i).Trim();
          dicSimple[name] = item.Substring(i+1).Replace("\r", "").Trim();
        }
        else
        {
        }
      });

    }

    private void Tag2Html(Fb2Tag tag)
    {
      string name = tag.Name;
      if(tag.Tail != null)
      {
      }

      if (tag.HasChildren)
      {
        if (dicComplex.ContainsKey (tag.Name))
        {
          //string s = (tag.Value + tag.Tail).Replace(" ","&nbsp;");
          string s = tag.Value + tag.Tail;
          string begin = string.Format(dicComplex[tag.Name][0], tag.Name, s, tag.Attributes2Html());
          Tag2Html(tag, begin, dicComplex[tag.Name][1]);
          return;
        }
        throw new Exception("todo complex tag");
      }
      if (dicSimple.ContainsKey(tag.Name))
      {
        string format = dicSimple[tag.Name];
        //string s = (tag.Value + tag.Tail).Replace(" ","&nbsp;");;
        string s = tag.Value + tag.Tail;
        if (tag.Name == "p" && !string.IsNullOrEmpty(s))
        {
          s = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + s;
        }
        if (tag.Name == "strong")
        {
          sb.AppendFormat(format, tag.Name,tag.Value,tag.Attributes2Html(),tag.Tail);
        }
        else
        {
          if (tag.Name == "image")
          {
            string id = tag.Attributes[0].Value.Substring(1);
            string base64 = this.Binaries[id].Data;
            sb.AppendFormat(format, tag.Name, this.Binaries[id].ContentType, base64);
          }
          else
          {
            sb.AppendFormat(format, tag.Name, s, tag.Attributes2Html());
          }
        }
        return;
      }
      throw new Exception("todo simple tag");
    }

  }

  public class Fb2Reader
  {

    static private void ReadChilds(Fb2Tag parent, ref List<Fb2Tag> list)
    {
      Stack<Fb2Tag> stack = new Stack<Fb2Tag>();
      parent = list[0];
      int i = 0;
      while( i + 1 < list.Count)
      {
        Fb2Tag curr = list[i];
        Fb2Tag next = list[i + 1];
        if (curr.Status == TagStatus.Begin  && next.Status == TagStatus.End && curr.Name == next.Name)
        {
          curr.Status = TagStatus.Complite;
          if(!string.IsNullOrEmpty(next.Tail ))
          {
            curr.Tail += next.Tail;
          }
          list.RemoveAt(i + 1);
          continue;
        }
        i++;
      }

      while (list.Count > 0)
      {
        Fb2Tag child = list[0];
        if (child.Status == TagStatus.Begin)
        {
          stack.Push(child);
          list.RemoveAt(0);
          continue;
        }
        Fb2Tag top = stack.Count == 0 ? null : stack.Peek();
        if (child.Status == TagStatus.Complite)
        {
          top.Childs.Add(child);
          list.RemoveAt(0);
          continue;
        }
        if (child.Status == TagStatus.End && child.Name == top.Name)
        {
          top.Status = TagStatus.Complite;
          if (!string.IsNullOrEmpty(child.Value))
          {
          }
          top.Tail += (child.Tail == null ? "" : " CH_Tail: " + child.Tail);
          list.RemoveAt(0);
          child = stack.Pop();
          if (stack.Count == 0)
          {
            return;
          }
          top = stack.Peek();
          top.Childs.Add(child);
          continue;
        }
        throw new Exception("Invalide fb2 format!");
      }
      }
  

    static public Fb2Tag ReadBook(string file)
    {
      List<Fb2Tag> list = Read(file);
      Fb2Tag root = list[0];
      ReadChilds(root, ref list);
      return root;
    }
    static public string Convert2Html(string file)
    {
      string templ= Properties.Resources.Fb2Template;

     // string templ = System.IO.File.ReadAllText(templFile,Encoding.UTF8);
      Fb2Book.LoadDictionary(templ);

      Fb2Book book = new Fb2Book();
      book.Root = ReadBook(file);

      book.Attributes = book.Root.Attributes;

      int i = 0;
      while (i < book.Root.Childs.Count)
      {
        Fb2Tag child = book.Root.Childs[i];
        switch (child.Name)
        {
          case "body":
          case "stylesheet":
          case "description":
          case "document-info":
            i++;
            break;
          case "binary":
            Fb2Binary bin = new Fb2Binary();
            bin.Id = child["id"].Value;
            bin.ContentType = child["content-type"].Value;
            bin.Data = child.Value;
            book.Binaries[bin.Id] = bin;
            book.Root.Childs.RemoveAt(i);
            continue;
          default:
            throw new Exception("invalide fb2 format!");
        }
      }

      string s = book.Convert2Html();

      templ = templ.Replace("##file_name##", file);
      templ = templ.Replace("##body##", s);
      return templ;
    }
    static public List<Fb2Tag> Read(string file)
    {

      XmlReaderSettings rs = new XmlReaderSettings();
      rs.IgnoreComments = true;
      rs.IgnoreProcessingInstructions = true;
      //rs.IgnoreWhitespace = true;
      rs.CheckCharacters = false;

      List<Fb2Tag> list = new List<Fb2Tag>();
      Fb2Tag tag = null;

      using (XmlReader r = XmlReader.Create(file,rs))
      {
        while (r.Read())
        {
          string name = r.Name;
          string value = r.Value;
          if (name == "strong")
          {
          }
          switch (r.NodeType)
          {
            case XmlNodeType.Element:
              tag = new Fb2Tag(name,r.Depth,r.IsEmptyElement);
              if (r.AttributeCount > 0)
              {
                for (int i = 0; i < r.AttributeCount; i++)
                {
                  r.MoveToAttribute(i);
                  tag.Attributes.Add(new Fb2Tag(r.Name,r.Value));
                }
              }
              
              list.Add(tag);
              break;
            case XmlNodeType.EndElement:
              tag = new Fb2Tag(name,r.Depth,r.IsEmptyElement);
              tag.Status = TagStatus.End;
              //if (tag.Depth == end.Depth && end.Name == tag.Name)
              //{
              //  tag.Status = TagStatus.Complite;
              //}
              //else
              //{
              //}
              list.Add(tag);
              //tag = end;
              break;
            case XmlNodeType.Text:
              //Fb2Tag txt = new Fb2Tag("#text",r.Depth,r.IsEmptyElement);
              //txt.Status = TagStatus.Complite;
              //txt.Value = value;
              //list.Add(txt);
              if (tag.Status == TagStatus.Begin)
              {
                tag.Value = value;
              }
              else if (tag.Status == TagStatus.End)
              {
                tag.Tail  = value;
              }
              else if (tag.Status == TagStatus.Complite)
              {
                tag.Value = value;
              }
              else
              {
              }
              break;
            case XmlNodeType.Whitespace:
              if (tag != null)
              {
                if (tag.Status == TagStatus.Begin)
                {
                  if (tag.Value == null)
                  {
                    tag.Value = value;
                  }
                  else
                  {
                    tag.Value += value;
                  }
                }
                else if (tag.Status == TagStatus.End)
                {
                  if (tag.Value == null)
                  {
                    tag.Value = value;
                  }
                  else
                  {
                    tag.Value += value;
                  }
                }
                else
                {
                  if (tag.Value == null)
                  {
                    tag.Value = value;
                  }
                  else
                  {
                    tag.Value += value;
                  }
                }
              }
              break;
            case XmlNodeType.XmlDeclaration:
              break;
            case XmlNodeType.Attribute:
            case XmlNodeType.CDATA:
            case XmlNodeType.Comment:
            case XmlNodeType.Document:
            case XmlNodeType.DocumentFragment:
            case XmlNodeType.DocumentType:
            case XmlNodeType.EndEntity:
            case XmlNodeType.Entity:
            case XmlNodeType.EntityReference:
            case XmlNodeType.None:
            case XmlNodeType.Notation:
            case XmlNodeType.ProcessingInstruction:
            case XmlNodeType.SignificantWhitespace:
            default:
              throw new Exception("??");
          }
        }
      }
      return list;
    }
  }
}
