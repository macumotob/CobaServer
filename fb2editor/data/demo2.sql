 /*
      create demo database
  */

--begin transaction

drop table if exists statist;

create table statist (
 id integer primary key  AUTOINCREMENT,
 stat real 
);

insert into statist (stat) values(1);

drop table if exists books;

create table books (
 id integer primary key AUTOINCREMENT,
 title varchar(512) not null,
 author varchar(250) not null,
 genre varchar(80),
 link varchar(5120)
);

create trigger books_after_insert after insert on books
begin
  -- this is simple comment
  update statist set stat = stat + 1;

end;
insert into books (title,author ,link) 
values('blog as','my blog','https://www.blogger.com/blogger.g?blogID=650485751858931244#allpages');

--commit transaction