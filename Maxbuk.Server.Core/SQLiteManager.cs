﻿using System;
using System.Collections.Generic;
using System.Data;
using System.Data.SQLite;
using System.IO;
using System.Linq;


namespace Maxbuk.Server.Core
{
  public class SQLiteManager
  {
    SQLiteConnection _sqliteCon;
    SQLiteReader _sqlitereader;
    SQLiteWriter _sqlitewriter;
    public string RootDirectory;
    string _filesource;

    private static SQLiteManager _SQLiteManager;

    protected SQLiteManager()
    {
      _sqlitereader = new SQLiteReader();
      _sqlitewriter = new SQLiteWriter();
    }

    public static SQLiteManager Instance
    {
      get
      {
        if (_SQLiteManager == null) _SQLiteManager = new SQLiteManager();
        return _SQLiteManager;
      }
    }


    private string _createprojectdb = @"
CREATE TABLE ZBOOKSPARAMS ( 
    Z_PK      INTEGER PRIMARY KEY,
    Z_ENT     INTEGER,
    Z_OPT     INTEGER,
    ZSEQUENCE INTEGER 
);

CREATE TABLE ZLOCALBOOKINFO ( 
    Z_PK     INTEGER PRIMARY KEY,
    Z_ENT    INTEGER,
    Z_OPT    INTEGER,
    ZEXTID   INTEGER,
    ZLOCALE  VARCHAR,
    ZNAME    VARCHAR,
    ZUID     VARCHAR,
    ZVERSION VARCHAR 
);

CREATE TABLE Z_METADATA ( 
    Z_VERSION INTEGER         PRIMARY KEY,
    Z_UUID    VARCHAR( 255 ),
    Z_PLIST   BLOB 
);

CREATE TABLE Z_PRIMARYKEY ( 
    Z_ENT   INTEGER PRIMARY KEY,
    Z_NAME  VARCHAR,
    Z_SUPER INTEGER,
    Z_MAX   INTEGER 
);
";

