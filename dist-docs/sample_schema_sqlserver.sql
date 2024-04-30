/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

This schema file is for the sample of INTER-Mediator using SQLServer.

Example:
$ sqlcmd -S localhost -U SA -P im4135devX < sample_schema_sqlserver.txt

Microsoft Drivers for PHP for SQL Server
[Windows] https://www.microsoft.com/en-us/download/details.aspx?id=20098
[UNIX OS] https://github.com/Microsoft/msphpsql/tree/dev#install-unix

SQL Server をインストールし、Ubuntu でデータベースを作成
https://docs.microsoft.com/ja-jp/sql/linux/quickstart-install-connect-ubuntu
*/
-- Create Database
DROP DATABASE IF EXISTS test_db;
CREATE DATABASE test_db COLLATE Japanese_CI_AI;
GO
-- Set Current Database to test_db
USE test_db;
GO
-- Create User and Set Password
DROP
LOGIN web;
CREATE
LOGIN web WITH PASSWORD='password', CHECK_POLICY=OFF;
GO
-- Grant to All operations for all objects with web account
CREATE USER web;
GRANT DELETE, INSERT, REFERENCES, SELECT, UPDATE TO web;
GO

--  The schema for the "Sample_form" and "Sample_Auth" sample set.

CREATE TABLE person
(
    id       INT IDENTITY(1,1),
    name     NVARCHAR(20),
    address  NVARCHAR(40),
    mail     NVARCHAR(40),
    category INT,
    checking INT,
    location INT,
    memo NTEXT,
    PRIMARY KEY (id)
);
INSERT person(name, address, mail)
VALUES ('Masayuki Nii', 'Saitama, Japan', 'msyk@msyk.net');
INSERT person(name, address, mail)
VALUES ('Someone', 'Tokyo, Japan', 'msyk@msyk.net');
INSERT person(name, address, mail)
VALUES ('Anyone', 'Osaka, Japan', 'msyk@msyk.net');
GO

CREATE TABLE contact
(
    id        INTEGER IDENTITY(1,1),
    person_id INTEGER,
    description NTEXT,
    datetime  DATETIME,
    summary   NVARCHAR(50),
    important INTEGER,
    way       INTEGER default 4,
    kind      INTEGER,
    PRIMARY KEY (id)
);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (1, '2009-12-1 15:23:00', 'Telephone', 4, 4);
INSERT INTO contact (person_id, datetime, summary, important, way, kind)
VALUES (1, '2009-12-2 15:23:00', 'Meeting', 1, 4, 7);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (1, '2009-12-3 15:23:00', 'Mail', 5, 8);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (2, '2009-12-4 15:23:00', 'Calling', 6, 12);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (2, '2009-12-1 15:23:00', 'Telephone', 4, 4);
INSERT INTO contact (person_id, datetime, summary, important, way, kind)
VALUES (3, '2009-12-2 15:23:00', 'Meeting', 1, 4, 7);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (3, '2009-12-3 15:23:00', 'Mail', 5, 8);
GO

CREATE TABLE contact_way
(
    id   INTEGER IDENTITY(4,1),
    name NVARCHAR(50),
    PRIMARY KEY (id)
);
INSERT INTO contact_way(name)
VALUES ('Direct');
INSERT INTO contact_way(name)
VALUES ('Indirect');
INSERT INTO contact_way(name)
VALUES ('Others');
GO

CREATE TABLE contact_kind
(
    id   INTEGER IDENTITY(4,1),
    name NVARCHAR(50),
    PRIMARY KEY (id)
);
INSERT INTO contact_kind(name)
VALUES ('Talk');
INSERT INTO contact_kind(name)
VALUES ('Meet');
INSERT INTO contact_kind(name)
VALUES ('Calling');
INSERT INTO contact_kind(name)
VALUES ('Meeting');
INSERT INTO contact_kind(name)
VALUES ('Mail');
INSERT INTO contact_kind(name)
VALUES ('Email');
INSERT INTO contact_kind(name)
VALUES ('See on Web');
INSERT INTO contact_kind(name)
VALUES ('See on Chat');
INSERT INTO contact_kind(name)
VALUES ('Twitter');
INSERT INTO contact_kind(name)
VALUES ('Conference');
GO

CREATE TABLE cor_way_kind
(
    id      INTEGER IDENTITY(1,1),
    way_id  INTEGER,
    kind_id INTEGER,
    PRIMARY KEY (id)
);
--
CREATE INDEX cor_way_kind_way_id ON cor_way_kind (way_id);
--
CREATE INDEX cor_way_kind_kind_id ON cor_way_kind (kind_id);

INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (4, 4);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (4, 5);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (5, 6);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (4, 7);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (5, 8);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (5, 9);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (6, 10);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (5, 11);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (6, 12);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (5, 12);
INSERT INTO cor_way_kind(way_id, kind_id)
VALUES (6, 13);
GO

CREATE VIEW cor_way_kindname AS
SELECT cor_way_kind.*, contact_kind.name as name_kind
FROM cor_way_kind,
     contact_kind
WHERE cor_way_kind.kind_id = contact_kind.id;
GO

CREATE TABLE history
(
    id          INTEGER IDENTITY(1,1),
    person_id   INTEGER,
    description NVARCHAR(50),
    startdate   DATE,
    enddate     DATE,
    username    NVARCHAR(20),
    PRIMARY KEY (id)
);
--
CREATE INDEX history_person_id ON history (person_id);
INSERT INTO history(person_id, startdate, enddate, description)
VALUES (1, '2001-4-1', '2003-3-31', 'Hight School');
INSERT INTO history(person_id, startdate, enddate, description)
VALUES (1, '2003-4-1', '2007-3-31', 'University');
GO
-- The schema for the "Sample_search" sample set.

