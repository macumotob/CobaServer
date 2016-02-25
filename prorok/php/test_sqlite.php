<?
/*
if (!extension_loaded('sqlite')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        dl('D:/apache/php5445mt/ext/php_sqlite3.dll ');
    } else {
        dl('sqlite.so');
    }
}
*/
$db = new SQLite3('D:/github/db.sqlite');
$db->exec('CREATE TABLE files (id INTEGER PRIMARY KEY, filename TEXT, content BLOB);');

$statement = $db->prepare('INSERT INTO files (filename, content) VALUES (?, ?);');
$statement->bindValue('filename', 'Archive.zip');
$statement->bindValue('content', file_get_contents('Archive.zip'));
$statement->execute();
exit;
$fp = $db->openBlob('files', 'content', $id);

while (!feof($fp))
{
    echo fgets($fp);
}

fclose($fp);

?>