    private string _createbookdb = @"
CREATE TABLE ZAUTHOR ( 
    Z_PK                  INTEGER PRIMARY KEY,
    Z_ENT                 INTEGER,
    Z_OPT                 INTEGER,
    ZAUTHOR_BOOKLOCALIZED INTEGER,
    ZEMAIL                VARCHAR,
    ZFIRSTNAME            VARCHAR,
    ZLASTNAME             VARCHAR,
    ZUSERNAME             VARCHAR 
);

CREATE TABLE ZBOOK ( 
    Z_PK            INTEGER PRIMARY KEY,
    Z_ENT           INTEGER,
    Z_OPT           INTEGER,
    ZREADONLY       INTEGER,
    ZDEFAULT_LOCALE VARCHAR,
    ZEXTID          VARCHAR,
    ZUID            VARCHAR 
);

CREATE TABLE ZBOOKLOCALIZED ( 
    Z_PK                         INTEGER   PRIMARY KEY,
    Z_ENT                        INTEGER,
    Z_OPT                        INTEGER,
    ZBOOKLOCALIZED_AUTHOR        INTEGER,
    ZBOOKLOCALIZED_BOOKVERSIONED INTEGER,
    ZBOOKLOCALIZED_THEME         INTEGER,
    ZCDATE                       TIMESTAMP,
    ZLOCALE                      VARCHAR,
    ZNAME                        VARCHAR,
    ZUSERPREFS                   VARCHAR 
);

CREATE TABLE ZBOOKVERSIONED ( 
    Z_PK                INTEGER PRIMARY KEY,
    Z_ENT               INTEGER,
    Z_OPT               INTEGER,
    ZBOOKVERSIONED_BOOK INTEGER,
    ZVERSION            VARCHAR 
);

CREATE TABLE ZCHAPTER ( 
    Z_PK                   INTEGER PRIMARY KEY,
    Z_ENT                  INTEGER,
    Z_OPT                  INTEGER,
    ZEXTID                 INTEGER,
    ZNUM                   INTEGER,
    ZCHAPTER_PAGE          INTEGER,
    ZCHAPTER_PARENTCHAPTER INTEGER,
    ZNAME                  VARCHAR,
    ZUID                   VARCHAR 
);

CREATE TABLE ZDECORATION ( 
    Z_PK                   INTEGER PRIMARY KEY,
    Z_ENT                  INTEGER,
    Z_OPT                  INTEGER,
    ZTYPE                  INTEGER,
    ZDECORATION_OBJECT     INTEGER,
    ZDECORATION_PAGEOBJECT INTEGER 
);

CREATE TABLE ZGLOSSARY ( 
    Z_PK                    INTEGER PRIMARY KEY,
    Z_ENT                   INTEGER,
    Z_OPT                   INTEGER,
    ZEXTID                  INTEGER,
    ZGLOSSARY_BOOKLOCALIZED INTEGER,
    ZUID                    VARCHAR,
    ZWORD                   VARCHAR,
    ZWORD_DESCRIPTION       VARCHAR 
);

CREATE TABLE ZOBJECT ( 
    Z_PK           INTEGER   PRIMARY KEY,
    Z_ENT          INTEGER,
    Z_OPT          INTEGER,
    ZEXTID         INTEGER,
    ZTYPE          INTEGER,
    ZOBJECT_AUTHOR INTEGER,
    ZCDATE         TIMESTAMP,
    ZDATA          VARCHAR,
    ZEXTURL        VARCHAR,
    ZFILENAME      VARCHAR,
    ZNAME          VARCHAR,
    ZSYSTEMLIBID   VARCHAR,
    ZTEMPUID       VARCHAR,
    ZUID           VARCHAR 
);

CREATE TABLE ZPAGE ( 
    Z_PK                INTEGER PRIMARY KEY,
    Z_ENT               INTEGER,
    Z_OPT               INTEGER,
    ZEXTID              INTEGER,
    ZNUM                INTEGER,
    ZPAGE_BOOKLOCALIZED INTEGER,
    ZPAGE_TEMPLATE      INTEGER,
    ZUID                VARCHAR 
);

CREATE TABLE ZPAGEOBJECT ( 
    Z_PK                         INTEGER PRIMARY KEY,
    Z_ENT                        INTEGER,
    Z_OPT                        INTEGER,
    ZEXTID                       INTEGER,
    ZPARENTPAGEOBJECTEXTID       INTEGER,
    ZPAGEOBJECT_OBJECT           INTEGER,
    ZPAGEOBJECT_PAGE             INTEGER,
    ZPAGEOBJECT_PARENTPAGEOBJECT INTEGER,
    ZPAGEOBJECT_TEMPLATE         INTEGER,
    ZDATA                        VARCHAR,
    ZNAME                        VARCHAR,
    ZPARENTPAGEOBJECTUID         VARCHAR,
    ZUID                         VARCHAR 
);

CREATE TABLE ZTEMPLATE ( 
    Z_PK            INTEGER PRIMARY KEY,
    Z_ENT           INTEGER,
    Z_OPT           INTEGER,
    ZEXTID          INTEGER,
    ZTEMPLATE_THEME INTEGER,
    ZUID            VARCHAR 
);

CREATE TABLE ZTHEME ( 
    Z_PK                 INTEGER PRIMARY KEY,
    Z_ENT                INTEGER,
    Z_OPT                INTEGER,
    ZTHEME_BOOKLOCALIZED INTEGER 
);

CREATE TABLE Z_METADATA ( 
    Z_VERSION INTEGER         PRIMARY KEY,
    Z_UUID    VARCHAR( 255 ),
    Z_PLIST   BLOB 
);

CREATE TABLE Z_PRIMARYKEY ( 
    Z_ENT   INTEGER PRIMARY KEY,
    Z_NAME  VARCHAR,
    Z_SUPER INTEGER,
    Z_MAX   INTEGER 
);

CREATE TABLE ZANIMATEDOBJECT ( 
    Z_PK                       INTEGER PRIMARY KEY,
    Z_ENT                      INTEGER,
    Z_OPT                      INTEGER,
    ZANIMATEDOBJECT_ANIMATION  INTEGER,
    ZANIMATEDOBJECT_PAGEOBJECT INTEGER 
);

CREATE TABLE ZANIMATION ( 
    Z_PK            INTEGER PRIMARY KEY,
    Z_ENT           INTEGER,
    Z_OPT           INTEGER,
    ZANIMATION_PAGE INTEGER,
    ZNAME           VARCHAR 
);

CREATE TABLE ZANIMATIONITEM ( 
    Z_PK                             INTEGER PRIMARY KEY,
    Z_ENT                            INTEGER,
    Z_OPT                            INTEGER,
    ZANIMATIONITEM_ANIMATIONSEQUENCE INTEGER,
    ZDATA                            VARCHAR 
);

CREATE TABLE ZANIMATIONSEQUENCE ( 
    Z_PK                              INTEGER PRIMARY KEY,
    Z_ENT                             INTEGER,
    Z_OPT                             INTEGER,
    ZTYPE                             INTEGER,
    ZANIMATIONSEQUENCE_ANIMATEDOBJECT INTEGER 
);
";