CREATE TABLE postalcode
(
    id   INTEGER IDENTITY(1,1),
    f3   NVARCHAR(20),
    f7   NVARCHAR(40),
    f8   NVARCHAR(15),
    f9   NVARCHAR(40),
    memo NVARCHAR(200),
    PRIMARY KEY (id)
);
CREATE INDEX postalcode_f3 ON postalcode (f3);
CREATE INDEX postalcode_f8 ON postalcode (f8);
GO
/*
# The schema for the "Sample_products" sample set.                                                                REAL
#
# The sample data for these table, invoice, item and products is another part of this file.
# Please scroll down to check it.
*/
CREATE TABLE invoice
(
    id        INTEGER IDENTITY(1,1),
    issued    DATE,
    title     NVARCHAR(30),
    authuser  NVARCHAR(30),
    authgroup NVARCHAR(30),
    authpriv  NVARCHAR(30),
    PRIMARY KEY (id)
);
CREATE TABLE item
(
    id          INTEGER IDENTITY(1,1),
    invoice_id  INTEGER,
    category_id INTEGER,
    product_id  INTEGER,
    qty         INTEGER,
    unitprice   NUMERIC(10, 2),
    user_id     INTEGER,
    group_id    INTEGER,
    priv_id     INTEGER,
    PRIMARY KEY (id)
);
CREATE TABLE product
(
    id              INTEGER IDENTITY(1,1),
    category_id     INTEGER,
    unitprice       NUMERIC(10, 2),
    name            NVARCHAR(20),
    photofile       NVARCHAR(20),
    acknowledgement NVARCHAR(100),
    ack_link        NVARCHAR(100),
    memo            NVARCHAR(120),
    PRIMARY KEY (id)
);
GO
-- The schema for the "Sample_Asset" sample set.
CREATE TABLE asset
(
    asset_id    INTEGER,
    name        NVARCHAR(20),
    category    NVARCHAR(20),
    manifacture NVARCHAR(20),
    productinfo NVARCHAR(20),
    purchase    DATE,
    discard     DATE,
    memo NTEXT,
    PRIMARY KEY (asset_id)
);
CREATE INDEX asset_purchase ON asset (purchase);
CREATE INDEX asset_discard ON asset (discard);
CREATE TABLE rent
(
    rent_id  INTEGER IDENTITY(1,1),
    asset_id INT,
    staff_id INT,
    rentdate DATE,
    backdate DATE,
    memo NTEXT,
    PRIMARY KEY (rent_id)
);
CREATE INDEX rent_rentdate ON rent (rentdate);
CREATE INDEX rent_backdate ON rent (backdate);
CREATE INDEX rent_asset_id ON rent (asset_id);
CREATE INDEX rent_staff_id ON rent (staff_id);
CREATE TABLE staff
(
    staff_id INTEGER,
    name     NVARCHAR(20),
    section  NVARCHAR(20),
    memo NTEXT,
    PRIMARY KEY (staff_id)
);
CREATE TABLE category
(
    category_id INTEGER,
    name        NVARCHAR(20),
    PRIMARY KEY (category_id)
);
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (11, N'個人用', 'MacBook Air[1]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (12, N'個人用', 'MacBook Air[2]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (13, N'個人用', 'MacBook Air[3]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (14, N'個人用', 'VAIO type A[1]', N'ソニー', 'VGN-AR85S', '2008-06-12', '2012-02-02');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (15, N'個人用', 'VAIO type A[2]', N'ソニー', 'VGN-AR85S', '2008-06-12', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (16, N'共用', N'プロジェクタ', N'エプソン', 'EB-460T', '2010-11-23', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (17, N'共用', N'ホワイトボード[1]', N'不明', N'不明', NULL, '2005-03-22');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (18, N'共用', N'ホワイトボード[2]', N'不明', N'不明', NULL, '2005-03-22');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (19, N'共用', N'加湿器', N'シャープ', N'プラズマクラスター加湿器', '2011-12-02', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (20, N'共用', N'事務室エアコン', '', '', NULL, '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (21, N'共用', N'会議室エアコン', '', '', NULL, '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (22, N'共用', N'携帯電話ドコモ', N'京セラ', 'P904i', '2010-04-04', '2012-03-03');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (23, N'個人用', N'携帯電話au', N'シャープ', 'SH001', '2012-03-03', '2012-10-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (24, N'個人用', N'携帯電話Softbank[1]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (25, N'個人用', N'携帯電話Softbank[2]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (26, N'個人用', N'携帯電話Softbank[3]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (27, N'個人用', N'携帯電話Softbank[4]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (28, N'個人用', N'携帯電話Softbank[5]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (29, N'個人用', N'携帯電話Softbank[6]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO staff (staff_id, name, section)
VALUES (101, N'田中次郎', N'代表取締役社長');
INSERT INTO staff (staff_id, name, section)
VALUES (102, N'山本三郎', N'専務取締役');
INSERT INTO staff (staff_id, name, section)
VALUES (103, N'北野六郎', N'営業部長');
INSERT INTO staff (staff_id, name, section)
VALUES (104, N'東原七海', N'営業部');
INSERT INTO staff (staff_id, name, section)
VALUES (105, N'内村久郎', N'営業部');
INSERT INTO staff (staff_id, name, section)
VALUES (106, N'菅沼健一郎', N'開発部長');
INSERT INTO staff (staff_id, name, section)
VALUES (107, N'西森裕太', N'開発部');
INSERT INTO staff (staff_id, name, section)
VALUES (108, N'野村顕昭', N'開発部');
INSERT INTO staff (staff_id, name, section)
VALUES (109, N'辻野均', N'開発部');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (22, 101, '2010-04-04', '2012-03-03');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (23, 101, '2012-03-03', '2012-10-01');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (24, 101, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (25, 102, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (26, 103, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (27, 106, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (28, 107, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (29, 108, '2012-10-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (14, 106, '2008-06-12', '2012-02-02');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (15, 106, '2008-06-12', '2011-03-31');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (15, 109, '2011-04-06', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (11, 107, '2012-08-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (12, 108, '2012-08-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (13, 109, '2012-08-01', NULL);
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 109, '2010-11-29', '2010-11-29');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 105, '2010-12-29', '2010-12-29');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 103, '2011-02-28', '2011-03-29');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 104, '2011-05-29', '2011-06-03');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 109, '2011-08-09', '2011-08-31');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 102, '2011-09-29', '2011-09-30');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 101, '2011-12-02', '2011-12-09');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 108, '2012-01-29', '2012-01-31');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 108, '2012-04-29', '2012-05-10');
INSERT INTO rent (asset_id, staff_id, rentdate, backdate)
VALUES (16, 109, '2012-06-29', '2012-07-29');
INSERT INTO category (category_id, name)
VALUES (1, N'個人用');
INSERT INTO category (category_id, name)
VALUES (2, N'共用');
GO

-- The schema for the "Sample_Auth" sample set.
CREATE TABLE chat
(
    id        INTEGER IDENTITY(1,1),
    username  NVARCHAR(16),
    groupname NVARCHAR(16),
    postdt    DATETIME,
    message   NVARCHAR(800),
    PRIMARY KEY (id)
);
-- Observable
CREATE TABLE registeredcontext
(
    id           INTEGER IDENTITY(1,1) PRIMARY KEY,
    clientid     NVARCHAR(64),
    entity       NVARCHAR(100),
    conditions   NVARCHAR(250),
    registereddt DATETIME
);
CREATE TABLE registeredpks
(
    context_id INTEGER,
    pk         INTEGER,
    PRIMARY KEY (context_id, pk),
    FOREIGN KEY (context_id) REFERENCES registeredcontext (id) ON DELETE CASCADE
);
GO

-- Authetication tables
CREATE TABLE authuser
(
    id           INTEGER IDENTITY(1,1),
    username     NVARCHAR(64),
    hashedpasswd NVARCHAR(72),
    email        NVARCHAR(100),
    realname     NVARCHAR(20),
    limitdt      DATETIME,
    PRIMARY KEY (id)
);
CREATE INDEX authuser_username ON authuser (username);
CREATE INDEX authuser_email ON authuser (email);
CREATE INDEX authuser_limitdt ON authuser (limitdt);
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('user1', 'd83eefa0a9bd7190c94e7911688503737a99db0154455354', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('user2', '5115aba773983066bcf4a8655ddac8525c1d3c6354455354', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('user3', 'd1a7981108a73e9fbd570e23ecca87c2c5cb967554455354', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('user4', '8c1b394577d0191417e8d962c5f6e3ca15068f8254455354', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('user5', 'ee403ef2642f2e63dca12af72856620e6a24102d54455354', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('mig2m', 'cd85a299c154c4714b23ce4b63618527289296ba6642c2685651ad8b9f20ce02285d7b34', 'msyk@msyk.net');
INSERT INTO authuser(username, hashedpasswd, email)
VALUES ('mig2', 'fcc2ab4678963966614b5544a40f4b814ba3da41b3b69df6622e51b74818232864235970', 'msyk@msyk.net');
/*
# The user1 has the password 'user1'. It's salted with the string 'NTEXT'.
# All users have the password the same as user name. All are salted with 'NTEXT'
# The following command lines are used to generate above hashed-hexed-password.
#
#  $ echo -n 'user1TEST' | openssl sha1 -sha1
#  d83eefa0a9bd7190c94e7911688503737a99db01
#  echo -n 'TEST' | xxd -ps
#  54455354
#  - combine above two results:
#  d83eefa0a9bd7190c94e7911688503737a99db0154455354
*/
CREATE TABLE authgroup
(
    id        INTEGER IDENTITY(1,1),
    groupname NVARCHAR(48),
    PRIMARY KEY (id)
);
INSERT INTO authgroup(groupname)
VALUES ('group1');
INSERT INTO authgroup(groupname)
VALUES ('group2');
INSERT INTO authgroup(groupname)
VALUES ('group3');
CREATE TABLE authcor
(
    id            INTEGER IDENTITY(1,1),
    user_id       INTEGER,
    group_id      INTEGER,
    dest_group_id INTEGER,
    privname      NVARCHAR(48),
    PRIMARY KEY (id)
);
CREATE INDEX authcor_user_id ON authcor (user_id);
CREATE INDEX authcor_group_id ON authcor (group_id);
CREATE INDEX authcor_dest_group_id ON authcor (dest_group_id);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (1, 1);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (2, 1);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (3, 1);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (4, 2);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (5, 2);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (4, 3);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (5, 3);
INSERT INTO authcor(group_id, dest_group_id)
VALUES (1, 3);
CREATE TABLE issuedhash
(
    id         INTEGER IDENTITY(1,1),
    user_id    INTEGER,
    clienthost NVARCHAR(64),
    hash       NVARCHAR(64),
    expired    DATETIME,
    PRIMARY KEY (id)
);
CREATE INDEX issuedhash_user_id ON issuedhash (user_id);
CREATE INDEX issuedhash_expired ON issuedhash (expired);
CREATE INDEX issuedhash_clienthost ON issuedhash (clienthost);
CREATE INDEX issuedhash_user_id_clienthost ON issuedhash (user_id, clienthost);
GO
/* # Sample Data */
INSERT INTO product(name, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Apple', 1, 340, 'mela-verde.png', 'Image: djcodrin / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=982');
INSERT INTO product(name, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Orange', 1, 1540, 'orange_1.png', 'Image: Suat Eman / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=151');
INSERT INTO product(name, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Melon', 1, 3840, 'galia-melon.png', 'Image: FreeDigitalPhotos.net', 'http://www.freedigitalphotos.net');
INSERT INTO product(name, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Tomato', 1, 2440, 'tomatos.png', 'Image: Tina Phillips / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=503');
INSERT INTO product(name, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Onion', 1, 21340, 'onion2.png', 'Image: FreeDigitalPhotos.net', 'http://www.freedigitalphotos.net');
INSERT INTO invoice(issued, title)
VALUES ('2010-2-4', 'Invoice');
INSERT INTO invoice(issued, title)
VALUES ('2010-2-6', 'Invoice');
INSERT INTO invoice(issued, title)
VALUES ('2010-2-14', 'Invoice');
INSERT INTO item(invoice_id, product_id, qty)
VALUES (1, 1, 12);
INSERT INTO item(invoice_id, product_id, qty, unitprice)
VALUES (1, 2, 12, 1340);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (1, 3, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (2, 4, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (2, 5, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (3, 3, 12);
GO
/*
The following is the postalcode for Tokyo Pref at Jan 2009.
These are come from JP, and JP doesn't claim the copyright for postalcode data.
http://www.post.japanpost.jp/zipcode/download.html
*/
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000000', N'東京都', N'千代田区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020072', N'東京都', N'千代田区', N'飯田橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020082', N'東京都', N'千代田区', N'一番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010032', N'東京都', N'千代田区', N'岩本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010047', N'東京都', N'千代田区', N'内神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000011', N'東京都', N'千代田区', N'内幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000004', N'東京都', N'千代田区', N'大手町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006890', N'東京都', N'千代田区', N'大手町ＪＡビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006801', N'東京都', N'千代田区', N'大手町ＪＡビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006802', N'東京都', N'千代田区', N'大手町ＪＡビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006803', N'東京都', N'千代田区', N'大手町ＪＡビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006804', N'東京都', N'千代田区', N'大手町ＪＡビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006805', N'東京都', N'千代田区', N'大手町ＪＡビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006806', N'東京都', N'千代田区', N'大手町ＪＡビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006807', N'東京都', N'千代田区', N'大手町ＪＡビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006808', N'東京都', N'千代田区', N'大手町ＪＡビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006809', N'東京都', N'千代田区', N'大手町ＪＡビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006810', N'東京都', N'千代田区', N'大手町ＪＡビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006811', N'東京都', N'千代田区', N'大手町ＪＡビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006812', N'東京都', N'千代田区', N'大手町ＪＡビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006813', N'東京都', N'千代田区', N'大手町ＪＡビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006814', N'東京都', N'千代田区', N'大手町ＪＡビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006815', N'東京都', N'千代田区', N'大手町ＪＡビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006816', N'東京都', N'千代田区', N'大手町ＪＡビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006817', N'東京都', N'千代田区', N'大手町ＪＡビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006818', N'東京都', N'千代田区', N'大手町ＪＡビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006819', N'東京都', N'千代田区', N'大手町ＪＡビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006820', N'東京都', N'千代田区', N'大手町ＪＡビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006821', N'東京都', N'千代田区', N'大手町ＪＡビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006822', N'東京都', N'千代田区', N'大手町ＪＡビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006823', N'東京都', N'千代田区', N'大手町ＪＡビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006824', N'東京都', N'千代田区', N'大手町ＪＡビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006825', N'東京都', N'千代田区', N'大手町ＪＡビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006826', N'東京都', N'千代田区', N'大手町ＪＡビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006827', N'東京都', N'千代田区', N'大手町ＪＡビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006828', N'東京都', N'千代田区', N'大手町ＪＡビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006829', N'東京都', N'千代田区', N'大手町ＪＡビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006830', N'東京都', N'千代田区', N'大手町ＪＡビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006831', N'東京都', N'千代田区', N'大手町ＪＡビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006832', N'東京都', N'千代田区', N'大手町ＪＡビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006833', N'東京都', N'千代田区', N'大手町ＪＡビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006834', N'東京都', N'千代田区', N'大手町ＪＡビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006835', N'東京都', N'千代田区', N'大手町ＪＡビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006836', N'東京都', N'千代田区', N'大手町ＪＡビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006837', N'東京都', N'千代田区', N'大手町ＪＡビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010044', N'東京都', N'千代田区', N'鍛冶町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000013', N'東京都', N'千代田区', N'霞が関（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006090', N'東京都', N'千代田区', N'霞が関霞が関ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006001', N'東京都', N'千代田区', N'霞が関霞が関ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006002', N'東京都', N'千代田区', N'霞が関霞が関ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006003', N'東京都', N'千代田区', N'霞が関霞が関ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006004', N'東京都', N'千代田区', N'霞が関霞が関ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006005', N'東京都', N'千代田区', N'霞が関霞が関ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006006', N'東京都', N'千代田区', N'霞が関霞が関ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006007', N'東京都', N'千代田区', N'霞が関霞が関ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006008', N'東京都', N'千代田区', N'霞が関霞が関ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006009', N'東京都', N'千代田区', N'霞が関霞が関ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006010', N'東京都', N'千代田区', N'霞が関霞が関ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006011', N'東京都', N'千代田区', N'霞が関霞が関ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006012', N'東京都', N'千代田区', N'霞が関霞が関ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006013', N'東京都', N'千代田区', N'霞が関霞が関ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006014', N'東京都', N'千代田区', N'霞が関霞が関ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006015', N'東京都', N'千代田区', N'霞が関霞が関ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006016', N'東京都', N'千代田区', N'霞が関霞が関ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006017', N'東京都', N'千代田区', N'霞が関霞が関ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006018', N'東京都', N'千代田区', N'霞が関霞が関ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006019', N'東京都', N'千代田区', N'霞が関霞が関ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006020', N'東京都', N'千代田区', N'霞が関霞が関ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006021', N'東京都', N'千代田区', N'霞が関霞が関ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006022', N'東京都', N'千代田区', N'霞が関霞が関ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006023', N'東京都', N'千代田区', N'霞が関霞が関ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006024', N'東京都', N'千代田区', N'霞が関霞が関ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006025', N'東京都', N'千代田区', N'霞が関霞が関ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006026', N'東京都', N'千代田区', N'霞が関霞が関ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006027', N'東京都', N'千代田区', N'霞が関霞が関ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006028', N'東京都', N'千代田区', N'霞が関霞が関ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006029', N'東京都', N'千代田区', N'霞が関霞が関ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006030', N'東京都', N'千代田区', N'霞が関霞が関ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006031', N'東京都', N'千代田区', N'霞が関霞が関ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006032', N'東京都', N'千代田区', N'霞が関霞が関ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006033', N'東京都', N'千代田区', N'霞が関霞が関ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006034', N'東京都', N'千代田区', N'霞が関霞が関ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006035', N'東京都', N'千代田区', N'霞が関霞が関ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006036', N'東京都', N'千代田区', N'霞が関霞が関ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010029', N'東京都', N'千代田区', N'神田相生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010063', N'東京都', N'千代田区', N'神田淡路町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010024', N'東京都', N'千代田区', N'神田和泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010033', N'東京都', N'千代田区', N'神田岩本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010052', N'東京都', N'千代田区', N'神田小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010045', N'東京都', N'千代田区', N'神田鍛冶町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010036', N'東京都', N'千代田区', N'神田北乗物町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010035', N'東京都', N'千代田区', N'神田紺屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010026', N'東京都', N'千代田区', N'神田佐久間河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010025', N'東京都', N'千代田区', N'神田佐久間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010051', N'東京都', N'千代田区', N'神田神保町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010041', N'東京都', N'千代田区', N'神田須田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010062', N'東京都', N'千代田区', N'神田駿河台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010046', N'東京都', N'千代田区', N'神田多町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010048', N'東京都', N'千代田区', N'神田司町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010043', N'東京都', N'千代田区', N'神田富山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010054', N'東京都', N'千代田区', N'神田錦町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010037', N'東京都', N'千代田区', N'神田西福田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010022', N'東京都', N'千代田区', N'神田練塀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010028', N'東京都', N'千代田区', N'神田花岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010034', N'東京都', N'千代田区', N'神田東紺屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010042', N'東京都', N'千代田区', N'神田東松下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010027', N'東京都', N'千代田区', N'神田平河町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010023', N'東京都', N'千代田区', N'神田松永町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010038', N'東京都', N'千代田区', N'神田美倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010053', N'東京都', N'千代田区', N'神田美土代町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020094', N'東京都', N'千代田区', N'紀尾井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020091', N'東京都', N'千代田区', N'北の丸公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020074', N'東京都', N'千代田区', N'九段南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020073', N'東京都', N'千代田区', N'九段北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000002', N'東京都', N'千代田区', N'皇居外苑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020083', N'東京都', N'千代田区', N'麹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020076', N'東京都', N'千代田区', N'五番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010064', N'東京都', N'千代田区', N'猿楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020075', N'東京都', N'千代田区', N'三番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010021', N'東京都', N'千代田区', N'外神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000001', N'東京都', N'千代田区', N'千代田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000014', N'東京都', N'千代田区', N'永田町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006190', N'東京都', N'千代田区', N'永田町山王パークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006101', N'東京都', N'千代田区', N'永田町山王パークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006102', N'東京都', N'千代田区', N'永田町山王パークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006103', N'東京都', N'千代田区', N'永田町山王パークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006104', N'東京都', N'千代田区', N'永田町山王パークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006105', N'東京都', N'千代田区', N'永田町山王パークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006106', N'東京都', N'千代田区', N'永田町山王パークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006107', N'東京都', N'千代田区', N'永田町山王パークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006108', N'東京都', N'千代田区', N'永田町山王パークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006109', N'東京都', N'千代田区', N'永田町山王パークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006110', N'東京都', N'千代田区', N'永田町山王パークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006111', N'東京都', N'千代田区', N'永田町山王パークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006112', N'東京都', N'千代田区', N'永田町山王パークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006113', N'東京都', N'千代田区', N'永田町山王パークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006114', N'東京都', N'千代田区', N'永田町山王パークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006115', N'東京都', N'千代田区', N'永田町山王パークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006116', N'東京都', N'千代田区', N'永田町山王パークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006117', N'東京都', N'千代田区', N'永田町山王パークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006118', N'東京都', N'千代田区', N'永田町山王パークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006119', N'東京都', N'千代田区', N'永田町山王パークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006120', N'東京都', N'千代田区', N'永田町山王パークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006121', N'東京都', N'千代田区', N'永田町山王パークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006122', N'東京都', N'千代田区', N'永田町山王パークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006123', N'東京都', N'千代田区', N'永田町山王パークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006124', N'東京都', N'千代田区', N'永田町山王パークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006125', N'東京都', N'千代田区', N'永田町山王パークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006126', N'東京都', N'千代田区', N'永田町山王パークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006127', N'東京都', N'千代田区', N'永田町山王パークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006128', N'東京都', N'千代田区', N'永田町山王パークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006129', N'東京都', N'千代田区', N'永田町山王パークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006130', N'東京都', N'千代田区', N'永田町山王パークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006131', N'東京都', N'千代田区', N'永田町山王パークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006132', N'東京都', N'千代田区', N'永田町山王パークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006133', N'東京都', N'千代田区', N'永田町山王パークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006134', N'東京都', N'千代田区', N'永田町山王パークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006135', N'東京都', N'千代田区', N'永田町山王パークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006136', N'東京都', N'千代田区', N'永田町山王パークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006137', N'東京都', N'千代田区', N'永田町山王パークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006138', N'東京都', N'千代田区', N'永田町山王パークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006139', N'東京都', N'千代田区', N'永田町山王パークタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006140', N'東京都', N'千代田区', N'永田町山王パークタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006141', N'東京都', N'千代田区', N'永田町山王パークタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006142', N'東京都', N'千代田区', N'永田町山王パークタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006143', N'東京都', N'千代田区', N'永田町山王パークタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006144', N'東京都', N'千代田区', N'永田町山王パークタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010065', N'東京都', N'千代田区', N'西神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020084', N'東京都', N'千代田区', N'二番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020092', N'東京都', N'千代田区', N'隼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010031', N'東京都', N'千代田区', N'東神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000003', N'東京都', N'千代田区', N'一ツ橋（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010003', N'東京都', N'千代田区', N'一ツ橋（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000012', N'東京都', N'千代田区', N'日比谷公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020093', N'東京都', N'千代田区', N'平河町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020071', N'東京都', N'千代田区', N'富士見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000005', N'東京都', N'千代田区', N'丸の内（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006690', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006601', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006602', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006603', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006604', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006605', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006606', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006607', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006608', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006609', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006610', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006611', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006612', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006613', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006614', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006615', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006616', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006617', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006618', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006619', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006620', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006621', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006622', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006623', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006624', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006625', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006626', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006627', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006628', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006629', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006630', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006631', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006632', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006633', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006634', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006635', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006636', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006637', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006638', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006639', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006640', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006641', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006642', N'東京都', N'千代田区', N'丸の内グラントウキョウサウスタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006790', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006701', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006702', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006703', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006704', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006705', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006706', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006707', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006708', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006709', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006710', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006711', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006712', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006713', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006714', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006715', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006716', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006717', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006718', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006719', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006720', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006721', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006722', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006723', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006724', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006725', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006726', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006727', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006728', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006729', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006730', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006731', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006732', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006733', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006734', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006735', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006736', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006737', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006738', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006739', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006740', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006741', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006742', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006743', N'東京都', N'千代田区', N'丸の内グラントウキョウノースタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006590', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006501', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006502', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006503', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006504', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006505', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006506', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006507', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006508', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006509', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006510', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006511', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006512', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006513', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006514', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006515', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006516', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006517', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006518', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006519', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006520', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006521', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006522', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006523', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006524', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006525', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006526', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006527', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006528', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006529', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006530', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006531', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006532', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006533', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006534', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006535', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006536', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006537', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006538', N'東京都', N'千代田区', N'丸の内新丸の内ビルディング（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006490', N'東京都', N'千代田区', N'丸の内東京ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006401', N'東京都', N'千代田区', N'丸の内東京ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006402', N'東京都', N'千代田区', N'丸の内東京ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006403', N'東京都', N'千代田区', N'丸の内東京ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006404', N'東京都', N'千代田区', N'丸の内東京ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006405', N'東京都', N'千代田区', N'丸の内東京ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006406', N'東京都', N'千代田区', N'丸の内東京ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006407', N'東京都', N'千代田区', N'丸の内東京ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006408', N'東京都', N'千代田区', N'丸の内東京ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006409', N'東京都', N'千代田区', N'丸の内東京ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006410', N'東京都', N'千代田区', N'丸の内東京ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006411', N'東京都', N'千代田区', N'丸の内東京ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006412', N'東京都', N'千代田区', N'丸の内東京ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006413', N'東京都', N'千代田区', N'丸の内東京ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006414', N'東京都', N'千代田区', N'丸の内東京ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006415', N'東京都', N'千代田区', N'丸の内東京ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006416', N'東京都', N'千代田区', N'丸の内東京ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006417', N'東京都', N'千代田区', N'丸の内東京ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006418', N'東京都', N'千代田区', N'丸の内東京ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006419', N'東京都', N'千代田区', N'丸の内東京ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006420', N'東京都', N'千代田区', N'丸の内東京ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006421', N'東京都', N'千代田区', N'丸の内東京ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006422', N'東京都', N'千代田区', N'丸の内東京ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006423', N'東京都', N'千代田区', N'丸の内東京ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006424', N'東京都', N'千代田区', N'丸の内東京ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006425', N'東京都', N'千代田区', N'丸の内東京ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006426', N'東京都', N'千代田区', N'丸の内東京ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006427', N'東京都', N'千代田区', N'丸の内東京ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006428', N'東京都', N'千代田区', N'丸の内東京ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006429', N'東京都', N'千代田区', N'丸の内東京ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006430', N'東京都', N'千代田区', N'丸の内東京ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006431', N'東京都', N'千代田区', N'丸の内東京ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006432', N'東京都', N'千代田区', N'丸の内東京ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006433', N'東京都', N'千代田区', N'丸の内東京ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006290', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006201', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006202', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006203', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006204', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006205', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006206', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006207', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006208', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006209', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006210', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006211', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006212', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006213', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006214', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006215', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006216', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006217', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006218', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006219', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006220', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006221', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006222', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006223', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006224', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006225', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006226', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006227', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006228', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006229', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006230', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006231', N'東京都', N'千代田区', N'丸の内パシフィックセンチュリープレイス丸の内（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006990', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006901', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006902', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006903', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006904', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006905', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006906', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006907', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006908', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006909', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006910', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006911', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006912', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006913', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006914', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006915', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006916', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006917', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006918', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006919', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006920', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006921', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006922', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006923', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006924', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006925', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006926', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006927', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006928', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006929', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006930', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006931', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006932', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006933', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006934', N'東京都', N'千代田区', N'丸の内丸の内パークビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006390', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006301', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006302', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006303', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006304', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006305', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006306', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006307', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006308', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006309', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006310', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006311', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006312', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006313', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006314', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006315', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006316', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006317', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006318', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006319', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006320', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006321', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006322', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006323', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006324', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006325', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006326', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006327', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006328', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006329', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006330', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006331', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006332', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006333', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006334', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006335', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006336', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006337', N'東京都', N'千代田区', N'丸の内丸の内ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010061', N'東京都', N'千代田区', N'三崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000006', N'東京都', N'千代田区', N'有楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020081', N'東京都', N'千代田区', N'四番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020085', N'東京都', N'千代田区', N'六番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030000', N'東京都', N'中央区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040044', N'東京都', N'中央区', N'明石町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040042', N'東京都', N'中央区', N'入船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040054', N'東京都', N'中央区', N'勝どき');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040031', N'東京都', N'中央区', N'京橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040061', N'東京都', N'中央区', N'銀座');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040033', N'東京都', N'中央区', N'新川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040041', N'東京都', N'中央区', N'新富');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040052', N'東京都', N'中央区', N'月島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040045', N'東京都', N'中央区', N'築地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040051', N'東京都', N'中央区', N'佃');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040055', N'東京都', N'中央区', N'豊海町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030027', N'東京都', N'中央区', N'日本橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030011', N'東京都', N'中央区', N'日本橋大伝馬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030014', N'東京都', N'中央区', N'日本橋蛎殻町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030026', N'東京都', N'中央区', N'日本橋兜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030025', N'東京都', N'中央区', N'日本橋茅場町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030016', N'東京都', N'中央区', N'日本橋小網町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030001', N'東京都', N'中央区', N'日本橋小伝馬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030024', N'東京都', N'中央区', N'日本橋小舟町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030006', N'東京都', N'中央区', N'日本橋富沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030008', N'東京都', N'中央区', N'日本橋中洲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030013', N'東京都', N'中央区', N'日本橋人形町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030015', N'東京都', N'中央区', N'日本橋箱崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030007', N'東京都', N'中央区', N'日本橋浜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030002', N'東京都', N'中央区', N'日本橋馬喰町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030005', N'東京都', N'中央区', N'日本橋久松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030012', N'東京都', N'中央区', N'日本橋堀留町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030021', N'東京都', N'中央区', N'日本橋本石町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030023', N'東京都', N'中央区', N'日本橋本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030022', N'東京都', N'中央区', N'日本橋室町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030003', N'東京都', N'中央区', N'日本橋横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040032', N'東京都', N'中央区', N'八丁堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040046', N'東京都', N'中央区', N'浜離宮庭園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040053', N'東京都', N'中央区', N'晴海（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046090', N'東京都', N'中央区', N'晴海オフィスタワーＸ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046001', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046002', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046003', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046004', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046005', N'東京都', N'中央区', N'晴海オフィスタワーＸ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046006', N'東京都', N'中央区', N'晴海オフィスタワーＸ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046007', N'東京都', N'中央区', N'晴海オフィスタワーＸ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046008', N'東京都', N'中央区', N'晴海オフィスタワーＸ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046009', N'東京都', N'中央区', N'晴海オフィスタワーＸ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046010', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046011', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046012', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046013', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046014', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046015', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046016', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046017', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046018', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046019', N'東京都', N'中央区', N'晴海オフィスタワーＸ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046020', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046021', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046022', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046023', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046024', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046025', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046026', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046027', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046028', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046029', N'東京都', N'中央区', N'晴海オフィスタワーＸ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046030', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046031', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046032', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046033', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046034', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046035', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046036', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046037', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046038', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046039', N'東京都', N'中央区', N'晴海オフィスタワーＸ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046040', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046041', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046042', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046043', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046044', N'東京都', N'中央区', N'晴海オフィスタワーＸ（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046190', N'東京都', N'中央区', N'晴海オフィスタワーＹ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046101', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046102', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046103', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046104', N'東京都', N'中央区', N'晴海オフィスタワーＹ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046105', N'東京都', N'中央区', N'晴海オフィスタワーＹ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046106', N'東京都', N'中央区', N'晴海オフィスタワーＹ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046107', N'東京都', N'中央区', N'晴海オフィスタワーＹ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046108', N'東京都', N'中央区', N'晴海オフィスタワーＹ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046109', N'東京都', N'中央区', N'晴海オフィスタワーＹ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046110', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046111', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046112', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046113', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046114', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046115', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046116', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046117', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046118', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046119', N'東京都', N'中央区', N'晴海オフィスタワーＹ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046120', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046121', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046122', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046123', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046124', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046125', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046126', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046127', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046128', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046129', N'東京都', N'中央区', N'晴海オフィスタワーＹ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046130', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046131', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046132', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046133', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046134', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046135', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046136', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046137', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046138', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046139', N'東京都', N'中央区', N'晴海オフィスタワーＹ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046290', N'東京都', N'中央区', N'晴海オフィスタワーＺ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046201', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046202', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046203', N'東京都', N'中央区', N'晴海オフィスタワーＺ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046204', N'東京都', N'中央区', N'晴海オフィスタワーＺ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046205', N'東京都', N'中央区', N'晴海オフィスタワーＺ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046206', N'東京都', N'中央区', N'晴海オフィスタワーＺ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046207', N'東京都', N'中央区', N'晴海オフィスタワーＺ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046208', N'東京都', N'中央区', N'晴海オフィスタワーＺ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046209', N'東京都', N'中央区', N'晴海オフィスタワーＺ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046210', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046211', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046212', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046213', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046214', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046215', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046216', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046217', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046218', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046219', N'東京都', N'中央区', N'晴海オフィスタワーＺ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046220', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046221', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046222', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046223', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046224', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046225', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046226', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046227', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046228', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046229', N'東京都', N'中央区', N'晴海オフィスタワーＺ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046230', N'東京都', N'中央区', N'晴海オフィスタワーＺ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046231', N'東京都', N'中央区', N'晴海オフィスタワーＺ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046232', N'東京都', N'中央区', N'晴海オフィスタワーＺ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046233', N'東京都', N'中央区', N'晴海オフィスタワーＺ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030004', N'東京都', N'中央区', N'東日本橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040043', N'東京都', N'中央区', N'湊');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030028', N'東京都', N'中央区', N'八重洲（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040028', N'東京都', N'中央区', N'八重洲（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050000', N'東京都', N'港区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070052', N'東京都', N'港区', N'赤坂（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076090', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076001', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076002', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076003', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076004', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076005', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076006', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076007', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076008', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076009', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076010', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076011', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076012', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076013', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076014', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076015', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076016', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076017', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076018', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076019', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076020', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076021', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076022', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076023', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076024', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076025', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076026', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076027', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076028', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076029', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076030', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076031', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076032', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076033', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076034', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076035', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076036', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076037', N'東京都', N'港区', N'赤坂赤坂アークヒルズ・アーク森ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076190', N'東京都', N'港区', N'赤坂赤坂パークビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076101', N'東京都', N'港区', N'赤坂赤坂パークビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076102', N'東京都', N'港区', N'赤坂赤坂パークビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076103', N'東京都', N'港区', N'赤坂赤坂パークビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076104', N'東京都', N'港区', N'赤坂赤坂パークビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076105', N'東京都', N'港区', N'赤坂赤坂パークビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076106', N'東京都', N'港区', N'赤坂赤坂パークビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076107', N'東京都', N'港区', N'赤坂赤坂パークビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076108', N'東京都', N'港区', N'赤坂赤坂パークビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076109', N'東京都', N'港区', N'赤坂赤坂パークビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076110', N'東京都', N'港区', N'赤坂赤坂パークビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076111', N'東京都', N'港区', N'赤坂赤坂パークビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076112', N'東京都', N'港区', N'赤坂赤坂パークビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076113', N'東京都', N'港区', N'赤坂赤坂パークビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076114', N'東京都', N'港区', N'赤坂赤坂パークビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076115', N'東京都', N'港区', N'赤坂赤坂パークビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076116', N'東京都', N'港区', N'赤坂赤坂パークビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076117', N'東京都', N'港区', N'赤坂赤坂パークビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076118', N'東京都', N'港区', N'赤坂赤坂パークビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076119', N'東京都', N'港区', N'赤坂赤坂パークビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076120', N'東京都', N'港区', N'赤坂赤坂パークビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076121', N'東京都', N'港区', N'赤坂赤坂パークビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076122', N'東京都', N'港区', N'赤坂赤坂パークビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076123', N'東京都', N'港区', N'赤坂赤坂パークビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076124', N'東京都', N'港区', N'赤坂赤坂パークビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076125', N'東京都', N'港区', N'赤坂赤坂パークビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076126', N'東京都', N'港区', N'赤坂赤坂パークビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076127', N'東京都', N'港区', N'赤坂赤坂パークビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076128', N'東京都', N'港区', N'赤坂赤坂パークビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076129', N'東京都', N'港区', N'赤坂赤坂パークビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076130', N'東京都', N'港区', N'赤坂赤坂パークビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076390', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076301', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076302', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076303', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076304', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076305', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076306', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076307', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076308', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076309', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076310', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076311', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076312', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076313', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076314', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076315', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076316', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076317', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076318', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076319', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076320', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076321', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076322', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076323', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076324', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076325', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076326', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076327', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076328', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076329', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076330', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076331', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076332', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076333', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076334', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076335', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076336', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076337', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076338', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076339', N'東京都', N'港区', N'赤坂赤坂Ｂｉｚタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076290', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076201', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076202', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076203', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076204', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076205', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076206', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076207', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076208', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076209', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076210', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076211', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076212', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076213', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076214', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076215', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076216', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076217', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076218', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076219', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076220', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076221', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076222', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076223', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076224', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076225', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076226', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076227', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076228', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076229', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076230', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076231', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076232', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076233', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076234', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076235', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076236', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076237', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076238', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076239', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076240', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076241', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076242', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076243', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076244', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076245', N'東京都', N'港区', N'赤坂ミッドタウン・タワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060045', N'東京都', N'港区', N'麻布十番');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060041', N'東京都', N'港区', N'麻布台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060043', N'東京都', N'港区', N'麻布永坂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060042', N'東京都', N'港区', N'麻布狸穴町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050002', N'東京都', N'港区', N'愛宕（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056290', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056201', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056202', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056203', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056204', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056205', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056206', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056207', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056208', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056209', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056210', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056211', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056212', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056213', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056214', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056215', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056216', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056217', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056218', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056219', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056220', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056221', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056222', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056223', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056224', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056225', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056226', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056227', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056228', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056229', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056230', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056231', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056232', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056233', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056234', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056235', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056236', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056237', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056238', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056239', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056240', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056241', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056242', N'東京都', N'港区', N'愛宕愛宕グリーンヒルズＭＯＲＩタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050022', N'東京都', N'港区', N'海岸（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080022', N'東京都', N'港区', N'海岸（３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070061', N'東京都', N'港区', N'北青山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080075', N'東京都', N'港区', N'港南（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086090', N'東京都', N'港区', N'港南品川インターシティＡ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086001', N'東京都', N'港区', N'港南品川インターシティＡ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086002', N'東京都', N'港区', N'港南品川インターシティＡ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086003', N'東京都', N'港区', N'港南品川インターシティＡ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086004', N'東京都', N'港区', N'港南品川インターシティＡ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086005', N'東京都', N'港区', N'港南品川インターシティＡ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086006', N'東京都', N'港区', N'港南品川インターシティＡ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086007', N'東京都', N'港区', N'港南品川インターシティＡ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086008', N'東京都', N'港区', N'港南品川インターシティＡ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086009', N'東京都', N'港区', N'港南品川インターシティＡ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086010', N'東京都', N'港区', N'港南品川インターシティＡ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086011', N'東京都', N'港区', N'港南品川インターシティＡ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086012', N'東京都', N'港区', N'港南品川インターシティＡ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086013', N'東京都', N'港区', N'港南品川インターシティＡ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086014', N'東京都', N'港区', N'港南品川インターシティＡ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086015', N'東京都', N'港区', N'港南品川インターシティＡ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086016', N'東京都', N'港区', N'港南品川インターシティＡ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086017', N'東京都', N'港区', N'港南品川インターシティＡ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086018', N'東京都', N'港区', N'港南品川インターシティＡ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086019', N'東京都', N'港区', N'港南品川インターシティＡ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086020', N'東京都', N'港区', N'港南品川インターシティＡ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086021', N'東京都', N'港区', N'港南品川インターシティＡ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086022', N'東京都', N'港区', N'港南品川インターシティＡ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086023', N'東京都', N'港区', N'港南品川インターシティＡ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086024', N'東京都', N'港区', N'港南品川インターシティＡ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086025', N'東京都', N'港区', N'港南品川インターシティＡ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086026', N'東京都', N'港区', N'港南品川インターシティＡ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086027', N'東京都', N'港区', N'港南品川インターシティＡ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086028', N'東京都', N'港区', N'港南品川インターシティＡ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086029', N'東京都', N'港区', N'港南品川インターシティＡ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086030', N'東京都', N'港区', N'港南品川インターシティＡ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086031', N'東京都', N'港区', N'港南品川インターシティＡ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086032', N'東京都', N'港区', N'港南品川インターシティＡ棟（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086190', N'東京都', N'港区', N'港南品川インターシティＢ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086101', N'東京都', N'港区', N'港南品川インターシティＢ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086102', N'東京都', N'港区', N'港南品川インターシティＢ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086103', N'東京都', N'港区', N'港南品川インターシティＢ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086104', N'東京都', N'港区', N'港南品川インターシティＢ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086105', N'東京都', N'港区', N'港南品川インターシティＢ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086106', N'東京都', N'港区', N'港南品川インターシティＢ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086107', N'東京都', N'港区', N'港南品川インターシティＢ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086108', N'東京都', N'港区', N'港南品川インターシティＢ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086109', N'東京都', N'港区', N'港南品川インターシティＢ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086110', N'東京都', N'港区', N'港南品川インターシティＢ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086111', N'東京都', N'港区', N'港南品川インターシティＢ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086112', N'東京都', N'港区', N'港南品川インターシティＢ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086113', N'東京都', N'港区', N'港南品川インターシティＢ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086114', N'東京都', N'港区', N'港南品川インターシティＢ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086115', N'東京都', N'港区', N'港南品川インターシティＢ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086116', N'東京都', N'港区', N'港南品川インターシティＢ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086117', N'東京都', N'港区', N'港南品川インターシティＢ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086118', N'東京都', N'港区', N'港南品川インターシティＢ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086119', N'東京都', N'港区', N'港南品川インターシティＢ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086120', N'東京都', N'港区', N'港南品川インターシティＢ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086121', N'東京都', N'港区', N'港南品川インターシティＢ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086122', N'東京都', N'港区', N'港南品川インターシティＢ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086123', N'東京都', N'港区', N'港南品川インターシティＢ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086124', N'東京都', N'港区', N'港南品川インターシティＢ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086125', N'東京都', N'港区', N'港南品川インターシティＢ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086126', N'東京都', N'港区', N'港南品川インターシティＢ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086127', N'東京都', N'港区', N'港南品川インターシティＢ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086128', N'東京都', N'港区', N'港南品川インターシティＢ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086129', N'東京都', N'港区', N'港南品川インターシティＢ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086130', N'東京都', N'港区', N'港南品川インターシティＢ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086131', N'東京都', N'港区', N'港南品川インターシティＢ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086290', N'東京都', N'港区', N'港南品川インターシティＣ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086201', N'東京都', N'港区', N'港南品川インターシティＣ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086202', N'東京都', N'港区', N'港南品川インターシティＣ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086203', N'東京都', N'港区', N'港南品川インターシティＣ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086204', N'東京都', N'港区', N'港南品川インターシティＣ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086205', N'東京都', N'港区', N'港南品川インターシティＣ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086206', N'東京都', N'港区', N'港南品川インターシティＣ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086207', N'東京都', N'港区', N'港南品川インターシティＣ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086208', N'東京都', N'港区', N'港南品川インターシティＣ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086209', N'東京都', N'港区', N'港南品川インターシティＣ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086210', N'東京都', N'港区', N'港南品川インターシティＣ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086211', N'東京都', N'港区', N'港南品川インターシティＣ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086212', N'東京都', N'港区', N'港南品川インターシティＣ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086213', N'東京都', N'港区', N'港南品川インターシティＣ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086214', N'東京都', N'港区', N'港南品川インターシティＣ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086215', N'東京都', N'港区', N'港南品川インターシティＣ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086216', N'東京都', N'港区', N'港南品川インターシティＣ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086217', N'東京都', N'港区', N'港南品川インターシティＣ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086218', N'東京都', N'港区', N'港南品川インターシティＣ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086219', N'東京都', N'港区', N'港南品川インターシティＣ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086220', N'東京都', N'港区', N'港南品川インターシティＣ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086221', N'東京都', N'港区', N'港南品川インターシティＣ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086222', N'東京都', N'港区', N'港南品川インターシティＣ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086223', N'東京都', N'港区', N'港南品川インターシティＣ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086224', N'東京都', N'港区', N'港南品川インターシティＣ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086225', N'東京都', N'港区', N'港南品川インターシティＣ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086226', N'東京都', N'港区', N'港南品川インターシティＣ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086227', N'東京都', N'港区', N'港南品川インターシティＣ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086228', N'東京都', N'港区', N'港南品川インターシティＣ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086229', N'東京都', N'港区', N'港南品川インターシティＣ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086230', N'東京都', N'港区', N'港南品川インターシティＣ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086231', N'東京都', N'港区', N'港南品川インターシティＣ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050014', N'東京都', N'港区', N'芝（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080014', N'東京都', N'港区', N'芝（４、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050023', N'東京都', N'港区', N'芝浦（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080023', N'東京都', N'港区', N'芝浦（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050011', N'東京都', N'港区', N'芝公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050012', N'東京都', N'港区', N'芝大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080072', N'東京都', N'港区', N'白金');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080071', N'東京都', N'港区', N'白金台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050004', N'東京都', N'港区', N'新橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350091', N'東京都', N'港区', N'台場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080074', N'東京都', N'港区', N'高輪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050001', N'東京都', N'港区', N'虎ノ門（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056090', N'東京都', N'港区', N'虎ノ門城山トラストタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056001', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056002', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056003', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056004', N'東京都', N'港区', N'虎ノ門城山トラストタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056005', N'東京都', N'港区', N'虎ノ門城山トラストタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056006', N'東京都', N'港区', N'虎ノ門城山トラストタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056007', N'東京都', N'港区', N'虎ノ門城山トラストタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056008', N'東京都', N'港区', N'虎ノ門城山トラストタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056009', N'東京都', N'港区', N'虎ノ門城山トラストタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056010', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056011', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056012', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056013', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056014', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056015', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056016', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056017', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056018', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056019', N'東京都', N'港区', N'虎ノ門城山トラストタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056020', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056021', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056022', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056023', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056024', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056025', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056026', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056027', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056028', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056029', N'東京都', N'港区', N'虎ノ門城山トラストタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056030', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056031', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056032', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056033', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056034', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056035', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056036', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056037', N'東京都', N'港区', N'虎ノ門城山トラストタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060031', N'東京都', N'港区', N'西麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050003', N'東京都', N'港区', N'西新橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050013', N'東京都', N'港区', N'浜松町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056190', N'東京都', N'港区', N'浜松町世界貿易センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056101', N'東京都', N'港区', N'浜松町世界貿易センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056102', N'東京都', N'港区', N'浜松町世界貿易センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056103', N'東京都', N'港区', N'浜松町世界貿易センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056104', N'東京都', N'港区', N'浜松町世界貿易センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056105', N'東京都', N'港区', N'浜松町世界貿易センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056106', N'東京都', N'港区', N'浜松町世界貿易センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056107', N'東京都', N'港区', N'浜松町世界貿易センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056108', N'東京都', N'港区', N'浜松町世界貿易センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056109', N'東京都', N'港区', N'浜松町世界貿易センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056110', N'東京都', N'港区', N'浜松町世界貿易センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056111', N'東京都', N'港区', N'浜松町世界貿易センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056112', N'東京都', N'港区', N'浜松町世界貿易センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056113', N'東京都', N'港区', N'浜松町世界貿易センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056114', N'東京都', N'港区', N'浜松町世界貿易センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056115', N'東京都', N'港区', N'浜松町世界貿易センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056116', N'東京都', N'港区', N'浜松町世界貿易センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056117', N'東京都', N'港区', N'浜松町世界貿易センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056118', N'東京都', N'港区', N'浜松町世界貿易センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056119', N'東京都', N'港区', N'浜松町世界貿易センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056120', N'東京都', N'港区', N'浜松町世界貿易センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056121', N'東京都', N'港区', N'浜松町世界貿易センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056122', N'東京都', N'港区', N'浜松町世界貿易センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056123', N'東京都', N'港区', N'浜松町世界貿易センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056124', N'東京都', N'港区', N'浜松町世界貿易センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056125', N'東京都', N'港区', N'浜松町世界貿易センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056126', N'東京都', N'港区', N'浜松町世界貿易センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056127', N'東京都', N'港区', N'浜松町世界貿易センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056128', N'東京都', N'港区', N'浜松町世界貿易センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056129', N'東京都', N'港区', N'浜松町世界貿易センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056130', N'東京都', N'港区', N'浜松町世界貿易センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056131', N'東京都', N'港区', N'浜松町世界貿易センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056132', N'東京都', N'港区', N'浜松町世界貿易センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056133', N'東京都', N'港区', N'浜松町世界貿易センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056134', N'東京都', N'港区', N'浜松町世界貿易センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056135', N'東京都', N'港区', N'浜松町世界貿易センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056136', N'東京都', N'港区', N'浜松町世界貿易センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056137', N'東京都', N'港区', N'浜松町世界貿易センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056138', N'東京都', N'港区', N'浜松町世界貿易センタービル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056139', N'東京都', N'港区', N'浜松町世界貿易センタービル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056140', N'東京都', N'港区', N'浜松町世界貿易センタービル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060044', N'東京都', N'港区', N'東麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050021', N'東京都', N'港区', N'東新橋（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057190', N'東京都', N'港区', N'東新橋汐留シティセンター（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057101', N'東京都', N'港区', N'東新橋汐留シティセンター（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057102', N'東京都', N'港区', N'東新橋汐留シティセンター（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057103', N'東京都', N'港区', N'東新橋汐留シティセンター（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057104', N'東京都', N'港区', N'東新橋汐留シティセンター（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057105', N'東京都', N'港区', N'東新橋汐留シティセンター（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057106', N'東京都', N'港区', N'東新橋汐留シティセンター（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057107', N'東京都', N'港区', N'東新橋汐留シティセンター（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057108', N'東京都', N'港区', N'東新橋汐留シティセンター（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057109', N'東京都', N'港区', N'東新橋汐留シティセンター（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057110', N'東京都', N'港区', N'東新橋汐留シティセンター（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057111', N'東京都', N'港区', N'東新橋汐留シティセンター（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057112', N'東京都', N'港区', N'東新橋汐留シティセンター（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057113', N'東京都', N'港区', N'東新橋汐留シティセンター（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057114', N'東京都', N'港区', N'東新橋汐留シティセンター（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057115', N'東京都', N'港区', N'東新橋汐留シティセンター（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057116', N'東京都', N'港区', N'東新橋汐留シティセンター（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057117', N'東京都', N'港区', N'東新橋汐留シティセンター（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057118', N'東京都', N'港区', N'東新橋汐留シティセンター（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057119', N'東京都', N'港区', N'東新橋汐留シティセンター（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057120', N'東京都', N'港区', N'東新橋汐留シティセンター（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057121', N'東京都', N'港区', N'東新橋汐留シティセンター（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057122', N'東京都', N'港区', N'東新橋汐留シティセンター（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057123', N'東京都', N'港区', N'東新橋汐留シティセンター（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057124', N'東京都', N'港区', N'東新橋汐留シティセンター（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057125', N'東京都', N'港区', N'東新橋汐留シティセンター（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057126', N'東京都', N'港区', N'東新橋汐留シティセンター（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057127', N'東京都', N'港区', N'東新橋汐留シティセンター（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057128', N'東京都', N'港区', N'東新橋汐留シティセンター（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057129', N'東京都', N'港区', N'東新橋汐留シティセンター（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057130', N'東京都', N'港区', N'東新橋汐留シティセンター（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057131', N'東京都', N'港区', N'東新橋汐留シティセンター（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057132', N'東京都', N'港区', N'東新橋汐留シティセンター（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057133', N'東京都', N'港区', N'東新橋汐留シティセンター（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057134', N'東京都', N'港区', N'東新橋汐留シティセンター（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057135', N'東京都', N'港区', N'東新橋汐留シティセンター（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057136', N'東京都', N'港区', N'東新橋汐留シティセンター（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057137', N'東京都', N'港区', N'東新橋汐留シティセンター（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057138', N'東京都', N'港区', N'東新橋汐留シティセンター（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057139', N'東京都', N'港区', N'東新橋汐留シティセンター（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057140', N'東京都', N'港区', N'東新橋汐留シティセンター（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057141', N'東京都', N'港区', N'東新橋汐留シティセンター（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057142', N'東京都', N'港区', N'東新橋汐留シティセンター（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057143', N'東京都', N'港区', N'東新橋汐留シティセンター（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057290', N'東京都', N'港区', N'東新橋汐留メディアタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057201', N'東京都', N'港区', N'東新橋汐留メディアタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057202', N'東京都', N'港区', N'東新橋汐留メディアタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057203', N'東京都', N'港区', N'東新橋汐留メディアタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057204', N'東京都', N'港区', N'東新橋汐留メディアタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057205', N'東京都', N'港区', N'東新橋汐留メディアタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057206', N'東京都', N'港区', N'東新橋汐留メディアタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057207', N'東京都', N'港区', N'東新橋汐留メディアタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057208', N'東京都', N'港区', N'東新橋汐留メディアタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057209', N'東京都', N'港区', N'東新橋汐留メディアタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057210', N'東京都', N'港区', N'東新橋汐留メディアタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057211', N'東京都', N'港区', N'東新橋汐留メディアタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057212', N'東京都', N'港区', N'東新橋汐留メディアタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057213', N'東京都', N'港区', N'東新橋汐留メディアタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057214', N'東京都', N'港区', N'東新橋汐留メディアタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057215', N'東京都', N'港区', N'東新橋汐留メディアタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057216', N'東京都', N'港区', N'東新橋汐留メディアタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057217', N'東京都', N'港区', N'東新橋汐留メディアタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057218', N'東京都', N'港区', N'東新橋汐留メディアタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057219', N'東京都', N'港区', N'東新橋汐留メディアタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057220', N'東京都', N'港区', N'東新橋汐留メディアタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057221', N'東京都', N'港区', N'東新橋汐留メディアタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057222', N'東京都', N'港区', N'東新橋汐留メディアタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057223', N'東京都', N'港区', N'東新橋汐留メディアタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057224', N'東京都', N'港区', N'東新橋汐留メディアタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057225', N'東京都', N'港区', N'東新橋汐留メディアタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057226', N'東京都', N'港区', N'東新橋汐留メディアタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057227', N'東京都', N'港区', N'東新橋汐留メディアタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057228', N'東京都', N'港区', N'東新橋汐留メディアタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057229', N'東京都', N'港区', N'東新橋汐留メディアタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057230', N'東京都', N'港区', N'東新橋汐留メディアタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057231', N'東京都', N'港区', N'東新橋汐留メディアタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057232', N'東京都', N'港区', N'東新橋汐留メディアタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057233', N'東京都', N'港区', N'東新橋汐留メディアタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057234', N'東京都', N'港区', N'東新橋汐留メディアタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057090', N'東京都', N'港区', N'東新橋電通本社ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057001', N'東京都', N'港区', N'東新橋電通本社ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057002', N'東京都', N'港区', N'東新橋電通本社ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057003', N'東京都', N'港区', N'東新橋電通本社ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057004', N'東京都', N'港区', N'東新橋電通本社ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057005', N'東京都', N'港区', N'東新橋電通本社ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057006', N'東京都', N'港区', N'東新橋電通本社ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057007', N'東京都', N'港区', N'東新橋電通本社ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057008', N'東京都', N'港区', N'東新橋電通本社ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057009', N'東京都', N'港区', N'東新橋電通本社ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057010', N'東京都', N'港区', N'東新橋電通本社ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057011', N'東京都', N'港区', N'東新橋電通本社ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057012', N'東京都', N'港区', N'東新橋電通本社ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057013', N'東京都', N'港区', N'東新橋電通本社ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057014', N'東京都', N'港区', N'東新橋電通本社ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057015', N'東京都', N'港区', N'東新橋電通本社ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057016', N'東京都', N'港区', N'東新橋電通本社ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057017', N'東京都', N'港区', N'東新橋電通本社ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057018', N'東京都', N'港区', N'東新橋電通本社ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057019', N'東京都', N'港区', N'東新橋電通本社ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057020', N'東京都', N'港区', N'東新橋電通本社ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057021', N'東京都', N'港区', N'東新橋電通本社ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057022', N'東京都', N'港区', N'東新橋電通本社ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057023', N'東京都', N'港区', N'東新橋電通本社ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057024', N'東京都', N'港区', N'東新橋電通本社ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057025', N'東京都', N'港区', N'東新橋電通本社ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057026', N'東京都', N'港区', N'東新橋電通本社ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057027', N'東京都', N'港区', N'東新橋電通本社ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057028', N'東京都', N'港区', N'東新橋電通本社ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057029', N'東京都', N'港区', N'東新橋電通本社ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057030', N'東京都', N'港区', N'東新橋電通本社ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057031', N'東京都', N'港区', N'東新橋電通本社ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057032', N'東京都', N'港区', N'東新橋電通本社ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057033', N'東京都', N'港区', N'東新橋電通本社ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057034', N'東京都', N'港区', N'東新橋電通本社ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057035', N'東京都', N'港区', N'東新橋電通本社ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057036', N'東京都', N'港区', N'東新橋電通本社ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057037', N'東京都', N'港区', N'東新橋電通本社ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057038', N'東京都', N'港区', N'東新橋電通本社ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057039', N'東京都', N'港区', N'東新橋電通本社ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057040', N'東京都', N'港区', N'東新橋電通本社ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057041', N'東京都', N'港区', N'東新橋電通本社ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057042', N'東京都', N'港区', N'東新橋電通本社ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057043', N'東京都', N'港区', N'東新橋電通本社ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057044', N'東京都', N'港区', N'東新橋電通本社ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057045', N'東京都', N'港区', N'東新橋電通本社ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057046', N'東京都', N'港区', N'東新橋電通本社ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057047', N'東京都', N'港区', N'東新橋電通本社ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057390', N'東京都', N'港区', N'東新橋東京汐留ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057301', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057302', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057303', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057304', N'東京都', N'港区', N'東新橋東京汐留ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057305', N'東京都', N'港区', N'東新橋東京汐留ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057306', N'東京都', N'港区', N'東新橋東京汐留ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057307', N'東京都', N'港区', N'東新橋東京汐留ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057308', N'東京都', N'港区', N'東新橋東京汐留ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057309', N'東京都', N'港区', N'東新橋東京汐留ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057310', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057311', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057312', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057313', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057314', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057315', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057316', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057317', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057318', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057319', N'東京都', N'港区', N'東新橋東京汐留ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057320', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057321', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057322', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057323', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057324', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057325', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057326', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057327', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057328', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057329', N'東京都', N'港区', N'東新橋東京汐留ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057330', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057331', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057332', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057333', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057334', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057335', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057336', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057337', N'東京都', N'港区', N'東新橋東京汐留ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057490', N'東京都', N'港区', N'東新橋日本テレビタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057401', N'東京都', N'港区', N'東新橋日本テレビタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057402', N'東京都', N'港区', N'東新橋日本テレビタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057403', N'東京都', N'港区', N'東新橋日本テレビタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057404', N'東京都', N'港区', N'東新橋日本テレビタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057405', N'東京都', N'港区', N'東新橋日本テレビタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057406', N'東京都', N'港区', N'東新橋日本テレビタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057407', N'東京都', N'港区', N'東新橋日本テレビタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057408', N'東京都', N'港区', N'東新橋日本テレビタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057409', N'東京都', N'港区', N'東新橋日本テレビタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057410', N'東京都', N'港区', N'東新橋日本テレビタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057411', N'東京都', N'港区', N'東新橋日本テレビタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057412', N'東京都', N'港区', N'東新橋日本テレビタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057413', N'東京都', N'港区', N'東新橋日本テレビタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057414', N'東京都', N'港区', N'東新橋日本テレビタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057415', N'東京都', N'港区', N'東新橋日本テレビタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057416', N'東京都', N'港区', N'東新橋日本テレビタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057417', N'東京都', N'港区', N'東新橋日本テレビタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057418', N'東京都', N'港区', N'東新橋日本テレビタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057419', N'東京都', N'港区', N'東新橋日本テレビタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057420', N'東京都', N'港区', N'東新橋日本テレビタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057421', N'東京都', N'港区', N'東新橋日本テレビタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057422', N'東京都', N'港区', N'東新橋日本テレビタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057423', N'東京都', N'港区', N'東新橋日本テレビタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057424', N'東京都', N'港区', N'東新橋日本テレビタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057425', N'東京都', N'港区', N'東新橋日本テレビタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057426', N'東京都', N'港区', N'東新橋日本テレビタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057427', N'東京都', N'港区', N'東新橋日本テレビタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057428', N'東京都', N'港区', N'東新橋日本テレビタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057429', N'東京都', N'港区', N'東新橋日本テレビタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057430', N'東京都', N'港区', N'東新橋日本テレビタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057431', N'東京都', N'港区', N'東新橋日本テレビタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057432', N'東京都', N'港区', N'東新橋日本テレビタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080073', N'東京都', N'港区', N'三田（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086390', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086301', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086302', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086303', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086304', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086305', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086306', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086307', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086308', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086309', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086310', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086311', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086312', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086313', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086314', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086315', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086316', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086317', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086318', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086319', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086320', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086321', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086322', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086323', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086324', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086325', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086326', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086327', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086328', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086329', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086330', N'東京都', N'港区', N'三田住友不動産三田ツインビル西館（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070062', N'東京都', N'港区', N'南青山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060047', N'東京都', N'港区', N'南麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070051', N'東京都', N'港区', N'元赤坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060046', N'東京都', N'港区', N'元麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060032', N'東京都', N'港区', N'六本木（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066090', N'東京都', N'港区', N'六本木泉ガーデンタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066001', N'東京都', N'港区', N'六本木泉ガーデンタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066002', N'東京都', N'港区', N'六本木泉ガーデンタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066003', N'東京都', N'港区', N'六本木泉ガーデンタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066004', N'東京都', N'港区', N'六本木泉ガーデンタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066005', N'東京都', N'港区', N'六本木泉ガーデンタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066006', N'東京都', N'港区', N'六本木泉ガーデンタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066007', N'東京都', N'港区', N'六本木泉ガーデンタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066008', N'東京都', N'港区', N'六本木泉ガーデンタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066009', N'東京都', N'港区', N'六本木泉ガーデンタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066010', N'東京都', N'港区', N'六本木泉ガーデンタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066011', N'東京都', N'港区', N'六本木泉ガーデンタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066012', N'東京都', N'港区', N'六本木泉ガーデンタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066013', N'東京都', N'港区', N'六本木泉ガーデンタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066014', N'東京都', N'港区', N'六本木泉ガーデンタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066015', N'東京都', N'港区', N'六本木泉ガーデンタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066016', N'東京都', N'港区', N'六本木泉ガーデンタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066017', N'東京都', N'港区', N'六本木泉ガーデンタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066018', N'東京都', N'港区', N'六本木泉ガーデンタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066019', N'東京都', N'港区', N'六本木泉ガーデンタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066020', N'東京都', N'港区', N'六本木泉ガーデンタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066021', N'東京都', N'港区', N'六本木泉ガーデンタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066022', N'東京都', N'港区', N'六本木泉ガーデンタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066023', N'東京都', N'港区', N'六本木泉ガーデンタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066024', N'東京都', N'港区', N'六本木泉ガーデンタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066025', N'東京都', N'港区', N'六本木泉ガーデンタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066026', N'東京都', N'港区', N'六本木泉ガーデンタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066027', N'東京都', N'港区', N'六本木泉ガーデンタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066028', N'東京都', N'港区', N'六本木泉ガーデンタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066029', N'東京都', N'港区', N'六本木泉ガーデンタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066030', N'東京都', N'港区', N'六本木泉ガーデンタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066031', N'東京都', N'港区', N'六本木泉ガーデンタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066032', N'東京都', N'港区', N'六本木泉ガーデンタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066033', N'東京都', N'港区', N'六本木泉ガーデンタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066034', N'東京都', N'港区', N'六本木泉ガーデンタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066035', N'東京都', N'港区', N'六本木泉ガーデンタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066036', N'東京都', N'港区', N'六本木泉ガーデンタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066037', N'東京都', N'港区', N'六本木泉ガーデンタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066038', N'東京都', N'港区', N'六本木泉ガーデンタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066039', N'東京都', N'港区', N'六本木泉ガーデンタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066040', N'東京都', N'港区', N'六本木泉ガーデンタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066041', N'東京都', N'港区', N'六本木泉ガーデンタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066042', N'東京都', N'港区', N'六本木泉ガーデンタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066043', N'東京都', N'港区', N'六本木泉ガーデンタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066044', N'東京都', N'港区', N'六本木泉ガーデンタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066045', N'東京都', N'港区', N'六本木泉ガーデンタワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066190', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066101', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066102', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066103', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066104', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066105', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066106', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066107', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066108', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066109', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066110', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066111', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066112', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066113', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066114', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066115', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066116', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066117', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066118', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066119', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066120', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066121', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066122', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066123', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066124', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066125', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066126', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066127', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066128', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066129', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066130', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066131', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066132', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066133', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066134', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066135', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066136', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066137', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066138', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066139', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066140', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066141', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066142', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066143', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066144', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066145', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066146', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066147', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066148', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066149', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066150', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066151', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066152', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066153', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066154', N'東京都', N'港区', N'六本木六本木ヒルズ森タワー（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600000', N'東京都', N'新宿区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600005', N'東京都', N'新宿区', N'愛住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620803', N'東京都', N'新宿区', N'赤城下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620817', N'東京都', N'新宿区', N'赤城元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620824', N'東京都', N'新宿区', N'揚場町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600007', N'東京都', N'新宿区', N'荒木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620062', N'東京都', N'新宿区', N'市谷加賀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620856', N'東京都', N'新宿区', N'市谷甲良町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620842', N'東京都', N'新宿区', N'市谷砂土原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620846', N'東京都', N'新宿区', N'市谷左内町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620848', N'東京都', N'新宿区', N'市谷鷹匠町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620843', N'東京都', N'新宿区', N'市谷田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620066', N'東京都', N'新宿区', N'市谷台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620847', N'東京都', N'新宿区', N'市谷長延寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620064', N'東京都', N'新宿区', N'市谷仲之町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620844', N'東京都', N'新宿区', N'市谷八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620826', N'東京都', N'新宿区', N'市谷船河原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620845', N'東京都', N'新宿区', N'市谷本村町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620063', N'東京都', N'新宿区', N'市谷薬王寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620061', N'東京都', N'新宿区', N'市谷柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620857', N'東京都', N'新宿区', N'市谷山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620832', N'東京都', N'新宿区', N'岩戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620806', N'東京都', N'新宿区', N'榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690072', N'東京都', N'新宿区', N'大久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620802', N'東京都', N'新宿区', N'改代町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620823', N'東京都', N'新宿区', N'神楽河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620825', N'東京都', N'新宿区', N'神楽坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600013', N'東京都', N'新宿区', N'霞ケ丘町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600001', N'東京都', N'新宿区', N'片町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600021', N'東京都', N'新宿区', N'歌舞伎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610034', N'東京都', N'新宿区', N'上落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620054', N'東京都', N'新宿区', N'河田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620044', N'東京都', N'新宿区', N'喜久井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690074', N'東京都', N'新宿区', N'北新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620834', N'東京都', N'新宿区', N'北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620853', N'東京都', N'新宿区', N'北山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620838', N'東京都', N'新宿区', N'細工町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600002', N'東京都', N'新宿区', N'坂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600017', N'東京都', N'新宿区', N'左門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600008', N'東京都', N'新宿区', N'三栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600016', N'東京都', N'新宿区', N'信濃町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610033', N'東京都', N'新宿区', N'下落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620822', N'東京都', N'新宿区', N'下宮比町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620816', N'東京都', N'新宿区', N'白銀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620814', N'東京都', N'新宿区', N'新小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600022', N'東京都', N'新宿区', N'新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620811', N'東京都', N'新宿区', N'水道町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600018', N'東京都', N'新宿区', N'須賀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620065', N'東京都', N'新宿区', N'住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600015', N'東京都', N'新宿区', N'大京町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690075', N'東京都', N'新宿区', N'高田馬場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620833', N'東京都', N'新宿区', N'箪笥町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620818', N'東京都', N'新宿区', N'築地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620821', N'東京都', N'新宿区', N'津久戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620815', N'東京都', N'新宿区', N'筑土八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620808', N'東京都', N'新宿区', N'天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690071', N'東京都', N'新宿区', N'戸塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620067', N'東京都', N'新宿区', N'富久町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690052', N'東京都', N'新宿区', N'戸山（３丁目１８・２１番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620052', N'東京都', N'新宿区', N'戸山（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600014', N'東京都', N'新宿区', N'内藤町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610035', N'東京都', N'新宿区', N'中井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610032', N'東京都', N'新宿区', N'中落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620804', N'東京都', N'新宿区', N'中里町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620835', N'東京都', N'新宿区', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620837', N'東京都', N'新宿区', N'納戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610031', N'東京都', N'新宿区', N'西落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620812', N'東京都', N'新宿区', N'西五軒町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600023', N'東京都', N'新宿区', N'西新宿（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631390', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631301', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631302', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631303', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631304', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631305', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631306', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631307', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631308', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631309', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631310', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631311', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631312', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631313', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631314', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631315', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631316', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631317', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631318', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631319', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631320', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631321', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631322', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631323', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631324', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631325', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631326', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631327', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631328', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631329', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631330', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631331', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631332', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631333', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631334', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631335', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631336', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631337', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631338', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631339', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631340', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631341', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631342', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631343', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631344', N'東京都', N'新宿区', N'西新宿新宿アイランドタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630890', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630801', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630802', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630803', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630804', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630805', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630806', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630807', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630808', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630809', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630810', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630811', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630812', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630813', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630814', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630815', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630816', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630817', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630818', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630819', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630820', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630821', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630822', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630823', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630824', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630825', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630826', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630827', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630828', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630829', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630830', N'東京都', N'新宿区', N'西新宿新宿ＮＳビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631590', N'東京都', N'新宿区', N'西新宿新宿エルタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631501', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631502', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631503', N'東京都', N'新宿区', N'西新宿新宿エルタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631504', N'東京都', N'新宿区', N'西新宿新宿エルタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631505', N'東京都', N'新宿区', N'西新宿新宿エルタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631506', N'東京都', N'新宿区', N'西新宿新宿エルタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631507', N'東京都', N'新宿区', N'西新宿新宿エルタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631508', N'東京都', N'新宿区', N'西新宿新宿エルタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631509', N'東京都', N'新宿区', N'西新宿新宿エルタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631510', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631511', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631512', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631513', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631514', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631515', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631516', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631517', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631518', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631519', N'東京都', N'新宿区', N'西新宿新宿エルタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631520', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631521', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631522', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631523', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631524', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631525', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631526', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631527', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631528', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631529', N'東京都', N'新宿区', N'西新宿新宿エルタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631530', N'東京都', N'新宿区', N'西新宿新宿エルタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631531', N'東京都', N'新宿区', N'西新宿新宿エルタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631190', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631101', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631102', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631103', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631104', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631105', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631106', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631107', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631108', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631109', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631110', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631111', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631112', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631113', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631114', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631115', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631116', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631117', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631118', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631119', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631120', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631121', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631122', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631123', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631124', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631125', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631126', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631127', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631128', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631129', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631130', N'東京都', N'新宿区', N'西新宿新宿スクエアタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630290', N'東京都', N'新宿区', N'西新宿新宿住友ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630201', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630202', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630203', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630204', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630205', N'東京都', N'新宿区', N'西新宿新宿住友ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630206', N'東京都', N'新宿区', N'西新宿新宿住友ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630207', N'東京都', N'新宿区', N'西新宿新宿住友ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630208', N'東京都', N'新宿区', N'西新宿新宿住友ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630209', N'東京都', N'新宿区', N'西新宿新宿住友ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630210', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630211', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630212', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630213', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630214', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630215', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630216', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630217', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630218', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630219', N'東京都', N'新宿区', N'西新宿新宿住友ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630220', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630221', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630222', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630223', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630224', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630225', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630226', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630227', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630228', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630229', N'東京都', N'新宿区', N'西新宿新宿住友ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630230', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630231', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630232', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630233', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630234', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630235', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630236', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630237', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630238', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630239', N'東京都', N'新宿区', N'西新宿新宿住友ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630240', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630241', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630242', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630243', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630244', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630245', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630246', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630247', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630248', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630249', N'東京都', N'新宿区', N'西新宿新宿住友ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630250', N'東京都', N'新宿区', N'西新宿新宿住友ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630251', N'東京都', N'新宿区', N'西新宿新宿住友ビル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630252', N'東京都', N'新宿区', N'西新宿新宿住友ビル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630690', N'東京都', N'新宿区', N'西新宿新宿センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630601', N'東京都', N'新宿区', N'西新宿新宿センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630602', N'東京都', N'新宿区', N'西新宿新宿センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630603', N'東京都', N'新宿区', N'西新宿新宿センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630604', N'東京都', N'新宿区', N'西新宿新宿センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630605', N'東京都', N'新宿区', N'西新宿新宿センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630606', N'東京都', N'新宿区', N'西新宿新宿センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630607', N'東京都', N'新宿区', N'西新宿新宿センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630608', N'東京都', N'新宿区', N'西新宿新宿センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630609', N'東京都', N'新宿区', N'西新宿新宿センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630610', N'東京都', N'新宿区', N'西新宿新宿センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630611', N'東京都', N'新宿区', N'西新宿新宿センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630612', N'東京都', N'新宿区', N'西新宿新宿センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630613', N'東京都', N'新宿区', N'西新宿新宿センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630614', N'東京都', N'新宿区', N'西新宿新宿センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630615', N'東京都', N'新宿区', N'西新宿新宿センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630616', N'東京都', N'新宿区', N'西新宿新宿センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630617', N'東京都', N'新宿区', N'西新宿新宿センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630618', N'東京都', N'新宿区', N'西新宿新宿センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630619', N'東京都', N'新宿区', N'西新宿新宿センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630620', N'東京都', N'新宿区', N'西新宿新宿センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630621', N'東京都', N'新宿区', N'西新宿新宿センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630622', N'東京都', N'新宿区', N'西新宿新宿センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630623', N'東京都', N'新宿区', N'西新宿新宿センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630624', N'東京都', N'新宿区', N'西新宿新宿センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630625', N'東京都', N'新宿区', N'西新宿新宿センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630626', N'東京都', N'新宿区', N'西新宿新宿センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630627', N'東京都', N'新宿区', N'西新宿新宿センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630628', N'東京都', N'新宿区', N'西新宿新宿センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630629', N'東京都', N'新宿区', N'西新宿新宿センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630630', N'東京都', N'新宿区', N'西新宿新宿センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630631', N'東京都', N'新宿区', N'西新宿新宿センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630632', N'東京都', N'新宿区', N'西新宿新宿センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630633', N'東京都', N'新宿区', N'西新宿新宿センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630634', N'東京都', N'新宿区', N'西新宿新宿センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630635', N'東京都', N'新宿区', N'西新宿新宿センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630636', N'東京都', N'新宿区', N'西新宿新宿センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630637', N'東京都', N'新宿区', N'西新宿新宿センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630638', N'東京都', N'新宿区', N'西新宿新宿センタービル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630639', N'東京都', N'新宿区', N'西新宿新宿センタービル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630640', N'東京都', N'新宿区', N'西新宿新宿センタービル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630641', N'東京都', N'新宿区', N'西新宿新宿センタービル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630642', N'東京都', N'新宿区', N'西新宿新宿センタービル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630643', N'東京都', N'新宿区', N'西新宿新宿センタービル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630644', N'東京都', N'新宿区', N'西新宿新宿センタービル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630645', N'東京都', N'新宿区', N'西新宿新宿センタービル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630646', N'東京都', N'新宿区', N'西新宿新宿センタービル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630647', N'東京都', N'新宿区', N'西新宿新宿センタービル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630648', N'東京都', N'新宿区', N'西新宿新宿センタービル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630649', N'東京都', N'新宿区', N'西新宿新宿センタービル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630650', N'東京都', N'新宿区', N'西新宿新宿センタービル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630651', N'東京都', N'新宿区', N'西新宿新宿センタービル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630652', N'東京都', N'新宿区', N'西新宿新宿センタービル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630653', N'東京都', N'新宿区', N'西新宿新宿センタービル（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630654', N'東京都', N'新宿区', N'西新宿新宿センタービル（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630790', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630701', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630702', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630703', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630704', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630705', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630706', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630707', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630708', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630709', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630710', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630711', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630712', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630713', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630714', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630715', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630716', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630717', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630718', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630719', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630720', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630721', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630722', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630723', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630724', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630725', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630726', N'東京都', N'新宿区', N'西新宿新宿第一生命ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630590', N'東京都', N'新宿区', N'西新宿新宿野村ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630501', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630502', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630503', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630504', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630505', N'東京都', N'新宿区', N'西新宿新宿野村ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630506', N'東京都', N'新宿区', N'西新宿新宿野村ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630507', N'東京都', N'新宿区', N'西新宿新宿野村ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630508', N'東京都', N'新宿区', N'西新宿新宿野村ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630509', N'東京都', N'新宿区', N'西新宿新宿野村ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630510', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630511', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630512', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630513', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630514', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630515', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630516', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630517', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630518', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630519', N'東京都', N'新宿区', N'西新宿新宿野村ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630520', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630521', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630522', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630523', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630524', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630525', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630526', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630527', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630528', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630529', N'東京都', N'新宿区', N'西新宿新宿野村ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630530', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630531', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630532', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630533', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630534', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630535', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630536', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630537', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630538', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630539', N'東京都', N'新宿区', N'西新宿新宿野村ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630540', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630541', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630542', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630543', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630544', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630545', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630546', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630547', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630548', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630549', N'東京都', N'新宿区', N'西新宿新宿野村ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630550', N'東京都', N'新宿区', N'西新宿新宿野村ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631090', N'東京都', N'新宿区', N'西新宿新宿パークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631001', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631002', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631003', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631004', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631005', N'東京都', N'新宿区', N'西新宿新宿パークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631006', N'東京都', N'新宿区', N'西新宿新宿パークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631007', N'東京都', N'新宿区', N'西新宿新宿パークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631008', N'東京都', N'新宿区', N'西新宿新宿パークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631009', N'東京都', N'新宿区', N'西新宿新宿パークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631010', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631011', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631012', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631013', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631014', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631015', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631016', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631017', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631018', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631019', N'東京都', N'新宿区', N'西新宿新宿パークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631020', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631021', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631022', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631023', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631024', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631025', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631026', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631027', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631028', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631029', N'東京都', N'新宿区', N'西新宿新宿パークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631030', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631031', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631032', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631033', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631034', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631035', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631036', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631037', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631038', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631039', N'東京都', N'新宿区', N'西新宿新宿パークタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631040', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631041', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631042', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631043', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631044', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631045', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631046', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631047', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631048', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631049', N'東京都', N'新宿区', N'西新宿新宿パークタワー（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631050', N'東京都', N'新宿区', N'西新宿新宿パークタワー（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631051', N'東京都', N'新宿区', N'西新宿新宿パークタワー（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631052', N'東京都', N'新宿区', N'西新宿新宿パークタワー（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630490', N'東京都', N'新宿区', N'西新宿新宿三井ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630401', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630402', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630403', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630404', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630405', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630406', N'東京都', N'新宿区', N'西新宿新宿三井ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630407', N'東京都', N'新宿区', N'西新宿新宿三井ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630408', N'東京都', N'新宿区', N'西新宿新宿三井ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630409', N'東京都', N'新宿区', N'西新宿新宿三井ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630410', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630411', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630412', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630413', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630414', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630415', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630416', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630417', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630418', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630419', N'東京都', N'新宿区', N'西新宿新宿三井ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630420', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630421', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630422', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630423', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630424', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630425', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630426', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630427', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630428', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630429', N'東京都', N'新宿区', N'西新宿新宿三井ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630430', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630431', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630432', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630433', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630434', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630435', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630436', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630437', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630438', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630439', N'東京都', N'新宿区', N'西新宿新宿三井ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630440', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630441', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630442', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630443', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630444', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630445', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630446', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630447', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630448', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630449', N'東京都', N'新宿区', N'西新宿新宿三井ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630450', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630451', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630452', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630453', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630454', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630455', N'東京都', N'新宿区', N'西新宿新宿三井ビル（５５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630990', N'東京都', N'新宿区', N'西新宿新宿モノリス（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630901', N'東京都', N'新宿区', N'西新宿新宿モノリス（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630902', N'東京都', N'新宿区', N'西新宿新宿モノリス（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630903', N'東京都', N'新宿区', N'西新宿新宿モノリス（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630904', N'東京都', N'新宿区', N'西新宿新宿モノリス（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630905', N'東京都', N'新宿区', N'西新宿新宿モノリス（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630906', N'東京都', N'新宿区', N'西新宿新宿モノリス（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630907', N'東京都', N'新宿区', N'西新宿新宿モノリス（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630908', N'東京都', N'新宿区', N'西新宿新宿モノリス（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630909', N'東京都', N'新宿区', N'西新宿新宿モノリス（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630910', N'東京都', N'新宿区', N'西新宿新宿モノリス（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630911', N'東京都', N'新宿区', N'西新宿新宿モノリス（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630912', N'東京都', N'新宿区', N'西新宿新宿モノリス（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630913', N'東京都', N'新宿区', N'西新宿新宿モノリス（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630914', N'東京都', N'新宿区', N'西新宿新宿モノリス（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630915', N'東京都', N'新宿区', N'西新宿新宿モノリス（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630916', N'東京都', N'新宿区', N'西新宿新宿モノリス（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630917', N'東京都', N'新宿区', N'西新宿新宿モノリス（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630918', N'東京都', N'新宿区', N'西新宿新宿モノリス（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630919', N'東京都', N'新宿区', N'西新宿新宿モノリス（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630920', N'東京都', N'新宿区', N'西新宿新宿モノリス（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630921', N'東京都', N'新宿区', N'西新宿新宿モノリス（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630922', N'東京都', N'新宿区', N'西新宿新宿モノリス（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630923', N'東京都', N'新宿区', N'西新宿新宿モノリス（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630924', N'東京都', N'新宿区', N'西新宿新宿モノリス（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630925', N'東京都', N'新宿区', N'西新宿新宿モノリス（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630926', N'東京都', N'新宿区', N'西新宿新宿モノリス（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630927', N'東京都', N'新宿区', N'西新宿新宿モノリス（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630928', N'東京都', N'新宿区', N'西新宿新宿モノリス（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630929', N'東京都', N'新宿区', N'西新宿新宿モノリス（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630930', N'東京都', N'新宿区', N'西新宿新宿モノリス（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636090', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636001', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636002', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636003', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636004', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636005', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636006', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636007', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636008', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636009', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636010', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636011', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636012', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636013', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636014', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636015', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636016', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636017', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636018', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636019', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636020', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636021', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636022', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636023', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636024', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636025', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636026', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636027', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636028', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636029', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636030', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636031', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636032', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636033', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636034', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636035', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636036', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636037', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636038', N'東京都', N'新宿区', N'西新宿住友不動産新宿オークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631490', N'東京都', N'新宿区', N'西新宿東京オペラシティ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631401', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631402', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631403', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631404', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631405', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631406', N'東京都', N'新宿区', N'西新宿東京オペラシティ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631407', N'東京都', N'新宿区', N'西新宿東京オペラシティ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631408', N'東京都', N'新宿区', N'西新宿東京オペラシティ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631409', N'東京都', N'新宿区', N'西新宿東京オペラシティ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631410', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631411', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631412', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631413', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631414', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631415', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631416', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631417', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631418', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631419', N'東京都', N'新宿区', N'西新宿東京オペラシティ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631420', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631421', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631422', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631423', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631424', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631425', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631426', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631427', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631428', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631429', N'東京都', N'新宿区', N'西新宿東京オペラシティ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631430', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631431', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631432', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631433', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631434', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631435', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631436', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631437', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631438', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631439', N'東京都', N'新宿区', N'西新宿東京オペラシティ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631440', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631441', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631442', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631443', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631444', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631445', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631446', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631447', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631448', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631449', N'東京都', N'新宿区', N'西新宿東京オペラシティ（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631450', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631451', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631452', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631453', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631454', N'東京都', N'新宿区', N'西新宿東京オペラシティ（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620051', N'東京都', N'新宿区', N'西早稲田（２丁目１番１〜２３号、２番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690051', N'東京都', N'新宿区', N'西早稲田（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620855', N'東京都', N'新宿区', N'二十騎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620045', N'東京都', N'新宿区', N'馬場下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620841', N'東京都', N'新宿区', N'払方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620053', N'東京都', N'新宿区', N'原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620807', N'東京都', N'新宿区', N'東榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620813', N'東京都', N'新宿区', N'東五軒町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690073', N'東京都', N'新宿区', N'百人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620828', N'東京都', N'新宿区', N'袋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600006', N'東京都', N'新宿区', N'舟町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620851', N'東京都', N'新宿区', N'弁天町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600003', N'東京都', N'新宿区', N'本塩町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620852', N'東京都', N'新宿区', N'南榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620836', N'東京都', N'新宿区', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600012', N'東京都', N'新宿区', N'南元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620854', N'東京都', N'新宿区', N'南山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620801', N'東京都', N'新宿区', N'山吹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620805', N'東京都', N'新宿区', N'矢来町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620831', N'東京都', N'新宿区', N'横寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620055', N'東京都', N'新宿区', N'余丁町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600004', N'東京都', N'新宿区', N'四谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600011', N'東京都', N'新宿区', N'若葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620056', N'東京都', N'新宿区', N'若松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620827', N'東京都', N'新宿区', N'若宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620041', N'東京都', N'新宿区', N'早稲田鶴巻町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620043', N'東京都', N'新宿区', N'早稲田南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620042', N'東京都', N'新宿区', N'早稲田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120000', N'東京都', N'文京区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120012', N'東京都', N'文京区', N'大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120013', N'東京都', N'文京区', N'音羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120003', N'東京都', N'文京区', N'春日');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120002', N'東京都', N'文京区', N'小石川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120004', N'東京都', N'文京区', N'後楽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120006', N'東京都', N'文京区', N'小日向');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120005', N'東京都', N'文京区', N'水道');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120014', N'東京都', N'文京区', N'関口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120011', N'東京都', N'文京区', N'千石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130022', N'東京都', N'文京区', N'千駄木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130024', N'東京都', N'文京区', N'西片');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130031', N'東京都', N'文京区', N'根津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130001', N'東京都', N'文京区', N'白山（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120001', N'東京都', N'文京区', N'白山（２〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130021', N'東京都', N'文京区', N'本駒込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130033', N'東京都', N'文京区', N'本郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130023', N'東京都', N'文京区', N'向丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120015', N'東京都', N'文京区', N'目白台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130032', N'東京都', N'文京区', N'弥生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130034', N'東京都', N'文京区', N'湯島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100000', N'東京都', N'台東区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100006', N'東京都', N'台東区', N'秋葉原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110032', N'東京都', N'台東区', N'浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110053', N'東京都', N'台東区', N'浅草橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100008', N'東京都', N'台東区', N'池之端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110024', N'東京都', N'台東区', N'今戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100013', N'東京都', N'台東区', N'入谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100005', N'東京都', N'台東区', N'上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100007', N'東京都', N'台東区', N'上野公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100002', N'東京都', N'台東区', N'上野桜木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110034', N'東京都', N'台東区', N'雷門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100014', N'東京都', N'台東区', N'北上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110022', N'東京都', N'台東区', N'清川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110051', N'東京都', N'台東区', N'蔵前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110056', N'東京都', N'台東区', N'小島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110042', N'東京都', N'台東区', N'寿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110043', N'東京都', N'台東区', N'駒形');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100004', N'東京都', N'台東区', N'下谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110031', N'東京都', N'台東区', N'千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100016', N'東京都', N'台東区', N'台東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110054', N'東京都', N'台東区', N'鳥越');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110035', N'東京都', N'台東区', N'西浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110021', N'東京都', N'台東区', N'日本堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100003', N'東京都', N'台東区', N'根岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110023', N'東京都', N'台東区', N'橋場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110033', N'東京都', N'台東区', N'花川戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110025', N'東京都', N'台東区', N'東浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100015', N'東京都', N'台東区', N'東上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110036', N'東京都', N'台東区', N'松が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110055', N'東京都', N'台東区', N'三筋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100011', N'東京都', N'台東区', N'三ノ輪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110041', N'東京都', N'台東区', N'元浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100001', N'東京都', N'台東区', N'谷中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110052', N'東京都', N'台東区', N'柳橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100012', N'東京都', N'台東区', N'竜泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300000', N'東京都', N'墨田区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300001', N'東京都', N'墨田区', N'吾妻橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300011', N'東京都', N'墨田区', N'石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310045', N'東京都', N'墨田区', N'押上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300014', N'東京都', N'墨田区', N'亀沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300024', N'東京都', N'墨田区', N'菊川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310046', N'東京都', N'墨田区', N'京島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300013', N'東京都', N'墨田区', N'錦糸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300022', N'東京都', N'墨田区', N'江東橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310031', N'東京都', N'墨田区', N'墨田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300012', N'東京都', N'墨田区', N'太平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310043', N'東京都', N'墨田区', N'立花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300023', N'東京都', N'墨田区', N'立川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300025', N'東京都', N'墨田区', N'千歳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310034', N'東京都', N'墨田区', N'堤通');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300002', N'東京都', N'墨田区', N'業平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300005', N'東京都', N'墨田区', N'東駒形');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310042', N'東京都', N'墨田区', N'東墨田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310032', N'東京都', N'墨田区', N'東向島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310044', N'東京都', N'墨田区', N'文花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300004', N'東京都', N'墨田区', N'本所');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300021', N'東京都', N'墨田区', N'緑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310033', N'東京都', N'墨田区', N'向島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310041', N'東京都', N'墨田区', N'八広');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300015', N'東京都', N'墨田区', N'横網');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300003', N'東京都', N'墨田区', N'横川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300026', N'東京都', N'墨田区', N'両国');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350000', N'東京都', N'江東区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350064', N'東京都', N'江東区', N'青海');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350063', N'東京都', N'江東区', N'有明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350014', N'東京都', N'江東区', N'石島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350012', N'東京都', N'江東区', N'海辺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350034', N'東京都', N'江東区', N'永代');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350051', N'東京都', N'江東区', N'枝川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350044', N'東京都', N'江東区', N'越中島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350011', N'東京都', N'江東区', N'扇橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360072', N'東京都', N'江東区', N'大島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360071', N'東京都', N'江東区', N'亀戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360073', N'東京都', N'江東区', N'北砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350042', N'東京都', N'江東区', N'木場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350024', N'東京都', N'江東区', N'清澄');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350031', N'東京都', N'江東区', N'佐賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350003', N'東京都', N'江東区', N'猿江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350043', N'東京都', N'江東区', N'塩浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350052', N'東京都', N'江東区', N'潮見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350062', N'東京都', N'江東区', N'東雲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350021', N'東京都', N'江東区', N'白河');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350007', N'東京都', N'江東区', N'新大橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360082', N'東京都', N'江東区', N'新木場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360075', N'東京都', N'江東区', N'新砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350002', N'東京都', N'江東区', N'住吉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350015', N'東京都', N'江東区', N'千石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350013', N'東京都', N'江東区', N'千田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350005', N'東京都', N'江東区', N'高橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350053', N'東京都', N'江東区', N'辰巳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350065', N'東京都', N'江東区', N'中央防波堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350016', N'東京都', N'江東区', N'東陽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350006', N'東京都', N'江東区', N'常盤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350047', N'東京都', N'江東区', N'富岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350061', N'東京都', N'江東区', N'豊洲（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356090', N'東京都', N'江東区', N'豊洲豊洲センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356001', N'東京都', N'江東区', N'豊洲豊洲センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356002', N'東京都', N'江東区', N'豊洲豊洲センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356003', N'東京都', N'江東区', N'豊洲豊洲センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356004', N'東京都', N'江東区', N'豊洲豊洲センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356005', N'東京都', N'江東区', N'豊洲豊洲センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356006', N'東京都', N'江東区', N'豊洲豊洲センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356007', N'東京都', N'江東区', N'豊洲豊洲センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356008', N'東京都', N'江東区', N'豊洲豊洲センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356009', N'東京都', N'江東区', N'豊洲豊洲センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356010', N'東京都', N'江東区', N'豊洲豊洲センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356011', N'東京都', N'江東区', N'豊洲豊洲センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356012', N'東京都', N'江東区', N'豊洲豊洲センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356013', N'東京都', N'江東区', N'豊洲豊洲センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356014', N'東京都', N'江東区', N'豊洲豊洲センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356015', N'東京都', N'江東区', N'豊洲豊洲センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356016', N'東京都', N'江東区', N'豊洲豊洲センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356017', N'東京都', N'江東区', N'豊洲豊洲センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356018', N'東京都', N'江東区', N'豊洲豊洲センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356019', N'東京都', N'江東区', N'豊洲豊洲センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356020', N'東京都', N'江東区', N'豊洲豊洲センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356021', N'東京都', N'江東区', N'豊洲豊洲センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356022', N'東京都', N'江東区', N'豊洲豊洲センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356023', N'東京都', N'江東区', N'豊洲豊洲センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356024', N'東京都', N'江東区', N'豊洲豊洲センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356025', N'東京都', N'江東区', N'豊洲豊洲センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356026', N'東京都', N'江東区', N'豊洲豊洲センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356027', N'東京都', N'江東区', N'豊洲豊洲センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356028', N'東京都', N'江東区', N'豊洲豊洲センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356029', N'東京都', N'江東区', N'豊洲豊洲センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356030', N'東京都', N'江東区', N'豊洲豊洲センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356031', N'東京都', N'江東区', N'豊洲豊洲センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356032', N'東京都', N'江東区', N'豊洲豊洲センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356033', N'東京都', N'江東区', N'豊洲豊洲センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356034', N'東京都', N'江東区', N'豊洲豊洲センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356035', N'東京都', N'江東区', N'豊洲豊洲センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356036', N'東京都', N'江東区', N'豊洲豊洲センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356037', N'東京都', N'江東区', N'豊洲豊洲センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360074', N'東京都', N'江東区', N'東砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350023', N'東京都', N'江東区', N'平野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350033', N'東京都', N'江東区', N'深川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350032', N'東京都', N'江東区', N'福住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350041', N'東京都', N'江東区', N'冬木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350045', N'東京都', N'江東区', N'古石場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350046', N'東京都', N'江東区', N'牡丹');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360076', N'東京都', N'江東区', N'南砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350022', N'東京都', N'江東区', N'三好');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350001', N'東京都', N'江東区', N'毛利');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350004', N'東京都', N'江東区', N'森下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350048', N'東京都', N'江東区', N'門前仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360081', N'東京都', N'江東区', N'夢の島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360083', N'東京都', N'江東区', N'若洲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400000', N'東京都', N'品川区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420063', N'東京都', N'品川区', N'荏原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400014', N'東京都', N'品川区', N'大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410032', N'東京都', N'品川区', N'大崎（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416090', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416001', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416002', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416003', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416004', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416005', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416006', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416007', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416008', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416009', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416010', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416011', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416012', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416013', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416014', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416015', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416016', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416017', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416018', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416019', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416020', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416021', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416022', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416023', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416024', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416025', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416026', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416027', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416028', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416029', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416030', N'東京都', N'品川区', N'大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400012', N'東京都', N'品川区', N'勝島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410021', N'東京都', N'品川区', N'上大崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400001', N'東京都', N'品川区', N'北品川（１〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410001', N'東京都', N'品川区', N'北品川（５、６丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420062', N'東京都', N'品川区', N'小山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420061', N'東京都', N'品川区', N'小山台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420041', N'東京都', N'品川区', N'戸越');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420053', N'東京都', N'品川区', N'中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400015', N'東京都', N'品川区', N'西大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410031', N'東京都', N'品川区', N'西五反田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410033', N'東京都', N'品川区', N'西品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420054', N'東京都', N'品川区', N'西中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420064', N'東京都', N'品川区', N'旗の台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400011', N'東京都', N'品川区', N'東大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410022', N'東京都', N'品川区', N'東五反田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400002', N'東京都', N'品川区', N'東品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420052', N'東京都', N'品川区', N'東中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350092', N'東京都', N'品川区', N'東八潮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420051', N'東京都', N'品川区', N'平塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400005', N'東京都', N'品川区', N'広町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420043', N'東京都', N'品川区', N'二葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400013', N'東京都', N'品川区', N'南大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400004', N'東京都', N'品川区', N'南品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400003', N'東京都', N'品川区', N'八潮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420042', N'東京都', N'品川区', N'豊町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520000', N'東京都', N'目黒区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530042', N'東京都', N'目黒区', N'青葉台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520033', N'東京都', N'目黒区', N'大岡山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530044', N'東京都', N'目黒区', N'大橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520022', N'東京都', N'目黒区', N'柿の木坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530051', N'東京都', N'目黒区', N'上目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530053', N'東京都', N'目黒区', N'五本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530041', N'東京都', N'目黒区', N'駒場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530064', N'東京都', N'目黒区', N'下目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520035', N'東京都', N'目黒区', N'自由が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520012', N'東京都', N'目黒区', N'洗足');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520032', N'東京都', N'目黒区', N'平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520004', N'東京都', N'目黒区', N'鷹番');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520001', N'東京都', N'目黒区', N'中央町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530065', N'東京都', N'目黒区', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520031', N'東京都', N'目黒区', N'中根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530061', N'東京都', N'目黒区', N'中目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520011', N'東京都', N'目黒区', N'原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520021', N'東京都', N'目黒区', N'東が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530043', N'東京都', N'目黒区', N'東山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520003', N'東京都', N'目黒区', N'碑文谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530062', N'東京都', N'目黒区', N'三田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520034', N'東京都', N'目黒区', N'緑が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520013', N'東京都', N'目黒区', N'南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530063', N'東京都', N'目黒区', N'目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520002', N'東京都', N'目黒区', N'目黒本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520023', N'東京都', N'目黒区', N'八雲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530052', N'東京都', N'目黒区', N'祐天寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440000', N'東京都', N'大田区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460082', N'東京都', N'大田区', N'池上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450061', N'東京都', N'大田区', N'石川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460091', N'東京都', N'大田区', N'鵜の木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430014', N'東京都', N'大田区', N'大森中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430011', N'東京都', N'大田区', N'大森本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430012', N'東京都', N'大田区', N'大森東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430015', N'東京都', N'大田区', N'大森西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430013', N'東京都', N'大田区', N'大森南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430016', N'東京都', N'大田区', N'大森北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440052', N'東京都', N'大田区', N'蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440053', N'東京都', N'大田区', N'蒲田本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450064', N'東京都', N'大田区', N'上池台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440032', N'東京都', N'大田区', N'北糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450062', N'東京都', N'大田区', N'北千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430021', N'東京都', N'大田区', N'北馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450073', N'東京都', N'大田区', N'北嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460085', N'東京都', N'大田区', N'久が原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430003', N'東京都', N'大田区', N'京浜島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430023', N'東京都', N'大田区', N'山王');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460092', N'東京都', N'大田区', N'下丸子');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430002', N'東京都', N'大田区', N'城南島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430004', N'東京都', N'大田区', N'昭和島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440054', N'東京都', N'大田区', N'新蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460095', N'東京都', N'大田区', N'多摩川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460083', N'東京都', N'大田区', N'千鳥');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430024', N'東京都', N'大田区', N'中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450071', N'東京都', N'大田区', N'田園調布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450072', N'東京都', N'大田区', N'田園調布本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450076', N'東京都', N'大田区', N'田園調布南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430001', N'東京都', N'大田区', N'東海');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460081', N'東京都', N'大田区', N'仲池上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430027', N'東京都', N'大田区', N'中馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440055', N'東京都', N'大田区', N'仲六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440051', N'東京都', N'大田区', N'西蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440034', N'東京都', N'大田区', N'西糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430026', N'東京都', N'大田区', N'西馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450075', N'東京都', N'大田区', N'西嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440056', N'東京都', N'大田区', N'西六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440047', N'東京都', N'大田区', N'萩中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440043', N'東京都', N'大田区', N'羽田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440042', N'東京都', N'大田区', N'羽田旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440041', N'東京都', N'大田区', N'羽田空港');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440031', N'東京都', N'大田区', N'東蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440033', N'東京都', N'大田区', N'東糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430022', N'東京都', N'大田区', N'東馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450074', N'東京都', N'大田区', N'東嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460094', N'東京都', N'大田区', N'東矢口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450065', N'東京都', N'大田区', N'東雪谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440046', N'東京都', N'大田区', N'東六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430007', N'東京都', N'大田区', N'ふるさとの浜辺公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430006', N'東京都', N'大田区', N'平和島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430005', N'東京都', N'大田区', N'平和の森公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440044', N'東京都', N'大田区', N'本羽田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440035', N'東京都', N'大田区', N'南蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460084', N'東京都', N'大田区', N'南久が原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450063', N'東京都', N'大田区', N'南千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430025', N'東京都', N'大田区', N'南馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450066', N'東京都', N'大田区', N'南雪谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440045', N'東京都', N'大田区', N'南六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460093', N'東京都', N'大田区', N'矢口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450067', N'東京都', N'大田区', N'雪谷大塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540000', N'東京都', N'世田谷区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560044', N'東京都', N'世田谷区', N'赤堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540001', N'東京都', N'世田谷区', N'池尻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570068', N'東京都', N'世田谷区', N'宇奈根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540022', N'東京都', N'世田谷区', N'梅丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570074', N'東京都', N'世田谷区', N'大蔵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560041', N'東京都', N'世田谷区', N'大原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570076', N'東京都', N'世田谷区', N'岡本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580083', N'東京都', N'世田谷区', N'奥沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580086', N'東京都', N'世田谷区', N'尾山台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570063', N'東京都', N'世田谷区', N'粕谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570077', N'東京都', N'世田谷区', N'鎌田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540011', N'東京都', N'世田谷区', N'上馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560057', N'東京都', N'世田谷区', N'上北沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570065', N'東京都', N'世田谷区', N'上祖師谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580093', N'東京都', N'世田谷区', N'上野毛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580098', N'東京都', N'世田谷区', N'上用賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570061', N'東京都', N'世田谷区', N'北烏山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550031', N'東京都', N'世田谷区', N'北沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570067', N'東京都', N'世田谷区', N'喜多見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570073', N'東京都', N'世田谷区', N'砧');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570075', N'東京都', N'世田谷区', N'砧公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570064', N'東京都', N'世田谷区', N'給田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560052', N'東京都', N'世田谷区', N'経堂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540021', N'東京都', N'世田谷区', N'豪徳寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540012', N'東京都', N'世田谷区', N'駒沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540013', N'東京都', N'世田谷区', N'駒沢公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560053', N'東京都', N'世田谷区', N'桜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560054', N'東京都', N'世田谷区', N'桜丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540015', N'東京都', N'世田谷区', N'桜新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560045', N'東京都', N'世田谷区', N'桜上水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540024', N'東京都', N'世田谷区', N'三軒茶屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540002', N'東京都', N'世田谷区', N'下馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540014', N'東京都', N'世田谷区', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570066', N'東京都', N'世田谷区', N'成城');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580095', N'東京都', N'世田谷区', N'瀬田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540017', N'東京都', N'世田谷区', N'世田谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570072', N'東京都', N'世田谷区', N'祖師谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540004', N'東京都', N'世田谷区', N'太子堂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550032', N'東京都', N'世田谷区', N'代沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550033', N'東京都', N'世田谷区', N'代田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580094', N'東京都', N'世田谷区', N'玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580096', N'東京都', N'世田谷区', N'玉川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580085', N'東京都', N'世田谷区', N'玉川田園調布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580087', N'東京都', N'世田谷区', N'玉堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570071', N'東京都', N'世田谷区', N'千歳台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540016', N'東京都', N'世田谷区', N'弦巻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580082', N'東京都', N'世田谷区', N'等々力');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580091', N'東京都', N'世田谷区', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580092', N'東京都', N'世田谷区', N'野毛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540003', N'東京都', N'世田谷区', N'野沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560056', N'東京都', N'世田谷区', N'八幡山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560042', N'東京都', N'世田谷区', N'羽根木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580084', N'東京都', N'世田谷区', N'東玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580081', N'東京都', N'世田谷区', N'深沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560055', N'東京都', N'世田谷区', N'船橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560043', N'東京都', N'世田谷区', N'松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540005', N'東京都', N'世田谷区', N'三宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570062', N'東京都', N'世田谷区', N'南烏山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560051', N'東京都', N'世田谷区', N'宮坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580097', N'東京都', N'世田谷区', N'用賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540023', N'東京都', N'世田谷区', N'若林');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500000', N'東京都', N'渋谷区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510064', N'東京都', N'渋谷区', N'上原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500032', N'東京都', N'渋谷区', N'鶯谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500042', N'東京都', N'渋谷区', N'宇田川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500013', N'東京都', N'渋谷区', N'恵比寿（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506090', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506001', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506002', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506003', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506004', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506005', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506006', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506007', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506008', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506009', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506010', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506011', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506012', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506013', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506014', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506015', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506016', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506017', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506018', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506019', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506020', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506021', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506022', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506023', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506024', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506025', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506026', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506027', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506028', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506029', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506030', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506031', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506032', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506033', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506034', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506035', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506036', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506037', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506038', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506039', N'東京都', N'渋谷区', N'恵比寿恵比寿ガーデンプレイス（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500021', N'東京都', N'渋谷区', N'恵比寿西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500022', N'東京都', N'渋谷区', N'恵比寿南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510065', N'東京都', N'渋谷区', N'大山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500047', N'東京都', N'渋谷区', N'神山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500031', N'東京都', N'渋谷区', N'桜丘町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510073', N'東京都', N'渋谷区', N'笹塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500033', N'東京都', N'渋谷区', N'猿楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500002', N'東京都', N'渋谷区', N'渋谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500046', N'東京都', N'渋谷区', N'松濤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500001', N'東京都', N'渋谷区', N'神宮前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500045', N'東京都', N'渋谷区', N'神泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500041', N'東京都', N'渋谷区', N'神南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510051', N'東京都', N'渋谷区', N'千駄ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500034', N'東京都', N'渋谷区', N'代官山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500043', N'東京都', N'渋谷区', N'道玄坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510063', N'東京都', N'渋谷区', N'富ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500036', N'東京都', N'渋谷区', N'南平台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510066', N'東京都', N'渋谷区', N'西原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510072', N'東京都', N'渋谷区', N'幡ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500035', N'東京都', N'渋谷区', N'鉢山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510061', N'東京都', N'渋谷区', N'初台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500011', N'東京都', N'渋谷区', N'東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500012', N'東京都', N'渋谷区', N'広尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510071', N'東京都', N'渋谷区', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500044', N'東京都', N'渋谷区', N'円山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510062', N'東京都', N'渋谷区', N'元代々木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510053', N'東京都', N'渋谷区', N'代々木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510052', N'東京都', N'渋谷区', N'代々木神園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640000', N'東京都', N'中野区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650026', N'東京都', N'中野区', N'新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650022', N'東京都', N'中野区', N'江古田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650023', N'東京都', N'中野区', N'江原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650031', N'東京都', N'中野区', N'上鷺宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640002', N'東京都', N'中野区', N'上高田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650032', N'東京都', N'中野区', N'鷺宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650035', N'東京都', N'中野区', N'白鷺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640011', N'東京都', N'中野区', N'中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640001', N'東京都', N'中野区', N'中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650025', N'東京都', N'中野区', N'沼袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650027', N'東京都', N'中野区', N'野方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640003', N'東京都', N'中野区', N'東中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640012', N'東京都', N'中野区', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650024', N'東京都', N'中野区', N'松が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650021', N'東京都', N'中野区', N'丸山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640014', N'東京都', N'中野区', N'南台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650034', N'東京都', N'中野区', N'大和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640013', N'東京都', N'中野区', N'弥生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650033', N'東京都', N'中野区', N'若宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660000', N'東京都', N'杉並区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660004', N'東京都', N'杉並区', N'阿佐谷南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660001', N'東京都', N'杉並区', N'阿佐谷北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670032', N'東京都', N'杉並区', N'天沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670021', N'東京都', N'杉並区', N'井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680063', N'東京都', N'杉並区', N'和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670035', N'東京都', N'杉並区', N'今川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660011', N'東京都', N'杉並区', N'梅里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680064', N'東京都', N'杉並区', N'永福');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680061', N'東京都', N'杉並区', N'大宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670051', N'東京都', N'杉並区', N'荻窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670023', N'東京都', N'杉並区', N'上井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670043', N'東京都', N'杉並区', N'上荻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680074', N'東京都', N'杉並区', N'上高井戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680082', N'東京都', N'杉並区', N'久我山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660003', N'東京都', N'杉並区', N'高円寺南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660002', N'東京都', N'杉並区', N'高円寺北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670033', N'東京都', N'杉並区', N'清水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670022', N'東京都', N'杉並区', N'下井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680073', N'東京都', N'杉並区', N'下高井戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670054', N'東京都', N'杉並区', N'松庵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670041', N'東京都', N'杉並区', N'善福寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680072', N'東京都', N'杉並区', N'高井戸東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680071', N'東京都', N'杉並区', N'高井戸西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660015', N'東京都', N'杉並区', N'成田東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660016', N'東京都', N'杉並区', N'成田西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670053', N'東京都', N'杉並区', N'西荻南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670042', N'東京都', N'杉並区', N'西荻北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680065', N'東京都', N'杉並区', N'浜田山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680062', N'東京都', N'杉並区', N'方南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660013', N'東京都', N'杉並区', N'堀ノ内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670031', N'東京都', N'杉並区', N'本天沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660014', N'東京都', N'杉並区', N'松ノ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670052', N'東京都', N'杉並区', N'南荻窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680081', N'東京都', N'杉並区', N'宮前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670034', N'東京都', N'杉並区', N'桃井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660012', N'東京都', N'杉並区', N'和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700000', N'東京都', N'豊島区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700014', N'東京都', N'豊島区', N'池袋（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710014', N'東京都', N'豊島区', N'池袋（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700011', N'東京都', N'豊島区', N'池袋本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710043', N'東京都', N'豊島区', N'要町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700012', N'東京都', N'豊島区', N'上池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700004', N'東京都', N'豊島区', N'北大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700003', N'東京都', N'豊島区', N'駒込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700002', N'東京都', N'豊島区', N'巣鴨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710041', N'東京都', N'豊島区', N'千川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710032', N'東京都', N'豊島区', N'雑司が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710033', N'東京都', N'豊島区', N'高田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710042', N'東京都', N'豊島区', N'高松');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710044', N'東京都', N'豊島区', N'千早');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710051', N'東京都', N'豊島区', N'長崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710021', N'東京都', N'豊島区', N'西池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700001', N'東京都', N'豊島区', N'西巣鴨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700013', N'東京都', N'豊島区', N'東池袋（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706090', N'東京都', N'豊島区', N'東池袋サンシャイン６０（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706001', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706002', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706003', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706004', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706005', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706006', N'東京都', N'豊島区', N'東池袋サンシャイン６０（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706007', N'東京都', N'豊島区', N'東池袋サンシャイン６０（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706008', N'東京都', N'豊島区', N'東池袋サンシャイン６０（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706009', N'東京都', N'豊島区', N'東池袋サンシャイン６０（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706010', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706011', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706012', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706013', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706014', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706015', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706016', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706017', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706018', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706019', N'東京都', N'豊島区', N'東池袋サンシャイン６０（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706020', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706021', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706022', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706023', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706024', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706025', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706026', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706027', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706028', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706029', N'東京都', N'豊島区', N'東池袋サンシャイン６０（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706030', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706031', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706032', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706033', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706034', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706035', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706036', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706037', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706038', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706039', N'東京都', N'豊島区', N'東池袋サンシャイン６０（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706040', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706041', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706042', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706043', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706044', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706045', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706046', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706047', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706048', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706049', N'東京都', N'豊島区', N'東池袋サンシャイン６０（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706050', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706051', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706052', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706053', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706054', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706055', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706056', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706057', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706058', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706059', N'東京都', N'豊島区', N'東池袋サンシャイン６０（５９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706060', N'東京都', N'豊島区', N'東池袋サンシャイン６０（６０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710022', N'東京都', N'豊島区', N'南池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700005', N'東京都', N'豊島区', N'南大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710052', N'東京都', N'豊島区', N'南長崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710031', N'東京都', N'豊島区', N'目白');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140000', N'東京都', N'北区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150045', N'東京都', N'北区', N'赤羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150053', N'東京都', N'北区', N'赤羽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150055', N'東京都', N'北区', N'赤羽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150044', N'東京都', N'北区', N'赤羽南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150052', N'東京都', N'北区', N'赤羽北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150041', N'東京都', N'北区', N'岩淵町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150051', N'東京都', N'北区', N'浮間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140002', N'東京都', N'北区', N'王子');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140022', N'東京都', N'北区', N'王子本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140034', N'東京都', N'北区', N'上十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140016', N'東京都', N'北区', N'上中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150043', N'東京都', N'北区', N'神谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140021', N'東京都', N'北区', N'岸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150054', N'東京都', N'北区', N'桐ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140005', N'東京都', N'北区', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150042', N'東京都', N'北区', N'志茂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140033', N'東京都', N'北区', N'十条台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140031', N'東京都', N'北区', N'十条仲原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140011', N'東京都', N'北区', N'昭和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140023', N'東京都', N'北区', N'滝野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140014', N'東京都', N'北区', N'田端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140012', N'東京都', N'北区', N'田端新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140003', N'東京都', N'北区', N'豊島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140015', N'東京都', N'北区', N'中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140032', N'東京都', N'北区', N'中十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150056', N'東京都', N'北区', N'西が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140024', N'東京都', N'北区', N'西ケ原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140001', N'東京都', N'北区', N'東十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140013', N'東京都', N'北区', N'東田端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140004', N'東京都', N'北区', N'堀船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160000', N'東京都', N'荒川区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160002', N'東京都', N'荒川区', N'荒川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160011', N'東京都', N'荒川区', N'西尾久');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160013', N'東京都', N'荒川区', N'西日暮里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160012', N'東京都', N'荒川区', N'東尾久');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160014', N'東京都', N'荒川区', N'東日暮里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160001', N'東京都', N'荒川区', N'町屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160003', N'東京都', N'荒川区', N'南千住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740000', N'東京都', N'板橋区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740044', N'東京都', N'板橋区', N'相生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750092', N'東京都', N'板橋区', N'赤塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750093', N'東京都', N'板橋区', N'赤塚新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740051', N'東京都', N'板橋区', N'小豆沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740055', N'東京都', N'板橋区', N'泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730004', N'東京都', N'板橋区', N'板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730002', N'東京都', N'板橋区', N'稲荷台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740061', N'東京都', N'板橋区', N'大原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730035', N'東京都', N'板橋区', N'大谷口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730032', N'東京都', N'板橋区', N'大谷口上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730031', N'東京都', N'板橋区', N'大谷口北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730024', N'東京都', N'板橋区', N'大山金井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730014', N'東京都', N'板橋区', N'大山東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730033', N'東京都', N'板橋区', N'大山西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730023', N'東京都', N'板橋区', N'大山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730003', N'東京都', N'板橋区', N'加賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740076', N'東京都', N'板橋区', N'上板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730025', N'東京都', N'板橋区', N'熊野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730037', N'東京都', N'板橋区', N'小茂根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730034', N'東京都', N'板橋区', N'幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730015', N'東京都', N'板橋区', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740043', N'東京都', N'板橋区', N'坂下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740075', N'東京都', N'板橋区', N'桜川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740053', N'東京都', N'板橋区', N'清水町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740056', N'東京都', N'板橋区', N'志村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750081', N'東京都', N'板橋区', N'新河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750085', N'東京都', N'板橋区', N'大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750082', N'東京都', N'板橋区', N'高島平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740074', N'東京都', N'板橋区', N'東新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740071', N'東京都', N'板橋区', N'常盤台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750083', N'東京都', N'板橋区', N'徳丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730016', N'東京都', N'板橋区', N'中板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730005', N'東京都', N'板橋区', N'仲宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740064', N'東京都', N'板橋区', N'中台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730022', N'東京都', N'板橋区', N'仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730026', N'東京都', N'板橋区', N'中丸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750094', N'東京都', N'板橋区', N'成増');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740045', N'東京都', N'板橋区', N'西台（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750045', N'東京都', N'板橋区', N'西台（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740052', N'東京都', N'板橋区', N'蓮沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740046', N'東京都', N'板橋区', N'蓮根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730013', N'東京都', N'板橋区', N'氷川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740042', N'東京都', N'板橋区', N'東坂下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740073', N'東京都', N'板橋区', N'東山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740062', N'東京都', N'板橋区', N'富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730011', N'東京都', N'板橋区', N'双葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740041', N'東京都', N'板橋区', N'舟渡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730001', N'東京都', N'板橋区', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740063', N'東京都', N'板橋区', N'前野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750091', N'東京都', N'板橋区', N'三園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730027', N'東京都', N'板橋区', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740072', N'東京都', N'板橋区', N'南常盤台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740054', N'東京都', N'板橋区', N'宮本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730036', N'東京都', N'板橋区', N'向原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730012', N'東京都', N'板橋区', N'大和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730021', N'東京都', N'板橋区', N'弥生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750084', N'東京都', N'板橋区', N'四葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740065', N'東京都', N'板橋区', N'若木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760000', N'東京都', N'練馬区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760005', N'東京都', N'練馬区', N'旭丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790071', N'東京都', N'練馬区', N'旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780061', N'東京都', N'練馬区', N'大泉学園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780062', N'東京都', N'練馬区', N'大泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790074', N'東京都', N'練馬区', N'春日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770044', N'東京都', N'練馬区', N'上石神井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770043', N'東京都', N'練馬区', N'上石神井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790081', N'東京都', N'練馬区', N'北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760022', N'東京都', N'練馬区', N'向山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760004', N'東京都', N'練馬区', N'小竹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760006', N'東京都', N'練馬区', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760002', N'東京都', N'練馬区', N'桜台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770042', N'東京都', N'練馬区', N'下石神井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770045', N'東京都', N'練馬区', N'石神井台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770041', N'東京都', N'練馬区', N'石神井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770052', N'東京都', N'練馬区', N'関町東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770053', N'東京都', N'練馬区', N'関町南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770051', N'東京都', N'練馬区', N'関町北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770033', N'東京都', N'練馬区', N'高野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790075', N'東京都', N'練馬区', N'高松');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790073', N'東京都', N'練馬区', N'田柄');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770054', N'東京都', N'練馬区', N'立野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790076', N'東京都', N'練馬区', N'土支田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760011', N'東京都', N'練馬区', N'豊玉上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760013', N'東京都', N'練馬区', N'豊玉中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760014', N'東京都', N'練馬区', N'豊玉南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760012', N'東京都', N'練馬区', N'豊玉北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760024', N'東京都', N'練馬区', N'中村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760025', N'東京都', N'練馬区', N'中村南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760023', N'東京都', N'練馬区', N'中村北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780065', N'東京都', N'練馬区', N'西大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780066', N'東京都', N'練馬区', N'西大泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790082', N'東京都', N'練馬区', N'錦');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760021', N'東京都', N'練馬区', N'貫井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760001', N'東京都', N'練馬区', N'練馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760003', N'東京都', N'練馬区', N'羽沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790085', N'東京都', N'練馬区', N'早宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790072', N'東京都', N'練馬区', N'光が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790084', N'東京都', N'練馬区', N'氷川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780063', N'東京都', N'練馬区', N'東大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770034', N'東京都', N'練馬区', N'富士見台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790083', N'東京都', N'練馬区', N'平和台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780064', N'東京都', N'練馬区', N'南大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770035', N'東京都', N'練馬区', N'南田中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770031', N'東京都', N'練馬区', N'三原台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770032', N'東京都', N'練馬区', N'谷原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200000', N'東京都', N'足立区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200012', N'東京都', N'足立区', N'青井（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210012', N'東京都', N'足立区', N'青井（４〜６丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200015', N'東京都', N'足立区', N'足立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200005', N'東京都', N'足立区', N'綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210823', N'東京都', N'足立区', N'伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210807', N'東京都', N'足立区', N'伊興本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210836', N'東京都', N'足立区', N'入谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210834', N'東京都', N'足立区', N'入谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210816', N'東京都', N'足立区', N'梅島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230851', N'東京都', N'足立区', N'梅田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230873', N'東京都', N'足立区', N'扇');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200001', N'東京都', N'足立区', N'大谷田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230844', N'東京都', N'足立区', N'興野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200046', N'東京都', N'足立区', N'小台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230861', N'東京都', N'足立区', N'加賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210055', N'東京都', N'足立区', N'加平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210056', N'東京都', N'足立区', N'北加平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230842', N'東京都', N'足立区', N'栗原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200013', N'東京都', N'足立区', N'弘道');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230872', N'東京都', N'足立区', N'江北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210833', N'東京都', N'足立区', N'古千谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210832', N'東京都', N'足立区', N'古千谷本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210053', N'東京都', N'足立区', N'佐野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230862', N'東京都', N'足立区', N'皿沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230864', N'東京都', N'足立区', N'鹿浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210815', N'東京都', N'足立区', N'島根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230865', N'東京都', N'足立区', N'新田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210051', N'東京都', N'足立区', N'神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210057', N'東京都', N'足立区', N'神明南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230852', N'東京都', N'足立区', N'関原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200034', N'東京都', N'足立区', N'千住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200023', N'東京都', N'足立区', N'千住曙町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200026', N'東京都', N'足立区', N'千住旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200025', N'東京都', N'足立区', N'千住東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200031', N'東京都', N'足立区', N'千住大川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200037', N'東京都', N'足立区', N'千住河原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200033', N'東京都', N'足立区', N'千住寿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200045', N'東京都', N'足立区', N'千住桜木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200024', N'東京都', N'足立区', N'千住関屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200042', N'東京都', N'足立区', N'千住龍田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200035', N'東京都', N'足立区', N'千住中居町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200036', N'東京都', N'足立区', N'千住仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200038', N'東京都', N'足立区', N'千住橋戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200044', N'東京都', N'足立区', N'千住緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200043', N'東京都', N'足立区', N'千住宮元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200041', N'東京都', N'足立区', N'千住元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200032', N'東京都', N'足立区', N'千住柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210813', N'東京都', N'足立区', N'竹の塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210054', N'東京都', N'足立区', N'辰沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200011', N'東京都', N'足立区', N'中央本町（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210011', N'東京都', N'足立区', N'中央本町（３〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230871', N'東京都', N'足立区', N'椿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200003', N'東京都', N'足立区', N'東和');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210831', N'東京都', N'足立区', N'舎人');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210837', N'東京都', N'足立区', N'舎人公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210835', N'東京都', N'足立区', N'舎人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200002', N'東京都', N'足立区', N'中川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200014', N'東京都', N'足立区', N'西綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230841', N'東京都', N'足立区', N'西新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230843', N'東京都', N'足立区', N'西新井栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230845', N'東京都', N'足立区', N'西新井本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210824', N'東京都', N'足立区', N'西伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210825', N'東京都', N'足立区', N'西伊興町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210074', N'東京都', N'足立区', N'西加平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210822', N'東京都', N'足立区', N'西竹の塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210812', N'東京都', N'足立区', N'西保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210061', N'東京都', N'足立区', N'花畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200004', N'東京都', N'足立区', N'東綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210801', N'東京都', N'足立区', N'東伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210063', N'東京都', N'足立区', N'東保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210071', N'東京都', N'足立区', N'東六月町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210075', N'東京都', N'足立区', N'一ツ家');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200021', N'東京都', N'足立区', N'日ノ出町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210076', N'東京都', N'足立区', N'平野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210064', N'東京都', N'足立区', N'保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210072', N'東京都', N'足立区', N'保塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230874', N'東京都', N'足立区', N'堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210062', N'東京都', N'足立区', N'南花畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200047', N'東京都', N'足立区', N'宮城');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210052', N'東京都', N'足立区', N'六木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230853', N'東京都', N'足立区', N'本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230854', N'東京都', N'足立区', N'本木東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230856', N'東京都', N'足立区', N'本木西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230855', N'東京都', N'足立区', N'本木南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230857', N'東京都', N'足立区', N'本木北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230863', N'東京都', N'足立区', N'谷在家');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200006', N'東京都', N'足立区', N'谷中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200022', N'東京都', N'足立区', N'柳原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210814', N'東京都', N'足立区', N'六月');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210073', N'東京都', N'足立区', N'六町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240000', N'東京都', N'葛飾区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250062', N'東京都', N'葛飾区', N'青戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240022', N'東京都', N'葛飾区', N'奥戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240003', N'東京都', N'葛飾区', N'お花茶屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250042', N'東京都', N'葛飾区', N'金町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250043', N'東京都', N'葛飾区', N'金町浄水場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250053', N'東京都', N'葛飾区', N'鎌倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250061', N'東京都', N'葛飾区', N'亀有');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240001', N'東京都', N'葛飾区', N'小菅');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250052', N'東京都', N'葛飾区', N'柴又');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250063', N'東京都', N'葛飾区', N'白鳥');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240024', N'東京都', N'葛飾区', N'新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250054', N'東京都', N'葛飾区', N'高砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240005', N'東京都', N'葛飾区', N'宝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240012', N'東京都', N'葛飾区', N'立石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250051', N'東京都', N'葛飾区', N'新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240002', N'東京都', N'葛飾区', N'西亀有（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250002', N'東京都', N'葛飾区', N'西亀有（３、４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240025', N'東京都', N'葛飾区', N'西新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250031', N'東京都', N'葛飾区', N'西水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250041', N'東京都', N'葛飾区', N'東金町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240023', N'東京都', N'葛飾区', N'東新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240013', N'東京都', N'葛飾区', N'東立石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240004', N'東京都', N'葛飾区', N'東堀切');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250033', N'東京都', N'葛飾区', N'東水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240014', N'東京都', N'葛飾区', N'東四つ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240021', N'東京都', N'葛飾区', N'細田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240006', N'東京都', N'葛飾区', N'堀切');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250032', N'東京都', N'葛飾区', N'水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250034', N'東京都', N'葛飾区', N'水元公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250035', N'東京都', N'葛飾区', N'南水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240011', N'東京都', N'葛飾区', N'四つ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320000', N'東京都', N'江戸川区', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320024', N'東京都', N'江戸川区', N'一之江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340092', N'東京都', N'江戸川区', N'一之江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340082', N'東京都', N'江戸川区', N'宇喜田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320013', N'東京都', N'江戸川区', N'江戸川（１〜３丁目、４丁目１〜１４番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340013', N'東京都', N'江戸川区', N'江戸川（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320022', N'東京都', N'江戸川区', N'大杉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330042', N'東京都', N'江戸川区', N'興宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330041', N'東京都', N'江戸川区', N'上一色');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330054', N'東京都', N'江戸川区', N'上篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340081', N'東京都', N'江戸川区', N'北葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330051', N'東京都', N'江戸川区', N'北小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330053', N'東京都', N'江戸川区', N'北篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320034', N'東京都', N'江戸川区', N'小松川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330073', N'東京都', N'江戸川区', N'鹿骨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330072', N'東京都', N'江戸川区', N'鹿骨町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330061', N'東京都', N'江戸川区', N'篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330064', N'東京都', N'江戸川区', N'下篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340087', N'東京都', N'江戸川区', N'清新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320021', N'東京都', N'江戸川区', N'中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340083', N'東京都', N'江戸川区', N'中葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320001', N'東京都', N'江戸川区', N'新堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320023', N'東京都', N'江戸川区', N'西一之江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340088', N'東京都', N'江戸川区', N'西葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330057', N'東京都', N'江戸川区', N'西小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320032', N'東京都', N'江戸川区', N'西小松川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330055', N'東京都', N'江戸川区', N'西篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320015', N'東京都', N'江戸川区', N'西瑞江（２〜３丁目、４丁目３〜９番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340015', N'東京都', N'江戸川区', N'西瑞江（４丁目１〜２番・１０〜２７番、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340093', N'東京都', N'江戸川区', N'二之江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320003', N'東京都', N'江戸川区', N'春江町（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340003', N'東京都', N'江戸川区', N'春江町（４、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340084', N'東京都', N'江戸川区', N'東葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330052', N'東京都', N'江戸川区', N'東小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320033', N'東京都', N'江戸川区', N'東小松川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330063', N'東京都', N'江戸川区', N'東篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330062', N'東京都', N'江戸川区', N'東篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330071', N'東京都', N'江戸川区', N'東松本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320014', N'東京都', N'江戸川区', N'東瑞江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320035', N'東京都', N'江戸川区', N'平井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340091', N'東京都', N'江戸川区', N'船堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330044', N'東京都', N'江戸川区', N'本一色');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320025', N'東京都', N'江戸川区', N'松江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320031', N'東京都', N'江戸川区', N'松島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330043', N'東京都', N'江戸川区', N'松本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320011', N'東京都', N'江戸川区', N'瑞江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340085', N'東京都', N'江戸川区', N'南葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330056', N'東京都', N'江戸川区', N'南小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330065', N'東京都', N'江戸川区', N'南篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320002', N'東京都', N'江戸川区', N'谷河内（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330002', N'東京都', N'江戸川区', N'谷河内（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340086', N'東京都', N'江戸川区', N'臨海町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920000', N'東京都', N'八王子市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920043', N'東京都', N'八王子市', N'暁町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920083', N'東京都', N'八王子市', N'旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920082', N'東京都', N'八王子市', N'東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920032', N'東京都', N'八王子市', N'石川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930814', N'東京都', N'八王子市', N'泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930802', N'東京都', N'八王子市', N'犬目町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920902', N'東京都', N'八王子市', N'上野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920911', N'東京都', N'八王子市', N'打越町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920024', N'東京都', N'八王子市', N'宇津木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920915', N'東京都', N'八王子市', N'宇津貫町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920013', N'東京都', N'八王子市', N'梅坪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930841', N'東京都', N'八王子市', N'裏高尾町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920056', N'東京都', N'八王子市', N'追分町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920352', N'東京都', N'八王子市', N'大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930935', N'東京都', N'八王子市', N'大船町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920034', N'東京都', N'八王子市', N'大谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920062', N'東京都', N'八王子市', N'大横町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920045', N'東京都', N'八王子市', N'大和田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920054', N'東京都', N'八王子市', N'小門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920025', N'東京都', N'八王子市', N'尾崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920155', N'東京都', N'八王子市', N'小津町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920353', N'東京都', N'八王子市', N'鹿島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920004', N'東京都', N'八王子市', N'加住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920914', N'東京都', N'八王子市', N'片倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930815', N'東京都', N'八王子市', N'叶谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930811', N'東京都', N'八王子市', N'上壱分方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920156', N'東京都', N'八王子市', N'上恩方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920151', N'東京都', N'八王子市', N'上川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920373', N'東京都', N'八王子市', N'上柚木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930801', N'東京都', N'八王子市', N'川口町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930821', N'東京都', N'八王子市', N'川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920913', N'東京都', N'八王子市', N'北野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920906', N'東京都', N'八王子市', N'北野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920912', N'東京都', N'八王子市', N'絹ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930804', N'東京都', N'八王子市', N'清川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930942', N'東京都', N'八王子市', N'椚田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920023', N'東京都', N'八王子市', N'久保山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920361', N'東京都', N'八王子市', N'越野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930934', N'東京都', N'八王子市', N'小比企町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920031', N'東京都', N'八王子市', N'小宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920904', N'東京都', N'八王子市', N'子安町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920012', N'東京都', N'八王子市', N'左入町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930832', N'東京都', N'八王子市', N'散田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920154', N'東京都', N'八王子市', N'下恩方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920372', N'東京都', N'八王子市', N'下柚木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930825', N'東京都', N'八王子市', N'城山手');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920065', N'東京都', N'八王子市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930812', N'東京都', N'八王子市', N'諏訪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930835', N'東京都', N'八王子市', N'千人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930931', N'東京都', N'八王子市', N'台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930816', N'東京都', N'八王子市', N'大楽寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920022', N'東京都', N'八王子市', N'平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930844', N'東京都', N'八王子市', N'高尾町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920033', N'東京都', N'八王子市', N'高倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920002', N'東京都', N'八王子市', N'高月町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920011', N'東京都', N'八王子市', N'滝山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930944', N'東京都', N'八王子市', N'館町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920064', N'東京都', N'八王子市', N'田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920003', N'東京都', N'八王子市', N'丹木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930943', N'東京都', N'八王子市', N'寺田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920073', N'東京都', N'八王子市', N'寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920074', N'東京都', N'八王子市', N'天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930843', N'東京都', N'八王子市', N'廿里町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920001', N'東京都', N'八王子市', N'戸吹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920085', N'東京都', N'八王子市', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920041', N'東京都', N'八王子市', N'中野上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920042', N'東京都', N'八王子市', N'中野山王');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920015', N'東京都', N'八王子市', N'中野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920374', N'東京都', N'八王子市', N'中山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920907', N'東京都', N'八王子市', N'長沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930824', N'東京都', N'八王子市', N'長房町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920919', N'東京都', N'八王子市', N'七国');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930831', N'東京都', N'八王子市', N'並木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930803', N'東京都', N'八王子市', N'楢原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920371', N'東京都', N'八王子市', N'南陽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930842', N'東京都', N'八王子市', N'西浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920917', N'東京都', N'八王子市', N'西片倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920153', N'東京都', N'八王子市', N'西寺方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930822', N'東京都', N'八王子市', N'弐分方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930941', N'東京都', N'八王子市', N'狭間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920053', N'東京都', N'八王子市', N'八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930845', N'東京都', N'八王子市', N'初沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930834', N'東京都', N'八王子市', N'東浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920351', N'東京都', N'八王子市', N'東中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920918', N'東京都', N'八王子市', N'兵衛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930836', N'東京都', N'八王子市', N'日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920061', N'東京都', N'八王子市', N'平岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920044', N'東京都', N'八王子市', N'富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920363', N'東京都', N'八王子市', N'別所');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920355', N'東京都', N'八王子市', N'堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920052', N'東京都', N'八王子市', N'本郷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920066', N'東京都', N'八王子市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920354', N'東京都', N'八王子市', N'松が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920362', N'東京都', N'八王子市', N'松木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920021', N'東京都', N'八王子市', N'丸山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920084', N'東京都', N'八王子市', N'三崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920014', N'東京都', N'八王子市', N'みつい台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930932', N'東京都', N'八王子市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930846', N'東京都', N'八王子市', N'南浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920364', N'東京都', N'八王子市', N'南大沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920075', N'東京都', N'八王子市', N'南新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920072', N'東京都', N'八王子市', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920916', N'東京都', N'八王子市', N'みなみ野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920005', N'東京都', N'八王子市', N'宮下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920152', N'東京都', N'八王子市', N'美山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920046', N'東京都', N'八王子市', N'明神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930833', N'東京都', N'八王子市', N'めじろ台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930826', N'東京都', N'八王子市', N'元八王子町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920051', N'東京都', N'八王子市', N'元本郷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920063', N'東京都', N'八王子市', N'元横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920055', N'東京都', N'八王子市', N'八木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920016', N'東京都', N'八王子市', N'谷野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930933', N'東京都', N'八王子市', N'山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920375', N'東京都', N'八王子市', N'鑓水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920071', N'東京都', N'八王子市', N'八日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930823', N'東京都', N'八王子市', N'横川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920081', N'東京都', N'八王子市', N'横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930813', N'東京都', N'八王子市', N'四谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920903', N'東京都', N'八王子市', N'万町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900000', N'東京都', N'立川市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900012', N'東京都', N'立川市', N'曙町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900015', N'東京都', N'立川市', N'泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900033', N'東京都', N'立川市', N'一番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900004', N'東京都', N'立川市', N'柏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900032', N'東京都', N'立川市', N'上砂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900002', N'東京都', N'立川市', N'幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900003', N'東京都', N'立川市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900023', N'東京都', N'立川市', N'柴崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900031', N'東京都', N'立川市', N'砂川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900011', N'東京都', N'立川市', N'高松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900022', N'東京都', N'立川市', N'錦町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900034', N'東京都', N'立川市', N'西砂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900021', N'東京都', N'立川市', N'羽衣町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900013', N'東京都', N'立川市', N'富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900014', N'東京都', N'立川市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900001', N'東京都', N'立川市', N'若葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800000', N'東京都', N'武蔵野市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800004', N'東京都', N'武蔵野市', N'吉祥寺本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800002', N'東京都', N'武蔵野市', N'吉祥寺東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800003', N'東京都', N'武蔵野市', N'吉祥寺南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800001', N'東京都', N'武蔵野市', N'吉祥寺北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800023', N'東京都', N'武蔵野市', N'境南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800005', N'東京都', N'武蔵野市', N'御殿山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800022', N'東京都', N'武蔵野市', N'境');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800021', N'東京都', N'武蔵野市', N'桜堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800014', N'東京都', N'武蔵野市', N'関前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800006', N'東京都', N'武蔵野市', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800013', N'東京都', N'武蔵野市', N'西久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800012', N'東京都', N'武蔵野市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800011', N'東京都', N'武蔵野市', N'八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810000', N'東京都', N'三鷹市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810011', N'東京都', N'三鷹市', N'井口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810001', N'東京都', N'三鷹市', N'井の頭');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810015', N'東京都', N'三鷹市', N'大沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810012', N'東京都', N'三鷹市', N'上連雀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810003', N'東京都', N'三鷹市', N'北野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810013', N'東京都', N'三鷹市', N'下連雀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810004', N'東京都', N'三鷹市', N'新川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810016', N'東京都', N'三鷹市', N'深大寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810005', N'東京都', N'三鷹市', N'中原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810014', N'東京都', N'三鷹市', N'野崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810002', N'東京都', N'三鷹市', N'牟礼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980000', N'東京都', N'青梅市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980087', N'東京都', N'青梅市', N'天ケ瀬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980023', N'東京都', N'青梅市', N'今井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980021', N'東京都', N'青梅市', N'今寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980088', N'東京都', N'青梅市', N'裏宿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980086', N'東京都', N'青梅市', N'大柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980003', N'東京都', N'青梅市', N'小曾木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980041', N'東京都', N'青梅市', N'勝沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980036', N'東京都', N'青梅市', N'河辺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980081', N'東京都', N'青梅市', N'上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980013', N'東京都', N'青梅市', N'木野下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980005', N'東京都', N'青梅市', N'黒沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980053', N'東京都', N'青梅市', N'駒木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980172', N'東京都', N'青梅市', N'沢井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980011', N'東京都', N'青梅市', N'塩船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980024', N'東京都', N'青梅市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980025', N'東京都', N'青梅市', N'末広町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980084', N'東京都', N'青梅市', N'住江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980014', N'東京都', N'青梅市', N'大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980085', N'東京都', N'青梅市', N'滝ノ上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980043', N'東京都', N'青梅市', N'千ケ瀬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980002', N'東京都', N'青梅市', N'富岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980051', N'東京都', N'青梅市', N'友田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980082', N'東京都', N'青梅市', N'仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980052', N'東京都', N'青梅市', N'長淵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980001', N'東京都', N'青梅市', N'成木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980044', N'東京都', N'青梅市', N'西分町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980004', N'東京都', N'青梅市', N'根ケ布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980032', N'東京都', N'青梅市', N'野上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980063', N'東京都', N'青梅市', N'梅郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980061', N'東京都', N'青梅市', N'畑中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980042', N'東京都', N'青梅市', N'東青梅');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980046', N'東京都', N'青梅市', N'日向和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980015', N'東京都', N'青梅市', N'吹上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980022', N'東京都', N'青梅市', N'藤橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980171', N'東京都', N'青梅市', N'二俣尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980083', N'東京都', N'青梅市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980174', N'東京都', N'青梅市', N'御岳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980175', N'東京都', N'青梅市', N'御岳山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980173', N'東京都', N'青梅市', N'御岳本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980089', N'東京都', N'青梅市', N'森下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980031', N'東京都', N'青梅市', N'師岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980012', N'東京都', N'青梅市', N'谷野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980064', N'東京都', N'青梅市', N'柚木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980062', N'東京都', N'青梅市', N'和田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830000', N'東京都', N'府中市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830003', N'東京都', N'府中市', N'朝日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830012', N'東京都', N'府中市', N'押立町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830021', N'東京都', N'府中市', N'片町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830041', N'東京都', N'府中市', N'北山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830056', N'東京都', N'府中市', N'寿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830013', N'東京都', N'府中市', N'小柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830014', N'東京都', N'府中市', N'是政');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830054', N'東京都', N'府中市', N'幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830051', N'東京都', N'府中市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830015', N'東京都', N'府中市', N'清水が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830011', N'東京都', N'府中市', N'白糸台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830052', N'東京都', N'府中市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830034', N'東京都', N'府中市', N'住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830001', N'東京都', N'府中市', N'浅間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830002', N'東京都', N'府中市', N'多磨町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830053', N'東京都', N'府中市', N'天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830043', N'東京都', N'府中市', N'東芝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830046', N'東京都', N'府中市', N'西原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830031', N'東京都', N'府中市', N'西府町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830044', N'東京都', N'府中市', N'日鋼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830036', N'東京都', N'府中市', N'日新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830016', N'東京都', N'府中市', N'八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830057', N'東京都', N'府中市', N'晴見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830024', N'東京都', N'府中市', N'日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830055', N'東京都', N'府中市', N'府中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830033', N'東京都', N'府中市', N'分梅町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830032', N'東京都', N'府中市', N'本宿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830027', N'東京都', N'府中市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830006', N'東京都', N'府中市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830026', N'東京都', N'府中市', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830022', N'東京都', N'府中市', N'宮西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830023', N'東京都', N'府中市', N'宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830045', N'東京都', N'府中市', N'美好町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830042', N'東京都', N'府中市', N'武蔵台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830004', N'東京都', N'府中市', N'紅葉丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830025', N'東京都', N'府中市', N'矢崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830035', N'東京都', N'府中市', N'四谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830005', N'東京都', N'府中市', N'若松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960000', N'東京都', N'昭島市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960025', N'東京都', N'昭島市', N'朝日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960033', N'東京都', N'昭島市', N'東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960013', N'東京都', N'昭島市', N'大神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960032', N'東京都', N'昭島市', N'郷地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960011', N'東京都', N'昭島市', N'上川原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960015', N'東京都', N'昭島市', N'昭和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960014', N'東京都', N'昭島市', N'田中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960034', N'東京都', N'昭島市', N'玉川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960023', N'東京都', N'昭島市', N'築地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960012', N'東京都', N'昭島市', N'つつじが丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960022', N'東京都', N'昭島市', N'中神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960002', N'東京都', N'昭島市', N'拝島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960031', N'東京都', N'昭島市', N'福島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960003', N'東京都', N'昭島市', N'松原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960004', N'東京都', N'昭島市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960001', N'東京都', N'昭島市', N'美堀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960024', N'東京都', N'昭島市', N'宮沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960021', N'東京都', N'昭島市', N'武蔵野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820000', N'東京都', N'調布市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820004', N'東京都', N'調布市', N'入間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820035', N'東京都', N'調布市', N'上石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820007', N'東京都', N'調布市', N'菊野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820022', N'東京都', N'調布市', N'国領町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820026', N'東京都', N'調布市', N'小島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820016', N'東京都', N'調布市', N'佐須町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820014', N'東京都', N'調布市', N'柴崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820034', N'東京都', N'調布市', N'下石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820012', N'東京都', N'調布市', N'深大寺東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820013', N'東京都', N'調布市', N'深大寺南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820011', N'東京都', N'調布市', N'深大寺北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820017', N'東京都', N'調布市', N'深大寺元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820002', N'東京都', N'調布市', N'仙川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820023', N'東京都', N'調布市', N'染地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820025', N'東京都', N'調布市', N'多摩川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820021', N'東京都', N'調布市', N'調布ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820036', N'東京都', N'調布市', N'飛田給');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820006', N'東京都', N'調布市', N'西つつじケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820032', N'東京都', N'調布市', N'西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820031', N'東京都', N'調布市', N'野水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820005', N'東京都', N'調布市', N'東つつじケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820033', N'東京都', N'調布市', N'富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820024', N'東京都', N'調布市', N'布田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820001', N'東京都', N'調布市', N'緑ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820015', N'東京都', N'調布市', N'八雲台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820003', N'東京都', N'調布市', N'若葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940000', N'東京都', N'町田市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940211', N'東京都', N'町田市', N'相原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940023', N'東京都', N'町田市', N'旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950062', N'東京都', N'町田市', N'大蔵町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940003', N'東京都', N'町田市', N'小川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950064', N'東京都', N'町田市', N'小野路町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940215', N'東京都', N'町田市', N'小山ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940204', N'東京都', N'町田市', N'小山田桜台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940212', N'東京都', N'町田市', N'小山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950072', N'東京都', N'町田市', N'金井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950071', N'東京都', N'町田市', N'金井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940012', N'東京都', N'町田市', N'金森');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940201', N'東京都', N'町田市', N'上小山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940037', N'東京都', N'町田市', N'木曽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940036', N'東京都', N'町田市', N'木曽東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940033', N'東京都', N'町田市', N'木曽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940014', N'東京都', N'町田市', N'高ケ坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940202', N'東京都', N'町田市', N'下小山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950057', N'東京都', N'町田市', N'真光寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950051', N'東京都', N'町田市', N'真光寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940203', N'東京都', N'町田市', N'図師町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940035', N'東京都', N'町田市', N'忠生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940041', N'東京都', N'町田市', N'玉川学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940001', N'東京都', N'町田市', N'つくし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950061', N'東京都', N'町田市', N'鶴川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940004', N'東京都', N'町田市', N'鶴間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940213', N'東京都', N'町田市', N'常盤町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940021', N'東京都', N'町田市', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940044', N'東京都', N'町田市', N'成瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940011', N'東京都', N'町田市', N'成瀬が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940043', N'東京都', N'町田市', N'成瀬台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940038', N'東京都', N'町田市', N'根岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940034', N'東京都', N'町田市', N'根岸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950053', N'東京都', N'町田市', N'能ケ谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950063', N'東京都', N'町田市', N'野津田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940013', N'東京都', N'町田市', N'原町田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940042', N'東京都', N'町田市', N'東玉川学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950056', N'東京都', N'町田市', N'広袴');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950052', N'東京都', N'町田市', N'広袴町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940032', N'東京都', N'町田市', N'本町田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940031', N'東京都', N'町田市', N'南大谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940002', N'東京都', N'町田市', N'南つくし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940045', N'東京都', N'町田市', N'南成瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950054', N'東京都', N'町田市', N'三輪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950055', N'東京都', N'町田市', N'三輪緑山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940022', N'東京都', N'町田市', N'森野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950073', N'東京都', N'町田市', N'薬師台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940214', N'東京都', N'町田市', N'矢部町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950075', N'東京都', N'町田市', N'山崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950074', N'東京都', N'町田市', N'山崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840000', N'東京都', N'小金井市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840002', N'東京都', N'小金井市', N'梶野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840005', N'東京都', N'小金井市', N'桜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840001', N'東京都', N'小金井市', N'関野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840012', N'東京都', N'小金井市', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840014', N'東京都', N'小金井市', N'貫井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840015', N'東京都', N'小金井市', N'貫井北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840011', N'東京都', N'小金井市', N'東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840004', N'東京都', N'小金井市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840013', N'東京都', N'小金井市', N'前原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840003', N'東京都', N'小金井市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870000', N'東京都', N'小平市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870001', N'東京都', N'小平市', N'大沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870031', N'東京都', N'小平市', N'小川東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870035', N'東京都', N'小平市', N'小川西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870032', N'東京都', N'小平市', N'小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870043', N'東京都', N'小平市', N'学園東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870045', N'東京都', N'小平市', N'学園西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870044', N'東京都', N'小平市', N'喜平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870034', N'東京都', N'小平市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870023', N'東京都', N'小平市', N'上水新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870022', N'東京都', N'小平市', N'上水本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870021', N'東京都', N'小平市', N'上水南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870011', N'東京都', N'小平市', N'鈴木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870024', N'東京都', N'小平市', N'たかの台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870025', N'東京都', N'小平市', N'津田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870004', N'東京都', N'小平市', N'天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870033', N'東京都', N'小平市', N'中島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870042', N'東京都', N'小平市', N'仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870002', N'東京都', N'小平市', N'花小金井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870003', N'東京都', N'小平市', N'花小金井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870041', N'東京都', N'小平市', N'美園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870012', N'東京都', N'小平市', N'御幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870013', N'東京都', N'小平市', N'回田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910000', N'東京都', N'日野市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910065', N'東京都', N'日野市', N'旭が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910022', N'東京都', N'日野市', N'新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910021', N'東京都', N'日野市', N'石田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910061', N'東京都', N'日野市', N'大坂上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910034', N'東京都', N'日野市', N'落川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910014', N'東京都', N'日野市', N'上田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910015', N'東京都', N'日野市', N'川辺堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910001', N'東京都', N'日野市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910063', N'東京都', N'日野市', N'さくら町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910023', N'東京都', N'日野市', N'下田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910002', N'東京都', N'日野市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910016', N'東京都', N'日野市', N'神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910031', N'東京都', N'日野市', N'高幡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910062', N'東京都', N'日野市', N'多摩平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910051', N'東京都', N'日野市', N'豊田（大字）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910053', N'東京都', N'日野市', N'豊田（丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910055', N'東京都', N'日野市', N'西平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910052', N'東京都', N'日野市', N'東豊田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910054', N'東京都', N'日野市', N'東平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910012', N'東京都', N'日野市', N'日野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910003', N'東京都', N'日野市', N'日野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910011', N'東京都', N'日野市', N'日野本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910043', N'東京都', N'日野市', N'平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910064', N'東京都', N'日野市', N'富士町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910042', N'東京都', N'日野市', N'程久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910024', N'東京都', N'日野市', N'万願寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910032', N'東京都', N'日野市', N'三沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910041', N'東京都', N'日野市', N'南平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910013', N'東京都', N'日野市', N'宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910033', N'東京都', N'日野市', N'百草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890000', N'東京都', N'東村山市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890002', N'東京都', N'東村山市', N'青葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890001', N'東京都', N'東村山市', N'秋津町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890011', N'東京都', N'東村山市', N'恩多町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890003', N'東京都', N'東村山市', N'久米川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890013', N'東京都', N'東村山市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890021', N'東京都', N'東村山市', N'諏訪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890026', N'東京都', N'東村山市', N'多摩湖町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890022', N'東京都', N'東村山市', N'野口町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890012', N'東京都', N'東村山市', N'萩山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890024', N'東京都', N'東村山市', N'富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890014', N'東京都', N'東村山市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890023', N'東京都', N'東村山市', N'美住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890025', N'東京都', N'東村山市', N'廻田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850000', N'東京都', N'国分寺市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850024', N'東京都', N'国分寺市', N'泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850001', N'東京都', N'国分寺市', N'北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850004', N'東京都', N'国分寺市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850036', N'東京都', N'国分寺市', N'高木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850003', N'東京都', N'国分寺市', N'戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850033', N'東京都', N'国分寺市', N'内藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850005', N'東京都', N'国分寺市', N'並木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850013', N'東京都', N'国分寺市', N'西恋ケ窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850035', N'東京都', N'国分寺市', N'西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850023', N'東京都', N'国分寺市', N'西元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850034', N'東京都', N'国分寺市', N'光町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850014', N'東京都', N'国分寺市', N'東恋ケ窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850002', N'東京都', N'国分寺市', N'東戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850022', N'東京都', N'国分寺市', N'東元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850032', N'東京都', N'国分寺市', N'日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850031', N'東京都', N'国分寺市', N'富士本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850011', N'東京都', N'国分寺市', N'本多');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850012', N'東京都', N'国分寺市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850021', N'東京都', N'国分寺市', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860000', N'東京都', N'国立市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860013', N'東京都', N'国立市', N'青柳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860014', N'東京都', N'国立市', N'石田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860012', N'東京都', N'国立市', N'泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860001', N'東京都', N'国立市', N'北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860004', N'東京都', N'国立市', N'中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860005', N'東京都', N'国立市', N'西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860002', N'東京都', N'国立市', N'東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860003', N'東京都', N'国立市', N'富士見台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860015', N'東京都', N'国立市', N'矢川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860011', N'東京都', N'国立市', N'谷保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970000', N'東京都', N'福生市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970024', N'東京都', N'福生市', N'牛浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970012', N'東京都', N'福生市', N'加美平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970005', N'東京都', N'福生市', N'北田園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970003', N'東京都', N'福生市', N'熊川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970002', N'東京都', N'福生市', N'熊川二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970023', N'東京都', N'福生市', N'志茂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970021', N'東京都', N'福生市', N'東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970011', N'東京都', N'福生市', N'福生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970014', N'東京都', N'福生市', N'福生二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970022', N'東京都', N'福生市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970004', N'東京都', N'福生市', N'南田園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970013', N'東京都', N'福生市', N'武蔵野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970001', N'東京都', N'福生市', N'横田基地内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010000', N'東京都', N'狛江市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010003', N'東京都', N'狛江市', N'和泉本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010015', N'東京都', N'狛江市', N'猪方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010005', N'東京都', N'狛江市', N'岩戸南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010004', N'東京都', N'狛江市', N'岩戸北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010016', N'東京都', N'狛江市', N'駒井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010012', N'東京都', N'狛江市', N'中和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010011', N'東京都', N'狛江市', N'西和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010001', N'東京都', N'狛江市', N'西野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010014', N'東京都', N'狛江市', N'東和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010002', N'東京都', N'狛江市', N'東野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010013', N'東京都', N'狛江市', N'元和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070000', N'東京都', N'東大和市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070033', N'東京都', N'東大和市', N'芋窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070023', N'東京都', N'東大和市', N'上北台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070011', N'東京都', N'東大和市', N'清原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070002', N'東京都', N'東大和市', N'湖畔');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070022', N'東京都', N'東大和市', N'桜が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070003', N'東京都', N'東大和市', N'狭山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070004', N'東京都', N'東大和市', N'清水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070012', N'東京都', N'東大和市', N'新堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070032', N'東京都', N'東大和市', N'蔵敷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070005', N'東京都', N'東大和市', N'高木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070021', N'東京都', N'東大和市', N'立野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070001', N'東京都', N'東大和市', N'多摩湖');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070015', N'東京都', N'東大和市', N'中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070016', N'東京都', N'東大和市', N'仲原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070031', N'東京都', N'東大和市', N'奈良橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070014', N'東京都', N'東大和市', N'南街');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070013', N'東京都', N'東大和市', N'向原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040000', N'東京都', N'清瀬市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040002', N'東京都', N'清瀬市', N'旭が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040024', N'東京都', N'清瀬市', N'梅園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040013', N'東京都', N'清瀬市', N'上清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040001', N'東京都', N'清瀬市', N'下宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040011', N'東京都', N'清瀬市', N'下清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040023', N'東京都', N'清瀬市', N'竹丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040012', N'東京都', N'清瀬市', N'中清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040003', N'東京都', N'清瀬市', N'中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040004', N'東京都', N'清瀬市', N'野塩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040022', N'東京都', N'清瀬市', N'松山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040021', N'東京都', N'清瀬市', N'元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030000', N'東京都', N'東久留米市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030001', N'東京都', N'東久留米市', N'上の原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030021', N'東京都', N'東久留米市', N'学園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030003', N'東京都', N'東久留米市', N'金山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030051', N'東京都', N'東久留米市', N'小山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030052', N'東京都', N'東久留米市', N'幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030043', N'東京都', N'東久留米市', N'下里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030013', N'東京都', N'東久留米市', N'新川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030002', N'東京都', N'東久留米市', N'神宝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030012', N'東京都', N'東久留米市', N'浅間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030011', N'東京都', N'東久留米市', N'大門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030033', N'東京都', N'東久留米市', N'滝山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030054', N'東京都', N'東久留米市', N'中央町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030041', N'東京都', N'東久留米市', N'野火止');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030042', N'東京都', N'東久留米市', N'八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030004', N'東京都', N'東久留米市', N'氷川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030014', N'東京都', N'東久留米市', N'東本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030022', N'東京都', N'東久留米市', N'ひばりが丘団地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030053', N'東京都', N'東久留米市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030032', N'東京都', N'東久留米市', N'前沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030023', N'東京都', N'東久留米市', N'南沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030031', N'東京都', N'東久留米市', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030044', N'東京都', N'東久留米市', N'柳窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030034', N'東京都', N'東久留米市', N'弥生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080000', N'東京都', N'武蔵村山市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080023', N'東京都', N'武蔵村山市', N'伊奈平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080022', N'東京都', N'武蔵村山市', N'榎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080013', N'東京都', N'武蔵村山市', N'大南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080011', N'東京都', N'武蔵村山市', N'学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080031', N'東京都', N'武蔵村山市', N'岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080034', N'東京都', N'武蔵村山市', N'残堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080002', N'東京都', N'武蔵村山市', N'神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080003', N'東京都', N'武蔵村山市', N'中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080001', N'東京都', N'武蔵村山市', N'中藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080035', N'東京都', N'武蔵村山市', N'中原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080004', N'東京都', N'武蔵村山市', N'本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080033', N'東京都', N'武蔵村山市', N'三ツ木（大字）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080032', N'東京都', N'武蔵村山市', N'三ツ木（１〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080021', N'東京都', N'武蔵村山市', N'三ツ藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080012', N'東京都', N'武蔵村山市', N'緑が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060000', N'東京都', N'多摩市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060041', N'東京都', N'多摩市', N'愛宕');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060002', N'東京都', N'多摩市', N'一ノ宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060033', N'東京都', N'多摩市', N'落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060015', N'東京都', N'多摩市', N'落川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060012', N'東京都', N'多摩市', N'貝取');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060035', N'東京都', N'多摩市', N'唐木田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060014', N'東京都', N'多摩市', N'乞田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060013', N'東京都', N'多摩市', N'桜ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060042', N'東京都', N'多摩市', N'山王下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060024', N'東京都', N'多摩市', N'諏訪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060011', N'東京都', N'多摩市', N'関戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060034', N'東京都', N'多摩市', N'鶴牧');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060031', N'東京都', N'多摩市', N'豊ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060036', N'東京都', N'多摩市', N'中沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060025', N'東京都', N'多摩市', N'永山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060003', N'東京都', N'多摩市', N'東寺方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060022', N'東京都', N'多摩市', N'聖ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060023', N'東京都', N'多摩市', N'馬引沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060032', N'東京都', N'多摩市', N'南野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060004', N'東京都', N'多摩市', N'百草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060021', N'東京都', N'多摩市', N'連光寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060001', N'東京都', N'多摩市', N'和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060000', N'東京都', N'稲城市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060801', N'東京都', N'稲城市', N'大丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060811', N'東京都', N'稲城市', N'押立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060803', N'東京都', N'稲城市', N'向陽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060822', N'東京都', N'稲城市', N'坂浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060821', N'東京都', N'稲城市', N'長峰');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060802', N'東京都', N'稲城市', N'東長沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060823', N'東京都', N'稲城市', N'平尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060804', N'東京都', N'稲城市', N'百村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060812', N'東京都', N'稲城市', N'矢野口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060824', N'東京都', N'稲城市', N'若葉台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050000', N'東京都', N'羽村市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050001', N'東京都', N'羽村市', N'小作台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050021', N'東京都', N'羽村市', N'川崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050011', N'東京都', N'羽村市', N'五ノ神');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050002', N'東京都', N'羽村市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050023', N'東京都', N'羽村市', N'神明台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050024', N'東京都', N'羽村市', N'玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050012', N'東京都', N'羽村市', N'羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050016', N'東京都', N'羽村市', N'羽加美');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050015', N'東京都', N'羽村市', N'羽中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050014', N'東京都', N'羽村市', N'羽東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050017', N'東京都', N'羽村市', N'羽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050013', N'東京都', N'羽村市', N'富士見平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050022', N'東京都', N'羽村市', N'双葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050003', N'東京都', N'羽村市', N'緑ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900100', N'東京都', N'あきる野市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970804', N'東京都', N'あきる野市', N'秋川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970828', N'東京都', N'あきる野市', N'秋留');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900155', N'東京都', N'あきる野市', N'網代');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970827', N'東京都', N'あきる野市', N'油平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970825', N'東京都', N'あきる野市', N'雨間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900164', N'東京都', N'あきる野市', N'五日市');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900142', N'東京都', N'あきる野市', N'伊奈');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900161', N'東京都', N'あきる野市', N'入野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900143', N'東京都', N'あきる野市', N'上ノ台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970826', N'東京都', N'あきる野市', N'牛沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970821', N'東京都', N'あきる野市', N'小川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970822', N'東京都', N'あきる野市', N'小川東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900174', N'東京都', N'あきる野市', N'乙津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970832', N'東京都', N'あきる野市', N'上代継');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970824', N'東京都', N'あきる野市', N'切欠');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970802', N'東京都', N'あきる野市', N'草花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900165', N'東京都', N'あきる野市', N'小中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900153', N'東京都', N'あきる野市', N'小峰台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900151', N'東京都', N'あきる野市', N'小和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900162', N'東京都', N'あきる野市', N'三内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970831', N'東京都', N'あきる野市', N'下代継');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970801', N'東京都', N'あきる野市', N'菅生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970803', N'東京都', N'あきる野市', N'瀬戸岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900154', N'東京都', N'あきる野市', N'高尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900163', N'東京都', N'あきる野市', N'舘谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900166', N'東京都', N'あきる野市', N'舘谷台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900173', N'東京都', N'あきる野市', N'戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900152', N'東京都', N'あきる野市', N'留原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970814', N'東京都', N'あきる野市', N'二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970815', N'東京都', N'あきる野市', N'二宮東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970823', N'東京都', N'あきる野市', N'野辺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970811', N'東京都', N'あきる野市', N'原小宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970834', N'東京都', N'あきる野市', N'引田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970812', N'東京都', N'あきる野市', N'平沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970816', N'東京都', N'あきる野市', N'平沢西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970813', N'東京都', N'あきる野市', N'平沢東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900172', N'東京都', N'あきる野市', N'深沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970833', N'東京都', N'あきる野市', N'渕上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900144', N'東京都', N'あきる野市', N'山田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900171', N'東京都', N'あきる野市', N'養沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900141', N'東京都', N'あきる野市', N'横沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020000', N'東京都', N'西東京市', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020011', N'東京都', N'西東京市', N'泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880003', N'東京都', N'西東京市', N'北原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020003', N'東京都', N'西東京市', N'北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020006', N'東京都', N'西東京市', N'栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880014', N'東京都', N'西東京市', N'芝久保町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020004', N'東京都', N'西東京市', N'下保谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020023', N'東京都', N'西東京市', N'新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020005', N'東京都', N'西東京市', N'住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880011', N'東京都', N'西東京市', N'田無町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020013', N'東京都', N'西東京市', N'中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880004', N'東京都', N'西東京市', N'西原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020012', N'東京都', N'西東京市', N'東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020021', N'東京都', N'西東京市', N'東伏見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020001', N'東京都', N'西東京市', N'ひばりが丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020002', N'東京都', N'西東京市', N'ひばりが丘北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020014', N'東京都', N'西東京市', N'富士町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020015', N'東京都', N'西東京市', N'保谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880002', N'東京都', N'西東京市', N'緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880012', N'東京都', N'西東京市', N'南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880013', N'東京都', N'西東京市', N'向台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020022', N'東京都', N'西東京市', N'柳沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880001', N'東京都', N'西東京市', N'谷戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901200', N'東京都', N'西多摩郡瑞穂町', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901211', N'東京都', N'西多摩郡瑞穂町', N'石畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901202', N'東京都', N'西多摩郡瑞穂町', N'駒形富士山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901203', N'東京都', N'西多摩郡瑞穂町', N'高根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901212', N'東京都', N'西多摩郡瑞穂町', N'殿ケ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901232', N'東京都', N'西多摩郡瑞穂町', N'長岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901233', N'東京都', N'西多摩郡瑞穂町', N'長岡下師岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901231', N'東京都', N'西多摩郡瑞穂町', N'長岡長谷部');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901234', N'東京都', N'西多摩郡瑞穂町', N'長岡藤橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901201', N'東京都', N'西多摩郡瑞穂町', N'二本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901221', N'東京都', N'西多摩郡瑞穂町', N'箱根ケ崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901222', N'東京都', N'西多摩郡瑞穂町', N'箱根ケ崎東松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901223', N'東京都', N'西多摩郡瑞穂町', N'箱根ケ崎西松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901204', N'東京都', N'西多摩郡瑞穂町', N'富士山栗原新田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901224', N'東京都', N'西多摩郡瑞穂町', N'南平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901213', N'東京都', N'西多摩郡瑞穂町', N'武蔵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901214', N'東京都', N'西多摩郡瑞穂町', N'むさし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900100', N'東京都', N'西多摩郡日の出町', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900181', N'東京都', N'西多摩郡日の出町', N'大久野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900182', N'東京都', N'西多摩郡日の出町', N'平井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900200', N'東京都', N'西多摩郡檜原村', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900204', N'東京都', N'西多摩郡檜原村', N'小沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900221', N'東京都', N'西多摩郡檜原村', N'数馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900203', N'東京都', N'西多摩郡檜原村', N'神戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900212', N'東京都', N'西多摩郡檜原村', N'上元郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900201', N'東京都', N'西多摩郡檜原村', N'倉掛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900213', N'東京都', N'西多摩郡檜原村', N'下元郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900223', N'東京都', N'西多摩郡檜原村', N'南郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900205', N'東京都', N'西多摩郡檜原村', N'樋里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900202', N'東京都', N'西多摩郡檜原村', N'藤原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900222', N'東京都', N'西多摩郡檜原村', N'人里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900211', N'東京都', N'西多摩郡檜原村', N'三都郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900214', N'東京都', N'西多摩郡檜原村', N'本宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980000', N'東京都', N'西多摩郡奥多摩町', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980213', N'東京都', N'西多摩郡奥多摩町', N'海沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980103', N'東京都', N'西多摩郡奥多摩町', N'梅沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980101', N'東京都', N'西多摩郡奥多摩町', N'大丹波');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980102', N'東京都', N'西多摩郡奥多摩町', N'川井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980225', N'東京都', N'西多摩郡奥多摩町', N'川野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980224', N'東京都', N'西多摩郡奥多摩町', N'河内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980105', N'東京都', N'西多摩郡奥多摩町', N'小丹波');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980222', N'東京都', N'西多摩郡奥多摩町', N'境');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980107', N'東京都', N'西多摩郡奥多摩町', N'白丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980106', N'東京都', N'西多摩郡奥多摩町', N'棚沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980104', N'東京都', N'西多摩郡奥多摩町', N'丹三郎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980221', N'東京都', N'西多摩郡奥多摩町', N'留浦');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980211', N'東京都', N'西多摩郡奥多摩町', N'日原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980223', N'東京都', N'西多摩郡奥多摩町', N'原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980212', N'東京都', N'西多摩郡奥多摩町', N'氷川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000100', N'東京都', N'大島町', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000102', N'東京都', N'大島町', N'岡田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000211', N'東京都', N'大島町', N'差木地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000103', N'東京都', N'大島町', N'泉津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000104', N'東京都', N'大島町', N'野増');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000212', N'東京都', N'大島町', N'波浮港');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000101', N'東京都', N'大島町', N'元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000301', N'東京都', N'利島村', N'利島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000400', N'東京都', N'新島村', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000511', N'東京都', N'新島村', N'式根島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000402', N'東京都', N'新島村', N'本村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000401', N'東京都', N'新島村', N'若郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000601', N'東京都', N'神津島村', N'神津島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001100', N'東京都', N'三宅島三宅村', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001212', N'東京都', N'三宅島三宅村', N'阿古');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001103', N'東京都', N'三宅島三宅村', N'伊ケ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001102', N'東京都', N'三宅島三宅村', N'伊豆');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001213', N'東京都', N'三宅島三宅村', N'雄山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001101', N'東京都', N'三宅島三宅村', N'神着');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001211', N'東京都', N'三宅島三宅村', N'坪田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001301', N'東京都', N'御蔵島村', N'御蔵島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001400', N'東京都', N'八丈島八丈町', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001401', N'東京都', N'八丈島八丈町', N'大賀郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001621', N'東京都', N'八丈島八丈町', N'樫立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001622', N'東京都', N'八丈島八丈町', N'末吉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001623', N'東京都', N'八丈島八丈町', N'中之郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001511', N'東京都', N'八丈島八丈町', N'三根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001701', N'東京都', N'青ヶ島村', N'青ヶ島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002100', N'東京都', N'小笠原村', N'以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002101', N'東京都', N'小笠原村', N'父島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002211', N'東京都', N'小笠原村', N'母島');
GO

