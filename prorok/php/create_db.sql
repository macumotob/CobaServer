

DROP TABLE IF EXISTS prorok.prorok_logins;

CREATE TABLE prorok.prorok_logins (
   id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
   username VARCHAR(255) NOT NULL,
   password CHAR(32) NOT NULL )
;

insert into `prorok`.`prorok_logins`(username,password) values('waswas','1');
insert into `prorok`.`prorok_logins`(username,password) values('coba','1');