    public void CreateProjectDb(string filePath)
    {
      CreateDb(filePath, _createprojectdb);
    }

    public void CreateBookDb(string filePath)
    {
      CreateDb(filePath, _createbookdb);
    }

    public void CreateDb(string filePath, string commandText)
    {
      SQLiteConnection.CreateFile(filePath);

      var connect = GetConnect(filePath);
      using (connect)
      {
        try
        {
          connect.Open();
          SQLiteCommand createDataBase = connect.CreateCommand();
          createDataBase.CommandText = commandText;
          createDataBase.ExecuteNonQuery();
        }
        catch (Exception)
        {
          throw;
        }
        finally
        {
          connect.Close();
        }
      }
    }



    private SQLiteConnection GetConnect(string dataBaseFile)
    {
      SQLiteConnection connection = new SQLiteConnection();
      SQLiteConnectionStringBuilder cs = new SQLiteConnectionStringBuilder();
      cs.DataSource = dataBaseFile;
      connection.ConnectionString = cs.ToString();
      return connection;
    }
    /*
        public List<int> GetSequenceIdsProject(int count=1 )
        {
            List<int> list = new List<int>();
            var connect = GetConnect(FileUtils.Instance.GetProjectDbPath());
            using (connect)
            {
                try
                {
                    connect.Open();

                    string sql = "Select * From ZBooksparams";
                    var id = _sqlitereader.ExecuteOneReader(sql, "ZSEQUENCE", connect);
                    if (id == null)
                    {
                        sql = "Insert into ZBooksparams (Z_ENT,Z_OPT,ZSEQUENCE) values(1,1,1)";
                        _sqlitewriter.ExecuteQuery(sql, connect);
                    }
                    else
                    {
                        int seq = Convert.ToInt32(id);
                        for (int i = 0; i < count; i++)
                        {
                            seq++;
                            list.Add(seq);
                        }
                        sql = string.Format("Update ZBooksparams set Z_OPT={0},ZSEQUENCE={0}", seq);
                        _sqlitewriter.ExecuteQuery(sql, connect);

                    }
                }
                catch (Exception)
                {
                    throw;
                }
                finally
                {
                    connect.Close();
                }
            }
            return list;
        }
    
        public int GetSequenceIdProject(bool isUpdate=true)
        {
            int idBook = 1;
            var connect = GetConnect(FileUtils.Instance.GetProjectDbPath());
            using (connect)
            {
                try
                {
                    connect.Open();

                    string sql = "Select * From ZBooksparams";
                    var id = _sqlitereader.ExecuteOneReader(sql, "ZSEQUENCE", connect);
                    if (id == null)
                    {
                        sql = "Insert into ZBooksparams (Z_ENT,Z_OPT,ZSEQUENCE) values(1,1,1)";
                        _sqlitewriter.ExecuteQuery(sql, connect);
                    }
                    else
                    {
                        int seq = Convert.ToInt32(id);
                        seq++;
                        if (isUpdate)
                        {
                            sql = string.Format("Update ZBooksparams set Z_OPT={0},ZSEQUENCE={0}", seq);
                            _sqlitewriter.ExecuteQuery(sql, connect);    
                        }
                        idBook = seq;
                    }
                }
                catch (Exception)
                {
                    throw;
                }
                finally
                {
                    connect.Close();
                }
            }
            return idBook;
        }
    
        public void AddRecordToBookInfo(Book book)
        {
            var connect = GetConnect(FileUtils.Instance.GetProjectDbPath());
            using (connect)
            {
                try
                {
                    connect.Open();

                    string sql =
                        string.Format("Insert into ZLOCALBOOKINFO " +
                                      "(Z_ENT,Z_OPT,ZEXTID,ZLOCALE,ZNAME,ZUID,ZVERSION) " +
                                      "values(2,1,0,\"{0}\",\"{1}\",\"{2}\",\"{3}\")", book.Locale, book.Title, book.Uid, book.Version);
                    _sqlitewriter.ExecuteQuery(sql, connect);
                }
                catch (Exception)
                {
                    throw;
                }
                finally
                {
                    connect.Close();
                }
            }
        }
        

        public Dictionary<string, object> GetBookInfo(string bookUid)
        {
            Dictionary<string, object> dic = new Dictionary<string, object>();
            var connect = GetConnect(FileUtils.Instance.GetProjectDbPath());
            using (connect)
            {
                try
                {
                    connect.Open();

                    string sql = "Select ZNAME as Title,ZLOCALE as Locale,ZVERSION as Version From ZLOCALBOOKINFO where ZUID='"+bookUid+"'";
                    DataTable  tb = _sqlitereader.GeTable(sql, connect);
                    if (tb.Rows.Count > 0)
                    {
                       dic = RowToDictionary(tb.Rows[0]);
                    }
                }
                catch (Exception)
                {
                    throw;
                }
                finally
                {
                    connect.Close();
                }
            }
            return dic;
        }
        */
    private Dictionary<string, object> RowToDictionary(DataRow row)
    {
      Dictionary<string, object> dic = new Dictionary<string, object>();
      for (int i = 0; i < row.Table.Columns.Count; i++)
      {
        string name = row.Table.Columns[i].ColumnName;
        dic.Add(name, row[name]);
      }
      return dic;
    }
    /*
        public DataTable GetBooksInfo()
        {
            DataTable tb = new DataTable();
            var connect = GetConnect(FileUtils.Instance.GetProjectDbPath());
            using (connect)
            {
                try
                {
                    connect.Open();

                    string sql = "Select * From ZLOCALBOOKINFO";
                    tb =_sqlitereader.GeTable(sql, connect);

                }
                catch (Exception)
                {
                    throw;
                }
                finally
                {
                    connect.Close();
                }
            }
            return tb;
        }
        */