-- The schema for the "Sample_Extensible" sample set.

DROP TABLE IF EXISTS saleslog;
DROP TABLE IF EXISTS customer;
DROP TABLE IF EXISTS item_master;
CREATE TABLE saleslog
(
    id          INT PRIMARY KEY IDENTITY(1,1),
    dt          DATETIME,
    item        VARCHAR(20),
    customer    VARCHAR(70),
    qty         INT,
    item_id     INT,
    customer_id INT,
    unitprice   INT,
    total       INT
);
CREATE TABLE item_master
(
    id        INT PRIMARY KEY,
    name      VARCHAR(20),
    unitprice INT
);
CREATE TABLE customer
(
    id   INT PRIMARY KEY,
    name VARCHAR(70)
);
INSERT item_master(id, name, unitprice)
VALUES (1, 'Artichokes ', 957);
INSERT item_master(id, name, unitprice)
VALUES (2, 'Asparagus ', 103);
INSERT item_master(id, name, unitprice)
VALUES (3, 'Aubergine ', 294);
INSERT item_master(id, name, unitprice)
VALUES (4, 'Beans ', 245);
INSERT item_master(id, name, unitprice)
VALUES (5, 'Bok Choy ', 533);
INSERT item_master(id, name, unitprice)
VALUES (6, 'Broccoli ', 1025);
INSERT item_master(id, name, unitprice)
VALUES (7, 'Brussels Sprouts ', 776);
INSERT item_master(id, name, unitprice)
VALUES (8, 'Cabbage ', 492);
INSERT item_master(id, name, unitprice)
VALUES (9, 'Capsicum ', 578);
INSERT item_master(id, name, unitprice)
VALUES (10, 'Carrot ', 460);
INSERT item_master(id, name, unitprice)
VALUES (11, 'Cauliflower ', 565);
INSERT item_master(id, name, unitprice)
VALUES (12, 'Celeriac ', 462);
INSERT item_master(id, name, unitprice)
VALUES (13, 'Celery ', 536);
INSERT item_master(id, name, unitprice)
VALUES (14, 'Corn ', 739);
INSERT item_master(id, name, unitprice)
VALUES (15, 'Courgette ', 251);
INSERT item_master(id, name, unitprice)
VALUES (16, 'Cucumber ', 472);
INSERT item_master(id, name, unitprice)
VALUES (17, 'Dram sticks ', 164);
INSERT item_master(id, name, unitprice)
VALUES (18, 'Fennel ', 662);
INSERT item_master(id, name, unitprice)
VALUES (19, 'Garlic ', 484);
INSERT item_master(id, name, unitprice)
VALUES (20, 'Leek ', 708);
INSERT item_master(id, name, unitprice)
VALUES (21, 'Lettuce ', 964);
INSERT item_master(id, name, unitprice)
VALUES (22, 'Mushroom ', 347);
INSERT item_master(id, name, unitprice)
VALUES (23, 'Okra ', 1046);
INSERT item_master(id, name, unitprice)
VALUES (24, 'Olive ', 781);
INSERT item_master(id, name, unitprice)
VALUES (25, 'Onion ', 810);
INSERT item_master(id, name, unitprice)
VALUES (26, 'Parsnip ', 336);
INSERT item_master(id, name, unitprice)
VALUES (27, 'Peppers ', 297);
INSERT item_master(id, name, unitprice)
VALUES (28, 'Potato ', 232);
INSERT item_master(id, name, unitprice)
VALUES (29, 'Pumpkin ', 639);
INSERT item_master(id, name, unitprice)
VALUES (30, 'Peas ', 373);
INSERT item_master(id, name, unitprice)
VALUES (31, 'Rhubarb ', 574);
INSERT item_master(id, name, unitprice)
VALUES (32, 'Shallots ', 331);
INSERT item_master(id, name, unitprice)
VALUES (33, 'Spinach ', 409);
INSERT item_master(id, name, unitprice)
VALUES (34, 'Squash ', 190);
INSERT item_master(id, name, unitprice)
VALUES (35, 'Sweet Potato ', 650);
INSERT item_master(id, name, unitprice)
VALUES (36, 'Tomato ', 361);
INSERT item_master(id, name, unitprice)
VALUES (37, 'Turnip ', 471);
INSERT item_master(id, name, unitprice)
VALUES (38, 'Swede ', 528);
INSERT item_master(id, name, unitprice)
VALUES (39, 'Yam', 790);

