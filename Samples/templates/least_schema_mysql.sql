/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

This schema file is for the sample of INTER-Mediator using MySQL, encoded by UTF-8.

Example:
$ mysql -u root -p < least_schema_mysql.sql
Enter password:

*/
SET NAMES 'utf8mb4';

# - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Create the "test_db" database.

DROP DATABASE IF EXISTS test_db;
CREATE DATABASE test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test_db;

# - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Define user to use this database. The user name and password should be replaced.

# If the user does not exist, create it.
GRANT USAGE ON *.* TO 'web'@'localhost';
DROP USER 'web'@'localhost';
# MySQL 5.7.8 or later, supporting the following statement.
# DROP USER IF EXISTS 'web'@'localhost';
CREATE USER 'web'@'localhost' IDENTIFIED BY 'password';

# - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Grant to CRUD operations for web account

GRANT SELECT, INSERT, DELETE, UPDATE ON TABLE test_db.* TO 'web'@'localhost';

# - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# An example of table definition and insert a record

CREATE TABLE person	(
	person_id	INT AUTO_INCREMENT,
	name VARCHAR(20),
	address	VARCHAR(40),
	mail VARCHAR(40),
	age INT,
	memo TEXT,
	PRIMARY KEY(person_id)
)		CHARACTER SET utf8mb4,
		COLLATE utf8mb4_unicode_ci
		ENGINE=InnoDB;
CREATE INDEX person_name ON person (name);

INSERT person SET id=1,name='Masayuki Nii',address='Saitama, Japan',mail='msyk@msyk.net';

# - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Tables and sample data for Authentication/Authorization features.

CREATE TABLE authuser (
	id INT AUTO_INCREMENT,
	username VARCHAR(64),
	hashedpasswd VARCHAR(48),
	email VARCHAR(100),
	realname VARCHAR(48),
	yomi VARCHAR(48),
	limitdt DATETIME,
	PRIMARY KEY(id)
)		CHARACTER SET utf8mb4,
		COLLATE utf8mb4_unicode_ci
		ENGINE=InnoDB;

CREATE INDEX authuser_username
  ON authuser (username);
CREATE INDEX authuser_email
  ON authuser (email);
CREATE INDEX authuser_limitdt
  ON authuser (limitdt);

INSERT authuser SET id=1,username='user1',hashedpasswd='d83eefa0a9bd7190c94e7911688503737a99db0154455354';
INSERT authuser SET id=2,username='user2',hashedpasswd='5115aba773983066bcf4a8655ddac8525c1d3c6354455354';
INSERT authuser SET id=3,username='user3',hashedpasswd='d1a7981108a73e9fbd570e23ecca87c2c5cb967554455354';
INSERT authuser SET id=4,username='user4',hashedpasswd='8c1b394577d0191417e8d962c5f6e3ca15068f8254455354';
INSERT authuser SET id=5,username='user5',hashedpasswd='ee403ef2642f2e63dca12af72856620e6a24102d54455354';

# The user1 has the password 'user1'. It is salted with the string 'TEXT'.
# All users have the password the same as user name. All are salted with 'TEXT'
# The following command lines are used to generate above hashed-hexed-password.
#
#  $ echo -n 'user1TEST' | openssl sha1 -sha1
#  d83eefa0a9bd7190c94e7911688503737a99db01
#  echo -n 'TEST' | xxd -ps
#  54455354
#  - combine above two results:
#  d83eefa0a9bd7190c94e7911688503737a99db0154455354

CREATE TABLE authgroup (
	id INT AUTO_INCREMENT,
	groupname VARCHAR(48),
	PRIMARY KEY(id)
)		CHARACTER SET utf8mb4,
		COLLATE utf8mb4_unicode_ci
		ENGINE=InnoDB;

INSERT authgroup SET id=1,groupname='group1';
INSERT authgroup SET id=2,groupname='group2';
INSERT authgroup SET id=3,groupname='group3';

CREATE TABLE authcor (
	id INT AUTO_INCREMENT,
	user_id INT,
	group_id INT,
	dest_group_id INT,
	privname VARCHAR(48),
	PRIMARY KEY(id)
)		CHARACTER SET utf8mb4,
		COLLATE utf8mb4_unicode_ci
		ENGINE=InnoDB;

CREATE INDEX authcor_user_id
  ON authcor (user_id);
CREATE INDEX authcor_group_id
  ON authcor (group_id);
CREATE INDEX authcor_dest_group_id
  ON authcor (dest_group_id);

INSERT authcor SET user_id=1,dest_group_id=1;
INSERT authcor SET user_id=2,dest_group_id=1;
INSERT authcor SET user_id=3,dest_group_id=1;
INSERT authcor SET user_id=4,dest_group_id=2;
INSERT authcor SET user_id=5,dest_group_id=2;
INSERT authcor SET user_id=4,dest_group_id=3;
INSERT authcor SET user_id=5,dest_group_id=3;
INSERT authcor SET group_id=1,dest_group_id=3;

CREATE TABLE issuedhash (
	id INT AUTO_INCREMENT,
	user_id INT,
	clienthost VARCHAR(48),
	hash VARCHAR(48),
	expired DateTime,
	PRIMARY KEY(id)
)		CHARACTER SET utf8mb4,
		COLLATE utf8mb4_unicode_ci
		ENGINE=InnoDB;

CREATE INDEX issuedhash_user_id
  ON issuedhash (user_id);
CREATE INDEX issuedhash_expired
  ON issuedhash (expired);
CREATE INDEX issuedhash_clienthost
  ON issuedhash (clienthost);
CREATE INDEX issuedhash_user_id_clienthost
  ON issuedhash (user_id, clienthost);