    public void NewFile(string filePath)
    {
      this.FileSource = filePath;
      SetConnection(this.FileSource);
      CreateFile(this.FileSource);
      Open();
    }

    public string FileSource
    {
      get { return _filesource; }
      internal set { _filesource = value; }
    }

    public void OpenFile(string filesource)
    {
      if (_sqliteCon != null) _sqliteCon.Close();
      this.FileSource = filesource;
      SetConnection(this.FileSource);
      Open();

    }

    private void CreateFile(string filesource)
    {
      try
      {
        if (File.Exists(filesource))
        {
          File.Delete(filesource);
          System.Threading.Thread.Sleep(2000);
        }
        SQLiteConnection.CreateFile(filesource);
      }
      catch { }
    }



    public void Connect()
    {
      if (_sqliteCon != null) _sqliteCon.Open();
    }

    public bool IsOpen
    {
      get
      {
        if (_sqliteCon == null) return false;
        return (System.Data.ConnectionState.Open == _sqliteCon.State);
      }
    }

    public void Open()
    {
      if (_sqliteCon != null)
      {
        _sqliteCon.Open();
      }
    }

    public void Close()
    {
      if (_sqliteCon != null)
      {
        _sqliteCon.Close();
      }

    }

    private void SetConnection(string filesource)
    {
      _sqliteCon = new SQLiteConnection("Data Source=" + filesource + ";Version=3;");
    }