INSERT customer(id, name)
VALUES (1, 'African glass catfish Food, Co.');
INSERT customer(id, name)
VALUES (2, 'African lungfish Food, Co.');
INSERT customer(id, name)
VALUES (3, 'Aholehole Food, Co.');
INSERT customer(id, name)
VALUES (4, 'Airbreathing catfish Food, Co.');
INSERT customer(id, name)
VALUES (5, 'Airsac catfish Food, Co.');
INSERT customer(id, name)
VALUES (6, 'Alaska blackfish Food, Co.');
INSERT customer(id, name)
VALUES (7, 'Albacore Food, Co.');
INSERT customer(id, name)
VALUES (8, 'Alewife Food, Co.');
INSERT customer(id, name)
VALUES (9, 'Alfonsino Food, Co.');
INSERT customer(id, name)
VALUES (10, 'Algae eater Food, Co.');
INSERT customer(id, name)
VALUES (11, 'Alligatorfish Food, Co.');
INSERT customer(id, name)
VALUES (12, 'Amago Food, Co.');
INSERT customer(id, name)
VALUES (13, 'American sole Food, Co.');
INSERT customer(id, name)
VALUES (14, 'Amur pike Food, Co.');
INSERT customer(id, name)
VALUES (15, 'Anchovy Food, Co.');
INSERT customer(id, name)
VALUES (16, 'Anemonefish Food, Co.');
INSERT customer(id, name)
VALUES (17, 'Angelfish Food, Co.');
INSERT customer(id, name)
VALUES (18, 'Angler Food, Co.');
INSERT customer(id, name)
VALUES (19, 'Angler catfish Food, Co.');
INSERT customer(id, name)
VALUES (20, 'Anglerfish Food, Co.');
INSERT customer(id, name)
VALUES (21, 'Antarctic cod Food, Co.');
INSERT customer(id, name)
VALUES (22, 'Antarctic icefish Food, Co.');
INSERT customer(id, name)
VALUES (23, 'Antenna codlet Food, Co.');
INSERT customer(id, name)
VALUES (24, 'Arapaima Food, Co.');
INSERT customer(id, name)
VALUES (25, 'Archerfish Food, Co.');
INSERT customer(id, name)
VALUES (26, 'Arctic char Food, Co.');
INSERT customer(id, name)
VALUES (27, 'Armored gurnard Food, Co.');
INSERT customer(id, name)
VALUES (28, 'Armored searobin Food, Co.');
INSERT customer(id, name)
VALUES (29, 'Armorhead Food, Co.');
INSERT customer(id, name)
VALUES (30, 'Armorhead catfish Food, Co.');
INSERT customer(id, name)
VALUES (31, 'Armoured catfish Food, Co.');
INSERT customer(id, name)
VALUES (32, 'Arowana Food, Co.');
INSERT customer(id, name)
VALUES (33, 'Arrowtooth eel Food, Co.');
INSERT customer(id, name)
VALUES (34, 'Aruana Food, Co.');
INSERT customer(id, name)
VALUES (35, 'Asian carps Food, Co.');
INSERT customer(id, name)
VALUES (36, 'Asiatic glassfish Food, Co.');
INSERT customer(id, name)
VALUES (37, 'Atka mackerel Food, Co.');
INSERT customer(id, name)
VALUES (38, 'Atlantic cod Food, Co.');
INSERT customer(id, name)
VALUES (39, 'Atlantic eel Food, Co.');
INSERT customer(id, name)
VALUES (40, 'Atlantic herring Food, Co.');
INSERT customer(id, name)
VALUES (41, 'Atlantic salmon Food, Co.');
INSERT customer(id, name)
VALUES (42, 'Atlantic saury Food, Co.');
INSERT customer(id, name)
VALUES (43, 'Atlantic silverside Food, Co.');
INSERT customer(id, name)
VALUES (44, 'Atlantic trout Food, Co.');
INSERT customer(id, name)
VALUES (45, 'Australasian salmon Food, Co.');
INSERT customer(id, name)
VALUES (46, 'Australian grayling Food, Co.');
INSERT customer(id, name)
VALUES (47, 'Australian herring Food, Co.');
INSERT customer(id, name)
VALUES (48, 'Australian lungfish Food, Co.');
INSERT customer(id, name)
VALUES (49, 'Australian prowfish Food, Co.');
INSERT customer(id, name)
VALUES (50, 'Ayu Food, Co.');
INSERT customer(id, name)
VALUES (51, 'Alooh Food, Co.');
INSERT customer(id, name)
VALUES (52, 'Baikal oilfish Food, Co.');
INSERT customer(id, name)
VALUES (53, 'Bala shark Food, Co.');
INSERT customer(id, name)
VALUES (54, 'Ballan wrasse Food, Co.');
INSERT customer(id, name)
VALUES (55, 'Bamboo shark Food, Co.');
INSERT customer(id, name)
VALUES (56, 'Banded killifish Food, Co.');
INSERT customer(id, name)
VALUES (57, 'Bandfish Food, Co.');
INSERT customer(id, name)
VALUES (58, 'Bango Food, Co.');
INSERT customer(id, name)
VALUES (59, 'Bangus Food, Co.');
INSERT customer(id, name)
VALUES (60, 'Banjo catfish Food, Co.');
INSERT customer(id, name)
VALUES (61, 'Barb Food, Co.');
INSERT customer(id, name)
VALUES (62, 'Barbel Food, Co.');
INSERT customer(id, name)
VALUES (63, 'Barbeled dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (64, 'Barbeled houndshark Food, Co.');
INSERT customer(id, name)
VALUES (65, 'Barbelless catfish Food, Co.');
INSERT customer(id, name)
VALUES (66, 'Barfish Food, Co.');
INSERT customer(id, name)
VALUES (67, 'Barracuda Food, Co.');
INSERT customer(id, name)
VALUES (68, 'Barracudina Food, Co.');
INSERT customer(id, name)
VALUES (69, 'Barramundi Food, Co.');
INSERT customer(id, name)
VALUES (70, 'Barred danio Food, Co.');
INSERT customer(id, name)
VALUES (71, 'Barreleye Food, Co.');
INSERT customer(id, name)
VALUES (72, 'Basking shark Food, Co.');
INSERT customer(id, name)
VALUES (73, 'Bass Food, Co.');
INSERT customer(id, name)
VALUES (74, 'Basslet Food, Co.');
INSERT customer(id, name)
VALUES (75, 'Batfish Food, Co.');
INSERT customer(id, name)
VALUES (76, 'Bat ray Food, Co.');
INSERT customer(id, name)
VALUES (77, 'Beachsalmon Food, Co.');
INSERT customer(id, name)
VALUES (78, 'Beaked salmon Food, Co.');
INSERT customer(id, name)
VALUES (79, 'Beaked sandfish Food, Co.');
INSERT customer(id, name)
VALUES (80, 'Beardfish Food, Co.');
INSERT customer(id, name)
VALUES (81, 'Beluga sturgeon Food, Co.');
INSERT customer(id, name)
VALUES (82, 'Bengal danio Food, Co.');
INSERT customer(id, name)
VALUES (83, 'Bent-tooth Food, Co.');
INSERT customer(id, name)
VALUES (84, 'Betta Food, Co.');
INSERT customer(id, name)
VALUES (85, 'Bichir Food, Co.');
INSERT customer(id, name)
VALUES (86, 'Bigeye Food, Co.');
INSERT customer(id, name)
VALUES (87, 'Bigeye squaretail Food, Co.');
INSERT customer(id, name)
VALUES (88, 'Bighead carp Food, Co.');
INSERT customer(id, name)
VALUES (89, 'Bigmouth buffalo Food, Co.');
INSERT customer(id, name)
VALUES (90, 'Bigscale Food, Co.');
INSERT customer(id, name)
VALUES (91, 'Bigscale fish Food, Co.');
INSERT customer(id, name)
VALUES (92, 'Bigscale pomfret Food, Co.');
INSERT customer(id, name)
VALUES (93, 'Billfish Food, Co.');
INSERT customer(id, name)
VALUES (94, 'Bitterling Food, Co.');
INSERT customer(id, name)
VALUES (95, 'Black angelfish Food, Co.');
INSERT customer(id, name)
VALUES (96, 'Black bass Food, Co.');
INSERT customer(id, name)
VALUES (97, 'Black dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (98, 'Blackchin Food, Co.');
INSERT customer(id, name)
VALUES (99, 'Blackfish Food, Co.');
INSERT customer(id, name)
VALUES (100, 'Blacktip reef shark Food, Co.');
INSERT customer(id, name)
VALUES (101, 'Black mackerel Food, Co.');
INSERT customer(id, name)
VALUES (102, 'Black pickerel Food, Co.');
INSERT customer(id, name)
VALUES (103, 'Black prickleback Food, Co.');
INSERT customer(id, name)
VALUES (104, 'Black scalyfin Food, Co.');
INSERT customer(id, name)
VALUES (105, 'Black sea bass Food, Co.');
INSERT customer(id, name)
VALUES (106, 'Black scabbardfish Food, Co.');
INSERT customer(id, name)
VALUES (107, 'Blacksmelt Food, Co.');
INSERT customer(id, name)
VALUES (108, 'Black swallower Food, Co.');
INSERT customer(id, name)
VALUES (109, 'Black tetra Food, Co.');
INSERT customer(id, name)
VALUES (110, 'Black triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (111, 'Bleak Food, Co.');
INSERT customer(id, name)
VALUES (112, 'Blenny Food, Co.');
INSERT customer(id, name)
VALUES (113, 'Blind goby Food, Co.');
INSERT customer(id, name)
VALUES (114, 'Blind shark Food, Co.');
INSERT customer(id, name)
VALUES (115, 'Blobfish Food, Co.');
INSERT customer(id, name)
VALUES (116, 'Blowfish Food, Co.');
INSERT customer(id, name)
VALUES (117, 'Blue catfish Food, Co.');
INSERT customer(id, name)
VALUES (118, 'Blue danio Food, Co.');
INSERT customer(id, name)
VALUES (119, 'Blue-redstripe danio Food, Co.');
INSERT customer(id, name)
VALUES (120, 'Blue eye Food, Co.');
INSERT customer(id, name)
VALUES (121, 'Bluefin tuna Food, Co.');
INSERT customer(id, name)
VALUES (122, 'Bluefish Food, Co.');
INSERT customer(id, name)
VALUES (123, 'Bluegill Food, Co.');
INSERT customer(id, name)
VALUES (124, 'Blue gourami Food, Co.');
INSERT customer(id, name)
VALUES (125, 'Blue shark Food, Co.');
INSERT customer(id, name)
VALUES (126, 'Blue triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (127, 'Blue whiting Food, Co.');
INSERT customer(id, name)
VALUES (128, 'Bluntnose knifefish Food, Co.');
INSERT customer(id, name)
VALUES (129, 'Bluntnose minnow Food, Co.');
INSERT customer(id, name)
VALUES (130, 'Boafish Food, Co.');
INSERT customer(id, name)
VALUES (131, 'Boarfish Food, Co.');
INSERT customer(id, name)
VALUES (132, 'Bobtail snipe eel Food, Co.');
INSERT customer(id, name)
VALUES (133, 'Bocaccio Food, Co.');
INSERT customer(id, name)
VALUES (134, 'Boga Food, Co.');
INSERT customer(id, name)
VALUES (135, 'Bombay duck Food, Co.');
INSERT customer(id, name)
VALUES (136, 'Bonefish Food, Co.');
INSERT customer(id, name)
VALUES (137, 'Bonito Food, Co.');
INSERT customer(id, name)
VALUES (138, 'Bonnetmouth Food, Co.');
INSERT customer(id, name)
VALUES (139, 'Bonytail chub Food, Co.');
INSERT customer(id, name)
VALUES (140, 'Bonytongue Food, Co.');
INSERT customer(id, name)
VALUES (141, 'Bottlenose Food, Co.');
INSERT customer(id, name)
VALUES (142, 'Bowfin Food, Co.');
INSERT customer(id, name)
VALUES (143, 'Boxfish Food, Co.');
INSERT customer(id, name)
VALUES (144, 'Bramble shark Food, Co.');
INSERT customer(id, name)
VALUES (145, 'Bream Food, Co.');
INSERT customer(id, name)
VALUES (146, 'Bristlemouth Food, Co.');
INSERT customer(id, name)
VALUES (147, 'Bristlenose catfish Food, Co.');
INSERT customer(id, name)
VALUES (148, 'Broadband dogfish Food, Co.');
INSERT customer(id, name)
VALUES (149, 'Brook lamprey Food, Co.');
INSERT customer(id, name)
VALUES (150, 'Brook trout Food, Co.');
INSERT customer(id, name)
VALUES (151, 'Brotula Food, Co.');
INSERT customer(id, name)
VALUES (152, 'Brown trout Food, Co.');
INSERT customer(id, name)
VALUES (153, 'Buffalofish Food, Co.');
INSERT customer(id, name)
VALUES (154, 'Bullhead Food, Co.');
INSERT customer(id, name)
VALUES (155, 'Bullhead shark Food, Co.');
INSERT customer(id, name)
VALUES (156, 'Bull shark Food, Co.');
INSERT customer(id, name)
VALUES (157, 'Bull trout Food, Co.');
INSERT customer(id, name)
VALUES (158, 'Burbot Food, Co.');
INSERT customer(id, name)
VALUES (159, 'Buri Food, Co.');
INSERT customer(id, name)
VALUES (160, 'Burma danio Food, Co.');
INSERT customer(id, name)
VALUES (161, 'Burrowing goby Food, Co.');
INSERT customer(id, name)
VALUES (162, 'Butterfly ray Food, Co.');
INSERT customer(id, name)
VALUES (163, 'Butterflyfish Food, Co.');
INSERT customer(id, name)
VALUES (164, 'California flyingfish Food, Co.');
INSERT customer(id, name)
VALUES (165, 'California halibut Food, Co.');
INSERT customer(id, name)
VALUES (166, 'California smoothtongue Food, Co.');
INSERT customer(id, name)
VALUES (167, 'Canary rockfish Food, Co.');
INSERT customer(id, name)
VALUES (168, 'Candiru Food, Co.');
INSERT customer(id, name)
VALUES (169, 'Candlefish Food, Co.');
INSERT customer(id, name)
VALUES (170, 'Capelin Food, Co.');
INSERT customer(id, name)
VALUES (171, 'Cardinalfish Food, Co.');
INSERT customer(id, name)
VALUES (172, 'Carp Food, Co.');
INSERT customer(id, name)
VALUES (173, 'Carpetshark Food, Co.');
INSERT customer(id, name)
VALUES (174, 'Carpsucker Food, Co.');
INSERT customer(id, name)
VALUES (175, 'Catalufa Food, Co.');
INSERT customer(id, name)
VALUES (176, 'Catfish Food, Co.');
INSERT customer(id, name)
VALUES (177, 'Catla Food, Co.');
INSERT customer(id, name)
VALUES (178, 'Cat shark Food, Co.');
INSERT customer(id, name)
VALUES (179, 'Cavefish Food, Co.');
INSERT customer(id, name)
VALUES (180, 'Celebes rainbowfish Food, Co.');
INSERT customer(id, name)
VALUES (181, 'Central mudminnow Food, Co.');
INSERT customer(id, name)
VALUES (182, 'Cepalin Food, Co.');
INSERT customer(id, name)
VALUES (183, 'Chain pickerel Food, Co.');
INSERT customer(id, name)
VALUES (184, 'Channel bass Food, Co.');
INSERT customer(id, name)
VALUES (185, 'Channel catfish Food, Co.');
INSERT customer(id, name)
VALUES (186, 'Char Food, Co.');
INSERT customer(id, name)
VALUES (187, 'Cherry salmon Food, Co.');
INSERT customer(id, name)
VALUES (188, 'Chimaera Food, Co.');
INSERT customer(id, name)
VALUES (189, 'Chinook salmon Food, Co.');
INSERT customer(id, name)
VALUES (190, 'Cherubfish Food, Co.');
INSERT customer(id, name)
VALUES (191, 'Chub Food, Co.');
INSERT customer(id, name)
VALUES (192, 'Chubsucker Food, Co.');
INSERT customer(id, name)
VALUES (193, 'Chum salmon Food, Co.');
INSERT customer(id, name)
VALUES (194, 'Cichlid Food, Co.');
INSERT customer(id, name)
VALUES (195, 'Cisco Food, Co.');
INSERT customer(id, name)
VALUES (196, 'Climbing catfish Food, Co.');
INSERT customer(id, name)
VALUES (197, 'Climbing gourami Food, Co.');
INSERT customer(id, name)
VALUES (198, 'Climbing perch Food, Co.');
INSERT customer(id, name)
VALUES (199, 'Clingfish Food, Co.');
INSERT customer(id, name)
VALUES (200, 'Clownfish Food, Co.');
INSERT customer(id, name)
VALUES (201, 'Clown loach Food, Co.');
INSERT customer(id, name)
VALUES (202, 'Clown triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (203, 'Cobbler Food, Co.');
INSERT customer(id, name)
VALUES (204, 'Cobia Food, Co.');
INSERT customer(id, name)
VALUES (205, 'Cod Food, Co.');
INSERT customer(id, name)
VALUES (206, 'Cod icefish Food, Co.');
INSERT customer(id, name)
VALUES (207, 'Codlet Food, Co.');
INSERT customer(id, name)
VALUES (208, 'Codling Food, Co.');
INSERT customer(id, name)
VALUES (209, 'Coelacanth Food, Co.');
INSERT customer(id, name)
VALUES (210, 'Coffinfish Food, Co.');
INSERT customer(id, name)
VALUES (211, 'Coho salmon Food, Co.');
INSERT customer(id, name)
VALUES (212, 'Coley Food, Co.');
INSERT customer(id, name)
VALUES (213, 'Collared carpetshark Food, Co.');
INSERT customer(id, name)
VALUES (214, 'Collared dogfish Food, Co.');
INSERT customer(id, name)
VALUES (215, 'Colorado squawfish Food, Co.');
INSERT customer(id, name)
VALUES (216, 'Combfish Food, Co.');
INSERT customer(id, name)
VALUES (217, 'Combtail gourami Food, Co.');
INSERT customer(id, name)
VALUES (218, 'Combtooth blenny Food, Co.');
INSERT customer(id, name)
VALUES (219, 'Common carp Food, Co.');
INSERT customer(id, name)
VALUES (220, 'Common tunny Food, Co.');
INSERT customer(id, name)
VALUES (221, 'Conger eel Food, Co.');
INSERT customer(id, name)
VALUES (222, 'Convict blenny Food, Co.');
INSERT customer(id, name)
VALUES (223, 'Convict cichlid Food, Co.');
INSERT customer(id, name)
VALUES (224, 'Cookie-cutter shark Food, Co.');
INSERT customer(id, name)
VALUES (225, 'Coolie loach Food, Co.');
INSERT customer(id, name)
VALUES (226, 'Cornish Spaktailed Bream Food, Co.');
INSERT customer(id, name)
VALUES (227, 'Cornetfish Food, Co.');
INSERT customer(id, name)
VALUES (228, 'Cowfish Food, Co.');
INSERT customer(id, name)
VALUES (229, 'Cownose ray Food, Co.');
INSERT customer(id, name)
VALUES (230, 'Cow shark Food, Co.');
INSERT customer(id, name)
VALUES (231, 'Crappie Food, Co.');
INSERT customer(id, name)
VALUES (232, 'Creek chub Food, Co.');
INSERT customer(id, name)
VALUES (233, 'Crestfish Food, Co.');
INSERT customer(id, name)
VALUES (234, 'Crevice kelpfish Food, Co.');
INSERT customer(id, name)
VALUES (235, 'Croaker Food, Co.');
INSERT customer(id, name)
VALUES (236, 'Crocodile icefish Food, Co.');
INSERT customer(id, name)
VALUES (237, 'Crocodile shark Food, Co.');
INSERT customer(id, name)
VALUES (238, 'Crucian carp Food, Co.');
INSERT customer(id, name)
VALUES (239, 'Cuchia Food, Co.');
INSERT customer(id, name)
VALUES (240, 'Cuckoo wrasse Food, Co.');
INSERT customer(id, name)
VALUES (241, 'Cusk-eel Food, Co.');
INSERT customer(id, name)
VALUES (242, 'Cuskfish Food, Co.');
INSERT customer(id, name)
VALUES (243, 'Cutlassfish Food, Co.');
INSERT customer(id, name)
VALUES (244, 'Cutthroat eel Food, Co.');
INSERT customer(id, name)
VALUES (245, 'Cutthroat trout Food, Co.');
INSERT customer(id, name)
VALUES (246, 'Dab Food, Co.');
INSERT customer(id, name)
VALUES (247, 'Dace Food, Co.');
INSERT customer(id, name)
VALUES (248, 'Daggertooth pike conger Food, Co.');
INSERT customer(id, name)
VALUES (249, 'Damselfish Food, Co.');
INSERT customer(id, name)
VALUES (250, 'Danio Food, Co.');
INSERT customer(id, name)
VALUES (251, 'Darter Food, Co.');
INSERT customer(id, name)
VALUES (252, 'Dartfish Food, Co.');
INSERT customer(id, name)
VALUES (253, 'Dealfish Food, Co.');
INSERT customer(id, name)
VALUES (254, 'Death Valley pupfish Food, Co.');
INSERT customer(id, name)
VALUES (255, 'Deep sea anglerfish Food, Co.');
INSERT customer(id, name)
VALUES (256, 'Deep sea bonefish Food, Co.');
INSERT customer(id, name)
VALUES (257, 'Deep sea eel Food, Co.');
INSERT customer(id, name)
VALUES (258, 'Deep sea smelt Food, Co.');
INSERT customer(id, name)
VALUES (259, 'Deepwater cardinalfish Food, Co.');
INSERT customer(id, name)
VALUES (260, 'Deepwater flathead Food, Co.');
INSERT customer(id, name)
VALUES (261, 'Deepwater stingray Food, Co.');
INSERT customer(id, name)
VALUES (262, 'Delta smelt Food, Co.');
INSERT customer(id, name)
VALUES (263, 'Demoiselle Food, Co.');
INSERT customer(id, name)
VALUES (264, 'Denticle herring Food, Co.');
INSERT customer(id, name)
VALUES (265, 'Desert pupfish Food, Co.');
INSERT customer(id, name)
VALUES (266, 'Devario Food, Co.');
INSERT customer(id, name)
VALUES (267, 'Devil ray Food, Co.');
INSERT customer(id, name)
VALUES (268, 'Dhufish Food, Co.');
INSERT customer(id, name)
VALUES (269, 'Discus Food, Co.');
INSERT customer(id, name)
VALUES (270, 'diVer: New Zealand sand diver or Long-finned sand diver Food, Co.');
INSERT customer(id, name)
VALUES (271, 'Dogfish Food, Co.');
INSERT customer(id, name)
VALUES (272, 'Dogfish shark Food, Co.');
INSERT customer(id, name)
VALUES (273, 'Dogteeth tetra Food, Co.');
INSERT customer(id, name)
VALUES (274, 'Dojo loach Food, Co.');
INSERT customer(id, name)
VALUES (275, 'Dolly Varden trout Food, Co.');
INSERT customer(id, name)
VALUES (276, 'Dorab Food, Co.');
INSERT customer(id, name)
VALUES (277, 'Dorado Food, Co.');
INSERT customer(id, name)
VALUES (278, 'Dory Food, Co.');
INSERT customer(id, name)
VALUES (279, 'Dottyback Food, Co.');
INSERT customer(id, name)
VALUES (280, 'Dragonet Food, Co.');
INSERT customer(id, name)
VALUES (281, 'Dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (282, 'Dragon goby Food, Co.');
INSERT customer(id, name)
VALUES (283, 'Driftfish Food, Co.');
INSERT customer(id, name)
VALUES (284, 'Driftwood catfish Food, Co.');
INSERT customer(id, name)
VALUES (285, 'Drum Food, Co.');
INSERT customer(id, name)
VALUES (286, 'Duckbill Food, Co.');
INSERT customer(id, name)
VALUES (287, 'Duckbilled barracudina Food, Co.');
INSERT customer(id, name)
VALUES (288, 'Duckbill eel Food, Co.');
INSERT customer(id, name)
VALUES (289, 'Dusky grouper Food, Co.');
INSERT customer(id, name)
VALUES (290, 'Dwarf gourami Food, Co.');
INSERT customer(id, name)
VALUES (291, 'Dwarf loach Food, Co.');
INSERT customer(id, name)
VALUES (292, 'Eagle ray Food, Co.');
INSERT customer(id, name)
VALUES (293, 'Earthworm eel Food, Co.');
INSERT customer(id, name)
VALUES (294, 'Eel Food, Co.');
INSERT customer(id, name)
VALUES (295, 'Eelblenny Food, Co.');
INSERT customer(id, name)
VALUES (296, 'Eel cod Food, Co.');
INSERT customer(id, name)
VALUES (297, 'Eel-goby Food, Co.');
INSERT customer(id, name)
VALUES (298, 'Eelpout Food, Co.');
INSERT customer(id, name)
VALUES (299, 'Eeltail catfish Food, Co.');
INSERT customer(id, name)
VALUES (300, 'Elasmobranch Food, Co.');
INSERT customer(id, name)
VALUES (301, 'Electric catfish Food, Co.');
INSERT customer(id, name)
VALUES (302, 'Electric eel Food, Co.');
INSERT customer(id, name)
VALUES (303, 'Electric knifefish Food, Co.');
INSERT customer(id, name)
VALUES (304, 'Electric ray Food, Co.');
INSERT customer(id, name)
VALUES (305, 'Electric stargazer Food, Co.');
INSERT customer(id, name)
VALUES (306, 'Elephant fish Food, Co.');
INSERT customer(id, name)
VALUES (307, 'Elephantnose fish Food, Co.');
INSERT customer(id, name)
VALUES (308, 'Elver Food, Co.');
INSERT customer(id, name)
VALUES (309, 'Emperor Food, Co.');
INSERT customer(id, name)
VALUES (310, 'Emperor angelfish Food, Co.');
INSERT customer(id, name)
VALUES (311, 'Emperor bream Food, Co.');
INSERT customer(id, name)
VALUES (312, 'Escolar Food, Co.');
INSERT customer(id, name)
VALUES (313, 'Eucla cod Food, Co.');
INSERT customer(id, name)
VALUES (314, 'Eulachon Food, Co.');
INSERT customer(id, name)
VALUES (315, 'European chub Food, Co.');
INSERT customer(id, name)
VALUES (316, 'European eel Food, Co.');
INSERT customer(id, name)
VALUES (317, 'European flounder Food, Co.');
INSERT customer(id, name)
VALUES (318, 'European minnow Food, Co.');
INSERT customer(id, name)
VALUES (319, 'European perch Food, Co.');
INSERT customer(id, name)
VALUES (320, 'False brotula Food, Co.');
INSERT customer(id, name)
VALUES (321, 'False cat shark Food, Co.');
INSERT customer(id, name)
VALUES (322, 'False moray Food, Co.');
INSERT customer(id, name)
VALUES (323, 'False trevally Food, Co.');
INSERT customer(id, name)
VALUES (324, 'Fangtooth Food, Co.');
INSERT customer(id, name)
VALUES (325, 'Fathead sculpin Food, Co.');
INSERT customer(id, name)
VALUES (326, 'Featherback Food, Co.');
INSERT customer(id, name)
VALUES (327, 'Featherfin knifefish Food, Co.');
INSERT customer(id, name)
VALUES (328, 'Fierasfer Food, Co.');
INSERT customer(id, name)
VALUES (329, 'Fire Goby Food, Co.');
INSERT customer(id, name)
VALUES (330, 'Filefish Food, Co.');
INSERT customer(id, name)
VALUES (331, 'Finback cat shark Food, Co.');
INSERT customer(id, name)
VALUES (332, 'Fingerfish Food, Co.');
INSERT customer(id, name)
VALUES (333, 'Fire bar danio Food, Co.');
INSERT customer(id, name)
VALUES (334, 'Firefish Food, Co.');
INSERT customer(id, name)
VALUES (335, 'Flabby whalefish Food, Co.');
INSERT customer(id, name)
VALUES (336, 'Flagblenny Food, Co.');
INSERT customer(id, name)
VALUES (337, 'Flagfin Food, Co.');
INSERT customer(id, name)
VALUES (338, 'Flagfish Food, Co.');
INSERT customer(id, name)
VALUES (339, 'Flagtail Food, Co.');
INSERT customer(id, name)
VALUES (340, 'Flashlight fish Food, Co.');
INSERT customer(id, name)
VALUES (341, 'Flatfish Food, Co.');
INSERT customer(id, name)
VALUES (342, 'Flathead Food, Co.');
INSERT customer(id, name)
VALUES (343, 'Flathead catfish Food, Co.');
INSERT customer(id, name)
VALUES (344, 'Flat loach Food, Co.');
INSERT customer(id, name)
VALUES (345, 'Flier Food, Co.');
INSERT customer(id, name)
VALUES (346, 'Flounder Food, Co.');
INSERT customer(id, name)
VALUES (347, 'Flying characin Food, Co.');
INSERT customer(id, name)
VALUES (348, 'Flying gurnard Food, Co.');
INSERT customer(id, name)
VALUES (349, 'Flyingfish Food, Co.');
INSERT customer(id, name)
VALUES (350, 'Footballfish Food, Co.');
INSERT customer(id, name)
VALUES (351, 'Forehead brooder Food, Co.');
INSERT customer(id, name)
VALUES (352, 'Four-eyed fish Food, Co.');
INSERT customer(id, name)
VALUES (353, 'French angelfish Food, Co.');
INSERT customer(id, name)
VALUES (354, 'Freshwater eel Food, Co.');
INSERT customer(id, name)
VALUES (355, 'Freshwater flyingfish Food, Co.');
INSERT customer(id, name)
VALUES (356, 'Freshwater hatchetfish Food, Co.');
INSERT customer(id, name)
VALUES (357, 'Freshwater herring Food, Co.');
INSERT customer(id, name)
VALUES (358, 'Freshwater shark Food, Co.');
INSERT customer(id, name)
VALUES (359, 'Frigate mackerel Food, Co.');
INSERT customer(id, name)
VALUES (360, 'Frilled shark Food, Co.');
INSERT customer(id, name)
VALUES (361, 'Frogfish Food, Co.');
INSERT customer(id, name)
VALUES (362, 'Frogmouth catfish Food, Co.');
INSERT customer(id, name)
VALUES (363, 'Fusilier fish Food, Co.');
INSERT customer(id, name)
VALUES (364, 'Galjoen fish Food, Co.');
INSERT customer(id, name)
VALUES (365, 'Ganges shark Food, Co.');
INSERT customer(id, name)
VALUES (366, 'Gar Food, Co.');
INSERT customer(id, name)
VALUES (367, 'Garden eel Food, Co.');
INSERT customer(id, name)
VALUES (368, 'Garibaldi Food, Co.');
INSERT customer(id, name)
VALUES (369, 'Garpike Food, Co.');
INSERT customer(id, name)
VALUES (370, 'Ghost carp Food, Co.');
INSERT customer(id, name)
VALUES (371, 'Ghost fish Food, Co.');
INSERT customer(id, name)
VALUES (372, 'Ghost flathead Food, Co.');
INSERT customer(id, name)
VALUES (373, 'Ghost knifefish Food, Co.');
INSERT customer(id, name)
VALUES (374, 'Ghost pipefish Food, Co.');
INSERT customer(id, name)
VALUES (375, 'Ghoul Food, Co.');
INSERT customer(id, name)
VALUES (376, 'Giant danio Food, Co.');
INSERT customer(id, name)
VALUES (377, 'Giant gourami Food, Co.');
INSERT customer(id, name)
VALUES (378, 'Giant sea bass Food, Co.');
INSERT customer(id, name)
VALUES (379, 'Giant wels Food, Co.');
INSERT customer(id, name)
VALUES (380, 'Gianttail Food, Co.');
INSERT customer(id, name)
VALUES (381, 'Gibberfish Food, Co.');
INSERT customer(id, name)
VALUES (382, 'Gila trout Food, Co.');
INSERT customer(id, name)
VALUES (383, 'Gizzard shad Food, Co.');
INSERT customer(id, name)
VALUES (384, 'Glass catfish Food, Co.');
INSERT customer(id, name)
VALUES (385, 'Glassfish Food, Co.');
INSERT customer(id, name)
VALUES (386, 'Glass knifefish Food, Co.');
INSERT customer(id, name)
VALUES (387, 'Glowlight danio Food, Co.');
INSERT customer(id, name)
VALUES (388, 'Goatfish Food, Co.');
INSERT customer(id, name)
VALUES (389, 'Goblin shark Food, Co.');
INSERT customer(id, name)
VALUES (390, 'Goby Food, Co.');
INSERT customer(id, name)
VALUES (391, 'Golden dojo Food, Co.');
INSERT customer(id, name)
VALUES (392, 'Golden loach Food, Co.');
INSERT customer(id, name)
VALUES (393, 'Golden shiner Food, Co.');
INSERT customer(id, name)
VALUES (394, 'Golden trout Food, Co.');
INSERT customer(id, name)
VALUES (395, 'Goldeye Food, Co.');
INSERT customer(id, name)
VALUES (396, 'Goldfish Food, Co.');
INSERT customer(id, name)
VALUES (397, 'Goldspotted killifish Food, Co.');
INSERT customer(id, name)
VALUES (398, 'Gombessa Food, Co.');
INSERT customer(id, name)
VALUES (399, 'Goosefish Food, Co.');
INSERT customer(id, name)
VALUES (400, 'Gopher rockfish Food, Co.');
INSERT customer(id, name)
VALUES (401, 'Gouramie Food, Co.');
INSERT customer(id, name)
VALUES (402, 'Grass carp Food, Co.');
INSERT customer(id, name)
VALUES (403, 'Graveldiver Food, Co.');
INSERT customer(id, name)
VALUES (404, 'Gray eel-catfish Food, Co.');
INSERT customer(id, name)
VALUES (405, 'Grayling Food, Co.');
INSERT customer(id, name)
VALUES (406, 'Gray mullet Food, Co.');
INSERT customer(id, name)
VALUES (407, 'Gray reef shark Food, Co.');
INSERT customer(id, name)
VALUES (408, 'Great white shark Food, Co.');
INSERT customer(id, name)
VALUES (409, 'Green swordtail Food, Co.');
INSERT customer(id, name)
VALUES (410, 'Greeneye Food, Co.');
INSERT customer(id, name)
VALUES (411, 'Greenling Food, Co.');
INSERT customer(id, name)
VALUES (412, 'Grenadier Food, Co.');
INSERT customer(id, name)
VALUES (413, 'Grideye Food, Co.');
INSERT customer(id, name)
VALUES (414, 'Ground shark Food, Co.');
INSERT customer(id, name)
VALUES (415, 'Grouper Food, Co.');
INSERT customer(id, name)
VALUES (416, 'Grunion Food, Co.');
INSERT customer(id, name)
VALUES (417, 'Grunt Food, Co.');
INSERT customer(id, name)
VALUES (418, 'Grunter Food, Co.');
INSERT customer(id, name)
VALUES (419, 'Grunt sculpin Food, Co.');
INSERT customer(id, name)
VALUES (420, 'Gudgeon Food, Co.');
INSERT customer(id, name)
VALUES (421, 'Guitarfish Food, Co.');
INSERT customer(id, name)
VALUES (422, 'Gulf menhaden Food, Co.');
INSERT customer(id, name)
VALUES (423, 'Gulper eel Food, Co.');
INSERT customer(id, name)
VALUES (424, 'Gulper Food, Co.');
INSERT customer(id, name)
VALUES (425, 'Gunnel Food, Co.');
INSERT customer(id, name)
VALUES (426, 'Guppy Food, Co.');
INSERT customer(id, name)
VALUES (427, 'Gurnard Food, Co.');
INSERT customer(id, name)
VALUES (428, 'Haddock Food, Co.');
INSERT customer(id, name)
VALUES (429, 'Hagfish Food, Co.');
INSERT customer(id, name)
VALUES (430, 'Hairtail Food, Co.');
INSERT customer(id, name)
VALUES (431, 'Hake Food, Co.');
INSERT customer(id, name)
VALUES (432, 'Half-gill Food, Co.');
INSERT customer(id, name)
VALUES (433, 'Halfbeak Food, Co.');
INSERT customer(id, name)
VALUES (434, 'Halfmoon Food, Co.');
INSERT customer(id, name)
VALUES (435, 'Halibut Food, Co.');
INSERT customer(id, name)
VALUES (436, 'Halosaur Food, Co.');
INSERT customer(id, name)
VALUES (437, 'Hamlet Food, Co.');
INSERT customer(id, name)
VALUES (438, 'Hammerhead shark Food, Co.');
INSERT customer(id, name)
VALUES (439, 'Hammerjaw Food, Co.');
INSERT customer(id, name)
VALUES (440, 'Handfish Food, Co.');
INSERT customer(id, name)
VALUES (441, 'Hardhead catfish Food, Co.');
INSERT customer(id, name)
VALUES (442, 'Harelip sucker Food, Co.');
INSERT customer(id, name)
VALUES (443, 'Hatchetfish Food, Co.');
INSERT customer(id, name)
VALUES (444, 'Hawkfish Food, Co.');
INSERT customer(id, name)
VALUES (445, 'Herring Food, Co.');
INSERT customer(id, name)
VALUES (446, 'Herring smelt Food, Co.');
INSERT customer(id, name)
VALUES (447, 'Hillstream loach Food, Co.');
INSERT customer(id, name)
VALUES (448, 'Hog sucker Food, Co.');
INSERT customer(id, name)
VALUES (449, 'Hoki Food, Co.');
INSERT customer(id, name)
VALUES (450, 'Horn shark Food, Co.');
INSERT customer(id, name)
VALUES (451, 'Horsefish Food, Co.');
INSERT customer(id, name)
VALUES (452, 'Houndshark Food, Co.');
INSERT customer(id, name)
VALUES (453, 'Huchen Food, Co.');
INSERT customer(id, name)
VALUES (454, 'Humuhumu-nukunuku-apua‘a Food, Co.');
INSERT customer(id, name)
VALUES (455, 'Hussar Food, Co.');
INSERT customer(id, name)
VALUES (456, 'Icefish Food, Co.');
INSERT customer(id, name)
VALUES (457, 'Ide Food, Co.');
INSERT customer(id, name)
VALUES (458, 'Ilisha Food, Co.');
INSERT customer(id, name)
VALUES (459, 'Inanga Food, Co.');
INSERT customer(id, name)
VALUES (460, 'Inconnu Food, Co.');
INSERT customer(id, name)
VALUES (461, 'Indian mul Food, Co.');
INSERT customer(id, name)
VALUES (462, 'Jack Food, Co.');
INSERT customer(id, name)
VALUES (463, 'Jackfish Food, Co.');
INSERT customer(id, name)
VALUES (464, 'Jack Dempsey Food, Co.');
INSERT customer(id, name)
VALUES (465, 'Japanese eel Food, Co.');
INSERT customer(id, name)
VALUES (466, 'Javelin Food, Co.');
INSERT customer(id, name)
VALUES (467, 'Jawfish Food, Co.');
INSERT customer(id, name)
VALUES (468, 'Jellynose fish Food, Co.');
INSERT customer(id, name)
VALUES (469, 'Jewelfish Food, Co.');
INSERT customer(id, name)
VALUES (470, 'Jewel tetra Food, Co.');
INSERT customer(id, name)
VALUES (471, 'Jewfish Food, Co.');
INSERT customer(id, name)
VALUES (472, 'John dory Food, Co.');
INSERT customer(id, name)
VALUES (473, 'Kafue pike Food, Co.');
INSERT customer(id, name)
VALUES (474, 'Kahawai Food, Co.');
INSERT customer(id, name)
VALUES (475, 'Kaluga Food, Co.');
INSERT customer(id, name)
VALUES (476, 'Kanyu Food, Co.');
INSERT customer(id, name)
VALUES (477, 'Kelp perch Food, Co.');
INSERT customer(id, name)
VALUES (478, 'Kelpfish Food, Co.');
INSERT customer(id, name)
VALUES (479, 'Killifish Food, Co.');
INSERT customer(id, name)
VALUES (480, 'King of herring Food, Co.');
INSERT customer(id, name)
VALUES (481, 'Kingfish Food, Co.');
INSERT customer(id, name)
VALUES (482, 'King-of-the-salmon Food, Co.');
INSERT customer(id, name)
VALUES (483, 'Kissing gourami Food, Co.');
INSERT customer(id, name)
VALUES (484, 'Knifefish Food, Co.');
INSERT customer(id, name)
VALUES (485, 'Knifejaw Food, Co.');
INSERT customer(id, name)
VALUES (486, 'Koi Food, Co.');
INSERT customer(id, name)
VALUES (487, 'Kokanee Food, Co.');
INSERT customer(id, name)
VALUES (488, 'Kokopu Food, Co.');
INSERT customer(id, name)
VALUES (489, 'Kuhli loach Food, Co.');
INSERT customer(id, name)
VALUES (490, 'Labyrinth fish Food, Co.');
INSERT customer(id, name)
VALUES (491, 'Ladyfish Food, Co.');
INSERT customer(id, name)
VALUES (492, 'Lagena Food, Co.');
INSERT customer(id, name)
VALUES (493, 'Lake chub Food, Co.');
INSERT customer(id, name)
VALUES (494, 'Lake trout Food, Co.');
INSERT customer(id, name)
VALUES (495, 'Lake whitefish Food, Co.');
INSERT customer(id, name)
VALUES (496, 'Lampfish Food, Co.');
INSERT customer(id, name)
VALUES (497, 'Lamprey Food, Co.');
INSERT customer(id, name)
VALUES (498, 'Lancetfish Food, Co.');
INSERT customer(id, name)
VALUES (499, 'Lanternfish Food, Co.');
INSERT customer(id, name)
VALUES (500, 'Large-eye bream Food, Co.');
INSERT customer(id, name)
VALUES (501, 'Largemouth bass Food, Co.');
INSERT customer(id, name)
VALUES (502, 'Largenose fish Food, Co.');
INSERT customer(id, name)
VALUES (503, 'Leaffish Food, Co.');
INSERT customer(id, name)
VALUES (504, 'Leatherjacket Food, Co.');
INSERT customer(id, name)
VALUES (505, 'Lefteye flounder Food, Co.');
INSERT customer(id, name)
VALUES (506, 'Lemon shark Food, Co.');
INSERT customer(id, name)
VALUES (507, 'Lemon sole Food, Co.');
INSERT customer(id, name)
VALUES (508, 'Lenok Food, Co.');
INSERT customer(id, name)
VALUES (509, 'Leopard danio Food, Co.');
INSERT customer(id, name)
VALUES (510, 'Lightfish Food, Co.');
INSERT customer(id, name)
VALUES (511, 'Lighthousefish Food, Co.');
INSERT customer(id, name)
VALUES (512, 'Limia Food, Co.');
INSERT customer(id, name)
VALUES (513, 'Lined sole Food, Co.');
INSERT customer(id, name)
VALUES (514, 'Ling Food, Co.');
INSERT customer(id, name)
VALUES (515, 'Ling cod Food, Co.');
INSERT customer(id, name)
VALUES (516, 'Lionfish Food, Co.');
INSERT customer(id, name)
VALUES (517, 'Livebearer Food, Co.');
INSERT customer(id, name)
VALUES (518, 'Lizardfish Food, Co.');
INSERT customer(id, name)
VALUES (519, 'Loach Food, Co.');
INSERT customer(id, name)
VALUES (520, 'Loach catfish Food, Co.');
INSERT customer(id, name)
VALUES (521, 'Loach goby Food, Co.');
INSERT customer(id, name)
VALUES (522, 'Loach minnow Food, Co.');
INSERT customer(id, name)
VALUES (523, 'Longfin Food, Co.');
INSERT customer(id, name)
VALUES (524, 'Longfin dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (525, 'Longfin escolar Food, Co.');
INSERT customer(id, name)
VALUES (526, 'Longfin smelt Food, Co.');
INSERT customer(id, name)
VALUES (527, 'Long-finned char Food, Co.');
INSERT customer(id, name)
VALUES (528, 'Long-finned pike Food, Co.');
INSERT customer(id, name)
VALUES (529, 'Longjaw mudsucker Food, Co.');
INSERT customer(id, name)
VALUES (530, 'Longneck eel Food, Co.');
INSERT customer(id, name)
VALUES (531, 'Longnose chimaera Food, Co.');
INSERT customer(id, name)
VALUES (532, 'Longnose dace Food, Co.');
INSERT customer(id, name)
VALUES (533, 'Longnose lancetfish Food, Co.');
INSERT customer(id, name)
VALUES (534, 'Longnose sucker Food, Co.');
INSERT customer(id, name)
VALUES (535, 'Longnose whiptail catfish Food, Co.');
INSERT customer(id, name)
VALUES (536, 'Long-whiskered catfish Food, Co.');
INSERT customer(id, name)
VALUES (537, 'Lookdown catfish Food, Co.');
INSERT customer(id, name)
VALUES (538, 'Loosejaw Food, Co.');
INSERT customer(id, name)
VALUES (539, 'Lost River sucker Food, Co.');
INSERT customer(id, name)
VALUES (540, 'Louvar Food, Co.');
INSERT customer(id, name)
VALUES (541, 'Loweye catfish Food, Co.');
INSERT customer(id, name)
VALUES (542, 'Luderick Food, Co.');
INSERT customer(id, name)
VALUES (543, 'Luminous hake Food, Co.');
INSERT customer(id, name)
VALUES (544, 'Lumpsucker Food, Co.');
INSERT customer(id, name)
VALUES (545, 'Lungfish Food, Co.');
INSERT customer(id, name)
VALUES (546, 'Lyretail Food, Co.');
INSERT customer(id, name)
VALUES (547, 'Mackerel Food, Co.');
INSERT customer(id, name)
VALUES (548, 'Mackerel shark Food, Co.');
INSERT customer(id, name)
VALUES (549, 'Madtom Food, Co.');
INSERT customer(id, name)
VALUES (550, 'Mahi-mahi Food, Co.');
INSERT customer(id, name)
VALUES (551, 'Mahseer Food, Co.');
INSERT customer(id, name)
VALUES (552, 'Mail-cheeked fish Food, Co.');
INSERT customer(id, name)
VALUES (553, 'Mako shark Food, Co.');
INSERT customer(id, name)
VALUES (554, 'Mandarin fish Food, Co.');
INSERT customer(id, name)
VALUES (555, 'Manefish Food, Co.');
INSERT customer(id, name)
VALUES (556, 'Man-of-war fish Food, Co.');
INSERT customer(id, name)
VALUES (557, 'Manta Ray Food, Co.');
INSERT customer(id, name)
VALUES (558, 'Marblefish Food, Co.');
INSERT customer(id, name)
VALUES (559, 'Marine hatchetfish Food, Co.');
INSERT customer(id, name)
VALUES (560, 'Marlin Food, Co.');
INSERT customer(id, name)
VALUES (561, 'Masu salmon Food, Co.');
INSERT customer(id, name)
VALUES (562, 'Medaka Food, Co.');
INSERT customer(id, name)
VALUES (563, 'Medusafish Food, Co.');
INSERT customer(id, name)
VALUES (564, 'Megamouth shark Food, Co.');
INSERT customer(id, name)
VALUES (565, 'Menhaden Food, Co.');
INSERT customer(id, name)
VALUES (566, 'Merluccid hake Food, Co.');
INSERT customer(id, name)
VALUES (567, 'Mexican blind cavefish Food, Co.');
INSERT customer(id, name)
VALUES (568, 'Mexican golden trout Food, Co.');
INSERT customer(id, name)
VALUES (569, 'Midshipman Food, Co.');
INSERT customer(id, name)
VALUES (570, 'Milkfish Food, Co.');
INSERT customer(id, name)
VALUES (571, 'Minnow Food, Co.');
INSERT customer(id, name)
VALUES (572, 'Modoc sucker Food, Co.');
INSERT customer(id, name)
VALUES (573, 'Mojarra Food, Co.');
INSERT customer(id, name)
VALUES (574, 'Mola Food, Co.');
INSERT customer(id, name)
VALUES (575, 'Molly Food, Co.');
INSERT customer(id, name)
VALUES (576, 'Molly Miller Food, Co.');
INSERT customer(id, name)
VALUES (577, 'Monkeyface prickleback Food, Co.');
INSERT customer(id, name)
VALUES (578, 'Monkfish Food, Co.');
INSERT customer(id, name)
VALUES (579, 'Mooneye Food, Co.');
INSERT customer(id, name)
VALUES (580, 'Moonfish Food, Co.');
INSERT customer(id, name)
VALUES (581, 'Moorish idol Food, Co.');
INSERT customer(id, name)
VALUES (582, 'Mora Food, Co.');
INSERT customer(id, name)
VALUES (583, 'Moray eel Food, Co.');
INSERT customer(id, name)
VALUES (584, 'Morid cod Food, Co.');
INSERT customer(id, name)
VALUES (585, 'Morwong Food, Co.');
INSERT customer(id, name)
VALUES (586, 'Moses sole Food, Co.');
INSERT customer(id, name)
VALUES (587, 'Mosquitofish Food, Co.');
INSERT customer(id, name)
VALUES (588, 'Mosshead warbonnet Food, Co.');
INSERT customer(id, name)
VALUES (589, 'Mouthbrooder Food, Co.');
INSERT customer(id, name)
VALUES (590, 'Mozambique tilapia Food, Co.');
INSERT customer(id, name)
VALUES (591, 'Mrigal Food, Co.');
INSERT customer(id, name)
VALUES (592, 'Mudfish Food, Co.');
INSERT customer(id, name)
VALUES (593, 'Mudminnow Food, Co.');
INSERT customer(id, name)
VALUES (594, 'Mud minnow Food, Co.');
INSERT customer(id, name)
VALUES (595, 'Mudskipper Food, Co.');
INSERT customer(id, name)
VALUES (596, 'Mudsucker Food, Co.');
INSERT customer(id, name)
VALUES (597, 'Mullet Food, Co.');
INSERT customer(id, name)
VALUES (598, 'Mummichog Food, Co.');
INSERT customer(id, name)
VALUES (599, 'Murray cod Food, Co.');
INSERT customer(id, name)
VALUES (600, 'Muskellunge Food, Co.');
INSERT customer(id, name)
VALUES (601, 'Mustache triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (602, 'Mustard eel Food, Co.');
INSERT customer(id, name)
VALUES (603, 'Naked-back knifefish Food, Co.');
INSERT customer(id, name)
VALUES (604, 'Nase Food, Co.');
INSERT customer(id, name)
VALUES (605, 'Needlefish Food, Co.');
INSERT customer(id, name)
VALUES (606, 'Neon tetra Food, Co.');
INSERT customer(id, name)
VALUES (607, 'New World rivuline Food, Co.');
INSERT customer(id, name)
VALUES (608, 'New Zealand smelt Food, Co.');
INSERT customer(id, name)
VALUES (609, 'Nibble Fish Food, Co.');
INSERT customer(id, name)
VALUES (610, 'Noodlefish Food, Co.');
INSERT customer(id, name)
VALUES (611, 'North American darter Food, Co.');
INSERT customer(id, name)
VALUES (612, 'North American freshwater catfish Food, Co.');
INSERT customer(id, name)
VALUES (613, 'North Pacific daggertooth Food, Co.');
INSERT customer(id, name)
VALUES (614, 'Northern anchovy Food, Co.');
INSERT customer(id, name)
VALUES (615, 'Northern clingfish Food, Co.');
INSERT customer(id, name)
VALUES (616, 'Northern lampfish Food, Co.');
INSERT customer(id, name)
VALUES (617, 'Northern pearleye Food, Co.');
INSERT customer(id, name)
VALUES (618, 'Northern pike Food, Co.');
INSERT customer(id, name)
VALUES (619, 'Northern sea robin Food, Co.');
INSERT customer(id, name)
VALUES (620, 'Northern squawfish Food, Co.');
INSERT customer(id, name)
VALUES (621, 'Northern Stargazer Food, Co.');
INSERT customer(id, name)
VALUES (622, 'Norwegian Atlantic salmon Food, Co.');
INSERT customer(id, name)
VALUES (623, 'Nurseryfish Food, Co.');
INSERT customer(id, name)
VALUES (624, 'Nurse shark Food, Co.');
INSERT customer(id, name)
VALUES (625, 'Oarfish Food, Co.');
INSERT customer(id, name)
VALUES (626, 'Ocean perch Food, Co.');
INSERT customer(id, name)
VALUES (627, 'Ocean sunfish Food, Co.');
INSERT customer(id, name)
VALUES (628, 'Oceanic flyingfish Food, Co.');
INSERT customer(id, name)
VALUES (629, 'Oceanic whitetip shark Food, Co.');
INSERT customer(id, name)
VALUES (630, 'Oilfish Food, Co.');
INSERT customer(id, name)
VALUES (631, 'Oldwife Food, Co.');
INSERT customer(id, name)
VALUES (632, 'Old World knifefish Food, Co.');
INSERT customer(id, name)
VALUES (633, 'Old World rivuline Food, Co.');
INSERT customer(id, name)
VALUES (634, 'Olive flounder Food, Co.');
INSERT customer(id, name)
VALUES (635, 'Opah Food, Co.');
INSERT customer(id, name)
VALUES (636, 'Opaleye Food, Co.');
INSERT customer(id, name)
VALUES (637, 'Orange roughy Food, Co.');
INSERT customer(id, name)
VALUES (638, 'Orangespine unicorn fish Food, Co.');
INSERT customer(id, name)
VALUES (639, 'Orangestriped triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (640, 'Orbicular batfish Food, Co.');
INSERT customer(id, name)
VALUES (641, 'Orbicular velvetfish Food, Co.');
INSERT customer(id, name)
VALUES (642, 'Oregon chub Food, Co.');
INSERT customer(id, name)
VALUES (643, 'Oriental loach Food, Co.');
INSERT customer(id, name)
VALUES (644, 'Owens pupfish Food, Co.');
INSERT customer(id, name)
VALUES (645, 'Pacific albacore Food, Co.');
INSERT customer(id, name)
VALUES (646, 'Pacific argentine Food, Co.');
INSERT customer(id, name)
VALUES (647, 'Pacific cod Food, Co.');
INSERT customer(id, name)
VALUES (648, 'Pacific hake Food, Co.');
INSERT customer(id, name)
VALUES (649, 'Pacific herring Food, Co.');
INSERT customer(id, name)
VALUES (650, 'Pacific lamprey Food, Co.');
INSERT customer(id, name)
VALUES (651, 'Pacific salmon Food, Co.');
INSERT customer(id, name)
VALUES (652, 'Pacific saury Food, Co.');
INSERT customer(id, name)
VALUES (653, 'Pacific trout Food, Co.');
INSERT customer(id, name)
VALUES (654, 'Pacific viperfish Food, Co.');
INSERT customer(id, name)
VALUES (655, 'Paddlefish Food, Co.');
INSERT customer(id, name)
VALUES (656, 'Panga Food, Co.');
INSERT customer(id, name)
VALUES (657, 'Paperbone Food, Co.');
INSERT customer(id, name)
VALUES (658, 'Paradise fish Food, Co.');
INSERT customer(id, name)
VALUES (659, 'Parasitic catfish Food, Co.');
INSERT customer(id, name)
VALUES (660, 'Parrotfish Food, Co.');
INSERT customer(id, name)
VALUES (661, 'Peacock flounder Food, Co.');
INSERT customer(id, name)
VALUES (662, 'Peamouth Food, Co.');
INSERT customer(id, name)
VALUES (663, 'Pearleye Food, Co.');
INSERT customer(id, name)
VALUES (664, 'Pearlfish Food, Co.');
INSERT customer(id, name)
VALUES (665, 'Pearl danio Food, Co.');
INSERT customer(id, name)
VALUES (666, 'Pearl perch Food, Co.');
INSERT customer(id, name)
VALUES (667, 'Pejerrey Food, Co.');
INSERT customer(id, name)
VALUES (668, 'Peladillo Food, Co.');
INSERT customer(id, name)
VALUES (669, 'Pelagic cod Food, Co.');
INSERT customer(id, name)
VALUES (670, 'Pelican eel Food, Co.');
INSERT customer(id, name)
VALUES (671, 'Pelican gulper Food, Co.');
INSERT customer(id, name)
VALUES (672, 'Pencil catfish Food, Co.');
INSERT customer(id, name)
VALUES (673, 'Pencilfish Food, Co.');
INSERT customer(id, name)
VALUES (674, 'Pencilsmelt Food, Co.');
INSERT customer(id, name)
VALUES (675, 'Perch Food, Co.');
INSERT customer(id, name)
VALUES (676, "Peter's elephantnose fish Food, Co.");
INSERT customer(id, name)
VALUES (677, 'Pickerel Food, Co.');
INSERT customer(id, name)
VALUES (678, 'Pigfish Food, Co.');
INSERT customer(id, name)
VALUES (679, 'Pike characid Food, Co.');
INSERT customer(id, name)
VALUES (680, 'Pike conger Food, Co.');
INSERT customer(id, name)
VALUES (681, 'Pike eel Food, Co.');
INSERT customer(id, name)
VALUES (682, 'Pike Food, Co.');
INSERT customer(id, name)
VALUES (683, 'Pikeblenny Food, Co.');
INSERT customer(id, name)
VALUES (684, 'Pikehead Food, Co.');
INSERT customer(id, name)
VALUES (685, 'Pikeperch Food, Co.');
INSERT customer(id, name)
VALUES (686, 'Pilchard Food, Co.');
INSERT customer(id, name)
VALUES (687, 'Pilot fish Food, Co.');
INSERT customer(id, name)
VALUES (688, 'Pineconefish Food, Co.');
INSERT customer(id, name)
VALUES (689, 'Pink salmon Food, Co.');
INSERT customer(id, name)
VALUES (690, 'Píntano Food, Co.');
INSERT customer(id, name)
VALUES (691, 'Pipefish Food, Co.');
INSERT customer(id, name)
VALUES (692, 'Piranha Food, Co.');
INSERT customer(id, name)
VALUES (693, 'Pirarucu Food, Co.');
INSERT customer(id, name)
VALUES (694, 'Pirate perch Food, Co.');
INSERT customer(id, name)
VALUES (695, 'Plaice Food, Co.');
INSERT customer(id, name)
VALUES (696, 'Platy Food, Co.');
INSERT customer(id, name)
VALUES (697, 'Platyfish Food, Co.');
INSERT customer(id, name)
VALUES (698, 'Pleco Food, Co.');
INSERT customer(id, name)
VALUES (699, 'Plownose chimaera Food, Co.');
INSERT customer(id, name)
VALUES (700, 'Plunderfish Food, Co.');
INSERT customer(id, name)
VALUES (701, 'Poacher Food, Co.');
INSERT customer(id, name)
VALUES (702, 'Pollyfish Food, Co.');
INSERT customer(id, name)
VALUES (703, 'Pollock Food, Co.');
INSERT customer(id, name)
VALUES (704, 'Pomfret Food, Co.');
INSERT customer(id, name)
VALUES (705, 'Pompano Food, Co.');
INSERT customer(id, name)
VALUES (706, 'Pompano dolphinfish Food, Co.');
INSERT customer(id, name)
VALUES (707, 'Ponyfish Food, Co.');
INSERT customer(id, name)
VALUES (708, 'Poolfish Food, Co.');
INSERT customer(id, name)
VALUES (709, 'Popeye catafula Food, Co.');
INSERT customer(id, name)
VALUES (710, 'Porbeagle shark Food, Co.');
INSERT customer(id, name)
VALUES (711, 'Porcupinefish Food, Co.');
INSERT customer(id, name)
VALUES (712, 'Porgy Food, Co.');
INSERT customer(id, name)
VALUES (713, 'Port Jackson shark Food, Co.');
INSERT customer(id, name)
VALUES (714, 'Powen Food, Co.');
INSERT customer(id, name)
VALUES (715, 'Priapumfish Food, Co.');
INSERT customer(id, name)
VALUES (716, 'Prickleback Food, Co.');
INSERT customer(id, name)
VALUES (717, 'Pricklefish Food, Co.');
INSERT customer(id, name)
VALUES (718, 'Prickly shark Food, Co.');
INSERT customer(id, name)
VALUES (719, 'Prowfish Food, Co.');
INSERT customer(id, name)
VALUES (720, 'Pufferfish Food, Co.');
INSERT customer(id, name)
VALUES (721, 'Pumpkinseed Food, Co.');
INSERT customer(id, name)
VALUES (722, 'Pupfish Food, Co.');
INSERT customer(id, name)
VALUES (723, 'Pygmy sunfish Food, Co.');
INSERT customer(id, name)
VALUES (724, 'Queen danio Food, Co.');
INSERT customer(id, name)
VALUES (725, 'Queen parrotfish Food, Co.');
INSERT customer(id, name)
VALUES (726, 'Queen triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (727, 'Quillback Food, Co.');
INSERT customer(id, name)
VALUES (728, 'Quillfish Food, Co.');
INSERT customer(id, name)
VALUES (729, 'Rabbitfish Food, Co.');
INSERT customer(id, name)
VALUES (730, 'Raccoon butterfly fish Food, Co.');
INSERT customer(id, name)
VALUES (731, 'Ragfish Food, Co.');
INSERT customer(id, name)
VALUES (732, 'Rainbow trout Food, Co.');
INSERT customer(id, name)
VALUES (733, 'Rainbowfish Food, Co.');
INSERT customer(id, name)
VALUES (734, 'Rasbora Food, Co.');
INSERT customer(id, name)
VALUES (735, 'Ratfish Food, Co.');
INSERT customer(id, name)
VALUES (736, 'Rattail Food, Co.');
INSERT customer(id, name)
VALUES (737, 'Ray Food, Co.');
INSERT customer(id, name)
VALUES (738, 'Razorback sucker Food, Co.');
INSERT customer(id, name)
VALUES (739, 'Razorfish Food, Co.');
INSERT customer(id, name)
VALUES (740, 'Red salmon Food, Co.');
INSERT customer(id, name)
VALUES (741, 'Red snapper Food, Co.');
INSERT customer(id, name)
VALUES (742, 'Redfin perch Food, Co.');
INSERT customer(id, name)
VALUES (743, 'Redfish Food, Co.');
INSERT customer(id, name)
VALUES (744, 'Redhorse sucker Food, Co.');
INSERT customer(id, name)
VALUES (745, 'Redlip blenny Food, Co.');
INSERT customer(id, name)
VALUES (746, 'Redmouth whalefish Food, Co.');
INSERT customer(id, name)
VALUES (747, 'Redside Food, Co.');
INSERT customer(id, name)
VALUES (748, 'Redtooth triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (749, 'Red velvetfish Food, Co.');
INSERT customer(id, name)
VALUES (750, 'Red whalefish Food, Co.');
INSERT customer(id, name)
VALUES (751, 'Reedfish Food, Co.');
INSERT customer(id, name)
VALUES (752, 'Reef triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (753, 'Regal whiptail catfish Food, Co.');
INSERT customer(id, name)
VALUES (754, 'Remora Food, Co.');
INSERT customer(id, name)
VALUES (755, 'Requiem shark Food, Co.');
INSERT customer(id, name)
VALUES (756, 'Ribbon eel Food, Co.');
INSERT customer(id, name)
VALUES (757, 'Ribbon sawtail fish Food, Co.');
INSERT customer(id, name)
VALUES (758, 'Ribbonbearer Food, Co.');
INSERT customer(id, name)
VALUES (759, 'Ribbonfish Food, Co.');
INSERT customer(id, name)
VALUES (760, 'Rice eel Food, Co.');
INSERT customer(id, name)
VALUES (761, 'Ricefish Food, Co.');
INSERT customer(id, name)
VALUES (762, 'Ridgehead Food, Co.');
INSERT customer(id, name)
VALUES (763, 'Riffle dace Food, Co.');
INSERT customer(id, name)
VALUES (764, 'Righteye flounder Food, Co.');
INSERT customer(id, name)
VALUES (765, 'Rio Grande perch Food, Co.');
INSERT customer(id, name)
VALUES (766, 'River loach Food, Co.');
INSERT customer(id, name)
VALUES (767, 'River shark Food, Co.');
INSERT customer(id, name)
VALUES (768, 'River stingray Food, Co.');
INSERT customer(id, name)
VALUES (769, 'Rivuline Food, Co.');
INSERT customer(id, name)
VALUES (770, 'Roach Food, Co.');
INSERT customer(id, name)
VALUES (771, 'Roanoke bass Food, Co.');
INSERT customer(id, name)
VALUES (772, 'Rock bass Food, Co.');
INSERT customer(id, name)
VALUES (773, 'Rock beauty Food, Co.');
INSERT customer(id, name)
VALUES (774, 'Rock cod Food, Co.');
INSERT customer(id, name)
VALUES (775, 'Rocket danio Food, Co.');
INSERT customer(id, name)
VALUES (776, 'Rockfish Food, Co.');
INSERT customer(id, name)
VALUES (777, 'Rockling Food, Co.');
INSERT customer(id, name)
VALUES (778, 'Rockweed gunnel Food, Co.');
INSERT customer(id, name)
VALUES (779, 'Rohu Food, Co.');
INSERT customer(id, name)
VALUES (780, 'Ronquil Food, Co.');
INSERT customer(id, name)
VALUES (781, 'Roosterfish Food, Co.');
INSERT customer(id, name)
VALUES (782, 'Ropefish Food, Co.');
INSERT customer(id, name)
VALUES (783, 'Rough pomfret Food, Co.');
INSERT customer(id, name)
VALUES (784, 'Rough scad Food, Co.');
INSERT customer(id, name)
VALUES (785, 'Rough sculpin Food, Co.');
INSERT customer(id, name)
VALUES (786, 'Roughy Food, Co.');
INSERT customer(id, name)
VALUES (787, 'Roundhead Food, Co.');
INSERT customer(id, name)
VALUES (788, 'Round herring Food, Co.');
INSERT customer(id, name)
VALUES (789, 'Round stingray Food, Co.');
INSERT customer(id, name)
VALUES (790, 'Round whitefish Food, Co.');
INSERT customer(id, name)
VALUES (791, 'Rudd Food, Co.');
INSERT customer(id, name)
VALUES (792, 'Rudderfish Food, Co.');
INSERT customer(id, name)
VALUES (793, 'Ruffe Food, Co.');
INSERT customer(id, name)
VALUES (794, 'Russian sturgeon Food, Co.');
INSERT customer(id, name)
VALUES (795, 'Sábalo Food, Co.');
INSERT customer(id, name)
VALUES (796, 'Sabertooth Food, Co.');
INSERT customer(id, name)
VALUES (797, 'Saber-toothed blenny Food, Co.');
INSERT customer(id, name)
VALUES (798, 'Sabertooth fish Food, Co.');
INSERT customer(id, name)
VALUES (799, 'Sablefish Food, Co.');
INSERT customer(id, name)
VALUES (800, 'Sacramento blackfish Food, Co.');
INSERT customer(id, name)
VALUES (801, 'Sacramento splittail Food, Co.');
INSERT customer(id, name)
VALUES (802, 'Sailback scorpionfish Food, Co.');
INSERT customer(id, name)
VALUES (803, 'Sailbearer Food, Co.');
INSERT customer(id, name)
VALUES (804, 'Sailfin silverside Food, Co.');
INSERT customer(id, name)
VALUES (805, 'Sailfish Food, Co.');
INSERT customer(id, name)
VALUES (806, 'Salamanderfish Food, Co.');
INSERT customer(id, name)
VALUES (807, 'Salmon Food, Co.');
INSERT customer(id, name)
VALUES (808, 'Salmon shark Food, Co.');
INSERT customer(id, name)
VALUES (809, 'Sandbar shark Food, Co.');
INSERT customer(id, name)
VALUES (810, 'Sandburrower Food, Co.');
INSERT customer(id, name)
VALUES (811, 'Sand dab Food, Co.');
INSERT customer(id, name)
VALUES (812, 'Sparkle Food, Co.');
INSERT customer(id, name)
VALUES (813, 'Sand diver Food, Co.');
INSERT customer(id, name)
VALUES (814, 'Sand eel Food, Co.');
INSERT customer(id, name)
VALUES (815, 'Sandfish Food, Co.');
INSERT customer(id, name)
VALUES (816, 'Sand goby Food, Co.');
INSERT customer(id, name)
VALUES (817, 'Sand knifefish Food, Co.');
INSERT customer(id, name)
VALUES (818, 'Sand lance Food, Co.');
INSERT customer(id, name)
VALUES (819, 'Sandperch Food, Co.');
INSERT customer(id, name)
VALUES (820, 'Sandroller Food, Co.');
INSERT customer(id, name)
VALUES (821, 'Sand stargazer Food, Co.');
INSERT customer(id, name)
VALUES (822, 'Sand tiger Food, Co.');
INSERT customer(id, name)
VALUES (823, 'Sand tilefish Food, Co.');
INSERT customer(id, name)
VALUES (824, 'Sarcastic fringehead Food, Co.');
INSERT customer(id, name)
VALUES (825, 'Sardine Food, Co.');
INSERT customer(id, name)
VALUES (826, 'Sargassum fish Food, Co.');
INSERT customer(id, name)
VALUES (827, 'Sauger Food, Co.');
INSERT customer(id, name)
VALUES (828, 'Saury Food, Co.');
INSERT customer(id, name)
VALUES (829, 'Sawfish Food, Co.');
INSERT customer(id, name)
VALUES (830, 'Saw shark Food, Co.');
INSERT customer(id, name)
VALUES (831, 'Sawtooth eel Food, Co.');
INSERT customer(id, name)
VALUES (832, 'Scabbard fish Food, Co.');
INSERT customer(id, name)
VALUES (833, 'Scaleless black dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (834, 'Scaly dragonfish Food, Co.');
INSERT customer(id, name)
VALUES (835, 'Scat Food, Co.');
INSERT customer(id, name)
VALUES (836, 'Scissor-tail rasbora Food, Co.');
INSERT customer(id, name)
VALUES (837, 'Scorpionfish Food, Co.');
INSERT customer(id, name)
VALUES (838, 'Sculpin Food, Co.');
INSERT customer(id, name)
VALUES (839, 'Scup Food, Co.');
INSERT customer(id, name)
VALUES (840, 'Scythe butterfish Food, Co.');
INSERT customer(id, name)
VALUES (841, 'Sea bass Food, Co.');
INSERT customer(id, name)
VALUES (842, 'Sea bream Food, Co.');
INSERT customer(id, name)
VALUES (843, 'Sea catfish Food, Co.');
INSERT customer(id, name)
VALUES (844, 'Sea chub Food, Co.');
INSERT customer(id, name)
VALUES (845, 'Sea devil Food, Co.');
INSERT customer(id, name)
VALUES (846, 'Sea dragon Food, Co.');
INSERT customer(id, name)
VALUES (847, 'Seahorse Food, Co.');
INSERT customer(id, name)
VALUES (848, 'Sea lamprey Food, Co.');
INSERT customer(id, name)
VALUES (849, 'Seamoth Food, Co.');
INSERT customer(id, name)
VALUES (850, 'Sea raven Food, Co.');
INSERT customer(id, name)
VALUES (851, 'Searobin Food, Co.');
INSERT customer(id, name)
VALUES (852, 'Sea snail Food, Co.');
INSERT customer(id, name)
VALUES (853, 'Sea toad Food, Co.');
INSERT customer(id, name)
VALUES (854, 'Sevan trout Food, Co.');
INSERT customer(id, name)
VALUES (855, 'Seatrout Food, Co.');
INSERT customer(id, name)
VALUES (856, 'Sergeant major Food, Co.');
INSERT customer(id, name)
VALUES (857, 'Shad Food, Co.');
INSERT customer(id, name)
VALUES (858, 'Shark Food, Co.');
INSERT customer(id, name)
VALUES (859, 'Sharksucker Food, Co.');
INSERT customer(id, name)
VALUES (860, 'Canthigaster rostrata Food, Co.');
INSERT customer(id, name)
VALUES (861, 'Sheatfish Food, Co.');
INSERT customer(id, name)
VALUES (862, 'Shingle Fish Food, Co.');
INSERT customer(id, name)
VALUES (863, 'Sheepshead Food, Co.');
INSERT customer(id, name)
VALUES (864, 'Sheepshead minnow Food, Co.');
INSERT customer(id, name)
VALUES (865, 'Shell-ear Food, Co.');
INSERT customer(id, name)
VALUES (866, 'Shiner Food, Co.');
INSERT customer(id, name)
VALUES (867, 'Shortnose chimaera Food, Co.');
INSERT customer(id, name)
VALUES (868, 'Shortnose greeneye Food, Co.');
INSERT customer(id, name)
VALUES (869, 'Shortnose sucker Food, Co.');
INSERT customer(id, name)
VALUES (870, 'Shovelnose sturgeon Food, Co.');
INSERT customer(id, name)
VALUES (871, 'Shrimpfish Food, Co.');
INSERT customer(id, name)
VALUES (872, 'Siamese fighting fish Food, Co.');
INSERT customer(id, name)
VALUES (873, 'Sillago Food, Co.');
INSERT customer(id, name)
VALUES (874, 'Silver carp Food, Co.');
INSERT customer(id, name)
VALUES (875, 'Silver dollar Food, Co.');
INSERT customer(id, name)
VALUES (876, 'Silver driftfish Food, Co.');
INSERT customer(id, name)
VALUES (877, 'Silver hake Food, Co.');
INSERT customer(id, name)
VALUES (878, 'Silverside Food, Co.');
INSERT customer(id, name)
VALUES (879, 'Sind danio Food, Co.');
INSERT customer(id, name)
VALUES (880, 'Sixgill ray Food, Co.');
INSERT customer(id, name)
VALUES (881, 'Sixgill shark Food, Co.');
INSERT customer(id, name)
VALUES (882, 'Skate Food, Co.');
INSERT customer(id, name)
VALUES (883, 'Skilfish Food, Co.');
INSERT customer(id, name)
VALUES (884, 'Skipjack tuna Food, Co.');
INSERT customer(id, name)
VALUES (885, 'Skipping goby Food, Co.');
INSERT customer(id, name)
VALUES (886, 'Slender barracudina Food, Co.');
INSERT customer(id, name)
VALUES (887, 'Slender mola Food, Co.');
INSERT customer(id, name)
VALUES (888, 'Slender snipe eel Food, Co.');
INSERT customer(id, name)
VALUES (889, 'Sleeper Food, Co.');
INSERT customer(id, name)
VALUES (890, 'Sleeper shark Food, Co.');
INSERT customer(id, name)
VALUES (891, 'Slickhead Food, Co.');
INSERT customer(id, name)
VALUES (892, 'Slimehead Food, Co.');
INSERT customer(id, name)
VALUES (893, 'Slimy mackerel Food, Co.');
INSERT customer(id, name)
VALUES (894, 'Slimy sculpin Food, Co.');
INSERT customer(id, name)
VALUES (895, 'Slipmouth Food, Co.');
INSERT customer(id, name)
VALUES (896, 'Smalleye squaretail Food, Co.');
INSERT customer(id, name)
VALUES (897, 'Smalltooth sawfish Food, Co.');
INSERT customer(id, name)
VALUES (898, 'Smelt Food, Co.');
INSERT customer(id, name)
VALUES (899, 'Smelt-whiting Food, Co.');
INSERT customer(id, name)
VALUES (900, 'Smooth dogfish Food, Co.');
INSERT customer(id, name)
VALUES (901, 'Smoothtongue Food, Co.');
INSERT customer(id, name)
VALUES (902, 'Snailfish Food, Co.');
INSERT customer(id, name)
VALUES (903, 'Snake eel Food, Co.');
INSERT customer(id, name)
VALUES (904, 'Snakehead Food, Co.');
INSERT customer(id, name)
VALUES (905, 'Snake mackerel Food, Co.');
INSERT customer(id, name)
VALUES (906, 'Snake mudhead Food, Co.');
INSERT customer(id, name)
VALUES (907, 'Snapper Food, Co.');
INSERT customer(id, name)
VALUES (908, 'Snipe eel Food, Co.');
INSERT customer(id, name)
VALUES (909, 'Snipefish Food, Co.');
INSERT customer(id, name)
VALUES (910, 'Snoek Food, Co.');
INSERT customer(id, name)
VALUES (911, 'Snook Food, Co.');
INSERT customer(id, name)
VALUES (912, 'Snubnose eel Food, Co.');
INSERT customer(id, name)
VALUES (913, 'Snubnose parasitic eel Food, Co.');
INSERT customer(id, name)
VALUES (914, 'Soapfish Food, Co.');
INSERT customer(id, name)
VALUES (915, 'Sockeye salmon Food, Co.');
INSERT customer(id, name)
VALUES (916, 'Soldierfish Food, Co.');
INSERT customer(id, name)
VALUES (917, 'Sole Food, Co.');
INSERT customer(id, name)
VALUES (918, 'South American darter Food, Co.');
INSERT customer(id, name)
VALUES (919, 'South American Lungfish Food, Co.');
INSERT customer(id, name)
VALUES (920, 'Southern Dolly Varden Food, Co.');
INSERT customer(id, name)
VALUES (921, 'Southern flounder Food, Co.');
INSERT customer(id, name)
VALUES (922, 'Southern grayling Food, Co.');
INSERT customer(id, name)
VALUES (923, 'Southern hake Food, Co.');
INSERT customer(id, name)
VALUES (924, 'Southern sandfish Food, Co.');
INSERT customer(id, name)
VALUES (925, 'Southern smelt Food, Co.');
INSERT customer(id, name)
VALUES (926, 'Spadefish Food, Co.');
INSERT customer(id, name)
VALUES (927, 'Spaghetti eel Food, Co.');
INSERT customer(id, name)
VALUES (928, 'Spanish mackerel Food, Co.');
INSERT customer(id, name)
VALUES (929, 'Spearfish Food, Co.');
INSERT customer(id, name)
VALUES (930, 'Speckled trout Food, Co.');
INSERT customer(id, name)
VALUES (931, 'Spiderfish Food, Co.');
INSERT customer(id, name)
VALUES (932, 'Spikefish Food, Co.');
INSERT customer(id, name)
VALUES (933, 'Spinefoot Food, Co.');
INSERT customer(id, name)
VALUES (934, 'Spiny-back Food, Co.');
INSERT customer(id, name)
VALUES (935, 'Spiny basslet Food, Co.');
INSERT customer(id, name)
VALUES (936, 'Spiny dogfish Food, Co.');
INSERT customer(id, name)
VALUES (937, 'Spiny dwarf catfish Food, Co.');
INSERT customer(id, name)
VALUES (938, 'Spiny eel Food, Co.');
INSERT customer(id, name)
VALUES (939, 'Spinyfin Food, Co.');
INSERT customer(id, name)
VALUES (940, 'Splitfin Food, Co.');
INSERT customer(id, name)
VALUES (941, 'Spookfish Food, Co.');
INSERT customer(id, name)
VALUES (942, 'Spotted danio Food, Co.');
INSERT customer(id, name)
VALUES (943, 'Spotted dogfish Food, Co.');
INSERT customer(id, name)
VALUES (944, 'Sprat Food, Co.');
INSERT customer(id, name)
VALUES (945, 'Springfish Food, Co.');
INSERT customer(id, name)
VALUES (946, 'Squarehead catfish Food, Co.');
INSERT customer(id, name)
VALUES (947, 'Squaretail Food, Co.');
INSERT customer(id, name)
VALUES (948, 'Squawfish Food, Co.');
INSERT customer(id, name)
VALUES (949, 'Squeaker Food, Co.');
INSERT customer(id, name)
VALUES (950, 'Squirrelfish Food, Co.');
INSERT customer(id, name)
VALUES (951, 'Staghorn sculpin Food, Co.');
INSERT customer(id, name)
VALUES (952, 'Stargazer Food, Co.');
INSERT customer(id, name)
VALUES (953, 'Starry flounder Food, Co.');
INSERT customer(id, name)
VALUES (954, 'Steelhead Food, Co.');
INSERT customer(id, name)
VALUES (955, 'Stickleback Food, Co.');
INSERT customer(id, name)
VALUES (956, 'Stingfish Food, Co.');
INSERT customer(id, name)
VALUES (957, 'Stingray Food, Co.');
INSERT customer(id, name)
VALUES (958, 'Stonecat Food, Co.');
INSERT customer(id, name)
VALUES (959, 'Stonefish Food, Co.');
INSERT customer(id, name)
VALUES (960, 'Stoneroller minnow Food, Co.');
INSERT customer(id, name)
VALUES (961, 'Straptail Food, Co.');
INSERT customer(id, name)
VALUES (962, 'Stream catfish Food, Co.');
INSERT customer(id, name)
VALUES (963, 'Streamer fish Food, Co.');
INSERT customer(id, name)
VALUES (964, 'Striped bass Food, Co.');
INSERT customer(id, name)
VALUES (965, 'Striped burrfish Food, Co.');
INSERT customer(id, name)
VALUES (966, 'Sturgeon Food, Co.');
INSERT customer(id, name)
VALUES (967, 'Sucker Food, Co.');
INSERT customer(id, name)
VALUES (968, 'Suckermouth armored catfish Food, Co.');
INSERT customer(id, name)
VALUES (969, 'Summer flounder Food, Co.');
INSERT customer(id, name)
VALUES (970, 'Sundaland noodlefish Food, Co.');
INSERT customer(id, name)
VALUES (971, 'Sunfish (opah) Food, Co.');
INSERT customer(id, name)
VALUES (972, 'Sunfish (mola mola) Food, Co.');
INSERT customer(id, name)
VALUES (973, 'Surf sardine Food, Co.');
INSERT customer(id, name)
VALUES (974, 'Surfperch Food, Co.');
INSERT customer(id, name)
VALUES (975, 'Surgeonfish Food, Co.');
INSERT customer(id, name)
VALUES (976, 'Swallower Food, Co.');
INSERT customer(id, name)
VALUES (977, 'Swamp-eel Food, Co.');
INSERT customer(id, name)
VALUES (978, 'Swampfish Food, Co.');
INSERT customer(id, name)
VALUES (979, 'Sweeper Food, Co.');
INSERT customer(id, name)
VALUES (980, 'Swordfish Food, Co.');
INSERT customer(id, name)
VALUES (981, 'Swordtail Food, Co.');
INSERT customer(id, name)
VALUES (982, 'Tadpole cod Food, Co.');
INSERT customer(id, name)
VALUES (983, 'Tadpole fish Food, Co.');
INSERT customer(id, name)
VALUES (984, 'Tailor Food, Co.');
INSERT customer(id, name)
VALUES (985, 'Taimen Food, Co.');
INSERT customer(id, name)
VALUES (986, 'Tang Food, Co.');
INSERT customer(id, name)
VALUES (987, 'Tapetail Food, Co.');
INSERT customer(id, name)
VALUES (988, 'Tarpon Food, Co.');
INSERT customer(id, name)
VALUES (989, 'Tarwhine Food, Co.');
INSERT customer(id, name)
VALUES (990, 'Telescopefish Food, Co.');
INSERT customer(id, name)
VALUES (991, 'Temperate bass Food, Co.');
INSERT customer(id, name)
VALUES (992, 'Temperate ocean-bass Food, Co.');
INSERT customer(id, name)
VALUES (993, 'Temperate perch Food, Co.');
INSERT customer(id, name)
VALUES (994, 'Tench Food, Co.');
INSERT customer(id, name)
VALUES (995, 'Tenpounder Food, Co.');
INSERT customer(id, name)
VALUES (996, 'Tenuis Food, Co.');
INSERT customer(id, name)
VALUES (997, 'Tetra Food, Co.');
INSERT customer(id, name)
VALUES (998, 'Thorny catfish Food, Co.');
INSERT customer(id, name)
VALUES (999, 'Thornfish Food, Co.');
INSERT customer(id, name)
VALUES (1000, 'Thornyhead Food, Co.');
INSERT customer(id, name)
VALUES (1001, 'Threadfin Food, Co.');
INSERT customer(id, name)
VALUES (1002, 'Threadfin bream Food, Co.');
INSERT customer(id, name)
VALUES (1003, 'Threadsail Food, Co.');
INSERT customer(id, name)
VALUES (1004, 'Threadtail Food, Co.');
INSERT customer(id, name)
VALUES (1005, 'Three spot gourami Food, Co.');
INSERT customer(id, name)
VALUES (1006, 'Threespine stickleback Food, Co.');
INSERT customer(id, name)
VALUES (1007, 'Three-toothed puffer Food, Co.');
INSERT customer(id, name)
VALUES (1008, 'Thresher shark Food, Co.');
INSERT customer(id, name)
VALUES (1009, 'Tidewater goby Food, Co.');
INSERT customer(id, name)
VALUES (1010, 'Tiger barb Food, Co.');
INSERT customer(id, name)
VALUES (1011, 'Tigerperch Food, Co.');
INSERT customer(id, name)
VALUES (1012, 'Tiger shark Food, Co.');
INSERT customer(id, name)
VALUES (1013, 'Tiger shovelnose catfish Food, Co.');
INSERT customer(id, name)
VALUES (1014, 'Tilapia Food, Co.');
INSERT customer(id, name)
VALUES (1015, 'Tilefish Food, Co.');
INSERT customer(id, name)
VALUES (1016, 'Titan triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (1017, 'Toadfish Food, Co.');
INSERT customer(id, name)
VALUES (1018, 'Tommy ruff Food, Co.');
INSERT customer(id, name)
VALUES (1019, 'Tompot blenny Food, Co.');
INSERT customer(id, name)
VALUES (1020, 'Tonguefish Food, Co.');
INSERT customer(id, name)
VALUES (1021, 'Tope Food, Co.');
INSERT customer(id, name)
VALUES (1022, 'Topminnow Food, Co.');
INSERT customer(id, name)
VALUES (1023, 'Torpedo Food, Co.');
INSERT customer(id, name)
VALUES (1024, 'Torrent catfish Food, Co.');
INSERT customer(id, name)
VALUES (1025, 'Torrent fish Food, Co.');
INSERT customer(id, name)
VALUES (1026, 'Trahira Food, Co.');
INSERT customer(id, name)
VALUES (1027, 'Treefish Food, Co.');
INSERT customer(id, name)
VALUES (1028, 'Trevally Food, Co.');
INSERT customer(id, name)
VALUES (1029, 'Trench Food, Co.');
INSERT customer(id, name)
VALUES (1030, 'Triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (1031, 'Triplefin blenny Food, Co.');
INSERT customer(id, name)
VALUES (1032, 'Triplespine Food, Co.');
INSERT customer(id, name)
VALUES (1033, 'Tripletail Food, Co.');
INSERT customer(id, name)
VALUES (1034, 'Tripod fish Food, Co.');
INSERT customer(id, name)
VALUES (1035, 'Trout Food, Co.');
INSERT customer(id, name)
VALUES (1036, 'Trout cod Food, Co.');
INSERT customer(id, name)
VALUES (1037, 'Trout-perch Food, Co.');
INSERT customer(id, name)
VALUES (1038, 'Trumpeter Food, Co.');
INSERT customer(id, name)
VALUES (1039, 'Trumpetfish Food, Co.');
INSERT customer(id, name)
VALUES (1040, 'Trunkfish Food, Co.');
INSERT customer(id, name)
VALUES (1041, 'Tubeblenny Food, Co.');
INSERT customer(id, name)
VALUES (1042, 'Tube-eye Food, Co.');
INSERT customer(id, name)
VALUES (1043, 'Tube-snout Food, Co.');
INSERT customer(id, name)
VALUES (1044, 'Tubeshoulder Food, Co.');
INSERT customer(id, name)
VALUES (1045, 'Tui chub Food, Co.');
INSERT customer(id, name)
VALUES (1046, 'Tuna Food, Co.');
INSERT customer(id, name)
VALUES (1047, 'Turbot Food, Co.');
INSERT customer(id, name)
VALUES (1048, 'Turkeyfish Food, Co.');
INSERT customer(id, name)
VALUES (1049, 'Unicorn fish Food, Co.');
INSERT customer(id, name)
VALUES (1050, 'Upside-down catfish Food, Co.');
INSERT customer(id, name)
VALUES (1051, 'Velvet-belly shark Food, Co.');
INSERT customer(id, name)
VALUES (1052, 'Velvet catfish Food, Co.');
INSERT customer(id, name)
VALUES (1053, 'Velvetfish Food, Co.');
INSERT customer(id, name)
VALUES (1054, 'Vendace Food, Co.');
INSERT customer(id, name)
VALUES (1055, 'Vimba Food, Co.');
INSERT customer(id, name)
VALUES (1056, 'Viperfish Food, Co.');
INSERT customer(id, name)
VALUES (1057, 'Wahoo Food, Co.');
INSERT customer(id, name)
VALUES (1058, 'Walking catfish Food, Co.');
INSERT customer(id, name)
VALUES (1059, 'Wallago Food, Co.');
INSERT customer(id, name)
VALUES (1060, 'Walleye Food, Co.');
INSERT customer(id, name)
VALUES (1061, 'Walleye pollock Food, Co.');
INSERT customer(id, name)
VALUES (1062, 'Walu Food, Co.');
INSERT customer(id, name)
VALUES (1063, 'Warbonnet Food, Co.');
INSERT customer(id, name)
VALUES (1064, 'Warmouth Food, Co.');
INSERT customer(id, name)
VALUES (1065, 'Warty angler Food, Co.');
INSERT customer(id, name)
VALUES (1066, 'Waryfish Food, Co.');
INSERT customer(id, name)
VALUES (1067, 'Wasp fish Food, Co.');
INSERT customer(id, name)
VALUES (1068, 'Weasel shark Food, Co.');
INSERT customer(id, name)
VALUES (1069, 'Weatherfish Food, Co.');
INSERT customer(id, name)
VALUES (1070, 'Weever Food, Co.');
INSERT customer(id, name)
VALUES (1071, 'Weeverfish Food, Co.');
INSERT customer(id, name)
VALUES (1072, 'Wels catfish Food, Co.');
INSERT customer(id, name)
VALUES (1073, 'Whale catfish Food, Co.');
INSERT customer(id, name)
VALUES (1074, 'Whalefish Food, Co.');
INSERT customer(id, name)
VALUES (1075, 'Whale shark Food, Co.');
INSERT customer(id, name)
VALUES (1076, 'Whiff Food, Co.');
INSERT customer(id, name)
VALUES (1077, 'Whiptail gulper Food, Co.');
INSERT customer(id, name)
VALUES (1078, 'Whitebait Food, Co.');
INSERT customer(id, name)
VALUES (1079, 'White croaker Food, Co.');
INSERT customer(id, name)
VALUES (1080, 'Whitefish Food, Co.');
INSERT customer(id, name)
VALUES (1081, 'White marlin Food, Co.');
INSERT customer(id, name)
VALUES (1082, 'White shark Food, Co.');
INSERT customer(id, name)
VALUES (1083, 'Whitetip reef shark Food, Co.');
INSERT customer(id, name)
VALUES (1084, 'Whiting Food, Co.');
INSERT customer(id, name)
VALUES (1085, 'Wobbegong Food, Co.');
INSERT customer(id, name)
VALUES (1086, 'Wolf-eel Food, Co.');
INSERT customer(id, name)
VALUES (1087, 'Wolffish Food, Co.');
INSERT customer(id, name)
VALUES (1088, 'Wolf-herring Food, Co.');
INSERT customer(id, name)
VALUES (1089, 'Woody sculpin Food, Co.');
INSERT customer(id, name)
VALUES (1090, 'Worm eel Food, Co.');
INSERT customer(id, name)
VALUES (1091, 'Wormfish Food, Co.');
INSERT customer(id, name)
VALUES (1092, 'Wrasse Food, Co.');
INSERT customer(id, name)
VALUES (1093, 'Wrymouth Food, Co.');
INSERT customer(id, name)
VALUES (1094, 'X-ray tetra Food, Co.');
INSERT customer(id, name)
VALUES (1095, 'Yellow-and-black triplefin Food, Co.');
INSERT customer(id, name)
VALUES (1096, 'Yellowbanded perch Food, Co.');
INSERT customer(id, name)
VALUES (1097, 'Yellow bass Food, Co.');
INSERT customer(id, name)
VALUES (1098, 'Yellow-edged moray Food, Co.');
INSERT customer(id, name)
VALUES (1099, 'Yellow-eye mullet Food, Co.');
INSERT customer(id, name)
VALUES (1100, 'Yellowhead jawfish Food, Co.');
INSERT customer(id, name)
VALUES (1101, 'Yellowfin croaker Food, Co.');
INSERT customer(id, name)
VALUES (1102, 'Yellowfin cutthroat trout Food, Co.');
INSERT customer(id, name)
VALUES (1103, 'Yellowfin grouper Food, Co.');
INSERT customer(id, name)
VALUES (1104, 'Yellowfin pike Food, Co.');
INSERT customer(id, name)
VALUES (1105, 'Yellowfin surgeonfish Food, Co.');
INSERT customer(id, name)
VALUES (1106, 'Yellowfin tuna Food, Co.');
INSERT customer(id, name)
VALUES (1107, 'Yellow jack Food, Co.');
INSERT customer(id, name)
VALUES (1108, 'Yellowmargin triggerfish Food, Co.');
INSERT customer(id, name)
VALUES (1109, 'Yellow moray Food, Co.');
INSERT customer(id, name)
VALUES (1110, 'Yellow perch Food, Co.');
INSERT customer(id, name)
VALUES (1111, 'Yellowtail Food, Co.');
INSERT customer(id, name)
VALUES (1112, 'Yellowtail amberjack Food, Co.');
INSERT customer(id, name)
VALUES (1113, 'Yellowtail barracuda Food, Co.');
INSERT customer(id, name)
VALUES (1114, 'Yellowtail clownfish Food, Co.');
INSERT customer(id, name)
VALUES (1115, 'Yellowtail horse mackerel Food, Co.');
INSERT customer(id, name)
VALUES (1116, 'Yellowtail kingfish Food, Co.');
INSERT customer(id, name)
VALUES (1117, 'Yellowtail snapper Food, Co.');
INSERT customer(id, name)
VALUES (1118, 'Yellow tang Food, Co.');
INSERT customer(id, name)
VALUES (1119, 'Yellow weaver Food, Co.');
INSERT customer(id, name)
VALUES (1120, 'Yellowbelly tail catfish Food, Co.');
INSERT customer(id, name)
VALUES (1121, 'Zander Food, Co.');
INSERT customer(id, name)
VALUES (1122, 'Zebra bullhead shark Food, Co.');
INSERT customer(id, name)
VALUES (1123, 'Zebra danio Food, Co.');
INSERT customer(id, name)
VALUES (1124, 'Zebrafish Food, Co.');
INSERT customer(id, name)
VALUES (1125, 'Zebra lionfish Food, Co.');
INSERT customer(id, name)
VALUES (1126, 'Zebra loach Food, Co.');
INSERT customer(id, name)
VALUES (1127, 'Zebra oto Food, Co.');
INSERT customer(id, name)
VALUES (1128, 'Zebra pleco Food, Co.');
INSERT customer(id, name)
VALUES (1129, 'Zebra shark Food, Co.');
INSERT customer(id, name)
VALUES (1130, 'Zebra tilapia Food, Co.');
INSERT customer(id, name)
VALUES (1131, 'Ziege Food, Co.');
INSERT customer(id, name)
VALUES (1132, 'Zingel Food, Co.');
INSERT customer(id, name)
VALUES (1133, 'Zebra trout Food, Co.');
INSERT customer(id, name)
VALUES (1134, 'Zebra turkeyfish Food, Co.');

INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 00:00:00', 'Swede ', 'Madtom Food, Co.', 7, 38, 549, 528, 3696);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 00:04:45', 'Olive ', 'Old World knifefish Food, Co.', 16, 24, 632, 781, 12496);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 00:14:10', 'Peas ', 'Darter Food, Co.', 8, 30, 251, 373, 2984);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 00:27:02', 'Shallots ', 'Lampfish Food, Co.', 46, 32, 496, 331, 15226);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 00:49:21', 'Yam', 'Ratfish Food, Co.', 20, 39, 735, 790, 15800);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 01:02:15', 'Squash ', 'Green swordtail Food, Co.', 36, 34, 409, 190, 6840);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 01:12:20', 'Swede ', 'Cutthroat trout Food, Co.', 16, 38, 245, 528, 8448);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 01:37:52', 'Peas ', 'Frogfish Food, Co.', 84, 30, 361, 373, 31332);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 01:51:49', 'Tomato ', 'Combtail gourami Food, Co.', 45, 36, 217, 361, 16245);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 01:57:17', 'Carrot ', 'Lemon sole Food, Co.', 57, 10, 507, 460, 26220);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 02:13:52', 'Cabbage ', 'Cuskfish Food, Co.', 40, 8, 242, 492, 19680);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 02:39:36', 'Lettuce ', 'Ridgehead Food, Co.', 68, 21, 762, 964, 65552);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 02:59:45', 'Cucumber ', 'Sweeper Food, Co.', 78, 16, 979, 472, 36816);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 03:23:51', 'Aubergine ', 'Combtooth blenny Food, Co.', 68, 3, 218, 294, 19992);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 03:39:47', 'Mushroom ', 'Muskellunge Food, Co.', 71, 22, 600, 347, 24637);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 03:57:46', 'Peppers ', 'Lake whitefish Food, Co.', 93, 27, 495, 297, 27621);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 04:14:29', 'Tomato ', 'Quillback Food, Co.', 100, 36, 727, 361, 36100);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 04:42:10', 'Sweet Potato ', 'Piranha Food, Co.', 36, 35, 692, 650, 23400);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 04:55:42', 'Sweet Potato ', 'Eel Food, Co.', 18, 35, 294, 650, 11700);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 05:01:56', 'Cucumber ', 'Bluefin tuna Food, Co.', 81, 16, 121, 472, 38232);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 05:26:32', 'Olive ', 'Mosquitofish Food, Co.', 16, 24, 587, 781, 12496);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 05:31:29', 'Garlic ', 'Mojarra Food, Co.', 4, 19, 573, 484, 1936);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 05:48:00', 'Brussels Sprouts ', 'Sevan trout Food, Co.', 14, 7, 854, 776, 10864);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 05:54:16', 'Yam', 'Monkfish Food, Co.', 86, 39, 578, 790, 67940);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 06:18:03', 'Courgette ', 'Cow shark Food, Co.', 97, 15, 230, 251, 24347);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 06:40:03', 'Onion ', 'Australian prowfish Food, Co.', 83, 25, 49, 810, 67230);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 06:51:40', 'Olive ', 'Porgy Food, Co.', 90, 24, 712, 781, 70290);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 06:52:59', 'Sweet Potato ', 'Atlantic cod Food, Co.', 6, 35, 38, 650, 3900);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 07:09:00', 'Bok Choy ', 'Sundaland noodlefish Food, Co.', 21, 5, 970, 533, 11193);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 07:25:52', 'Parsnip ', 'Blacktip reef shark Food, Co.', 95, 26, 100, 336, 31920);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 07:47:33', 'Bok Choy ', 'Pacific cod Food, Co.', 64, 5, 647, 533, 34112);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 07:53:15', 'Celery ', 'Lined sole Food, Co.', 47, 13, 513, 536, 25192);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 08:16:55', 'Turnip ', 'Smalltooth sawfish Food, Co.', 56, 37, 897, 471, 26376);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 08:25:53', 'Potato ', 'Cownose ray Food, Co.', 74, 28, 229, 232, 17168);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 08:36:02', 'Spinach ', 'Cichlid Food, Co.', 85, 33, 194, 409, 34765);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 08:55:22', 'Olive ', 'Fierasfer Food, Co.', 22, 24, 328, 781, 17182);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 09:18:53', 'Garlic ', 'Summer flounder Food, Co.', 43, 19, 969, 484, 20812);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 09:47:06', 'Courgette ', 'Lyretail Food, Co.', 22, 15, 546, 251, 5522);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 10:13:23', 'Sweet Potato ', 'Dottyback Food, Co.', 22, 35, 279, 650, 14300);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 10:22:15', 'Olive ', 'Zebra trout Food, Co.', 39, 24, 1133, 781, 30459);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 10:34:01', 'Aubergine ', 'Walu Food, Co.', 57, 3, 1062, 294, 16758);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 10:38:06', 'Carrot ', 'Barramundi Food, Co.', 10, 10, 69, 460, 4600);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 10:52:33', 'Brussels Sprouts ', 'Fire bar danio Food, Co.', 78, 7, 333, 776, 60528);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 11:15:31', 'Spinach ', 'Parrotfish Food, Co.', 48, 33, 660, 409, 19632);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 11:24:12', 'Turnip ', 'Blenny Food, Co.', 7, 37, 112, 471, 3297);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 11:29:35', 'Tomato ', 'Deep sea bonefish Food, Co.', 42, 36, 256, 361, 15162);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 11:56:55', 'Okra ', 'Black mackerel Food, Co.', 67, 23, 101, 1046, 70082);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 12:14:49', 'Cabbage ', 'Lionfish Food, Co.', 63, 8, 516, 492, 30996);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 12:31:50', 'Squash ', 'Mudfish Food, Co.', 38, 34, 592, 190, 7220);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 12:38:35', 'Courgette ', 'Clown loach Food, Co.', 22, 15, 201, 251, 5522);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 12:48:54', 'Onion ', 'Bangus Food, Co.', 37, 25, 59, 810, 29970);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 13:14:24', 'Mushroom ', 'Weever Food, Co.', 88, 22, 1070, 347, 30536);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 13:34:12', 'Garlic ', 'Yellow-edged moray Food, Co.', 73, 19, 1098, 484, 35332);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 13:40:30', 'Cauliflower ', 'Tidewater goby Food, Co.', 16, 11, 1009, 565, 9040);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 13:42:58', 'Broccoli ', 'Spotted dogfish Food, Co.', 89, 6, 943, 1025, 91225);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 13:52:37', 'Dram sticks ', 'Beluga sturgeon Food, Co.', 41, 17, 81, 164, 6724);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 14:10:07', 'Corn ', 'Flatfish Food, Co.', 15, 14, 341, 739, 11085);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 14:30:17', 'Celery ', 'Sandbar shark Food, Co.', 11, 13, 809, 536, 5896);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 14:42:31', 'Peppers ', 'Common tunny Food, Co.', 88, 27, 220, 297, 26136);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 14:58:36', 'Mushroom ', 'Rohu Food, Co.', 87, 22, 779, 347, 30189);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 15:01:20', 'Okra ', 'Squirrelfish Food, Co.', 84, 23, 950, 1046, 87864);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 15:06:33', 'Spinach ', 'Southern sandfish Food, Co.', 90, 33, 924, 409, 36810);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 15:10:16', 'Onion ', 'Zebra tilapia Food, Co.', 88, 25, 1130, 810, 71280);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 15:32:41', 'Capsicum ', 'Devil ray Food, Co.', 20, 9, 267, 578, 11560);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 16:01:08', 'Asparagus ', 'Blue danio Food, Co.', 24, 2, 118, 103, 2472);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 16:13:19', 'Pumpkin ', 'Sandperch Food, Co.', 6, 29, 819, 639, 3834);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 16:28:45', 'Spinach ', 'Jewel tetra Food, Co.', 64, 33, 470, 409, 26176);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 16:53:37', 'Pumpkin ', 'Oilfish Food, Co.', 51, 29, 630, 639, 32589);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 16:57:16', 'Sweet Potato ', 'Sandburrower Food, Co.', 5, 35, 810, 650, 3250);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 17:00:58', 'Cabbage ', 'Whiff Food, Co.', 12, 8, 1076, 492, 5904);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 17:14:04', 'Onion ', 'Eel cod Food, Co.', 63, 25, 296, 810, 51030);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 17:25:44', 'Peas ', 'Glass knifefish Food, Co.', 26, 30, 386, 373, 9698);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 17:53:06', 'Cucumber ', 'Rough pomfret Food, Co.', 15, 16, 783, 472, 7080);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:03:35', 'Aubergine ', 'Chimaera Food, Co.', 90, 3, 188, 294, 26460);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:05:16', 'Yam', 'Hammerjaw Food, Co.', 39, 39, 439, 790, 30810);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:09:41', 'Tomato ', 'Northern lampfish Food, Co.', 18, 36, 616, 361, 6498);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:13:50', 'Olive ', 'Cowfish Food, Co.', 26, 24, 228, 781, 20306);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:23:39', 'Cabbage ', 'Goldspotted killifish Food, Co.', 73, 8, 397, 492, 35916);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:29:46', 'Bok Choy ', 'Old World knifefish Food, Co.', 34, 5, 632, 533, 18122);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:35:10', 'Carrot ', 'Crocodile icefish Food, Co.', 73, 10, 236, 460, 33580);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 18:39:23', 'Celeriac ', 'Mozambique tilapia Food, Co.', 39, 12, 590, 462, 18018);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 19:04:22', 'Peppers ', 'Atlantic herring Food, Co.', 1, 27, 40, 297, 297);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 19:10:06', 'Celery ', 'Ridgehead Food, Co.', 12, 13, 762, 536, 6432);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 19:10:49', 'Cabbage ', 'Ghoul Food, Co.', 84, 8, 375, 492, 41328);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 19:30:12', 'Carrot ', 'Flashlight fish Food, Co.', 46, 10, 340, 460, 21160);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 19:48:35', 'Garlic ', 'Pygmy sunfish Food, Co.', 94, 19, 723, 484, 45496);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 20:02:15', 'Shallots ', 'Dwarf loach Food, Co.', 14, 32, 291, 331, 4634);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 20:20:09', 'Pumpkin ', 'Blue-redstripe danio Food, Co.', 46, 29, 119, 639, 29394);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 20:32:12', 'Artichokes ', 'Rio Grande perch Food, Co.', 33, 1, 765, 957, 31581);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 20:42:44', 'Peppers ', 'Bluegill Food, Co.', 55, 27, 123, 297, 16335);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 20:48:21', 'Asparagus ', 'Freshwater eel Food, Co.', 25, 2, 354, 103, 2575);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 21:13:25', 'Carrot ', 'Grunt Food, Co.', 1, 10, 417, 460, 460);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 21:41:35', 'Brussels Sprouts ', 'Warmouth Food, Co.', 45, 7, 1064, 776, 34920);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 22:02:29', 'Cucumber ', 'Canthigaster rostrata Food, Co.', 4, 16, 860, 472, 1888);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 22:24:34', 'Peppers ', 'Deep sea anglerfish Food, Co.', 41, 27, 255, 297, 12177);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 22:36:34', 'Capsicum ', 'Triplefin blenny Food, Co.', 90, 9, 1031, 578, 52020);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 22:51:40', 'Shallots ', 'Bala shark Food, Co.', 55, 32, 53, 331, 18205);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 23:03:16', 'Carrot ', 'Oriental loach Food, Co.', 9, 10, 643, 460, 4140);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 23:29:13', 'Carrot ', 'Cepalin Food, Co.', 5, 10, 182, 460, 2300);
INSERT saleslog(dt, item, customer, qty, item_id, customer_id, unitprice, total)
VALUES ('2010-1-1 23:52:34', 'Courgette ', 'Snubnose parasitic eel Food, Co.', 5, 15, 913, 251, 1255);
GO
EXIT