    private void TablesInit()
    {
    }



    //~SQLiteManager()
    //{
    //    try { Close(); }
    //    catch { }
    //}  
    /*
    public List<Dictionary<string, object>> Select(Book book, string sql)
    {
        return Select(book, sql, new Dictionary<string, object>());
    }

    public List<Dictionary<string, object>> Select(Book book, string sql, Dictionary<string, object> prms)
    {
        List<Dictionary<string, object>> listSelect = new List<Dictionary<string, object>>();

        DataTable tb = ExecuteGeTable(GetConnect(FileUtils.Instance.GetBookFileDb(book.Uid)), sql, prms);
        if (tb != null)
        {
            for (int i = 0; i < tb.Rows.Count; i++)
            {
                var dic = RowToDictionary(tb.Rows[i]);
                listSelect.Add(dic);
            }
        }
        return listSelect;
    }

    public void Execute(Book book, string sql, Dictionary<string, object> prms)
    {
        var connect = GetConnect(FileUtils.Instance.GetBookFileDb(book.Uid));
        Execute(connect, sql, prms);
    }

    public void Insert(Book book, List<Dictionary<string, object>> listDicInsert)
    {
        if (listDicInsert.Count == 0)
        {
            return;
        }
        string sql = GetSqlInsert(listDicInsert[0]);
        SQLiteConnection connect = GetConnect(FileUtils.Instance.GetBookFileDb(book.Uid));
        Execute(connect, sql, listDicInsert);
    }

    public void Insert(Book book, Dictionary<string, object> dicInsert)
    {
        string sql = GetSqlInsert(dicInsert);
        SQLiteConnection connect = GetConnect(FileUtils.Instance.GetBookFileDb(book.Uid));
        Execute(connect, sql, dicInsert);
    }
*/
    private string GetSqlInsert(Dictionary<string, object> dicInsert)
    {
      string tableName = dicInsert["TableName"].ToString();
      dicInsert.Remove("TableName");
      string sql = "Insert into " + tableName;

      string insertFields = " (";
      string insertValues = " values(";
      for (int i = 0; i < dicInsert.Keys.Count; i++)
      {
        if (i > 0)
        {
          insertFields += ",";
          insertValues += ",";
        }
        insertFields += dicInsert.Keys.ElementAt(i);
        insertValues += ":" + dicInsert.Keys.ElementAt(i);

      }
      insertFields += ")";
      insertValues += ")";
      sql += insertFields + insertValues;
      return sql;
    }
    /*
        public int GetIdToInsert(Book book, Dictionary<string, object> dicInsert)
        {
            string sql = GetSqlInsert(dicInsert);
            SQLiteConnection connect = GetConnect(FileUtils.Instance.GetBookFileDb(book.Uid));
            return GetIdExecuteInsert(connect, sql, dicInsert);
        }
        */
    private int GetIdExecuteInsert(SQLiteConnection connect, string sql, Dictionary<string, object> prms)
    {
      int id = -1;
      using (connect)
      {
        try
        {
          connect.Open();
          _sqlitewriter.ExecuteQuery(sql, connect, prms);
          id = _sqlitereader.GetInsertLastRowId(connect);
        }
        catch (Exception)
        {
          throw;
        }
        finally
        {
          connect.Close();
        }
      }
      return id;
    }

    private void Execute(SQLiteConnection connect, string sql, Dictionary<string, object> prms)
    {
      using (connect)
      {
        try
        {
          connect.Open();
          _sqlitewriter.ExecuteQuery(sql, connect, prms);
        }
        catch (Exception)
        {
          throw;
        }
        finally
        {
          connect.Close();
        }
      }
    }

    private void Execute(SQLiteConnection connect, string sql, List<Dictionary<string, object>> prms)
    {
      using (connect)
      {
        try
        {
          connect.Open();
          _sqlitewriter.ExecuteQuery(sql, connect, prms);
        }
        catch (Exception)
        {
          throw;
        }
        finally
        {
          connect.Close();
        }
      }
    }

    public DataTable ExecuteGeTable(SQLiteConnection connect, string sql, Dictionary<string, object> prms)
    {
      DataTable tb = null;
      using (connect)
      {
        try
        {
          connect.Open();
          tb = _sqlitereader.GeTable(sql, connect, prms);
        }
        catch (Exception)
        {
          throw;
        }
        finally
        {
          connect.Close();
        }
      }
      return tb;
    }
    //ww
    private void Execute(string sql, Dictionary<string, object> prms)
    {
      try
      {
        _sqlitewriter.ExecuteQuery(sql, _sqliteCon, prms);
      }
      catch (Exception)
      {
        throw;
      }
      finally
      {

      }
    }
    public void Execute(Dictionary<string, string> command)
    {
      if (command.ContainsKey("db"))
      {
        string dbname = RootDirectory + command["db"];
        if (System.IO.File.Exists(dbname))
        {
          this.OpenFile(dbname);
        }
        else
        {
          this.NewFile(dbname);
        }
        
      }
      if (command.ContainsKey("exec"))
      {
        string sql = command["exec"];
        this.Execute(sql, null);

      }
    }

  }

  class SQLiteReader
  {
    public string ExecuteOneReader(string commandSQL, string returnColumn, SQLiteConnection connection)
    {
      SQLiteCommand command = new SQLiteCommand(commandSQL, connection);
      string result;
      using (SQLiteDataReader reader = command.ExecuteReader())
      {
        reader.Read();
        if (!reader.HasRows)
        {
          result = null;
        }
        else
        {
          result = reader[returnColumn].ToString();
        }
      }

      return result;
    }

    public List<string> ExecuteMoreReader(string commandSQL, string returnColumn, SQLiteConnection connection)
    {
      List<string> items = new List<string>();
      SQLiteCommand command = new SQLiteCommand(commandSQL, connection);
      using (SQLiteDataReader reader = command.ExecuteReader())
      {
        while (reader.Read()) items.Add((string)reader[returnColumn]);
      }
      return items;
    }

    public DataTable GeTable(string commandSQL, SQLiteConnection connection)
    {
      DataTable tb = new DataTable();
      SQLiteDataAdapter da = new SQLiteDataAdapter(commandSQL, connection);
      da.Fill(tb);
      return tb;
    }

    public DataTable GeTable(string commandSQL, SQLiteConnection connection, Dictionary<string, object> prms)
    {
      DataTable tb = new DataTable();
      SQLiteDataAdapter da = new SQLiteDataAdapter(commandSQL, connection);

      for (int i = 0; i < prms.Count; i++)
      {
        da.SelectCommand.Parameters.AddWithValue(prms.Keys.ElementAt(i), prms.Values.ElementAt(i));
      }
      da.Fill(tb);
      return tb;
    }

    public int GetInsertLastRowId(SQLiteConnection connection)
    {
      SQLiteCommand command = new SQLiteCommand("select last_insert_rowid()", connection);
      Int64 LastRowID64 = (Int64)command.ExecuteScalar();
      return (int)LastRowID64;
    }
  }

  class SQLiteWriter
  {
    public void ExecuteQuery(string commandSQL, SQLiteConnection connection)
    {
      SQLiteCommand command = new SQLiteCommand(commandSQL, connection);
      command.ExecuteNonQuery();
    }

    public void ExecuteQuery(string commandSQL, SQLiteConnection connection, Dictionary<string, object> prms = null)
    {
      SQLiteCommand command = new SQLiteCommand(commandSQL, connection);
      if (prms != null)
      {
        for (int i = 0; i < prms.Count; i++)
        {
          command.Parameters.AddWithValue(prms.Keys.ElementAt(i), prms.Values.ElementAt(i));
        }
      }
      command.ExecuteNonQuery();
    }

    public void ExecuteQuery(string commandSQL, SQLiteConnection connection, List<Dictionary<string, object>> prms)
    {
      for (int i = 0; i < prms.Count; i++)
      {
        ExecuteQuery(commandSQL, connection, prms[i]);
      }
    }
  }
}
