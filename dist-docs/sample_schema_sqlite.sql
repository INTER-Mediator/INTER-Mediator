/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt

This schema file is for the sample of INTER-Mediator using SQLite3.

Example of making database file for UNIX (including OS X):

$ sudo mkdir /var/db/im
$ sudo sqlite3 /var/db/im/sample.sq3 < sample_schema_sqlite.sql
$ sudo chown _www /var/db/im
$ sudo chown _www /var/db/im/sample.sq3

- "sample_schema_sqlite.sql" is this schema file.
- "sample.sq3" is database file.
- The full path of the database file should be specified on each definiton file.
*/

/*  The schema for the "Sample_form" and "Sample_Auth" sample set.*/
CREATE TABLE person
(
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name     TEXT,
    address  TEXT,
    mail     TEXT,
    category INTEGER,
    checking INTEGER,
    location INTEGER,
    memo     TEXT
);

INSERT INTO person(id, name, address, mail)
VALUES (1, 'Masayuki Nii', 'Saitama, Japan', 'msyk@msyk.net');
INSERT INTO person(id, name, address, mail)
VALUES (2, 'Someone', 'Tokyo, Japan', 'msyk@msyk.net');
INSERT INTO person(id, name, address, mail)
VALUES (3, 'Anyone', 'Osaka, Japan', 'msyk@msyk.net');

CREATE TABLE contact
(
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    person_id   INTEGER,
    description TEXT,
    datetime    DATETIME,
    summary     TEXT,
    important   INTEGER,
    way         INTEGER default 4,
    kind        INTEGER
);
CREATE INDEX contact_person_id ON contact (person_id);

INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (1, '2009-12-01 15:23:00', 'Telephone', 4, 4);
INSERT INTO contact (person_id, datetime, summary, important, way, kind)
VALUES (1, '2009-12-02 15:23:00', 'Meeting', 1, 4, 7);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (1, '2009-12-03 15:23:00', 'Mail', 5, 8);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (2, '2009-12-04 15:23:00', 'Calling', 6, 12);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (2, '2009-12-01 15:23:00', 'Telephone', 4, 4);
INSERT INTO contact (person_id, datetime, summary, important, way, kind)
VALUES (3, '2009-12-02 15:23:00', 'Meeting', 1, 4, 7);
INSERT INTO contact (person_id, datetime, summary, way, kind)
VALUES (3, '2009-12-03 15:23:00', 'Mail', 5, 8);

CREATE TABLE contact_way
(
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT
);

INSERT INTO contact_way(id, name)
VALUES (4, 'Direct');
INSERT INTO contact_way(id, name)
VALUES (5, 'Indirect');
INSERT INTO contact_way(id, name)
VALUES (6, 'Others');

CREATE TABLE contact_kind
(
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT
);

INSERT INTO contact_kind(id, name)
VALUES (4, 'Talk');
INSERT INTO contact_kind(id, name)
VALUES (5, 'Meet');
INSERT INTO contact_kind(id, name)
VALUES (6, 'Calling');
INSERT INTO contact_kind(id, name)
VALUES (7, 'Meeting');
INSERT INTO contact_kind(id, name)
VALUES (8, 'Mail');
INSERT INTO contact_kind(id, name)
VALUES (9, 'Email');
INSERT INTO contact_kind(id, name)
VALUES (10, 'See on Web');
INSERT INTO contact_kind(id, name)
VALUES (11, 'See on Chat');
INSERT INTO contact_kind(id, name)
VALUES (12, 'Twitter');
INSERT INTO contact_kind(id, name)
VALUES (13, 'Conference');

CREATE TABLE cor_way_kind
(
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    way_id  INTEGER,
    kind_id INTEGER
);
CREATE INDEX cor_way_id ON cor_way_kind (way_id);
CREATE INDEX cor_kind_id ON cor_way_kind (way_id);

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

CREATE TABLE history
(
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    person_id   INTEGER,
    description TEXT,
    startdate   DATE,
    enddate     DATE,
    username    TEXT
);
CREATE INDEX history_person_id ON history (person_id);

INSERT INTO history(person_id, startdate, enddate, description)
VALUES (1, '2001-04-01', '2003-03-31', 'Hight School');
INSERT INTO history(person_id, startdate, enddate, description)
VALUES (1, '2003-04-01', '2007-03-31', 'University');

/* The schema for the "Sample_search" sample set.*/

CREATE TABLE postalcode
(
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    f3   TEXT,
    f7   TEXT,
    f8   TEXT,
    f9   TEXT,
    memo TEXT
);
CREATE INDEX postalcode_f3 ON postalcode (f3);
CREATE INDEX postalcode_f8 ON postalcode (f8);
/*
 The schema for the "Sample_products" sample set.

 The sample data for these table, invoice, item and products is another part of this file.
 Please scroll down to check it.
 */
CREATE TABLE invoice
(
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    issued    DATE,
    title     TEXT,
    authuser  TEXT,
    authgroup TEXT,
    authpriv  TEXT
);

CREATE TABLE item
(
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id        INTEGER,
    category_id       INTEGER,
    product_id        INTEGER,
    qty               INTEGER,
    product_unitprice FLOAT,
    product_name      TEXT,
    product_taxrate   FLOAT,
    user_id           INTEGER,
    group_id          INTEGER,
    priv_id           INTEGER
);

CREATE TABLE product
(
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id     INTEGER,
    unitprice       REAL,
    name            TEXT,
    photofile       TEXT,
    acknowledgement TEXT,
    ack_link        TEXT,
    memo            TEXT
);
/*
CREATE VIEW item_display AS
    SELECT item.id, item.invoice_id, item.product_id, item.category_id, product.name, item.qty,
        item.unitprice, product.unitprice as unitprice_master,
        IF(item.unitprice is null, qty * product.unitprice, qty * item.unitprice) AS amount FROM item,
        product WHERE item.product_id=product.id;
*/

/* The schema for the "Sample_Asset" sample set. */

DROP TABLE IF EXISTS asset;
CREATE TABLE asset
(
    asset_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR(20),
    category    VARCHAR(20),
    manifacture VARCHAR(20),
    productinfo VARCHAR(20),
    purchase    DATE,
    discard     DATE,
    memo        TEXT
);
CREATE INDEX asset_purchase ON asset (purchase);
CREATE INDEX asset_discard ON asset (discard);

DROP TABLE IF EXISTS rent;
CREATE TABLE rent
(
    rent_id  INTEGER PRIMARY KEY AUTOINCREMENT,
    asset_id INT,
    staff_id INT,
    rentdate DATE,
    backdate DATE,
    memo     TEXT
);
CREATE INDEX rent_rentdate ON rent (rentdate);
CREATE INDEX rent_backdate ON rent (backdate);
CREATE INDEX rent_asset_id ON rent (asset_id);
CREATE INDEX rent_staff_id ON rent (staff_id);

DROP TABLE IF EXISTS staff;
CREATE TABLE staff
(
    staff_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name     VARCHAR(20),
    section  VARCHAR(20),
    memo     TEXT
);

DROP TABLE IF EXISTS category;
CREATE TABLE category
(
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name        VARCHAR(20)
);

INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (11, '個人用', 'MacBook Air[1]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (12, '個人用', 'MacBook Air[2]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (13, '個人用', 'MacBook Air[3]', 'Apple', '2012/250GB/4GB', '2012-08-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (14, '個人用', 'VAIO type A[1]', 'ソニー', 'VGN-AR85S', '2008-06-12', '2012-02-02');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (15, '個人用', 'VAIO type A[2]', 'ソニー', 'VGN-AR85S', '2008-06-12', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (16, '共用', 'プロジェクタ', 'エプソン', 'EB-460T', '2010-11-23', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (17, '共用', 'ホワイトボード[1]', '不明', '不明', NULL, '2005-03-22');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (18, '共用', 'ホワイトボード[2]', '不明', '不明', NULL, '2005-03-22');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (19, '共用', '加湿器', 'シャープ', 'プラズマクラスター加湿器', '2011-12-02', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (20, '共用', '事務室エアコン', '', '', NULL, '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (21, '共用', '会議室エアコン', '', '', NULL, '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (22, '共用', '携帯電話ドコモ', '京セラ', 'P904i', '2010-04-04', '2012-03-03');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (23, '個人用', '携帯電話au', 'シャープ', 'SH001', '2012-03-03', '2012-10-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (24, '個人用', '携帯電話Softbank[1]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (25, '個人用', '携帯電話Softbank[2]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (26, '個人用', '携帯電話Softbank[3]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (27, '個人用', '携帯電話Softbank[4]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (28, '個人用', '携帯電話Softbank[5]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');
INSERT INTO asset (asset_id, category, name, manifacture, productinfo, purchase, discard)
VALUES (29, '個人用', '携帯電話Softbank[6]', 'Apple', 'iPhone 5', '2012-10-01', '1904-01-01');

INSERT INTO staff (staff_id, name, section)
VALUES (101, '田中次郎', '代表取締役社長');
INSERT INTO staff (staff_id, name, section)
VALUES (102, '山本三郎', '専務取締役');
INSERT INTO staff (staff_id, name, section)
VALUES (103, '北野六郎', '営業部長');
INSERT INTO staff (staff_id, name, section)
VALUES (104, '東原七海', '営業部');
INSERT INTO staff (staff_id, name, section)
VALUES (105, '内村久郎', '営業部');
INSERT INTO staff (staff_id, name, section)
VALUES (106, '菅沼健一郎', '開発部長');
INSERT INTO staff (staff_id, name, section)
VALUES (107, '西森裕太', '開発部');
INSERT INTO staff (staff_id, name, section)
VALUES (108, '野村顕昭', '開発部');
INSERT INTO staff (staff_id, name, section)
VALUES (109, '辻野均', '開発部');

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
VALUES (1, '個人用');
INSERT INTO category (category_id, name)
VALUES (2, '共用');

/* The schema for the "Sample_Auth" sample set. */

CREATE TABLE chat
(
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user      TEXT,
    groupname TEXT,
    postdt    DATETIME,
    message   TEXT
);

CREATE TABLE fileupload
(
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    f_id INTEGER,
    path TEXT
);

/* Observable */

CREATE TABLE registeredcontext
(
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    clientid     TEXT,
    entity       TEXT,
    conditions   TEXT,
    registereddt DATETIME
);

CREATE TABLE registeredpks
(
    context_id INTEGER,
    pk         INTEGER,
    PRIMARY KEY (context_id, pk),
    FOREIGN KEY (context_id) REFERENCES registeredcontext (id) ON DELETE CASCADE
);

CREATE TABLE authuser
(
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    username     TEXT,
    hashedpasswd TEXT,
    email        TEXT,
    realname     TEXT,
    address      TEXT,
    birthdate    TEXT,
    gender       TEXT,
    sub          TEXT,
    limitdt      DateTime
);

CREATE INDEX authuser_username
    ON authuser (username);
CREATE INDEX authuser_email
    ON authuser (email);
CREATE INDEX authuser_limitdt
    ON authuser (limitdt);

INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (1, 'user1', 'd83eefa0a9bd7190c94e7911688503737a99db0154455354', 'user1@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (2, 'user2', '5115aba773983066bcf4a8655ddac8525c1d3c6354455354', 'user2@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (3, 'user3', 'd1a7981108a73e9fbd570e23ecca87c2c5cb967554455354', 'user3@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (4, 'user4', '8c1b394577d0191417e8d962c5f6e3ca15068f8254455354', 'user4@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (5, 'user5', 'ee403ef2642f2e63dca12af72856620e6a24102d54455354', 'user5@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (6, 'mig2m', 'cd85a299c154c4714b23ce4b63618527289296ba6642c2685651ad8b9f20ce02285d7b34', 'mig2m@msyk.net');
INSERT INTO authuser(id, username, hashedpasswd, email)
VALUES (7, 'mig2', 'b7d863d29021fc96de261da6a5dfb6c4c28d3d43c75ad5ddddea4ec8716bdaf074675473', 'mig2@msyk.net');
/*
 The user1 has the password 'user1'. It's salted with the string 'TEXT'.
 All users have the password the same as user name. All are salted with 'TEXT'
 The following command lines are used to generate above hashed-hexed-password.

  $ echo -n 'user1TEST' | openssl sha1 -sha1
  d83eefa0a9bd7190c94e7911688503737a99db01
  echo -n 'TEST' | xxd -ps
  54455354
  - combine above two results:
d  d83eefa0a9bd7190c94e7911688503737a99db0154455354
*/
CREATE TABLE authgroup
(
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    groupname TEXT
);

INSERT INTO authgroup(id, groupname)
VALUES (1, 'group1');
INSERT INTO authgroup(id, groupname)
VALUES (2, 'group2');
INSERT INTO authgroup(id, groupname)
VALUES (3, 'group3');

CREATE TABLE authcor
(
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id       INTEGER,
    group_id      INTEGER,
    dest_group_id INTEGER,
    privname      TEXT
);

CREATE INDEX authcor_user_id
    ON authcor (user_id);
CREATE INDEX authcor_group_id
    ON authcor (group_id);
CREATE INDEX authcor_dest_group_id
    ON authcor (dest_group_id);

INSERT INTO authcor(user_id, dest_group_id)
VALUES (1, 1);
INSERT INTO authcor(user_id, dest_group_id)
VALUES (2, 1);
/* INSERT INTO authcor(user_id, dest_group_id)
VALUES (3, 1);*/
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
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER,
    clienthost TEXT,
    hash       TEXT,
    expired    DateTime
);

CREATE INDEX issuedhash_user_id
    ON issuedhash (user_id);
CREATE INDEX issuedhash_expired
    ON issuedhash (expired);
CREATE INDEX issuedhash_clienthost
    ON issuedhash (clienthost);
CREATE INDEX issuedhash_user_id_clienthost
    ON issuedhash (user_id, clienthost);

-- Mail Template
CREATE TABLE mailtemplate
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    to_field   TEXT,
    bcc_field  TEXT,
    cc_field   TEXT,
    from_field TEXT,
    subject    TEXT,
    body       TEXT
);

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (1, '@@Q2@@', '', '', 'msyk@msyk.net', 'ご意見承りました',
        'ご意見を投稿していただき、ありがとうございます。伺った内容は以下の通りです。よろしくお願いします。\n\nお名前：@@Q1@@\nメールアドレス：@@Q2@@\nご意見：@@Q3@@\n\n====\nINTER-Mediator本部事務局');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (2, '@@mail@@', 'msyk@msyk.net', 'nii@msyk.net', 'msyk@msyk.net', 'テストメール2',
        'テストメールです。@@name@@様宛で、送信先は@@mail@@です。');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (991, '@@email@@', 'msyk@msyk.net', 'nii@msyk.net', 'msyk@msyk.net', 'ユーザ登録の確認',
        '@@realname@@ 様（@@email@@）\n\nユーザ登録を受け付けました。1時間以内に、以下のリンクのサイトに接続してください。\n\n'
            || '接続後にアカウントを発行してご指定のメールアドレスに送付します。\n\n<< Path to the script >>/confirm.php?c=@@hash@@\n\n'
            || '___________________________________\ninfo@msyk.net - Masayuki Nii');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (992, '@@email@@', 'msyk@msyk.net', 'nii@msyk.net', 'msyk@msyk.net', 'ユーザ登録の完了',
        '@@realname@@ 様（@@email@@）\n\nユーザ登録が完了しました。こちらのページにログインできるようになりました。'
            || 'ログインページ：\n<< URL to any page >>\n\nユーザ名： @@username@@\n初期パスワード： @@initialPassword@@\n\n'
            || '※ 初期パスワードは極力早めに変更してください。\n'
            || '___________________________________\ninfo@msyk.net - Masayuki Nii');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (993, '@@email@@', 'msyk@msyk.net', 'nii@msyk.net', 'msyk@msyk.net', 'パスワードのリセットを受け付けました',
        'パスワードのリセットを受け付けました。\n\nメールアドレス：@@email@@\n\n'
            || '以下のリンクをクリックし、新しいパスワードをご入力ください。\n\n'
            || '<< Path to the script >>/resetpassword.html?c=@@hash@@\n\n'
            || '___________________________________\ninfo@msyk.net - Masayuki Nii');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (994, '@@email@@', 'msyk@msyk.net', 'nii@msyk.net', 'msyk@msyk.net', 'パスワードをリセットしました',
        '以下のアカウントのパスワードをリセットしました。\n\nアカウント（メールアドレス）：@@email@@\n\n'
            || '以下のリンクをクリックし、新しいパスワードでマイページにログインしてください。\n\n<< Path to any page >>\n\n'
            || '___________________________________\ninfo@msyk.net - Masayuki Nii');

INSERT INTO mailtemplate(id, to_field, bcc_field, cc_field, from_field, subject, body)
VALUES (995, '@@mail@@', 'msyk@msyk.net', null, 'msyk@msyk.net', '認証コードを送付します',
        'ユーザ名とパスワードによるログインが成功したので、メールの内容と照らし合わせての再度の認証を行います。\n\n'
            || 'メールアドレス：@@mail@@\n認証コード：@@code@@\n\n'
            || 'ログインを行った画面に入力可能なパネルが表示されています。上記の認証コードを入力してください。\n\n'
            || '___________________________________\ninfo@msyk.net - Masayuki Nii');

-- Storing Sent Mail
CREATE TABLE maillog
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    to_field   TEXT,
    bcc_field  TEXT,
    cc_field   TEXT,
    from_field TEXT,
    subject    TEXT,
    body       TEXT,
    errors     TEXT,
    dt         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign_id INT
);

/* Operation Log Store */
CREATE TABLE operationlog
(
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    dt            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user          VARCHAR(48),
    client_id_in  VARCHAR(48),
    client_id_out VARCHAR(48),
    require_auth  BIT(1),
    set_auth      BIT(1),
    client_ip     VARCHAR(60),
    path          VARCHAR(256),
    access        VARCHAR(20),
    context       VARCHAR(50),
    get_data      TEXT,
    post_data     TEXT,
    result        TEXT,
    condition0    VARCHAR(50),
    condition1    VARCHAR(50),
    condition2    VARCHAR(50),
    condition3    VARCHAR(50),
    condition4    VARCHAR(50),
    field0        TEXT,
    field1        TEXT,
    field2        TEXT,
    field3        TEXT,
    field4        TEXT,
    field5        TEXT,
    field6        TEXT,
    field7        TEXT,
    field8        TEXT,
    field9        TEXT,
    error         TEXT
);
/* In case of real deployment, some indices are required for quick operations. */

CREATE TABLE testtable
(
    id     INTEGER PRIMARY KEY AUTOINCREMENT,
    num1   INT          NOT NULL DEFAULT 0,
    num2   INT,
    num3   INT,
    dt1    DateTime     NOT NULL DEFAULT '2001-01-01 00:00:00',
    dt2    DateTime,
    dt3    DateTime,
    date1  Date         NOT NULL DEFAULT '2001-01-01',
    date2  Date,
    time1  Time         NOT NULL DEFAULT '00:00:00',
    time2  Time,
    ts1    Timestamp    NOT NULL DEFAULT '2001-01-01 00:00:00',
    ts2    Timestamp,
    vc1    VARCHAR(100) NOT NULL DEFAULT '',
    vc2    VARCHAR(100),
    vc3    VARCHAR(100),
    text1  TEXT         NOT NULL DEFAULT '',
    text2  TEXT,
    float1 FLOAT        NOT NULL DEFAULT 0,
    float2 FLOAT,
    double1 DOUBLE NOT NULL DEFAULT 0,
    double2 DOUBLE,
    bool1  BOOLEAN      NOT NULL DEFAULT FALSE,
    bool2  BOOLEAN /* SQLite doesn't have the 'BOOLEAN' type, it's just synonym of INTEGER.*/
);

/* Sample Data */
INSERT INTO product('name', id, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Apple', 1, 1, 340, 'mela-verde.png', 'Image: djcodrin / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=982');
INSERT INTO product('name', id, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Orange', 2, 1, 1540, 'orange_1.png', 'Image: Suat Eman / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=151');
INSERT INTO product('name', id, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Melon', 3, 1, 3840, 'galia-melon.png', 'Image: FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net');
INSERT INTO product('name', id, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Tomato', 4, 1, 2440, 'tomatos.png', 'Image: Tina Phillips / FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net/images/view_photog.php?photogid=503');
INSERT INTO product('name', id, category_id, unitprice, photofile, acknowledgement, ack_link)
VALUES ('Onion', 5, 1, 21340, 'onion2.png', 'Image: FreeDigitalPhotos.net',
        'http://www.freedigitalphotos.net');

INSERT INTO invoice(id, issued, title)
VALUES (1, '2010-02-04', 'Invoice');
INSERT INTO invoice(id, issued, title)
VALUES (2, '2010-02-06', 'Invoice');
INSERT INTO invoice(id, issued, title)
VALUES (3, '2010-02-14', 'Invoice');

INSERT INTO item(invoice_id, product_id, qty)
VALUES (1, 1, 12);
INSERT INTO item(invoice_id, product_id, qty, product_unitprice)
VALUES (1, 2, 12, 1340);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (1, 3, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (2, 4, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (2, 5, 12);
INSERT INTO item(invoice_id, product_id, qty)
VALUES (3, 3, 12);
/*
The following is the postalcode for Tokyo Pref at Jan 2009.
These are come from JP, and JP doesn't claim the copyright for postalcode data.
http://www.post.japanpost.jp/zipcode/download.html
*/

INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000000', '東京都', '千代田区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020072', '東京都', '千代田区', '飯田橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020082', '東京都', '千代田区', '一番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010032', '東京都', '千代田区', '岩本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010047', '東京都', '千代田区', '内神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000011', '東京都', '千代田区', '内幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000004', '東京都', '千代田区', '大手町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006890', '東京都', '千代田区', '大手町ＪＡビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006801', '東京都', '千代田区', '大手町ＪＡビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006802', '東京都', '千代田区', '大手町ＪＡビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006803', '東京都', '千代田区', '大手町ＪＡビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006804', '東京都', '千代田区', '大手町ＪＡビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006805', '東京都', '千代田区', '大手町ＪＡビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006806', '東京都', '千代田区', '大手町ＪＡビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006807', '東京都', '千代田区', '大手町ＪＡビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006808', '東京都', '千代田区', '大手町ＪＡビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006809', '東京都', '千代田区', '大手町ＪＡビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006810', '東京都', '千代田区', '大手町ＪＡビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006811', '東京都', '千代田区', '大手町ＪＡビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006812', '東京都', '千代田区', '大手町ＪＡビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006813', '東京都', '千代田区', '大手町ＪＡビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006814', '東京都', '千代田区', '大手町ＪＡビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006815', '東京都', '千代田区', '大手町ＪＡビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006816', '東京都', '千代田区', '大手町ＪＡビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006817', '東京都', '千代田区', '大手町ＪＡビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006818', '東京都', '千代田区', '大手町ＪＡビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006819', '東京都', '千代田区', '大手町ＪＡビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006820', '東京都', '千代田区', '大手町ＪＡビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006821', '東京都', '千代田区', '大手町ＪＡビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006822', '東京都', '千代田区', '大手町ＪＡビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006823', '東京都', '千代田区', '大手町ＪＡビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006824', '東京都', '千代田区', '大手町ＪＡビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006825', '東京都', '千代田区', '大手町ＪＡビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006826', '東京都', '千代田区', '大手町ＪＡビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006827', '東京都', '千代田区', '大手町ＪＡビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006828', '東京都', '千代田区', '大手町ＪＡビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006829', '東京都', '千代田区', '大手町ＪＡビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006830', '東京都', '千代田区', '大手町ＪＡビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006831', '東京都', '千代田区', '大手町ＪＡビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006832', '東京都', '千代田区', '大手町ＪＡビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006833', '東京都', '千代田区', '大手町ＪＡビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006834', '東京都', '千代田区', '大手町ＪＡビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006835', '東京都', '千代田区', '大手町ＪＡビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006836', '東京都', '千代田区', '大手町ＪＡビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006837', '東京都', '千代田区', '大手町ＪＡビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010044', '東京都', '千代田区', '鍛冶町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000013', '東京都', '千代田区', '霞が関（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006090', '東京都', '千代田区', '霞が関霞が関ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006001', '東京都', '千代田区', '霞が関霞が関ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006002', '東京都', '千代田区', '霞が関霞が関ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006003', '東京都', '千代田区', '霞が関霞が関ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006004', '東京都', '千代田区', '霞が関霞が関ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006005', '東京都', '千代田区', '霞が関霞が関ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006006', '東京都', '千代田区', '霞が関霞が関ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006007', '東京都', '千代田区', '霞が関霞が関ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006008', '東京都', '千代田区', '霞が関霞が関ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006009', '東京都', '千代田区', '霞が関霞が関ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006010', '東京都', '千代田区', '霞が関霞が関ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006011', '東京都', '千代田区', '霞が関霞が関ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006012', '東京都', '千代田区', '霞が関霞が関ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006013', '東京都', '千代田区', '霞が関霞が関ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006014', '東京都', '千代田区', '霞が関霞が関ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006015', '東京都', '千代田区', '霞が関霞が関ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006016', '東京都', '千代田区', '霞が関霞が関ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006017', '東京都', '千代田区', '霞が関霞が関ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006018', '東京都', '千代田区', '霞が関霞が関ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006019', '東京都', '千代田区', '霞が関霞が関ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006020', '東京都', '千代田区', '霞が関霞が関ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006021', '東京都', '千代田区', '霞が関霞が関ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006022', '東京都', '千代田区', '霞が関霞が関ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006023', '東京都', '千代田区', '霞が関霞が関ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006024', '東京都', '千代田区', '霞が関霞が関ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006025', '東京都', '千代田区', '霞が関霞が関ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006026', '東京都', '千代田区', '霞が関霞が関ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006027', '東京都', '千代田区', '霞が関霞が関ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006028', '東京都', '千代田区', '霞が関霞が関ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006029', '東京都', '千代田区', '霞が関霞が関ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006030', '東京都', '千代田区', '霞が関霞が関ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006031', '東京都', '千代田区', '霞が関霞が関ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006032', '東京都', '千代田区', '霞が関霞が関ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006033', '東京都', '千代田区', '霞が関霞が関ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006034', '東京都', '千代田区', '霞が関霞が関ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006035', '東京都', '千代田区', '霞が関霞が関ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006036', '東京都', '千代田区', '霞が関霞が関ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010029', '東京都', '千代田区', '神田相生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010063', '東京都', '千代田区', '神田淡路町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010024', '東京都', '千代田区', '神田和泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010033', '東京都', '千代田区', '神田岩本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010052', '東京都', '千代田区', '神田小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010045', '東京都', '千代田区', '神田鍛冶町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010036', '東京都', '千代田区', '神田北乗物町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010035', '東京都', '千代田区', '神田紺屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010026', '東京都', '千代田区', '神田佐久間河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010025', '東京都', '千代田区', '神田佐久間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010051', '東京都', '千代田区', '神田神保町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010041', '東京都', '千代田区', '神田須田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010062', '東京都', '千代田区', '神田駿河台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010046', '東京都', '千代田区', '神田多町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010048', '東京都', '千代田区', '神田司町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010043', '東京都', '千代田区', '神田富山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010054', '東京都', '千代田区', '神田錦町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010037', '東京都', '千代田区', '神田西福田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010022', '東京都', '千代田区', '神田練塀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010028', '東京都', '千代田区', '神田花岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010034', '東京都', '千代田区', '神田東紺屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010042', '東京都', '千代田区', '神田東松下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010027', '東京都', '千代田区', '神田平河町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010023', '東京都', '千代田区', '神田松永町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010038', '東京都', '千代田区', '神田美倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010053', '東京都', '千代田区', '神田美土代町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020094', '東京都', '千代田区', '紀尾井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020091', '東京都', '千代田区', '北の丸公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020074', '東京都', '千代田区', '九段南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020073', '東京都', '千代田区', '九段北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000002', '東京都', '千代田区', '皇居外苑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020083', '東京都', '千代田区', '麹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020076', '東京都', '千代田区', '五番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010064', '東京都', '千代田区', '猿楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020075', '東京都', '千代田区', '三番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010021', '東京都', '千代田区', '外神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000001', '東京都', '千代田区', '千代田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000014', '東京都', '千代田区', '永田町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006190', '東京都', '千代田区', '永田町山王パークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006101', '東京都', '千代田区', '永田町山王パークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006102', '東京都', '千代田区', '永田町山王パークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006103', '東京都', '千代田区', '永田町山王パークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006104', '東京都', '千代田区', '永田町山王パークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006105', '東京都', '千代田区', '永田町山王パークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006106', '東京都', '千代田区', '永田町山王パークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006107', '東京都', '千代田区', '永田町山王パークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006108', '東京都', '千代田区', '永田町山王パークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006109', '東京都', '千代田区', '永田町山王パークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006110', '東京都', '千代田区', '永田町山王パークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006111', '東京都', '千代田区', '永田町山王パークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006112', '東京都', '千代田区', '永田町山王パークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006113', '東京都', '千代田区', '永田町山王パークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006114', '東京都', '千代田区', '永田町山王パークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006115', '東京都', '千代田区', '永田町山王パークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006116', '東京都', '千代田区', '永田町山王パークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006117', '東京都', '千代田区', '永田町山王パークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006118', '東京都', '千代田区', '永田町山王パークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006119', '東京都', '千代田区', '永田町山王パークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006120', '東京都', '千代田区', '永田町山王パークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006121', '東京都', '千代田区', '永田町山王パークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006122', '東京都', '千代田区', '永田町山王パークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006123', '東京都', '千代田区', '永田町山王パークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006124', '東京都', '千代田区', '永田町山王パークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006125', '東京都', '千代田区', '永田町山王パークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006126', '東京都', '千代田区', '永田町山王パークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006127', '東京都', '千代田区', '永田町山王パークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006128', '東京都', '千代田区', '永田町山王パークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006129', '東京都', '千代田区', '永田町山王パークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006130', '東京都', '千代田区', '永田町山王パークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006131', '東京都', '千代田区', '永田町山王パークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006132', '東京都', '千代田区', '永田町山王パークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006133', '東京都', '千代田区', '永田町山王パークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006134', '東京都', '千代田区', '永田町山王パークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006135', '東京都', '千代田区', '永田町山王パークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006136', '東京都', '千代田区', '永田町山王パークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006137', '東京都', '千代田区', '永田町山王パークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006138', '東京都', '千代田区', '永田町山王パークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006139', '東京都', '千代田区', '永田町山王パークタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006140', '東京都', '千代田区', '永田町山王パークタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006141', '東京都', '千代田区', '永田町山王パークタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006142', '東京都', '千代田区', '永田町山王パークタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006143', '東京都', '千代田区', '永田町山王パークタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006144', '東京都', '千代田区', '永田町山王パークタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010065', '東京都', '千代田区', '西神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020084', '東京都', '千代田区', '二番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020092', '東京都', '千代田区', '隼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010031', '東京都', '千代田区', '東神田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000003', '東京都', '千代田区', '一ツ橋（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010003', '東京都', '千代田区', '一ツ橋（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000012', '東京都', '千代田区', '日比谷公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020093', '東京都', '千代田区', '平河町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020071', '東京都', '千代田区', '富士見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000005', '東京都', '千代田区', '丸の内（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006690', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006601', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006602', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006603', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006604', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006605', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006606', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006607', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006608', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006609', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006610', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006611', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006612', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006613', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006614', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006615', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006616', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006617', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006618', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006619', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006620', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006621', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006622', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006623', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006624', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006625', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006626', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006627', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006628', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006629', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006630', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006631', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006632', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006633', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006634', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006635', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006636', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006637', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006638', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006639', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006640', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006641', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006642', '東京都', '千代田区', '丸の内グラントウキョウサウスタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006790', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006701', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006702', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006703', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006704', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006705', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006706', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006707', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006708', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006709', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006710', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006711', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006712', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006713', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006714', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006715', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006716', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006717', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006718', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006719', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006720', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006721', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006722', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006723', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006724', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006725', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006726', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006727', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006728', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006729', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006730', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006731', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006732', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006733', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006734', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006735', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006736', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006737', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006738', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006739', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006740', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006741', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006742', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006743', '東京都', '千代田区', '丸の内グラントウキョウノースタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006590', '東京都', '千代田区', '丸の内新丸の内ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006501', '東京都', '千代田区', '丸の内新丸の内ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006502', '東京都', '千代田区', '丸の内新丸の内ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006503', '東京都', '千代田区', '丸の内新丸の内ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006504', '東京都', '千代田区', '丸の内新丸の内ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006505', '東京都', '千代田区', '丸の内新丸の内ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006506', '東京都', '千代田区', '丸の内新丸の内ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006507', '東京都', '千代田区', '丸の内新丸の内ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006508', '東京都', '千代田区', '丸の内新丸の内ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006509', '東京都', '千代田区', '丸の内新丸の内ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006510', '東京都', '千代田区', '丸の内新丸の内ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006511', '東京都', '千代田区', '丸の内新丸の内ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006512', '東京都', '千代田区', '丸の内新丸の内ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006513', '東京都', '千代田区', '丸の内新丸の内ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006514', '東京都', '千代田区', '丸の内新丸の内ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006515', '東京都', '千代田区', '丸の内新丸の内ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006516', '東京都', '千代田区', '丸の内新丸の内ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006517', '東京都', '千代田区', '丸の内新丸の内ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006518', '東京都', '千代田区', '丸の内新丸の内ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006519', '東京都', '千代田区', '丸の内新丸の内ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006520', '東京都', '千代田区', '丸の内新丸の内ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006521', '東京都', '千代田区', '丸の内新丸の内ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006522', '東京都', '千代田区', '丸の内新丸の内ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006523', '東京都', '千代田区', '丸の内新丸の内ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006524', '東京都', '千代田区', '丸の内新丸の内ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006525', '東京都', '千代田区', '丸の内新丸の内ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006526', '東京都', '千代田区', '丸の内新丸の内ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006527', '東京都', '千代田区', '丸の内新丸の内ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006528', '東京都', '千代田区', '丸の内新丸の内ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006529', '東京都', '千代田区', '丸の内新丸の内ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006530', '東京都', '千代田区', '丸の内新丸の内ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006531', '東京都', '千代田区', '丸の内新丸の内ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006532', '東京都', '千代田区', '丸の内新丸の内ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006533', '東京都', '千代田区', '丸の内新丸の内ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006534', '東京都', '千代田区', '丸の内新丸の内ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006535', '東京都', '千代田区', '丸の内新丸の内ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006536', '東京都', '千代田区', '丸の内新丸の内ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006537', '東京都', '千代田区', '丸の内新丸の内ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006538', '東京都', '千代田区', '丸の内新丸の内ビルディング（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006490', '東京都', '千代田区', '丸の内東京ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006401', '東京都', '千代田区', '丸の内東京ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006402', '東京都', '千代田区', '丸の内東京ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006403', '東京都', '千代田区', '丸の内東京ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006404', '東京都', '千代田区', '丸の内東京ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006405', '東京都', '千代田区', '丸の内東京ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006406', '東京都', '千代田区', '丸の内東京ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006407', '東京都', '千代田区', '丸の内東京ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006408', '東京都', '千代田区', '丸の内東京ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006409', '東京都', '千代田区', '丸の内東京ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006410', '東京都', '千代田区', '丸の内東京ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006411', '東京都', '千代田区', '丸の内東京ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006412', '東京都', '千代田区', '丸の内東京ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006413', '東京都', '千代田区', '丸の内東京ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006414', '東京都', '千代田区', '丸の内東京ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006415', '東京都', '千代田区', '丸の内東京ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006416', '東京都', '千代田区', '丸の内東京ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006417', '東京都', '千代田区', '丸の内東京ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006418', '東京都', '千代田区', '丸の内東京ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006419', '東京都', '千代田区', '丸の内東京ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006420', '東京都', '千代田区', '丸の内東京ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006421', '東京都', '千代田区', '丸の内東京ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006422', '東京都', '千代田区', '丸の内東京ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006423', '東京都', '千代田区', '丸の内東京ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006424', '東京都', '千代田区', '丸の内東京ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006425', '東京都', '千代田区', '丸の内東京ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006426', '東京都', '千代田区', '丸の内東京ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006427', '東京都', '千代田区', '丸の内東京ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006428', '東京都', '千代田区', '丸の内東京ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006429', '東京都', '千代田区', '丸の内東京ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006430', '東京都', '千代田区', '丸の内東京ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006431', '東京都', '千代田区', '丸の内東京ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006432', '東京都', '千代田区', '丸の内東京ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006433', '東京都', '千代田区', '丸の内東京ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006290', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006201', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006202', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006203', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006204', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006205', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006206', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006207', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006208', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006209', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006210', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006211', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006212', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006213', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006214', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006215', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006216', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006217', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006218', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006219', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006220', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006221', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006222', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006223', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006224', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006225', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006226', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006227', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006228', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006229', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006230', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006231', '東京都', '千代田区', '丸の内パシフィックセンチュリープレイス丸の内（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006990', '東京都', '千代田区', '丸の内丸の内パークビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006901', '東京都', '千代田区', '丸の内丸の内パークビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006902', '東京都', '千代田区', '丸の内丸の内パークビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006903', '東京都', '千代田区', '丸の内丸の内パークビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006904', '東京都', '千代田区', '丸の内丸の内パークビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006905', '東京都', '千代田区', '丸の内丸の内パークビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006906', '東京都', '千代田区', '丸の内丸の内パークビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006907', '東京都', '千代田区', '丸の内丸の内パークビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006908', '東京都', '千代田区', '丸の内丸の内パークビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006909', '東京都', '千代田区', '丸の内丸の内パークビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006910', '東京都', '千代田区', '丸の内丸の内パークビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006911', '東京都', '千代田区', '丸の内丸の内パークビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006912', '東京都', '千代田区', '丸の内丸の内パークビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006913', '東京都', '千代田区', '丸の内丸の内パークビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006914', '東京都', '千代田区', '丸の内丸の内パークビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006915', '東京都', '千代田区', '丸の内丸の内パークビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006916', '東京都', '千代田区', '丸の内丸の内パークビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006917', '東京都', '千代田区', '丸の内丸の内パークビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006918', '東京都', '千代田区', '丸の内丸の内パークビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006919', '東京都', '千代田区', '丸の内丸の内パークビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006920', '東京都', '千代田区', '丸の内丸の内パークビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006921', '東京都', '千代田区', '丸の内丸の内パークビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006922', '東京都', '千代田区', '丸の内丸の内パークビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006923', '東京都', '千代田区', '丸の内丸の内パークビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006924', '東京都', '千代田区', '丸の内丸の内パークビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006925', '東京都', '千代田区', '丸の内丸の内パークビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006926', '東京都', '千代田区', '丸の内丸の内パークビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006927', '東京都', '千代田区', '丸の内丸の内パークビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006928', '東京都', '千代田区', '丸の内丸の内パークビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006929', '東京都', '千代田区', '丸の内丸の内パークビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006930', '東京都', '千代田区', '丸の内丸の内パークビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006931', '東京都', '千代田区', '丸の内丸の内パークビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006932', '東京都', '千代田区', '丸の内丸の内パークビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006933', '東京都', '千代田区', '丸の内丸の内パークビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006934', '東京都', '千代田区', '丸の内丸の内パークビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006390', '東京都', '千代田区', '丸の内丸の内ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006301', '東京都', '千代田区', '丸の内丸の内ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006302', '東京都', '千代田区', '丸の内丸の内ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006303', '東京都', '千代田区', '丸の内丸の内ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006304', '東京都', '千代田区', '丸の内丸の内ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006305', '東京都', '千代田区', '丸の内丸の内ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006306', '東京都', '千代田区', '丸の内丸の内ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006307', '東京都', '千代田区', '丸の内丸の内ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006308', '東京都', '千代田区', '丸の内丸の内ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006309', '東京都', '千代田区', '丸の内丸の内ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006310', '東京都', '千代田区', '丸の内丸の内ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006311', '東京都', '千代田区', '丸の内丸の内ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006312', '東京都', '千代田区', '丸の内丸の内ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006313', '東京都', '千代田区', '丸の内丸の内ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006314', '東京都', '千代田区', '丸の内丸の内ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006315', '東京都', '千代田区', '丸の内丸の内ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006316', '東京都', '千代田区', '丸の内丸の内ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006317', '東京都', '千代田区', '丸の内丸の内ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006318', '東京都', '千代田区', '丸の内丸の内ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006319', '東京都', '千代田区', '丸の内丸の内ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006320', '東京都', '千代田区', '丸の内丸の内ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006321', '東京都', '千代田区', '丸の内丸の内ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006322', '東京都', '千代田区', '丸の内丸の内ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006323', '東京都', '千代田区', '丸の内丸の内ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006324', '東京都', '千代田区', '丸の内丸の内ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006325', '東京都', '千代田区', '丸の内丸の内ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006326', '東京都', '千代田区', '丸の内丸の内ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006327', '東京都', '千代田区', '丸の内丸の内ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006328', '東京都', '千代田区', '丸の内丸の内ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006329', '東京都', '千代田区', '丸の内丸の内ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006330', '東京都', '千代田区', '丸の内丸の内ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006331', '東京都', '千代田区', '丸の内丸の内ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006332', '東京都', '千代田区', '丸の内丸の内ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006333', '東京都', '千代田区', '丸の内丸の内ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006334', '東京都', '千代田区', '丸の内丸の内ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006335', '東京都', '千代田区', '丸の内丸の内ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006336', '東京都', '千代田区', '丸の内丸の内ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1006337', '東京都', '千代田区', '丸の内丸の内ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1010061', '東京都', '千代田区', '三崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000006', '東京都', '千代田区', '有楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020081', '東京都', '千代田区', '四番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1020085', '東京都', '千代田区', '六番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030000', '東京都', '中央区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040044', '東京都', '中央区', '明石町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040042', '東京都', '中央区', '入船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040054', '東京都', '中央区', '勝どき');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040031', '東京都', '中央区', '京橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040061', '東京都', '中央区', '銀座');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040033', '東京都', '中央区', '新川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040041', '東京都', '中央区', '新富');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040052', '東京都', '中央区', '月島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040045', '東京都', '中央区', '築地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040051', '東京都', '中央区', '佃');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040055', '東京都', '中央区', '豊海町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030027', '東京都', '中央区', '日本橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030011', '東京都', '中央区', '日本橋大伝馬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030014', '東京都', '中央区', '日本橋蛎殻町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030026', '東京都', '中央区', '日本橋兜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030025', '東京都', '中央区', '日本橋茅場町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030016', '東京都', '中央区', '日本橋小網町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030001', '東京都', '中央区', '日本橋小伝馬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030024', '東京都', '中央区', '日本橋小舟町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030006', '東京都', '中央区', '日本橋富沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030008', '東京都', '中央区', '日本橋中洲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030013', '東京都', '中央区', '日本橋人形町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030015', '東京都', '中央区', '日本橋箱崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030007', '東京都', '中央区', '日本橋浜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030002', '東京都', '中央区', '日本橋馬喰町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030005', '東京都', '中央区', '日本橋久松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030012', '東京都', '中央区', '日本橋堀留町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030021', '東京都', '中央区', '日本橋本石町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030023', '東京都', '中央区', '日本橋本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030022', '東京都', '中央区', '日本橋室町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030003', '東京都', '中央区', '日本橋横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040032', '東京都', '中央区', '八丁堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040046', '東京都', '中央区', '浜離宮庭園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040053', '東京都', '中央区', '晴海（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046090', '東京都', '中央区', '晴海オフィスタワーＸ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046001', '東京都', '中央区', '晴海オフィスタワーＸ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046002', '東京都', '中央区', '晴海オフィスタワーＸ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046003', '東京都', '中央区', '晴海オフィスタワーＸ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046004', '東京都', '中央区', '晴海オフィスタワーＸ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046005', '東京都', '中央区', '晴海オフィスタワーＸ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046006', '東京都', '中央区', '晴海オフィスタワーＸ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046007', '東京都', '中央区', '晴海オフィスタワーＸ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046008', '東京都', '中央区', '晴海オフィスタワーＸ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046009', '東京都', '中央区', '晴海オフィスタワーＸ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046010', '東京都', '中央区', '晴海オフィスタワーＸ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046011', '東京都', '中央区', '晴海オフィスタワーＸ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046012', '東京都', '中央区', '晴海オフィスタワーＸ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046013', '東京都', '中央区', '晴海オフィスタワーＸ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046014', '東京都', '中央区', '晴海オフィスタワーＸ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046015', '東京都', '中央区', '晴海オフィスタワーＸ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046016', '東京都', '中央区', '晴海オフィスタワーＸ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046017', '東京都', '中央区', '晴海オフィスタワーＸ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046018', '東京都', '中央区', '晴海オフィスタワーＸ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046019', '東京都', '中央区', '晴海オフィスタワーＸ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046020', '東京都', '中央区', '晴海オフィスタワーＸ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046021', '東京都', '中央区', '晴海オフィスタワーＸ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046022', '東京都', '中央区', '晴海オフィスタワーＸ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046023', '東京都', '中央区', '晴海オフィスタワーＸ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046024', '東京都', '中央区', '晴海オフィスタワーＸ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046025', '東京都', '中央区', '晴海オフィスタワーＸ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046026', '東京都', '中央区', '晴海オフィスタワーＸ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046027', '東京都', '中央区', '晴海オフィスタワーＸ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046028', '東京都', '中央区', '晴海オフィスタワーＸ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046029', '東京都', '中央区', '晴海オフィスタワーＸ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046030', '東京都', '中央区', '晴海オフィスタワーＸ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046031', '東京都', '中央区', '晴海オフィスタワーＸ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046032', '東京都', '中央区', '晴海オフィスタワーＸ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046033', '東京都', '中央区', '晴海オフィスタワーＸ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046034', '東京都', '中央区', '晴海オフィスタワーＸ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046035', '東京都', '中央区', '晴海オフィスタワーＸ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046036', '東京都', '中央区', '晴海オフィスタワーＸ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046037', '東京都', '中央区', '晴海オフィスタワーＸ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046038', '東京都', '中央区', '晴海オフィスタワーＸ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046039', '東京都', '中央区', '晴海オフィスタワーＸ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046040', '東京都', '中央区', '晴海オフィスタワーＸ（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046041', '東京都', '中央区', '晴海オフィスタワーＸ（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046042', '東京都', '中央区', '晴海オフィスタワーＸ（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046043', '東京都', '中央区', '晴海オフィスタワーＸ（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046044', '東京都', '中央区', '晴海オフィスタワーＸ（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046190', '東京都', '中央区', '晴海オフィスタワーＹ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046101', '東京都', '中央区', '晴海オフィスタワーＹ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046102', '東京都', '中央区', '晴海オフィスタワーＹ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046103', '東京都', '中央区', '晴海オフィスタワーＹ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046104', '東京都', '中央区', '晴海オフィスタワーＹ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046105', '東京都', '中央区', '晴海オフィスタワーＹ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046106', '東京都', '中央区', '晴海オフィスタワーＹ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046107', '東京都', '中央区', '晴海オフィスタワーＹ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046108', '東京都', '中央区', '晴海オフィスタワーＹ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046109', '東京都', '中央区', '晴海オフィスタワーＹ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046110', '東京都', '中央区', '晴海オフィスタワーＹ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046111', '東京都', '中央区', '晴海オフィスタワーＹ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046112', '東京都', '中央区', '晴海オフィスタワーＹ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046113', '東京都', '中央区', '晴海オフィスタワーＹ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046114', '東京都', '中央区', '晴海オフィスタワーＹ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046115', '東京都', '中央区', '晴海オフィスタワーＹ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046116', '東京都', '中央区', '晴海オフィスタワーＹ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046117', '東京都', '中央区', '晴海オフィスタワーＹ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046118', '東京都', '中央区', '晴海オフィスタワーＹ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046119', '東京都', '中央区', '晴海オフィスタワーＹ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046120', '東京都', '中央区', '晴海オフィスタワーＹ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046121', '東京都', '中央区', '晴海オフィスタワーＹ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046122', '東京都', '中央区', '晴海オフィスタワーＹ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046123', '東京都', '中央区', '晴海オフィスタワーＹ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046124', '東京都', '中央区', '晴海オフィスタワーＹ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046125', '東京都', '中央区', '晴海オフィスタワーＹ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046126', '東京都', '中央区', '晴海オフィスタワーＹ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046127', '東京都', '中央区', '晴海オフィスタワーＹ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046128', '東京都', '中央区', '晴海オフィスタワーＹ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046129', '東京都', '中央区', '晴海オフィスタワーＹ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046130', '東京都', '中央区', '晴海オフィスタワーＹ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046131', '東京都', '中央区', '晴海オフィスタワーＹ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046132', '東京都', '中央区', '晴海オフィスタワーＹ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046133', '東京都', '中央区', '晴海オフィスタワーＹ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046134', '東京都', '中央区', '晴海オフィスタワーＹ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046135', '東京都', '中央区', '晴海オフィスタワーＹ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046136', '東京都', '中央区', '晴海オフィスタワーＹ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046137', '東京都', '中央区', '晴海オフィスタワーＹ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046138', '東京都', '中央区', '晴海オフィスタワーＹ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046139', '東京都', '中央区', '晴海オフィスタワーＹ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046290', '東京都', '中央区', '晴海オフィスタワーＺ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046201', '東京都', '中央区', '晴海オフィスタワーＺ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046202', '東京都', '中央区', '晴海オフィスタワーＺ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046203', '東京都', '中央区', '晴海オフィスタワーＺ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046204', '東京都', '中央区', '晴海オフィスタワーＺ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046205', '東京都', '中央区', '晴海オフィスタワーＺ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046206', '東京都', '中央区', '晴海オフィスタワーＺ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046207', '東京都', '中央区', '晴海オフィスタワーＺ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046208', '東京都', '中央区', '晴海オフィスタワーＺ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046209', '東京都', '中央区', '晴海オフィスタワーＺ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046210', '東京都', '中央区', '晴海オフィスタワーＺ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046211', '東京都', '中央区', '晴海オフィスタワーＺ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046212', '東京都', '中央区', '晴海オフィスタワーＺ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046213', '東京都', '中央区', '晴海オフィスタワーＺ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046214', '東京都', '中央区', '晴海オフィスタワーＺ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046215', '東京都', '中央区', '晴海オフィスタワーＺ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046216', '東京都', '中央区', '晴海オフィスタワーＺ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046217', '東京都', '中央区', '晴海オフィスタワーＺ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046218', '東京都', '中央区', '晴海オフィスタワーＺ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046219', '東京都', '中央区', '晴海オフィスタワーＺ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046220', '東京都', '中央区', '晴海オフィスタワーＺ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046221', '東京都', '中央区', '晴海オフィスタワーＺ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046222', '東京都', '中央区', '晴海オフィスタワーＺ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046223', '東京都', '中央区', '晴海オフィスタワーＺ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046224', '東京都', '中央区', '晴海オフィスタワーＺ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046225', '東京都', '中央区', '晴海オフィスタワーＺ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046226', '東京都', '中央区', '晴海オフィスタワーＺ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046227', '東京都', '中央区', '晴海オフィスタワーＺ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046228', '東京都', '中央区', '晴海オフィスタワーＺ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046229', '東京都', '中央区', '晴海オフィスタワーＺ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046230', '東京都', '中央区', '晴海オフィスタワーＺ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046231', '東京都', '中央区', '晴海オフィスタワーＺ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046232', '東京都', '中央区', '晴海オフィスタワーＺ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1046233', '東京都', '中央区', '晴海オフィスタワーＺ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030004', '東京都', '中央区', '東日本橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040043', '東京都', '中央区', '湊');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1030028', '東京都', '中央区', '八重洲（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1040028', '東京都', '中央区', '八重洲（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050000', '東京都', '港区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070052', '東京都', '港区', '赤坂（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076090', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076001', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076002', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076003', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076004', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076005', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076006', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076007', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076008', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076009', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076010', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076011', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076012', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076013', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076014', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076015', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076016', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076017', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076018', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076019', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076020', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076021', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076022', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076023', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076024', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076025', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076026', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076027', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076028', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076029', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076030', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076031', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076032', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076033', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076034', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076035', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076036', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076037', '東京都', '港区', '赤坂赤坂アークヒルズ・アーク森ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076190', '東京都', '港区', '赤坂赤坂パークビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076101', '東京都', '港区', '赤坂赤坂パークビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076102', '東京都', '港区', '赤坂赤坂パークビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076103', '東京都', '港区', '赤坂赤坂パークビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076104', '東京都', '港区', '赤坂赤坂パークビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076105', '東京都', '港区', '赤坂赤坂パークビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076106', '東京都', '港区', '赤坂赤坂パークビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076107', '東京都', '港区', '赤坂赤坂パークビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076108', '東京都', '港区', '赤坂赤坂パークビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076109', '東京都', '港区', '赤坂赤坂パークビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076110', '東京都', '港区', '赤坂赤坂パークビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076111', '東京都', '港区', '赤坂赤坂パークビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076112', '東京都', '港区', '赤坂赤坂パークビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076113', '東京都', '港区', '赤坂赤坂パークビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076114', '東京都', '港区', '赤坂赤坂パークビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076115', '東京都', '港区', '赤坂赤坂パークビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076116', '東京都', '港区', '赤坂赤坂パークビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076117', '東京都', '港区', '赤坂赤坂パークビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076118', '東京都', '港区', '赤坂赤坂パークビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076119', '東京都', '港区', '赤坂赤坂パークビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076120', '東京都', '港区', '赤坂赤坂パークビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076121', '東京都', '港区', '赤坂赤坂パークビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076122', '東京都', '港区', '赤坂赤坂パークビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076123', '東京都', '港区', '赤坂赤坂パークビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076124', '東京都', '港区', '赤坂赤坂パークビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076125', '東京都', '港区', '赤坂赤坂パークビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076126', '東京都', '港区', '赤坂赤坂パークビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076127', '東京都', '港区', '赤坂赤坂パークビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076128', '東京都', '港区', '赤坂赤坂パークビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076129', '東京都', '港区', '赤坂赤坂パークビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076130', '東京都', '港区', '赤坂赤坂パークビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076390', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076301', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076302', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076303', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076304', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076305', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076306', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076307', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076308', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076309', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076310', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076311', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076312', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076313', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076314', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076315', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076316', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076317', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076318', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076319', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076320', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076321', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076322', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076323', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076324', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076325', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076326', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076327', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076328', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076329', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076330', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076331', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076332', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076333', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076334', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076335', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076336', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076337', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076338', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076339', '東京都', '港区', '赤坂赤坂Ｂｉｚタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076290', '東京都', '港区', '赤坂ミッドタウン・タワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076201', '東京都', '港区', '赤坂ミッドタウン・タワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076202', '東京都', '港区', '赤坂ミッドタウン・タワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076203', '東京都', '港区', '赤坂ミッドタウン・タワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076204', '東京都', '港区', '赤坂ミッドタウン・タワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076205', '東京都', '港区', '赤坂ミッドタウン・タワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076206', '東京都', '港区', '赤坂ミッドタウン・タワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076207', '東京都', '港区', '赤坂ミッドタウン・タワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076208', '東京都', '港区', '赤坂ミッドタウン・タワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076209', '東京都', '港区', '赤坂ミッドタウン・タワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076210', '東京都', '港区', '赤坂ミッドタウン・タワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076211', '東京都', '港区', '赤坂ミッドタウン・タワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076212', '東京都', '港区', '赤坂ミッドタウン・タワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076213', '東京都', '港区', '赤坂ミッドタウン・タワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076214', '東京都', '港区', '赤坂ミッドタウン・タワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076215', '東京都', '港区', '赤坂ミッドタウン・タワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076216', '東京都', '港区', '赤坂ミッドタウン・タワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076217', '東京都', '港区', '赤坂ミッドタウン・タワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076218', '東京都', '港区', '赤坂ミッドタウン・タワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076219', '東京都', '港区', '赤坂ミッドタウン・タワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076220', '東京都', '港区', '赤坂ミッドタウン・タワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076221', '東京都', '港区', '赤坂ミッドタウン・タワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076222', '東京都', '港区', '赤坂ミッドタウン・タワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076223', '東京都', '港区', '赤坂ミッドタウン・タワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076224', '東京都', '港区', '赤坂ミッドタウン・タワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076225', '東京都', '港区', '赤坂ミッドタウン・タワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076226', '東京都', '港区', '赤坂ミッドタウン・タワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076227', '東京都', '港区', '赤坂ミッドタウン・タワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076228', '東京都', '港区', '赤坂ミッドタウン・タワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076229', '東京都', '港区', '赤坂ミッドタウン・タワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076230', '東京都', '港区', '赤坂ミッドタウン・タワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076231', '東京都', '港区', '赤坂ミッドタウン・タワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076232', '東京都', '港区', '赤坂ミッドタウン・タワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076233', '東京都', '港区', '赤坂ミッドタウン・タワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076234', '東京都', '港区', '赤坂ミッドタウン・タワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076235', '東京都', '港区', '赤坂ミッドタウン・タワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076236', '東京都', '港区', '赤坂ミッドタウン・タワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076237', '東京都', '港区', '赤坂ミッドタウン・タワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076238', '東京都', '港区', '赤坂ミッドタウン・タワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076239', '東京都', '港区', '赤坂ミッドタウン・タワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076240', '東京都', '港区', '赤坂ミッドタウン・タワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076241', '東京都', '港区', '赤坂ミッドタウン・タワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076242', '東京都', '港区', '赤坂ミッドタウン・タワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076243', '東京都', '港区', '赤坂ミッドタウン・タワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076244', '東京都', '港区', '赤坂ミッドタウン・タワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1076245', '東京都', '港区', '赤坂ミッドタウン・タワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060045', '東京都', '港区', '麻布十番');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060041', '東京都', '港区', '麻布台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060043', '東京都', '港区', '麻布永坂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060042', '東京都', '港区', '麻布狸穴町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050002', '東京都', '港区', '愛宕（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056290', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056201', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056202', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056203', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056204', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056205', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056206', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056207', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056208', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056209', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056210', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056211', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056212', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056213', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056214', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056215', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056216', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056217', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056218', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056219', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056220', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056221', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056222', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056223', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056224', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056225', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056226', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056227', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056228', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056229', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056230', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056231', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056232', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056233', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056234', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056235', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056236', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056237', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056238', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056239', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056240', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056241', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056242', '東京都', '港区', '愛宕愛宕グリーンヒルズＭＯＲＩタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050022', '東京都', '港区', '海岸（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080022', '東京都', '港区', '海岸（３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070061', '東京都', '港区', '北青山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080075', '東京都', '港区', '港南（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086090', '東京都', '港区', '港南品川インターシティＡ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086001', '東京都', '港区', '港南品川インターシティＡ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086002', '東京都', '港区', '港南品川インターシティＡ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086003', '東京都', '港区', '港南品川インターシティＡ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086004', '東京都', '港区', '港南品川インターシティＡ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086005', '東京都', '港区', '港南品川インターシティＡ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086006', '東京都', '港区', '港南品川インターシティＡ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086007', '東京都', '港区', '港南品川インターシティＡ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086008', '東京都', '港区', '港南品川インターシティＡ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086009', '東京都', '港区', '港南品川インターシティＡ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086010', '東京都', '港区', '港南品川インターシティＡ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086011', '東京都', '港区', '港南品川インターシティＡ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086012', '東京都', '港区', '港南品川インターシティＡ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086013', '東京都', '港区', '港南品川インターシティＡ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086014', '東京都', '港区', '港南品川インターシティＡ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086015', '東京都', '港区', '港南品川インターシティＡ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086016', '東京都', '港区', '港南品川インターシティＡ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086017', '東京都', '港区', '港南品川インターシティＡ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086018', '東京都', '港区', '港南品川インターシティＡ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086019', '東京都', '港区', '港南品川インターシティＡ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086020', '東京都', '港区', '港南品川インターシティＡ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086021', '東京都', '港区', '港南品川インターシティＡ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086022', '東京都', '港区', '港南品川インターシティＡ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086023', '東京都', '港区', '港南品川インターシティＡ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086024', '東京都', '港区', '港南品川インターシティＡ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086025', '東京都', '港区', '港南品川インターシティＡ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086026', '東京都', '港区', '港南品川インターシティＡ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086027', '東京都', '港区', '港南品川インターシティＡ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086028', '東京都', '港区', '港南品川インターシティＡ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086029', '東京都', '港区', '港南品川インターシティＡ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086030', '東京都', '港区', '港南品川インターシティＡ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086031', '東京都', '港区', '港南品川インターシティＡ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086032', '東京都', '港区', '港南品川インターシティＡ棟（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086190', '東京都', '港区', '港南品川インターシティＢ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086101', '東京都', '港区', '港南品川インターシティＢ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086102', '東京都', '港区', '港南品川インターシティＢ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086103', '東京都', '港区', '港南品川インターシティＢ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086104', '東京都', '港区', '港南品川インターシティＢ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086105', '東京都', '港区', '港南品川インターシティＢ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086106', '東京都', '港区', '港南品川インターシティＢ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086107', '東京都', '港区', '港南品川インターシティＢ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086108', '東京都', '港区', '港南品川インターシティＢ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086109', '東京都', '港区', '港南品川インターシティＢ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086110', '東京都', '港区', '港南品川インターシティＢ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086111', '東京都', '港区', '港南品川インターシティＢ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086112', '東京都', '港区', '港南品川インターシティＢ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086113', '東京都', '港区', '港南品川インターシティＢ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086114', '東京都', '港区', '港南品川インターシティＢ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086115', '東京都', '港区', '港南品川インターシティＢ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086116', '東京都', '港区', '港南品川インターシティＢ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086117', '東京都', '港区', '港南品川インターシティＢ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086118', '東京都', '港区', '港南品川インターシティＢ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086119', '東京都', '港区', '港南品川インターシティＢ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086120', '東京都', '港区', '港南品川インターシティＢ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086121', '東京都', '港区', '港南品川インターシティＢ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086122', '東京都', '港区', '港南品川インターシティＢ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086123', '東京都', '港区', '港南品川インターシティＢ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086124', '東京都', '港区', '港南品川インターシティＢ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086125', '東京都', '港区', '港南品川インターシティＢ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086126', '東京都', '港区', '港南品川インターシティＢ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086127', '東京都', '港区', '港南品川インターシティＢ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086128', '東京都', '港区', '港南品川インターシティＢ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086129', '東京都', '港区', '港南品川インターシティＢ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086130', '東京都', '港区', '港南品川インターシティＢ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086131', '東京都', '港区', '港南品川インターシティＢ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086290', '東京都', '港区', '港南品川インターシティＣ棟（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086201', '東京都', '港区', '港南品川インターシティＣ棟（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086202', '東京都', '港区', '港南品川インターシティＣ棟（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086203', '東京都', '港区', '港南品川インターシティＣ棟（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086204', '東京都', '港区', '港南品川インターシティＣ棟（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086205', '東京都', '港区', '港南品川インターシティＣ棟（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086206', '東京都', '港区', '港南品川インターシティＣ棟（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086207', '東京都', '港区', '港南品川インターシティＣ棟（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086208', '東京都', '港区', '港南品川インターシティＣ棟（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086209', '東京都', '港区', '港南品川インターシティＣ棟（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086210', '東京都', '港区', '港南品川インターシティＣ棟（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086211', '東京都', '港区', '港南品川インターシティＣ棟（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086212', '東京都', '港区', '港南品川インターシティＣ棟（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086213', '東京都', '港区', '港南品川インターシティＣ棟（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086214', '東京都', '港区', '港南品川インターシティＣ棟（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086215', '東京都', '港区', '港南品川インターシティＣ棟（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086216', '東京都', '港区', '港南品川インターシティＣ棟（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086217', '東京都', '港区', '港南品川インターシティＣ棟（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086218', '東京都', '港区', '港南品川インターシティＣ棟（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086219', '東京都', '港区', '港南品川インターシティＣ棟（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086220', '東京都', '港区', '港南品川インターシティＣ棟（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086221', '東京都', '港区', '港南品川インターシティＣ棟（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086222', '東京都', '港区', '港南品川インターシティＣ棟（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086223', '東京都', '港区', '港南品川インターシティＣ棟（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086224', '東京都', '港区', '港南品川インターシティＣ棟（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086225', '東京都', '港区', '港南品川インターシティＣ棟（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086226', '東京都', '港区', '港南品川インターシティＣ棟（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086227', '東京都', '港区', '港南品川インターシティＣ棟（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086228', '東京都', '港区', '港南品川インターシティＣ棟（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086229', '東京都', '港区', '港南品川インターシティＣ棟（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086230', '東京都', '港区', '港南品川インターシティＣ棟（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086231', '東京都', '港区', '港南品川インターシティＣ棟（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050014', '東京都', '港区', '芝（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080014', '東京都', '港区', '芝（４、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050023', '東京都', '港区', '芝浦（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080023', '東京都', '港区', '芝浦（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050011', '東京都', '港区', '芝公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050012', '東京都', '港区', '芝大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080072', '東京都', '港区', '白金');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080071', '東京都', '港区', '白金台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050004', '東京都', '港区', '新橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350091', '東京都', '港区', '台場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080074', '東京都', '港区', '高輪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050001', '東京都', '港区', '虎ノ門（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056090', '東京都', '港区', '虎ノ門城山トラストタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056001', '東京都', '港区', '虎ノ門城山トラストタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056002', '東京都', '港区', '虎ノ門城山トラストタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056003', '東京都', '港区', '虎ノ門城山トラストタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056004', '東京都', '港区', '虎ノ門城山トラストタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056005', '東京都', '港区', '虎ノ門城山トラストタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056006', '東京都', '港区', '虎ノ門城山トラストタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056007', '東京都', '港区', '虎ノ門城山トラストタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056008', '東京都', '港区', '虎ノ門城山トラストタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056009', '東京都', '港区', '虎ノ門城山トラストタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056010', '東京都', '港区', '虎ノ門城山トラストタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056011', '東京都', '港区', '虎ノ門城山トラストタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056012', '東京都', '港区', '虎ノ門城山トラストタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056013', '東京都', '港区', '虎ノ門城山トラストタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056014', '東京都', '港区', '虎ノ門城山トラストタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056015', '東京都', '港区', '虎ノ門城山トラストタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056016', '東京都', '港区', '虎ノ門城山トラストタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056017', '東京都', '港区', '虎ノ門城山トラストタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056018', '東京都', '港区', '虎ノ門城山トラストタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056019', '東京都', '港区', '虎ノ門城山トラストタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056020', '東京都', '港区', '虎ノ門城山トラストタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056021', '東京都', '港区', '虎ノ門城山トラストタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056022', '東京都', '港区', '虎ノ門城山トラストタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056023', '東京都', '港区', '虎ノ門城山トラストタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056024', '東京都', '港区', '虎ノ門城山トラストタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056025', '東京都', '港区', '虎ノ門城山トラストタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056026', '東京都', '港区', '虎ノ門城山トラストタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056027', '東京都', '港区', '虎ノ門城山トラストタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056028', '東京都', '港区', '虎ノ門城山トラストタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056029', '東京都', '港区', '虎ノ門城山トラストタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056030', '東京都', '港区', '虎ノ門城山トラストタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056031', '東京都', '港区', '虎ノ門城山トラストタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056032', '東京都', '港区', '虎ノ門城山トラストタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056033', '東京都', '港区', '虎ノ門城山トラストタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056034', '東京都', '港区', '虎ノ門城山トラストタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056035', '東京都', '港区', '虎ノ門城山トラストタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056036', '東京都', '港区', '虎ノ門城山トラストタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056037', '東京都', '港区', '虎ノ門城山トラストタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060031', '東京都', '港区', '西麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050003', '東京都', '港区', '西新橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050013', '東京都', '港区', '浜松町（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056190', '東京都', '港区', '浜松町世界貿易センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056101', '東京都', '港区', '浜松町世界貿易センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056102', '東京都', '港区', '浜松町世界貿易センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056103', '東京都', '港区', '浜松町世界貿易センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056104', '東京都', '港区', '浜松町世界貿易センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056105', '東京都', '港区', '浜松町世界貿易センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056106', '東京都', '港区', '浜松町世界貿易センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056107', '東京都', '港区', '浜松町世界貿易センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056108', '東京都', '港区', '浜松町世界貿易センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056109', '東京都', '港区', '浜松町世界貿易センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056110', '東京都', '港区', '浜松町世界貿易センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056111', '東京都', '港区', '浜松町世界貿易センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056112', '東京都', '港区', '浜松町世界貿易センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056113', '東京都', '港区', '浜松町世界貿易センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056114', '東京都', '港区', '浜松町世界貿易センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056115', '東京都', '港区', '浜松町世界貿易センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056116', '東京都', '港区', '浜松町世界貿易センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056117', '東京都', '港区', '浜松町世界貿易センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056118', '東京都', '港区', '浜松町世界貿易センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056119', '東京都', '港区', '浜松町世界貿易センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056120', '東京都', '港区', '浜松町世界貿易センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056121', '東京都', '港区', '浜松町世界貿易センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056122', '東京都', '港区', '浜松町世界貿易センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056123', '東京都', '港区', '浜松町世界貿易センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056124', '東京都', '港区', '浜松町世界貿易センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056125', '東京都', '港区', '浜松町世界貿易センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056126', '東京都', '港区', '浜松町世界貿易センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056127', '東京都', '港区', '浜松町世界貿易センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056128', '東京都', '港区', '浜松町世界貿易センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056129', '東京都', '港区', '浜松町世界貿易センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056130', '東京都', '港区', '浜松町世界貿易センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056131', '東京都', '港区', '浜松町世界貿易センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056132', '東京都', '港区', '浜松町世界貿易センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056133', '東京都', '港区', '浜松町世界貿易センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056134', '東京都', '港区', '浜松町世界貿易センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056135', '東京都', '港区', '浜松町世界貿易センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056136', '東京都', '港区', '浜松町世界貿易センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056137', '東京都', '港区', '浜松町世界貿易センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056138', '東京都', '港区', '浜松町世界貿易センタービル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056139', '東京都', '港区', '浜松町世界貿易センタービル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1056140', '東京都', '港区', '浜松町世界貿易センタービル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060044', '東京都', '港区', '東麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1050021', '東京都', '港区', '東新橋（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057190', '東京都', '港区', '東新橋汐留シティセンター（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057101', '東京都', '港区', '東新橋汐留シティセンター（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057102', '東京都', '港区', '東新橋汐留シティセンター（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057103', '東京都', '港区', '東新橋汐留シティセンター（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057104', '東京都', '港区', '東新橋汐留シティセンター（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057105', '東京都', '港区', '東新橋汐留シティセンター（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057106', '東京都', '港区', '東新橋汐留シティセンター（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057107', '東京都', '港区', '東新橋汐留シティセンター（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057108', '東京都', '港区', '東新橋汐留シティセンター（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057109', '東京都', '港区', '東新橋汐留シティセンター（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057110', '東京都', '港区', '東新橋汐留シティセンター（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057111', '東京都', '港区', '東新橋汐留シティセンター（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057112', '東京都', '港区', '東新橋汐留シティセンター（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057113', '東京都', '港区', '東新橋汐留シティセンター（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057114', '東京都', '港区', '東新橋汐留シティセンター（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057115', '東京都', '港区', '東新橋汐留シティセンター（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057116', '東京都', '港区', '東新橋汐留シティセンター（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057117', '東京都', '港区', '東新橋汐留シティセンター（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057118', '東京都', '港区', '東新橋汐留シティセンター（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057119', '東京都', '港区', '東新橋汐留シティセンター（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057120', '東京都', '港区', '東新橋汐留シティセンター（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057121', '東京都', '港区', '東新橋汐留シティセンター（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057122', '東京都', '港区', '東新橋汐留シティセンター（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057123', '東京都', '港区', '東新橋汐留シティセンター（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057124', '東京都', '港区', '東新橋汐留シティセンター（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057125', '東京都', '港区', '東新橋汐留シティセンター（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057126', '東京都', '港区', '東新橋汐留シティセンター（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057127', '東京都', '港区', '東新橋汐留シティセンター（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057128', '東京都', '港区', '東新橋汐留シティセンター（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057129', '東京都', '港区', '東新橋汐留シティセンター（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057130', '東京都', '港区', '東新橋汐留シティセンター（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057131', '東京都', '港区', '東新橋汐留シティセンター（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057132', '東京都', '港区', '東新橋汐留シティセンター（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057133', '東京都', '港区', '東新橋汐留シティセンター（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057134', '東京都', '港区', '東新橋汐留シティセンター（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057135', '東京都', '港区', '東新橋汐留シティセンター（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057136', '東京都', '港区', '東新橋汐留シティセンター（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057137', '東京都', '港区', '東新橋汐留シティセンター（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057138', '東京都', '港区', '東新橋汐留シティセンター（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057139', '東京都', '港区', '東新橋汐留シティセンター（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057140', '東京都', '港区', '東新橋汐留シティセンター（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057141', '東京都', '港区', '東新橋汐留シティセンター（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057142', '東京都', '港区', '東新橋汐留シティセンター（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057143', '東京都', '港区', '東新橋汐留シティセンター（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057290', '東京都', '港区', '東新橋汐留メディアタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057201', '東京都', '港区', '東新橋汐留メディアタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057202', '東京都', '港区', '東新橋汐留メディアタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057203', '東京都', '港区', '東新橋汐留メディアタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057204', '東京都', '港区', '東新橋汐留メディアタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057205', '東京都', '港区', '東新橋汐留メディアタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057206', '東京都', '港区', '東新橋汐留メディアタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057207', '東京都', '港区', '東新橋汐留メディアタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057208', '東京都', '港区', '東新橋汐留メディアタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057209', '東京都', '港区', '東新橋汐留メディアタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057210', '東京都', '港区', '東新橋汐留メディアタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057211', '東京都', '港区', '東新橋汐留メディアタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057212', '東京都', '港区', '東新橋汐留メディアタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057213', '東京都', '港区', '東新橋汐留メディアタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057214', '東京都', '港区', '東新橋汐留メディアタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057215', '東京都', '港区', '東新橋汐留メディアタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057216', '東京都', '港区', '東新橋汐留メディアタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057217', '東京都', '港区', '東新橋汐留メディアタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057218', '東京都', '港区', '東新橋汐留メディアタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057219', '東京都', '港区', '東新橋汐留メディアタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057220', '東京都', '港区', '東新橋汐留メディアタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057221', '東京都', '港区', '東新橋汐留メディアタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057222', '東京都', '港区', '東新橋汐留メディアタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057223', '東京都', '港区', '東新橋汐留メディアタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057224', '東京都', '港区', '東新橋汐留メディアタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057225', '東京都', '港区', '東新橋汐留メディアタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057226', '東京都', '港区', '東新橋汐留メディアタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057227', '東京都', '港区', '東新橋汐留メディアタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057228', '東京都', '港区', '東新橋汐留メディアタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057229', '東京都', '港区', '東新橋汐留メディアタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057230', '東京都', '港区', '東新橋汐留メディアタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057231', '東京都', '港区', '東新橋汐留メディアタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057232', '東京都', '港区', '東新橋汐留メディアタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057233', '東京都', '港区', '東新橋汐留メディアタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057234', '東京都', '港区', '東新橋汐留メディアタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057090', '東京都', '港区', '東新橋電通本社ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057001', '東京都', '港区', '東新橋電通本社ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057002', '東京都', '港区', '東新橋電通本社ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057003', '東京都', '港区', '東新橋電通本社ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057004', '東京都', '港区', '東新橋電通本社ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057005', '東京都', '港区', '東新橋電通本社ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057006', '東京都', '港区', '東新橋電通本社ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057007', '東京都', '港区', '東新橋電通本社ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057008', '東京都', '港区', '東新橋電通本社ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057009', '東京都', '港区', '東新橋電通本社ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057010', '東京都', '港区', '東新橋電通本社ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057011', '東京都', '港区', '東新橋電通本社ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057012', '東京都', '港区', '東新橋電通本社ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057013', '東京都', '港区', '東新橋電通本社ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057014', '東京都', '港区', '東新橋電通本社ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057015', '東京都', '港区', '東新橋電通本社ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057016', '東京都', '港区', '東新橋電通本社ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057017', '東京都', '港区', '東新橋電通本社ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057018', '東京都', '港区', '東新橋電通本社ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057019', '東京都', '港区', '東新橋電通本社ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057020', '東京都', '港区', '東新橋電通本社ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057021', '東京都', '港区', '東新橋電通本社ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057022', '東京都', '港区', '東新橋電通本社ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057023', '東京都', '港区', '東新橋電通本社ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057024', '東京都', '港区', '東新橋電通本社ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057025', '東京都', '港区', '東新橋電通本社ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057026', '東京都', '港区', '東新橋電通本社ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057027', '東京都', '港区', '東新橋電通本社ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057028', '東京都', '港区', '東新橋電通本社ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057029', '東京都', '港区', '東新橋電通本社ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057030', '東京都', '港区', '東新橋電通本社ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057031', '東京都', '港区', '東新橋電通本社ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057032', '東京都', '港区', '東新橋電通本社ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057033', '東京都', '港区', '東新橋電通本社ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057034', '東京都', '港区', '東新橋電通本社ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057035', '東京都', '港区', '東新橋電通本社ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057036', '東京都', '港区', '東新橋電通本社ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057037', '東京都', '港区', '東新橋電通本社ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057038', '東京都', '港区', '東新橋電通本社ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057039', '東京都', '港区', '東新橋電通本社ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057040', '東京都', '港区', '東新橋電通本社ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057041', '東京都', '港区', '東新橋電通本社ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057042', '東京都', '港区', '東新橋電通本社ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057043', '東京都', '港区', '東新橋電通本社ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057044', '東京都', '港区', '東新橋電通本社ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057045', '東京都', '港区', '東新橋電通本社ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057046', '東京都', '港区', '東新橋電通本社ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057047', '東京都', '港区', '東新橋電通本社ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057390', '東京都', '港区', '東新橋東京汐留ビルディング（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057301', '東京都', '港区', '東新橋東京汐留ビルディング（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057302', '東京都', '港区', '東新橋東京汐留ビルディング（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057303', '東京都', '港区', '東新橋東京汐留ビルディング（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057304', '東京都', '港区', '東新橋東京汐留ビルディング（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057305', '東京都', '港区', '東新橋東京汐留ビルディング（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057306', '東京都', '港区', '東新橋東京汐留ビルディング（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057307', '東京都', '港区', '東新橋東京汐留ビルディング（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057308', '東京都', '港区', '東新橋東京汐留ビルディング（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057309', '東京都', '港区', '東新橋東京汐留ビルディング（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057310', '東京都', '港区', '東新橋東京汐留ビルディング（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057311', '東京都', '港区', '東新橋東京汐留ビルディング（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057312', '東京都', '港区', '東新橋東京汐留ビルディング（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057313', '東京都', '港区', '東新橋東京汐留ビルディング（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057314', '東京都', '港区', '東新橋東京汐留ビルディング（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057315', '東京都', '港区', '東新橋東京汐留ビルディング（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057316', '東京都', '港区', '東新橋東京汐留ビルディング（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057317', '東京都', '港区', '東新橋東京汐留ビルディング（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057318', '東京都', '港区', '東新橋東京汐留ビルディング（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057319', '東京都', '港区', '東新橋東京汐留ビルディング（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057320', '東京都', '港区', '東新橋東京汐留ビルディング（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057321', '東京都', '港区', '東新橋東京汐留ビルディング（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057322', '東京都', '港区', '東新橋東京汐留ビルディング（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057323', '東京都', '港区', '東新橋東京汐留ビルディング（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057324', '東京都', '港区', '東新橋東京汐留ビルディング（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057325', '東京都', '港区', '東新橋東京汐留ビルディング（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057326', '東京都', '港区', '東新橋東京汐留ビルディング（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057327', '東京都', '港区', '東新橋東京汐留ビルディング（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057328', '東京都', '港区', '東新橋東京汐留ビルディング（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057329', '東京都', '港区', '東新橋東京汐留ビルディング（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057330', '東京都', '港区', '東新橋東京汐留ビルディング（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057331', '東京都', '港区', '東新橋東京汐留ビルディング（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057332', '東京都', '港区', '東新橋東京汐留ビルディング（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057333', '東京都', '港区', '東新橋東京汐留ビルディング（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057334', '東京都', '港区', '東新橋東京汐留ビルディング（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057335', '東京都', '港区', '東新橋東京汐留ビルディング（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057336', '東京都', '港区', '東新橋東京汐留ビルディング（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057337', '東京都', '港区', '東新橋東京汐留ビルディング（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057490', '東京都', '港区', '東新橋日本テレビタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057401', '東京都', '港区', '東新橋日本テレビタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057402', '東京都', '港区', '東新橋日本テレビタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057403', '東京都', '港区', '東新橋日本テレビタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057404', '東京都', '港区', '東新橋日本テレビタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057405', '東京都', '港区', '東新橋日本テレビタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057406', '東京都', '港区', '東新橋日本テレビタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057407', '東京都', '港区', '東新橋日本テレビタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057408', '東京都', '港区', '東新橋日本テレビタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057409', '東京都', '港区', '東新橋日本テレビタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057410', '東京都', '港区', '東新橋日本テレビタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057411', '東京都', '港区', '東新橋日本テレビタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057412', '東京都', '港区', '東新橋日本テレビタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057413', '東京都', '港区', '東新橋日本テレビタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057414', '東京都', '港区', '東新橋日本テレビタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057415', '東京都', '港区', '東新橋日本テレビタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057416', '東京都', '港区', '東新橋日本テレビタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057417', '東京都', '港区', '東新橋日本テレビタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057418', '東京都', '港区', '東新橋日本テレビタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057419', '東京都', '港区', '東新橋日本テレビタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057420', '東京都', '港区', '東新橋日本テレビタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057421', '東京都', '港区', '東新橋日本テレビタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057422', '東京都', '港区', '東新橋日本テレビタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057423', '東京都', '港区', '東新橋日本テレビタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057424', '東京都', '港区', '東新橋日本テレビタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057425', '東京都', '港区', '東新橋日本テレビタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057426', '東京都', '港区', '東新橋日本テレビタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057427', '東京都', '港区', '東新橋日本テレビタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057428', '東京都', '港区', '東新橋日本テレビタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057429', '東京都', '港区', '東新橋日本テレビタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057430', '東京都', '港区', '東新橋日本テレビタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057431', '東京都', '港区', '東新橋日本テレビタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1057432', '東京都', '港区', '東新橋日本テレビタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1080073', '東京都', '港区', '三田（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086390', '東京都', '港区', '三田住友不動産三田ツインビル西館（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086301', '東京都', '港区', '三田住友不動産三田ツインビル西館（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086302', '東京都', '港区', '三田住友不動産三田ツインビル西館（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086303', '東京都', '港区', '三田住友不動産三田ツインビル西館（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086304', '東京都', '港区', '三田住友不動産三田ツインビル西館（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086305', '東京都', '港区', '三田住友不動産三田ツインビル西館（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086306', '東京都', '港区', '三田住友不動産三田ツインビル西館（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086307', '東京都', '港区', '三田住友不動産三田ツインビル西館（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086308', '東京都', '港区', '三田住友不動産三田ツインビル西館（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086309', '東京都', '港区', '三田住友不動産三田ツインビル西館（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086310', '東京都', '港区', '三田住友不動産三田ツインビル西館（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086311', '東京都', '港区', '三田住友不動産三田ツインビル西館（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086312', '東京都', '港区', '三田住友不動産三田ツインビル西館（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086313', '東京都', '港区', '三田住友不動産三田ツインビル西館（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086314', '東京都', '港区', '三田住友不動産三田ツインビル西館（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086315', '東京都', '港区', '三田住友不動産三田ツインビル西館（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086316', '東京都', '港区', '三田住友不動産三田ツインビル西館（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086317', '東京都', '港区', '三田住友不動産三田ツインビル西館（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086318', '東京都', '港区', '三田住友不動産三田ツインビル西館（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086319', '東京都', '港区', '三田住友不動産三田ツインビル西館（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086320', '東京都', '港区', '三田住友不動産三田ツインビル西館（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086321', '東京都', '港区', '三田住友不動産三田ツインビル西館（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086322', '東京都', '港区', '三田住友不動産三田ツインビル西館（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086323', '東京都', '港区', '三田住友不動産三田ツインビル西館（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086324', '東京都', '港区', '三田住友不動産三田ツインビル西館（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086325', '東京都', '港区', '三田住友不動産三田ツインビル西館（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086326', '東京都', '港区', '三田住友不動産三田ツインビル西館（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086327', '東京都', '港区', '三田住友不動産三田ツインビル西館（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086328', '東京都', '港区', '三田住友不動産三田ツインビル西館（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086329', '東京都', '港区', '三田住友不動産三田ツインビル西館（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1086330', '東京都', '港区', '三田住友不動産三田ツインビル西館（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070062', '東京都', '港区', '南青山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060047', '東京都', '港区', '南麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1070051', '東京都', '港区', '元赤坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060046', '東京都', '港区', '元麻布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1060032', '東京都', '港区', '六本木（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066090', '東京都', '港区', '六本木泉ガーデンタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066001', '東京都', '港区', '六本木泉ガーデンタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066002', '東京都', '港区', '六本木泉ガーデンタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066003', '東京都', '港区', '六本木泉ガーデンタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066004', '東京都', '港区', '六本木泉ガーデンタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066005', '東京都', '港区', '六本木泉ガーデンタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066006', '東京都', '港区', '六本木泉ガーデンタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066007', '東京都', '港区', '六本木泉ガーデンタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066008', '東京都', '港区', '六本木泉ガーデンタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066009', '東京都', '港区', '六本木泉ガーデンタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066010', '東京都', '港区', '六本木泉ガーデンタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066011', '東京都', '港区', '六本木泉ガーデンタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066012', '東京都', '港区', '六本木泉ガーデンタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066013', '東京都', '港区', '六本木泉ガーデンタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066014', '東京都', '港区', '六本木泉ガーデンタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066015', '東京都', '港区', '六本木泉ガーデンタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066016', '東京都', '港区', '六本木泉ガーデンタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066017', '東京都', '港区', '六本木泉ガーデンタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066018', '東京都', '港区', '六本木泉ガーデンタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066019', '東京都', '港区', '六本木泉ガーデンタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066020', '東京都', '港区', '六本木泉ガーデンタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066021', '東京都', '港区', '六本木泉ガーデンタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066022', '東京都', '港区', '六本木泉ガーデンタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066023', '東京都', '港区', '六本木泉ガーデンタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066024', '東京都', '港区', '六本木泉ガーデンタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066025', '東京都', '港区', '六本木泉ガーデンタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066026', '東京都', '港区', '六本木泉ガーデンタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066027', '東京都', '港区', '六本木泉ガーデンタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066028', '東京都', '港区', '六本木泉ガーデンタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066029', '東京都', '港区', '六本木泉ガーデンタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066030', '東京都', '港区', '六本木泉ガーデンタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066031', '東京都', '港区', '六本木泉ガーデンタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066032', '東京都', '港区', '六本木泉ガーデンタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066033', '東京都', '港区', '六本木泉ガーデンタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066034', '東京都', '港区', '六本木泉ガーデンタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066035', '東京都', '港区', '六本木泉ガーデンタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066036', '東京都', '港区', '六本木泉ガーデンタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066037', '東京都', '港区', '六本木泉ガーデンタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066038', '東京都', '港区', '六本木泉ガーデンタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066039', '東京都', '港区', '六本木泉ガーデンタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066040', '東京都', '港区', '六本木泉ガーデンタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066041', '東京都', '港区', '六本木泉ガーデンタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066042', '東京都', '港区', '六本木泉ガーデンタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066043', '東京都', '港区', '六本木泉ガーデンタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066044', '東京都', '港区', '六本木泉ガーデンタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066045', '東京都', '港区', '六本木泉ガーデンタワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066190', '東京都', '港区', '六本木六本木ヒルズ森タワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066101', '東京都', '港区', '六本木六本木ヒルズ森タワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066102', '東京都', '港区', '六本木六本木ヒルズ森タワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066103', '東京都', '港区', '六本木六本木ヒルズ森タワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066104', '東京都', '港区', '六本木六本木ヒルズ森タワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066105', '東京都', '港区', '六本木六本木ヒルズ森タワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066106', '東京都', '港区', '六本木六本木ヒルズ森タワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066107', '東京都', '港区', '六本木六本木ヒルズ森タワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066108', '東京都', '港区', '六本木六本木ヒルズ森タワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066109', '東京都', '港区', '六本木六本木ヒルズ森タワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066110', '東京都', '港区', '六本木六本木ヒルズ森タワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066111', '東京都', '港区', '六本木六本木ヒルズ森タワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066112', '東京都', '港区', '六本木六本木ヒルズ森タワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066113', '東京都', '港区', '六本木六本木ヒルズ森タワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066114', '東京都', '港区', '六本木六本木ヒルズ森タワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066115', '東京都', '港区', '六本木六本木ヒルズ森タワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066116', '東京都', '港区', '六本木六本木ヒルズ森タワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066117', '東京都', '港区', '六本木六本木ヒルズ森タワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066118', '東京都', '港区', '六本木六本木ヒルズ森タワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066119', '東京都', '港区', '六本木六本木ヒルズ森タワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066120', '東京都', '港区', '六本木六本木ヒルズ森タワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066121', '東京都', '港区', '六本木六本木ヒルズ森タワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066122', '東京都', '港区', '六本木六本木ヒルズ森タワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066123', '東京都', '港区', '六本木六本木ヒルズ森タワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066124', '東京都', '港区', '六本木六本木ヒルズ森タワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066125', '東京都', '港区', '六本木六本木ヒルズ森タワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066126', '東京都', '港区', '六本木六本木ヒルズ森タワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066127', '東京都', '港区', '六本木六本木ヒルズ森タワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066128', '東京都', '港区', '六本木六本木ヒルズ森タワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066129', '東京都', '港区', '六本木六本木ヒルズ森タワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066130', '東京都', '港区', '六本木六本木ヒルズ森タワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066131', '東京都', '港区', '六本木六本木ヒルズ森タワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066132', '東京都', '港区', '六本木六本木ヒルズ森タワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066133', '東京都', '港区', '六本木六本木ヒルズ森タワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066134', '東京都', '港区', '六本木六本木ヒルズ森タワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066135', '東京都', '港区', '六本木六本木ヒルズ森タワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066136', '東京都', '港区', '六本木六本木ヒルズ森タワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066137', '東京都', '港区', '六本木六本木ヒルズ森タワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066138', '東京都', '港区', '六本木六本木ヒルズ森タワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066139', '東京都', '港区', '六本木六本木ヒルズ森タワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066140', '東京都', '港区', '六本木六本木ヒルズ森タワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066141', '東京都', '港区', '六本木六本木ヒルズ森タワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066142', '東京都', '港区', '六本木六本木ヒルズ森タワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066143', '東京都', '港区', '六本木六本木ヒルズ森タワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066144', '東京都', '港区', '六本木六本木ヒルズ森タワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066145', '東京都', '港区', '六本木六本木ヒルズ森タワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066146', '東京都', '港区', '六本木六本木ヒルズ森タワー（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066147', '東京都', '港区', '六本木六本木ヒルズ森タワー（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066148', '東京都', '港区', '六本木六本木ヒルズ森タワー（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066149', '東京都', '港区', '六本木六本木ヒルズ森タワー（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066150', '東京都', '港区', '六本木六本木ヒルズ森タワー（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066151', '東京都', '港区', '六本木六本木ヒルズ森タワー（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066152', '東京都', '港区', '六本木六本木ヒルズ森タワー（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066153', '東京都', '港区', '六本木六本木ヒルズ森タワー（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1066154', '東京都', '港区', '六本木六本木ヒルズ森タワー（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600000', '東京都', '新宿区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600005', '東京都', '新宿区', '愛住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620803', '東京都', '新宿区', '赤城下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620817', '東京都', '新宿区', '赤城元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620824', '東京都', '新宿区', '揚場町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600007', '東京都', '新宿区', '荒木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620062', '東京都', '新宿区', '市谷加賀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620856', '東京都', '新宿区', '市谷甲良町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620842', '東京都', '新宿区', '市谷砂土原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620846', '東京都', '新宿区', '市谷左内町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620848', '東京都', '新宿区', '市谷鷹匠町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620843', '東京都', '新宿区', '市谷田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620066', '東京都', '新宿区', '市谷台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620847', '東京都', '新宿区', '市谷長延寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620064', '東京都', '新宿区', '市谷仲之町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620844', '東京都', '新宿区', '市谷八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620826', '東京都', '新宿区', '市谷船河原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620845', '東京都', '新宿区', '市谷本村町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620063', '東京都', '新宿区', '市谷薬王寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620061', '東京都', '新宿区', '市谷柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620857', '東京都', '新宿区', '市谷山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620832', '東京都', '新宿区', '岩戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620806', '東京都', '新宿区', '榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690072', '東京都', '新宿区', '大久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620802', '東京都', '新宿区', '改代町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620823', '東京都', '新宿区', '神楽河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620825', '東京都', '新宿区', '神楽坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600013', '東京都', '新宿区', '霞ケ丘町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600001', '東京都', '新宿区', '片町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600021', '東京都', '新宿区', '歌舞伎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610034', '東京都', '新宿区', '上落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620054', '東京都', '新宿区', '河田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620044', '東京都', '新宿区', '喜久井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690074', '東京都', '新宿区', '北新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620834', '東京都', '新宿区', '北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620853', '東京都', '新宿区', '北山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620838', '東京都', '新宿区', '細工町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600002', '東京都', '新宿区', '坂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600017', '東京都', '新宿区', '左門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600008', '東京都', '新宿区', '三栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600016', '東京都', '新宿区', '信濃町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610033', '東京都', '新宿区', '下落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620822', '東京都', '新宿区', '下宮比町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620816', '東京都', '新宿区', '白銀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620814', '東京都', '新宿区', '新小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600022', '東京都', '新宿区', '新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620811', '東京都', '新宿区', '水道町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600018', '東京都', '新宿区', '須賀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620065', '東京都', '新宿区', '住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600015', '東京都', '新宿区', '大京町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690075', '東京都', '新宿区', '高田馬場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620833', '東京都', '新宿区', '箪笥町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620818', '東京都', '新宿区', '築地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620821', '東京都', '新宿区', '津久戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620815', '東京都', '新宿区', '筑土八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620808', '東京都', '新宿区', '天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690071', '東京都', '新宿区', '戸塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620067', '東京都', '新宿区', '富久町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690052', '東京都', '新宿区', '戸山（３丁目１８・２１番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620052', '東京都', '新宿区', '戸山（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600014', '東京都', '新宿区', '内藤町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610035', '東京都', '新宿区', '中井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610032', '東京都', '新宿区', '中落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620804', '東京都', '新宿区', '中里町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620835', '東京都', '新宿区', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620837', '東京都', '新宿区', '納戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1610031', '東京都', '新宿区', '西落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620812', '東京都', '新宿区', '西五軒町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600023', '東京都', '新宿区', '西新宿（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631390', '東京都', '新宿区', '西新宿新宿アイランドタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631301', '東京都', '新宿区', '西新宿新宿アイランドタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631302', '東京都', '新宿区', '西新宿新宿アイランドタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631303', '東京都', '新宿区', '西新宿新宿アイランドタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631304', '東京都', '新宿区', '西新宿新宿アイランドタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631305', '東京都', '新宿区', '西新宿新宿アイランドタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631306', '東京都', '新宿区', '西新宿新宿アイランドタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631307', '東京都', '新宿区', '西新宿新宿アイランドタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631308', '東京都', '新宿区', '西新宿新宿アイランドタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631309', '東京都', '新宿区', '西新宿新宿アイランドタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631310', '東京都', '新宿区', '西新宿新宿アイランドタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631311', '東京都', '新宿区', '西新宿新宿アイランドタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631312', '東京都', '新宿区', '西新宿新宿アイランドタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631313', '東京都', '新宿区', '西新宿新宿アイランドタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631314', '東京都', '新宿区', '西新宿新宿アイランドタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631315', '東京都', '新宿区', '西新宿新宿アイランドタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631316', '東京都', '新宿区', '西新宿新宿アイランドタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631317', '東京都', '新宿区', '西新宿新宿アイランドタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631318', '東京都', '新宿区', '西新宿新宿アイランドタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631319', '東京都', '新宿区', '西新宿新宿アイランドタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631320', '東京都', '新宿区', '西新宿新宿アイランドタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631321', '東京都', '新宿区', '西新宿新宿アイランドタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631322', '東京都', '新宿区', '西新宿新宿アイランドタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631323', '東京都', '新宿区', '西新宿新宿アイランドタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631324', '東京都', '新宿区', '西新宿新宿アイランドタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631325', '東京都', '新宿区', '西新宿新宿アイランドタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631326', '東京都', '新宿区', '西新宿新宿アイランドタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631327', '東京都', '新宿区', '西新宿新宿アイランドタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631328', '東京都', '新宿区', '西新宿新宿アイランドタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631329', '東京都', '新宿区', '西新宿新宿アイランドタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631330', '東京都', '新宿区', '西新宿新宿アイランドタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631331', '東京都', '新宿区', '西新宿新宿アイランドタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631332', '東京都', '新宿区', '西新宿新宿アイランドタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631333', '東京都', '新宿区', '西新宿新宿アイランドタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631334', '東京都', '新宿区', '西新宿新宿アイランドタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631335', '東京都', '新宿区', '西新宿新宿アイランドタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631336', '東京都', '新宿区', '西新宿新宿アイランドタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631337', '東京都', '新宿区', '西新宿新宿アイランドタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631338', '東京都', '新宿区', '西新宿新宿アイランドタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631339', '東京都', '新宿区', '西新宿新宿アイランドタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631340', '東京都', '新宿区', '西新宿新宿アイランドタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631341', '東京都', '新宿区', '西新宿新宿アイランドタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631342', '東京都', '新宿区', '西新宿新宿アイランドタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631343', '東京都', '新宿区', '西新宿新宿アイランドタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631344', '東京都', '新宿区', '西新宿新宿アイランドタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630890', '東京都', '新宿区', '西新宿新宿ＮＳビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630801', '東京都', '新宿区', '西新宿新宿ＮＳビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630802', '東京都', '新宿区', '西新宿新宿ＮＳビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630803', '東京都', '新宿区', '西新宿新宿ＮＳビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630804', '東京都', '新宿区', '西新宿新宿ＮＳビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630805', '東京都', '新宿区', '西新宿新宿ＮＳビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630806', '東京都', '新宿区', '西新宿新宿ＮＳビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630807', '東京都', '新宿区', '西新宿新宿ＮＳビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630808', '東京都', '新宿区', '西新宿新宿ＮＳビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630809', '東京都', '新宿区', '西新宿新宿ＮＳビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630810', '東京都', '新宿区', '西新宿新宿ＮＳビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630811', '東京都', '新宿区', '西新宿新宿ＮＳビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630812', '東京都', '新宿区', '西新宿新宿ＮＳビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630813', '東京都', '新宿区', '西新宿新宿ＮＳビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630814', '東京都', '新宿区', '西新宿新宿ＮＳビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630815', '東京都', '新宿区', '西新宿新宿ＮＳビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630816', '東京都', '新宿区', '西新宿新宿ＮＳビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630817', '東京都', '新宿区', '西新宿新宿ＮＳビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630818', '東京都', '新宿区', '西新宿新宿ＮＳビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630819', '東京都', '新宿区', '西新宿新宿ＮＳビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630820', '東京都', '新宿区', '西新宿新宿ＮＳビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630821', '東京都', '新宿区', '西新宿新宿ＮＳビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630822', '東京都', '新宿区', '西新宿新宿ＮＳビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630823', '東京都', '新宿区', '西新宿新宿ＮＳビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630824', '東京都', '新宿区', '西新宿新宿ＮＳビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630825', '東京都', '新宿区', '西新宿新宿ＮＳビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630826', '東京都', '新宿区', '西新宿新宿ＮＳビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630827', '東京都', '新宿区', '西新宿新宿ＮＳビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630828', '東京都', '新宿区', '西新宿新宿ＮＳビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630829', '東京都', '新宿区', '西新宿新宿ＮＳビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630830', '東京都', '新宿区', '西新宿新宿ＮＳビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631590', '東京都', '新宿区', '西新宿新宿エルタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631501', '東京都', '新宿区', '西新宿新宿エルタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631502', '東京都', '新宿区', '西新宿新宿エルタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631503', '東京都', '新宿区', '西新宿新宿エルタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631504', '東京都', '新宿区', '西新宿新宿エルタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631505', '東京都', '新宿区', '西新宿新宿エルタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631506', '東京都', '新宿区', '西新宿新宿エルタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631507', '東京都', '新宿区', '西新宿新宿エルタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631508', '東京都', '新宿区', '西新宿新宿エルタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631509', '東京都', '新宿区', '西新宿新宿エルタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631510', '東京都', '新宿区', '西新宿新宿エルタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631511', '東京都', '新宿区', '西新宿新宿エルタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631512', '東京都', '新宿区', '西新宿新宿エルタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631513', '東京都', '新宿区', '西新宿新宿エルタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631514', '東京都', '新宿区', '西新宿新宿エルタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631515', '東京都', '新宿区', '西新宿新宿エルタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631516', '東京都', '新宿区', '西新宿新宿エルタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631517', '東京都', '新宿区', '西新宿新宿エルタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631518', '東京都', '新宿区', '西新宿新宿エルタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631519', '東京都', '新宿区', '西新宿新宿エルタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631520', '東京都', '新宿区', '西新宿新宿エルタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631521', '東京都', '新宿区', '西新宿新宿エルタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631522', '東京都', '新宿区', '西新宿新宿エルタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631523', '東京都', '新宿区', '西新宿新宿エルタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631524', '東京都', '新宿区', '西新宿新宿エルタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631525', '東京都', '新宿区', '西新宿新宿エルタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631526', '東京都', '新宿区', '西新宿新宿エルタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631527', '東京都', '新宿区', '西新宿新宿エルタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631528', '東京都', '新宿区', '西新宿新宿エルタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631529', '東京都', '新宿区', '西新宿新宿エルタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631530', '東京都', '新宿区', '西新宿新宿エルタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631531', '東京都', '新宿区', '西新宿新宿エルタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631190', '東京都', '新宿区', '西新宿新宿スクエアタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631101', '東京都', '新宿区', '西新宿新宿スクエアタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631102', '東京都', '新宿区', '西新宿新宿スクエアタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631103', '東京都', '新宿区', '西新宿新宿スクエアタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631104', '東京都', '新宿区', '西新宿新宿スクエアタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631105', '東京都', '新宿区', '西新宿新宿スクエアタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631106', '東京都', '新宿区', '西新宿新宿スクエアタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631107', '東京都', '新宿区', '西新宿新宿スクエアタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631108', '東京都', '新宿区', '西新宿新宿スクエアタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631109', '東京都', '新宿区', '西新宿新宿スクエアタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631110', '東京都', '新宿区', '西新宿新宿スクエアタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631111', '東京都', '新宿区', '西新宿新宿スクエアタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631112', '東京都', '新宿区', '西新宿新宿スクエアタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631113', '東京都', '新宿区', '西新宿新宿スクエアタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631114', '東京都', '新宿区', '西新宿新宿スクエアタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631115', '東京都', '新宿区', '西新宿新宿スクエアタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631116', '東京都', '新宿区', '西新宿新宿スクエアタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631117', '東京都', '新宿区', '西新宿新宿スクエアタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631118', '東京都', '新宿区', '西新宿新宿スクエアタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631119', '東京都', '新宿区', '西新宿新宿スクエアタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631120', '東京都', '新宿区', '西新宿新宿スクエアタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631121', '東京都', '新宿区', '西新宿新宿スクエアタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631122', '東京都', '新宿区', '西新宿新宿スクエアタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631123', '東京都', '新宿区', '西新宿新宿スクエアタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631124', '東京都', '新宿区', '西新宿新宿スクエアタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631125', '東京都', '新宿区', '西新宿新宿スクエアタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631126', '東京都', '新宿区', '西新宿新宿スクエアタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631127', '東京都', '新宿区', '西新宿新宿スクエアタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631128', '東京都', '新宿区', '西新宿新宿スクエアタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631129', '東京都', '新宿区', '西新宿新宿スクエアタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631130', '東京都', '新宿区', '西新宿新宿スクエアタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630290', '東京都', '新宿区', '西新宿新宿住友ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630201', '東京都', '新宿区', '西新宿新宿住友ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630202', '東京都', '新宿区', '西新宿新宿住友ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630203', '東京都', '新宿区', '西新宿新宿住友ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630204', '東京都', '新宿区', '西新宿新宿住友ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630205', '東京都', '新宿区', '西新宿新宿住友ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630206', '東京都', '新宿区', '西新宿新宿住友ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630207', '東京都', '新宿区', '西新宿新宿住友ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630208', '東京都', '新宿区', '西新宿新宿住友ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630209', '東京都', '新宿区', '西新宿新宿住友ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630210', '東京都', '新宿区', '西新宿新宿住友ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630211', '東京都', '新宿区', '西新宿新宿住友ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630212', '東京都', '新宿区', '西新宿新宿住友ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630213', '東京都', '新宿区', '西新宿新宿住友ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630214', '東京都', '新宿区', '西新宿新宿住友ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630215', '東京都', '新宿区', '西新宿新宿住友ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630216', '東京都', '新宿区', '西新宿新宿住友ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630217', '東京都', '新宿区', '西新宿新宿住友ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630218', '東京都', '新宿区', '西新宿新宿住友ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630219', '東京都', '新宿区', '西新宿新宿住友ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630220', '東京都', '新宿区', '西新宿新宿住友ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630221', '東京都', '新宿区', '西新宿新宿住友ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630222', '東京都', '新宿区', '西新宿新宿住友ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630223', '東京都', '新宿区', '西新宿新宿住友ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630224', '東京都', '新宿区', '西新宿新宿住友ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630225', '東京都', '新宿区', '西新宿新宿住友ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630226', '東京都', '新宿区', '西新宿新宿住友ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630227', '東京都', '新宿区', '西新宿新宿住友ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630228', '東京都', '新宿区', '西新宿新宿住友ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630229', '東京都', '新宿区', '西新宿新宿住友ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630230', '東京都', '新宿区', '西新宿新宿住友ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630231', '東京都', '新宿区', '西新宿新宿住友ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630232', '東京都', '新宿区', '西新宿新宿住友ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630233', '東京都', '新宿区', '西新宿新宿住友ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630234', '東京都', '新宿区', '西新宿新宿住友ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630235', '東京都', '新宿区', '西新宿新宿住友ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630236', '東京都', '新宿区', '西新宿新宿住友ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630237', '東京都', '新宿区', '西新宿新宿住友ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630238', '東京都', '新宿区', '西新宿新宿住友ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630239', '東京都', '新宿区', '西新宿新宿住友ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630240', '東京都', '新宿区', '西新宿新宿住友ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630241', '東京都', '新宿区', '西新宿新宿住友ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630242', '東京都', '新宿区', '西新宿新宿住友ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630243', '東京都', '新宿区', '西新宿新宿住友ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630244', '東京都', '新宿区', '西新宿新宿住友ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630245', '東京都', '新宿区', '西新宿新宿住友ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630246', '東京都', '新宿区', '西新宿新宿住友ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630247', '東京都', '新宿区', '西新宿新宿住友ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630248', '東京都', '新宿区', '西新宿新宿住友ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630249', '東京都', '新宿区', '西新宿新宿住友ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630250', '東京都', '新宿区', '西新宿新宿住友ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630251', '東京都', '新宿区', '西新宿新宿住友ビル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630252', '東京都', '新宿区', '西新宿新宿住友ビル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630690', '東京都', '新宿区', '西新宿新宿センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630601', '東京都', '新宿区', '西新宿新宿センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630602', '東京都', '新宿区', '西新宿新宿センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630603', '東京都', '新宿区', '西新宿新宿センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630604', '東京都', '新宿区', '西新宿新宿センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630605', '東京都', '新宿区', '西新宿新宿センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630606', '東京都', '新宿区', '西新宿新宿センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630607', '東京都', '新宿区', '西新宿新宿センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630608', '東京都', '新宿区', '西新宿新宿センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630609', '東京都', '新宿区', '西新宿新宿センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630610', '東京都', '新宿区', '西新宿新宿センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630611', '東京都', '新宿区', '西新宿新宿センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630612', '東京都', '新宿区', '西新宿新宿センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630613', '東京都', '新宿区', '西新宿新宿センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630614', '東京都', '新宿区', '西新宿新宿センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630615', '東京都', '新宿区', '西新宿新宿センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630616', '東京都', '新宿区', '西新宿新宿センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630617', '東京都', '新宿区', '西新宿新宿センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630618', '東京都', '新宿区', '西新宿新宿センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630619', '東京都', '新宿区', '西新宿新宿センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630620', '東京都', '新宿区', '西新宿新宿センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630621', '東京都', '新宿区', '西新宿新宿センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630622', '東京都', '新宿区', '西新宿新宿センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630623', '東京都', '新宿区', '西新宿新宿センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630624', '東京都', '新宿区', '西新宿新宿センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630625', '東京都', '新宿区', '西新宿新宿センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630626', '東京都', '新宿区', '西新宿新宿センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630627', '東京都', '新宿区', '西新宿新宿センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630628', '東京都', '新宿区', '西新宿新宿センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630629', '東京都', '新宿区', '西新宿新宿センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630630', '東京都', '新宿区', '西新宿新宿センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630631', '東京都', '新宿区', '西新宿新宿センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630632', '東京都', '新宿区', '西新宿新宿センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630633', '東京都', '新宿区', '西新宿新宿センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630634', '東京都', '新宿区', '西新宿新宿センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630635', '東京都', '新宿区', '西新宿新宿センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630636', '東京都', '新宿区', '西新宿新宿センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630637', '東京都', '新宿区', '西新宿新宿センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630638', '東京都', '新宿区', '西新宿新宿センタービル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630639', '東京都', '新宿区', '西新宿新宿センタービル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630640', '東京都', '新宿区', '西新宿新宿センタービル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630641', '東京都', '新宿区', '西新宿新宿センタービル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630642', '東京都', '新宿区', '西新宿新宿センタービル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630643', '東京都', '新宿区', '西新宿新宿センタービル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630644', '東京都', '新宿区', '西新宿新宿センタービル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630645', '東京都', '新宿区', '西新宿新宿センタービル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630646', '東京都', '新宿区', '西新宿新宿センタービル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630647', '東京都', '新宿区', '西新宿新宿センタービル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630648', '東京都', '新宿区', '西新宿新宿センタービル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630649', '東京都', '新宿区', '西新宿新宿センタービル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630650', '東京都', '新宿区', '西新宿新宿センタービル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630651', '東京都', '新宿区', '西新宿新宿センタービル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630652', '東京都', '新宿区', '西新宿新宿センタービル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630653', '東京都', '新宿区', '西新宿新宿センタービル（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630654', '東京都', '新宿区', '西新宿新宿センタービル（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630790', '東京都', '新宿区', '西新宿新宿第一生命ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630701', '東京都', '新宿区', '西新宿新宿第一生命ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630702', '東京都', '新宿区', '西新宿新宿第一生命ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630703', '東京都', '新宿区', '西新宿新宿第一生命ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630704', '東京都', '新宿区', '西新宿新宿第一生命ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630705', '東京都', '新宿区', '西新宿新宿第一生命ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630706', '東京都', '新宿区', '西新宿新宿第一生命ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630707', '東京都', '新宿区', '西新宿新宿第一生命ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630708', '東京都', '新宿区', '西新宿新宿第一生命ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630709', '東京都', '新宿区', '西新宿新宿第一生命ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630710', '東京都', '新宿区', '西新宿新宿第一生命ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630711', '東京都', '新宿区', '西新宿新宿第一生命ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630712', '東京都', '新宿区', '西新宿新宿第一生命ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630713', '東京都', '新宿区', '西新宿新宿第一生命ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630714', '東京都', '新宿区', '西新宿新宿第一生命ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630715', '東京都', '新宿区', '西新宿新宿第一生命ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630716', '東京都', '新宿区', '西新宿新宿第一生命ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630717', '東京都', '新宿区', '西新宿新宿第一生命ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630718', '東京都', '新宿区', '西新宿新宿第一生命ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630719', '東京都', '新宿区', '西新宿新宿第一生命ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630720', '東京都', '新宿区', '西新宿新宿第一生命ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630721', '東京都', '新宿区', '西新宿新宿第一生命ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630722', '東京都', '新宿区', '西新宿新宿第一生命ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630723', '東京都', '新宿区', '西新宿新宿第一生命ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630724', '東京都', '新宿区', '西新宿新宿第一生命ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630725', '東京都', '新宿区', '西新宿新宿第一生命ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630726', '東京都', '新宿区', '西新宿新宿第一生命ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630590', '東京都', '新宿区', '西新宿新宿野村ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630501', '東京都', '新宿区', '西新宿新宿野村ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630502', '東京都', '新宿区', '西新宿新宿野村ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630503', '東京都', '新宿区', '西新宿新宿野村ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630504', '東京都', '新宿区', '西新宿新宿野村ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630505', '東京都', '新宿区', '西新宿新宿野村ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630506', '東京都', '新宿区', '西新宿新宿野村ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630507', '東京都', '新宿区', '西新宿新宿野村ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630508', '東京都', '新宿区', '西新宿新宿野村ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630509', '東京都', '新宿区', '西新宿新宿野村ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630510', '東京都', '新宿区', '西新宿新宿野村ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630511', '東京都', '新宿区', '西新宿新宿野村ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630512', '東京都', '新宿区', '西新宿新宿野村ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630513', '東京都', '新宿区', '西新宿新宿野村ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630514', '東京都', '新宿区', '西新宿新宿野村ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630515', '東京都', '新宿区', '西新宿新宿野村ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630516', '東京都', '新宿区', '西新宿新宿野村ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630517', '東京都', '新宿区', '西新宿新宿野村ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630518', '東京都', '新宿区', '西新宿新宿野村ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630519', '東京都', '新宿区', '西新宿新宿野村ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630520', '東京都', '新宿区', '西新宿新宿野村ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630521', '東京都', '新宿区', '西新宿新宿野村ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630522', '東京都', '新宿区', '西新宿新宿野村ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630523', '東京都', '新宿区', '西新宿新宿野村ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630524', '東京都', '新宿区', '西新宿新宿野村ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630525', '東京都', '新宿区', '西新宿新宿野村ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630526', '東京都', '新宿区', '西新宿新宿野村ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630527', '東京都', '新宿区', '西新宿新宿野村ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630528', '東京都', '新宿区', '西新宿新宿野村ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630529', '東京都', '新宿区', '西新宿新宿野村ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630530', '東京都', '新宿区', '西新宿新宿野村ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630531', '東京都', '新宿区', '西新宿新宿野村ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630532', '東京都', '新宿区', '西新宿新宿野村ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630533', '東京都', '新宿区', '西新宿新宿野村ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630534', '東京都', '新宿区', '西新宿新宿野村ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630535', '東京都', '新宿区', '西新宿新宿野村ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630536', '東京都', '新宿区', '西新宿新宿野村ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630537', '東京都', '新宿区', '西新宿新宿野村ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630538', '東京都', '新宿区', '西新宿新宿野村ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630539', '東京都', '新宿区', '西新宿新宿野村ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630540', '東京都', '新宿区', '西新宿新宿野村ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630541', '東京都', '新宿区', '西新宿新宿野村ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630542', '東京都', '新宿区', '西新宿新宿野村ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630543', '東京都', '新宿区', '西新宿新宿野村ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630544', '東京都', '新宿区', '西新宿新宿野村ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630545', '東京都', '新宿区', '西新宿新宿野村ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630546', '東京都', '新宿区', '西新宿新宿野村ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630547', '東京都', '新宿区', '西新宿新宿野村ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630548', '東京都', '新宿区', '西新宿新宿野村ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630549', '東京都', '新宿区', '西新宿新宿野村ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630550', '東京都', '新宿区', '西新宿新宿野村ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631090', '東京都', '新宿区', '西新宿新宿パークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631001', '東京都', '新宿区', '西新宿新宿パークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631002', '東京都', '新宿区', '西新宿新宿パークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631003', '東京都', '新宿区', '西新宿新宿パークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631004', '東京都', '新宿区', '西新宿新宿パークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631005', '東京都', '新宿区', '西新宿新宿パークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631006', '東京都', '新宿区', '西新宿新宿パークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631007', '東京都', '新宿区', '西新宿新宿パークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631008', '東京都', '新宿区', '西新宿新宿パークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631009', '東京都', '新宿区', '西新宿新宿パークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631010', '東京都', '新宿区', '西新宿新宿パークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631011', '東京都', '新宿区', '西新宿新宿パークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631012', '東京都', '新宿区', '西新宿新宿パークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631013', '東京都', '新宿区', '西新宿新宿パークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631014', '東京都', '新宿区', '西新宿新宿パークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631015', '東京都', '新宿区', '西新宿新宿パークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631016', '東京都', '新宿区', '西新宿新宿パークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631017', '東京都', '新宿区', '西新宿新宿パークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631018', '東京都', '新宿区', '西新宿新宿パークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631019', '東京都', '新宿区', '西新宿新宿パークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631020', '東京都', '新宿区', '西新宿新宿パークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631021', '東京都', '新宿区', '西新宿新宿パークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631022', '東京都', '新宿区', '西新宿新宿パークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631023', '東京都', '新宿区', '西新宿新宿パークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631024', '東京都', '新宿区', '西新宿新宿パークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631025', '東京都', '新宿区', '西新宿新宿パークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631026', '東京都', '新宿区', '西新宿新宿パークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631027', '東京都', '新宿区', '西新宿新宿パークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631028', '東京都', '新宿区', '西新宿新宿パークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631029', '東京都', '新宿区', '西新宿新宿パークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631030', '東京都', '新宿区', '西新宿新宿パークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631031', '東京都', '新宿区', '西新宿新宿パークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631032', '東京都', '新宿区', '西新宿新宿パークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631033', '東京都', '新宿区', '西新宿新宿パークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631034', '東京都', '新宿区', '西新宿新宿パークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631035', '東京都', '新宿区', '西新宿新宿パークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631036', '東京都', '新宿区', '西新宿新宿パークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631037', '東京都', '新宿区', '西新宿新宿パークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631038', '東京都', '新宿区', '西新宿新宿パークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631039', '東京都', '新宿区', '西新宿新宿パークタワー（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631040', '東京都', '新宿区', '西新宿新宿パークタワー（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631041', '東京都', '新宿区', '西新宿新宿パークタワー（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631042', '東京都', '新宿区', '西新宿新宿パークタワー（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631043', '東京都', '新宿区', '西新宿新宿パークタワー（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631044', '東京都', '新宿区', '西新宿新宿パークタワー（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631045', '東京都', '新宿区', '西新宿新宿パークタワー（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631046', '東京都', '新宿区', '西新宿新宿パークタワー（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631047', '東京都', '新宿区', '西新宿新宿パークタワー（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631048', '東京都', '新宿区', '西新宿新宿パークタワー（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631049', '東京都', '新宿区', '西新宿新宿パークタワー（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631050', '東京都', '新宿区', '西新宿新宿パークタワー（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631051', '東京都', '新宿区', '西新宿新宿パークタワー（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631052', '東京都', '新宿区', '西新宿新宿パークタワー（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630490', '東京都', '新宿区', '西新宿新宿三井ビル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630401', '東京都', '新宿区', '西新宿新宿三井ビル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630402', '東京都', '新宿区', '西新宿新宿三井ビル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630403', '東京都', '新宿区', '西新宿新宿三井ビル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630404', '東京都', '新宿区', '西新宿新宿三井ビル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630405', '東京都', '新宿区', '西新宿新宿三井ビル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630406', '東京都', '新宿区', '西新宿新宿三井ビル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630407', '東京都', '新宿区', '西新宿新宿三井ビル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630408', '東京都', '新宿区', '西新宿新宿三井ビル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630409', '東京都', '新宿区', '西新宿新宿三井ビル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630410', '東京都', '新宿区', '西新宿新宿三井ビル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630411', '東京都', '新宿区', '西新宿新宿三井ビル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630412', '東京都', '新宿区', '西新宿新宿三井ビル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630413', '東京都', '新宿区', '西新宿新宿三井ビル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630414', '東京都', '新宿区', '西新宿新宿三井ビル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630415', '東京都', '新宿区', '西新宿新宿三井ビル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630416', '東京都', '新宿区', '西新宿新宿三井ビル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630417', '東京都', '新宿区', '西新宿新宿三井ビル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630418', '東京都', '新宿区', '西新宿新宿三井ビル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630419', '東京都', '新宿区', '西新宿新宿三井ビル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630420', '東京都', '新宿区', '西新宿新宿三井ビル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630421', '東京都', '新宿区', '西新宿新宿三井ビル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630422', '東京都', '新宿区', '西新宿新宿三井ビル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630423', '東京都', '新宿区', '西新宿新宿三井ビル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630424', '東京都', '新宿区', '西新宿新宿三井ビル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630425', '東京都', '新宿区', '西新宿新宿三井ビル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630426', '東京都', '新宿区', '西新宿新宿三井ビル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630427', '東京都', '新宿区', '西新宿新宿三井ビル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630428', '東京都', '新宿区', '西新宿新宿三井ビル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630429', '東京都', '新宿区', '西新宿新宿三井ビル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630430', '東京都', '新宿区', '西新宿新宿三井ビル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630431', '東京都', '新宿区', '西新宿新宿三井ビル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630432', '東京都', '新宿区', '西新宿新宿三井ビル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630433', '東京都', '新宿区', '西新宿新宿三井ビル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630434', '東京都', '新宿区', '西新宿新宿三井ビル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630435', '東京都', '新宿区', '西新宿新宿三井ビル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630436', '東京都', '新宿区', '西新宿新宿三井ビル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630437', '東京都', '新宿区', '西新宿新宿三井ビル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630438', '東京都', '新宿区', '西新宿新宿三井ビル（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630439', '東京都', '新宿区', '西新宿新宿三井ビル（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630440', '東京都', '新宿区', '西新宿新宿三井ビル（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630441', '東京都', '新宿区', '西新宿新宿三井ビル（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630442', '東京都', '新宿区', '西新宿新宿三井ビル（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630443', '東京都', '新宿区', '西新宿新宿三井ビル（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630444', '東京都', '新宿区', '西新宿新宿三井ビル（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630445', '東京都', '新宿区', '西新宿新宿三井ビル（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630446', '東京都', '新宿区', '西新宿新宿三井ビル（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630447', '東京都', '新宿区', '西新宿新宿三井ビル（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630448', '東京都', '新宿区', '西新宿新宿三井ビル（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630449', '東京都', '新宿区', '西新宿新宿三井ビル（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630450', '東京都', '新宿区', '西新宿新宿三井ビル（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630451', '東京都', '新宿区', '西新宿新宿三井ビル（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630452', '東京都', '新宿区', '西新宿新宿三井ビル（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630453', '東京都', '新宿区', '西新宿新宿三井ビル（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630454', '東京都', '新宿区', '西新宿新宿三井ビル（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630455', '東京都', '新宿区', '西新宿新宿三井ビル（５５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630990', '東京都', '新宿区', '西新宿新宿モノリス（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630901', '東京都', '新宿区', '西新宿新宿モノリス（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630902', '東京都', '新宿区', '西新宿新宿モノリス（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630903', '東京都', '新宿区', '西新宿新宿モノリス（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630904', '東京都', '新宿区', '西新宿新宿モノリス（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630905', '東京都', '新宿区', '西新宿新宿モノリス（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630906', '東京都', '新宿区', '西新宿新宿モノリス（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630907', '東京都', '新宿区', '西新宿新宿モノリス（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630908', '東京都', '新宿区', '西新宿新宿モノリス（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630909', '東京都', '新宿区', '西新宿新宿モノリス（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630910', '東京都', '新宿区', '西新宿新宿モノリス（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630911', '東京都', '新宿区', '西新宿新宿モノリス（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630912', '東京都', '新宿区', '西新宿新宿モノリス（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630913', '東京都', '新宿区', '西新宿新宿モノリス（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630914', '東京都', '新宿区', '西新宿新宿モノリス（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630915', '東京都', '新宿区', '西新宿新宿モノリス（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630916', '東京都', '新宿区', '西新宿新宿モノリス（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630917', '東京都', '新宿区', '西新宿新宿モノリス（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630918', '東京都', '新宿区', '西新宿新宿モノリス（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630919', '東京都', '新宿区', '西新宿新宿モノリス（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630920', '東京都', '新宿区', '西新宿新宿モノリス（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630921', '東京都', '新宿区', '西新宿新宿モノリス（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630922', '東京都', '新宿区', '西新宿新宿モノリス（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630923', '東京都', '新宿区', '西新宿新宿モノリス（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630924', '東京都', '新宿区', '西新宿新宿モノリス（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630925', '東京都', '新宿区', '西新宿新宿モノリス（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630926', '東京都', '新宿区', '西新宿新宿モノリス（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630927', '東京都', '新宿区', '西新宿新宿モノリス（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630928', '東京都', '新宿区', '西新宿新宿モノリス（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630929', '東京都', '新宿区', '西新宿新宿モノリス（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1630930', '東京都', '新宿区', '西新宿新宿モノリス（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636090', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636001', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636002', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636003', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636004', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636005', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636006', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636007', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636008', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636009', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636010', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636011', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636012', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636013', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636014', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636015', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636016', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636017', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636018', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636019', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636020', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636021', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636022', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636023', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636024', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636025', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636026', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636027', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636028', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636029', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636030', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636031', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636032', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636033', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636034', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636035', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636036', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636037', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1636038', '東京都', '新宿区', '西新宿住友不動産新宿オークタワー（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631490', '東京都', '新宿区', '西新宿東京オペラシティ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631401', '東京都', '新宿区', '西新宿東京オペラシティ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631402', '東京都', '新宿区', '西新宿東京オペラシティ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631403', '東京都', '新宿区', '西新宿東京オペラシティ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631404', '東京都', '新宿区', '西新宿東京オペラシティ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631405', '東京都', '新宿区', '西新宿東京オペラシティ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631406', '東京都', '新宿区', '西新宿東京オペラシティ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631407', '東京都', '新宿区', '西新宿東京オペラシティ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631408', '東京都', '新宿区', '西新宿東京オペラシティ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631409', '東京都', '新宿区', '西新宿東京オペラシティ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631410', '東京都', '新宿区', '西新宿東京オペラシティ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631411', '東京都', '新宿区', '西新宿東京オペラシティ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631412', '東京都', '新宿区', '西新宿東京オペラシティ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631413', '東京都', '新宿区', '西新宿東京オペラシティ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631414', '東京都', '新宿区', '西新宿東京オペラシティ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631415', '東京都', '新宿区', '西新宿東京オペラシティ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631416', '東京都', '新宿区', '西新宿東京オペラシティ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631417', '東京都', '新宿区', '西新宿東京オペラシティ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631418', '東京都', '新宿区', '西新宿東京オペラシティ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631419', '東京都', '新宿区', '西新宿東京オペラシティ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631420', '東京都', '新宿区', '西新宿東京オペラシティ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631421', '東京都', '新宿区', '西新宿東京オペラシティ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631422', '東京都', '新宿区', '西新宿東京オペラシティ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631423', '東京都', '新宿区', '西新宿東京オペラシティ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631424', '東京都', '新宿区', '西新宿東京オペラシティ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631425', '東京都', '新宿区', '西新宿東京オペラシティ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631426', '東京都', '新宿区', '西新宿東京オペラシティ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631427', '東京都', '新宿区', '西新宿東京オペラシティ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631428', '東京都', '新宿区', '西新宿東京オペラシティ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631429', '東京都', '新宿区', '西新宿東京オペラシティ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631430', '東京都', '新宿区', '西新宿東京オペラシティ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631431', '東京都', '新宿区', '西新宿東京オペラシティ（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631432', '東京都', '新宿区', '西新宿東京オペラシティ（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631433', '東京都', '新宿区', '西新宿東京オペラシティ（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631434', '東京都', '新宿区', '西新宿東京オペラシティ（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631435', '東京都', '新宿区', '西新宿東京オペラシティ（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631436', '東京都', '新宿区', '西新宿東京オペラシティ（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631437', '東京都', '新宿区', '西新宿東京オペラシティ（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631438', '東京都', '新宿区', '西新宿東京オペラシティ（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631439', '東京都', '新宿区', '西新宿東京オペラシティ（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631440', '東京都', '新宿区', '西新宿東京オペラシティ（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631441', '東京都', '新宿区', '西新宿東京オペラシティ（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631442', '東京都', '新宿区', '西新宿東京オペラシティ（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631443', '東京都', '新宿区', '西新宿東京オペラシティ（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631444', '東京都', '新宿区', '西新宿東京オペラシティ（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631445', '東京都', '新宿区', '西新宿東京オペラシティ（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631446', '東京都', '新宿区', '西新宿東京オペラシティ（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631447', '東京都', '新宿区', '西新宿東京オペラシティ（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631448', '東京都', '新宿区', '西新宿東京オペラシティ（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631449', '東京都', '新宿区', '西新宿東京オペラシティ（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631450', '東京都', '新宿区', '西新宿東京オペラシティ（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631451', '東京都', '新宿区', '西新宿東京オペラシティ（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631452', '東京都', '新宿区', '西新宿東京オペラシティ（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631453', '東京都', '新宿区', '西新宿東京オペラシティ（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1631454', '東京都', '新宿区', '西新宿東京オペラシティ（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620051', '東京都', '新宿区', '西早稲田（２丁目１番１〜２３号、２番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690051', '東京都', '新宿区', '西早稲田（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620855', '東京都', '新宿区', '二十騎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620045', '東京都', '新宿区', '馬場下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620841', '東京都', '新宿区', '払方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620053', '東京都', '新宿区', '原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620807', '東京都', '新宿区', '東榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620813', '東京都', '新宿区', '東五軒町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1690073', '東京都', '新宿区', '百人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620828', '東京都', '新宿区', '袋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600006', '東京都', '新宿区', '舟町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620851', '東京都', '新宿区', '弁天町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600003', '東京都', '新宿区', '本塩町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620852', '東京都', '新宿区', '南榎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620836', '東京都', '新宿区', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600012', '東京都', '新宿区', '南元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620854', '東京都', '新宿区', '南山伏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620801', '東京都', '新宿区', '山吹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620805', '東京都', '新宿区', '矢来町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620831', '東京都', '新宿区', '横寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620055', '東京都', '新宿区', '余丁町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600004', '東京都', '新宿区', '四谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1600011', '東京都', '新宿区', '若葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620056', '東京都', '新宿区', '若松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620827', '東京都', '新宿区', '若宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620041', '東京都', '新宿区', '早稲田鶴巻町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620043', '東京都', '新宿区', '早稲田南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1620042', '東京都', '新宿区', '早稲田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120000', '東京都', '文京区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120012', '東京都', '文京区', '大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120013', '東京都', '文京区', '音羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120003', '東京都', '文京区', '春日');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120002', '東京都', '文京区', '小石川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120004', '東京都', '文京区', '後楽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120006', '東京都', '文京区', '小日向');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120005', '東京都', '文京区', '水道');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120014', '東京都', '文京区', '関口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120011', '東京都', '文京区', '千石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130022', '東京都', '文京区', '千駄木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130024', '東京都', '文京区', '西片');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130031', '東京都', '文京区', '根津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130001', '東京都', '文京区', '白山（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120001', '東京都', '文京区', '白山（２〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130021', '東京都', '文京区', '本駒込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130033', '東京都', '文京区', '本郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130023', '東京都', '文京区', '向丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1120015', '東京都', '文京区', '目白台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130032', '東京都', '文京区', '弥生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1130034', '東京都', '文京区', '湯島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100000', '東京都', '台東区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100006', '東京都', '台東区', '秋葉原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110032', '東京都', '台東区', '浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110053', '東京都', '台東区', '浅草橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100008', '東京都', '台東区', '池之端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110024', '東京都', '台東区', '今戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100013', '東京都', '台東区', '入谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100005', '東京都', '台東区', '上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100007', '東京都', '台東区', '上野公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100002', '東京都', '台東区', '上野桜木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110034', '東京都', '台東区', '雷門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100014', '東京都', '台東区', '北上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110022', '東京都', '台東区', '清川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110051', '東京都', '台東区', '蔵前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110056', '東京都', '台東区', '小島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110042', '東京都', '台東区', '寿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110043', '東京都', '台東区', '駒形');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100004', '東京都', '台東区', '下谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110031', '東京都', '台東区', '千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100016', '東京都', '台東区', '台東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110054', '東京都', '台東区', '鳥越');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110035', '東京都', '台東区', '西浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110021', '東京都', '台東区', '日本堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100003', '東京都', '台東区', '根岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110023', '東京都', '台東区', '橋場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110033', '東京都', '台東区', '花川戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110025', '東京都', '台東区', '東浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100015', '東京都', '台東区', '東上野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110036', '東京都', '台東区', '松が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110055', '東京都', '台東区', '三筋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100011', '東京都', '台東区', '三ノ輪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110041', '東京都', '台東区', '元浅草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100001', '東京都', '台東区', '谷中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1110052', '東京都', '台東区', '柳橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1100012', '東京都', '台東区', '竜泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300000', '東京都', '墨田区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300001', '東京都', '墨田区', '吾妻橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300011', '東京都', '墨田区', '石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310045', '東京都', '墨田区', '押上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300014', '東京都', '墨田区', '亀沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300024', '東京都', '墨田区', '菊川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310046', '東京都', '墨田区', '京島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300013', '東京都', '墨田区', '錦糸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300022', '東京都', '墨田区', '江東橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310031', '東京都', '墨田区', '墨田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300012', '東京都', '墨田区', '太平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310043', '東京都', '墨田区', '立花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300023', '東京都', '墨田区', '立川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300025', '東京都', '墨田区', '千歳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310034', '東京都', '墨田区', '堤通');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300002', '東京都', '墨田区', '業平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300005', '東京都', '墨田区', '東駒形');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310042', '東京都', '墨田区', '東墨田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310032', '東京都', '墨田区', '東向島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310044', '東京都', '墨田区', '文花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300004', '東京都', '墨田区', '本所');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300021', '東京都', '墨田区', '緑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310033', '東京都', '墨田区', '向島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1310041', '東京都', '墨田区', '八広');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300015', '東京都', '墨田区', '横網');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300003', '東京都', '墨田区', '横川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1300026', '東京都', '墨田区', '両国');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350000', '東京都', '江東区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350064', '東京都', '江東区', '青海');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350063', '東京都', '江東区', '有明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350014', '東京都', '江東区', '石島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350012', '東京都', '江東区', '海辺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350034', '東京都', '江東区', '永代');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350051', '東京都', '江東区', '枝川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350044', '東京都', '江東区', '越中島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350011', '東京都', '江東区', '扇橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360072', '東京都', '江東区', '大島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360071', '東京都', '江東区', '亀戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360073', '東京都', '江東区', '北砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350042', '東京都', '江東区', '木場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350024', '東京都', '江東区', '清澄');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350031', '東京都', '江東区', '佐賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350003', '東京都', '江東区', '猿江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350043', '東京都', '江東区', '塩浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350052', '東京都', '江東区', '潮見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350062', '東京都', '江東区', '東雲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350021', '東京都', '江東区', '白河');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350007', '東京都', '江東区', '新大橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360082', '東京都', '江東区', '新木場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360075', '東京都', '江東区', '新砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350002', '東京都', '江東区', '住吉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350015', '東京都', '江東区', '千石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350013', '東京都', '江東区', '千田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350005', '東京都', '江東区', '高橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350053', '東京都', '江東区', '辰巳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350065', '東京都', '江東区', '中央防波堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350016', '東京都', '江東区', '東陽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350006', '東京都', '江東区', '常盤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350047', '東京都', '江東区', '富岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350061', '東京都', '江東区', '豊洲（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356090', '東京都', '江東区', '豊洲豊洲センタービル（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356001', '東京都', '江東区', '豊洲豊洲センタービル（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356002', '東京都', '江東区', '豊洲豊洲センタービル（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356003', '東京都', '江東区', '豊洲豊洲センタービル（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356004', '東京都', '江東区', '豊洲豊洲センタービル（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356005', '東京都', '江東区', '豊洲豊洲センタービル（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356006', '東京都', '江東区', '豊洲豊洲センタービル（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356007', '東京都', '江東区', '豊洲豊洲センタービル（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356008', '東京都', '江東区', '豊洲豊洲センタービル（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356009', '東京都', '江東区', '豊洲豊洲センタービル（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356010', '東京都', '江東区', '豊洲豊洲センタービル（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356011', '東京都', '江東区', '豊洲豊洲センタービル（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356012', '東京都', '江東区', '豊洲豊洲センタービル（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356013', '東京都', '江東区', '豊洲豊洲センタービル（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356014', '東京都', '江東区', '豊洲豊洲センタービル（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356015', '東京都', '江東区', '豊洲豊洲センタービル（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356016', '東京都', '江東区', '豊洲豊洲センタービル（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356017', '東京都', '江東区', '豊洲豊洲センタービル（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356018', '東京都', '江東区', '豊洲豊洲センタービル（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356019', '東京都', '江東区', '豊洲豊洲センタービル（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356020', '東京都', '江東区', '豊洲豊洲センタービル（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356021', '東京都', '江東区', '豊洲豊洲センタービル（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356022', '東京都', '江東区', '豊洲豊洲センタービル（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356023', '東京都', '江東区', '豊洲豊洲センタービル（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356024', '東京都', '江東区', '豊洲豊洲センタービル（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356025', '東京都', '江東区', '豊洲豊洲センタービル（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356026', '東京都', '江東区', '豊洲豊洲センタービル（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356027', '東京都', '江東区', '豊洲豊洲センタービル（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356028', '東京都', '江東区', '豊洲豊洲センタービル（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356029', '東京都', '江東区', '豊洲豊洲センタービル（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356030', '東京都', '江東区', '豊洲豊洲センタービル（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356031', '東京都', '江東区', '豊洲豊洲センタービル（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356032', '東京都', '江東区', '豊洲豊洲センタービル（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356033', '東京都', '江東区', '豊洲豊洲センタービル（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356034', '東京都', '江東区', '豊洲豊洲センタービル（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356035', '東京都', '江東区', '豊洲豊洲センタービル（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356036', '東京都', '江東区', '豊洲豊洲センタービル（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1356037', '東京都', '江東区', '豊洲豊洲センタービル（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360074', '東京都', '江東区', '東砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350023', '東京都', '江東区', '平野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350033', '東京都', '江東区', '深川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350032', '東京都', '江東区', '福住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350041', '東京都', '江東区', '冬木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350045', '東京都', '江東区', '古石場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350046', '東京都', '江東区', '牡丹');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360076', '東京都', '江東区', '南砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350022', '東京都', '江東区', '三好');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350001', '東京都', '江東区', '毛利');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350004', '東京都', '江東区', '森下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350048', '東京都', '江東区', '門前仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360081', '東京都', '江東区', '夢の島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1360083', '東京都', '江東区', '若洲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400000', '東京都', '品川区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420063', '東京都', '品川区', '荏原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400014', '東京都', '品川区', '大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410032', '東京都', '品川区', '大崎（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416090', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416001', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416002', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416003', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416004', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416005', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416006', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416007', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416008', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416009', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416010', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416011', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416012', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416013', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416014', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416015', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416016', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416017', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416018', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416019', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416020', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416021', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416022', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416023', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416024', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416025', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416026', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416027', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416028', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416029', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1416030', '東京都', '品川区', '大崎ＴｈｉｎｋＰａｒｋＴｏｗｅｒ（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400012', '東京都', '品川区', '勝島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410021', '東京都', '品川区', '上大崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400001', '東京都', '品川区', '北品川（１〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410001', '東京都', '品川区', '北品川（５、６丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420062', '東京都', '品川区', '小山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420061', '東京都', '品川区', '小山台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420041', '東京都', '品川区', '戸越');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420053', '東京都', '品川区', '中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400015', '東京都', '品川区', '西大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410031', '東京都', '品川区', '西五反田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410033', '東京都', '品川区', '西品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420054', '東京都', '品川区', '西中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420064', '東京都', '品川区', '旗の台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400011', '東京都', '品川区', '東大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1410022', '東京都', '品川区', '東五反田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400002', '東京都', '品川区', '東品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420052', '東京都', '品川区', '東中延');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1350092', '東京都', '品川区', '東八潮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420051', '東京都', '品川区', '平塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400005', '東京都', '品川区', '広町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420043', '東京都', '品川区', '二葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400013', '東京都', '品川区', '南大井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400004', '東京都', '品川区', '南品川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1400003', '東京都', '品川区', '八潮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1420042', '東京都', '品川区', '豊町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520000', '東京都', '目黒区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530042', '東京都', '目黒区', '青葉台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520033', '東京都', '目黒区', '大岡山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530044', '東京都', '目黒区', '大橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520022', '東京都', '目黒区', '柿の木坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530051', '東京都', '目黒区', '上目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530053', '東京都', '目黒区', '五本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530041', '東京都', '目黒区', '駒場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530064', '東京都', '目黒区', '下目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520035', '東京都', '目黒区', '自由が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520012', '東京都', '目黒区', '洗足');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520032', '東京都', '目黒区', '平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520004', '東京都', '目黒区', '鷹番');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520001', '東京都', '目黒区', '中央町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530065', '東京都', '目黒区', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520031', '東京都', '目黒区', '中根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530061', '東京都', '目黒区', '中目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520011', '東京都', '目黒区', '原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520021', '東京都', '目黒区', '東が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530043', '東京都', '目黒区', '東山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520003', '東京都', '目黒区', '碑文谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530062', '東京都', '目黒区', '三田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520034', '東京都', '目黒区', '緑が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520013', '東京都', '目黒区', '南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530063', '東京都', '目黒区', '目黒');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520002', '東京都', '目黒区', '目黒本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1520023', '東京都', '目黒区', '八雲');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1530052', '東京都', '目黒区', '祐天寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440000', '東京都', '大田区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460082', '東京都', '大田区', '池上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450061', '東京都', '大田区', '石川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460091', '東京都', '大田区', '鵜の木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430014', '東京都', '大田区', '大森中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430011', '東京都', '大田区', '大森本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430012', '東京都', '大田区', '大森東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430015', '東京都', '大田区', '大森西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430013', '東京都', '大田区', '大森南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430016', '東京都', '大田区', '大森北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440052', '東京都', '大田区', '蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440053', '東京都', '大田区', '蒲田本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450064', '東京都', '大田区', '上池台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440032', '東京都', '大田区', '北糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450062', '東京都', '大田区', '北千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430021', '東京都', '大田区', '北馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450073', '東京都', '大田区', '北嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460085', '東京都', '大田区', '久が原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430003', '東京都', '大田区', '京浜島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430023', '東京都', '大田区', '山王');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460092', '東京都', '大田区', '下丸子');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430002', '東京都', '大田区', '城南島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430004', '東京都', '大田区', '昭和島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440054', '東京都', '大田区', '新蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460095', '東京都', '大田区', '多摩川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460083', '東京都', '大田区', '千鳥');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430024', '東京都', '大田区', '中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450071', '東京都', '大田区', '田園調布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450072', '東京都', '大田区', '田園調布本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450076', '東京都', '大田区', '田園調布南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430001', '東京都', '大田区', '東海');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460081', '東京都', '大田区', '仲池上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430027', '東京都', '大田区', '中馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440055', '東京都', '大田区', '仲六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440051', '東京都', '大田区', '西蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440034', '東京都', '大田区', '西糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430026', '東京都', '大田区', '西馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450075', '東京都', '大田区', '西嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440056', '東京都', '大田区', '西六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440047', '東京都', '大田区', '萩中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440043', '東京都', '大田区', '羽田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440042', '東京都', '大田区', '羽田旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440041', '東京都', '大田区', '羽田空港');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440031', '東京都', '大田区', '東蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440033', '東京都', '大田区', '東糀谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430022', '東京都', '大田区', '東馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450074', '東京都', '大田区', '東嶺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460094', '東京都', '大田区', '東矢口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450065', '東京都', '大田区', '東雪谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440046', '東京都', '大田区', '東六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430007', '東京都', '大田区', 'ふるさとの浜辺公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430006', '東京都', '大田区', '平和島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430005', '東京都', '大田区', '平和の森公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440044', '東京都', '大田区', '本羽田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440035', '東京都', '大田区', '南蒲田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460084', '東京都', '大田区', '南久が原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450063', '東京都', '大田区', '南千束');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1430025', '東京都', '大田区', '南馬込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450066', '東京都', '大田区', '南雪谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1440045', '東京都', '大田区', '南六郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1460093', '東京都', '大田区', '矢口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1450067', '東京都', '大田区', '雪谷大塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540000', '東京都', '世田谷区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560044', '東京都', '世田谷区', '赤堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540001', '東京都', '世田谷区', '池尻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570068', '東京都', '世田谷区', '宇奈根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540022', '東京都', '世田谷区', '梅丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570074', '東京都', '世田谷区', '大蔵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560041', '東京都', '世田谷区', '大原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570076', '東京都', '世田谷区', '岡本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580083', '東京都', '世田谷区', '奥沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580086', '東京都', '世田谷区', '尾山台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570063', '東京都', '世田谷区', '粕谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570077', '東京都', '世田谷区', '鎌田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540011', '東京都', '世田谷区', '上馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560057', '東京都', '世田谷区', '上北沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570065', '東京都', '世田谷区', '上祖師谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580093', '東京都', '世田谷区', '上野毛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580098', '東京都', '世田谷区', '上用賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570061', '東京都', '世田谷区', '北烏山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550031', '東京都', '世田谷区', '北沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570067', '東京都', '世田谷区', '喜多見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570073', '東京都', '世田谷区', '砧');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570075', '東京都', '世田谷区', '砧公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570064', '東京都', '世田谷区', '給田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560052', '東京都', '世田谷区', '経堂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540021', '東京都', '世田谷区', '豪徳寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540012', '東京都', '世田谷区', '駒沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540013', '東京都', '世田谷区', '駒沢公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560053', '東京都', '世田谷区', '桜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560054', '東京都', '世田谷区', '桜丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540015', '東京都', '世田谷区', '桜新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560045', '東京都', '世田谷区', '桜上水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540024', '東京都', '世田谷区', '三軒茶屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540002', '東京都', '世田谷区', '下馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540014', '東京都', '世田谷区', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570066', '東京都', '世田谷区', '成城');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580095', '東京都', '世田谷区', '瀬田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540017', '東京都', '世田谷区', '世田谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570072', '東京都', '世田谷区', '祖師谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540004', '東京都', '世田谷区', '太子堂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550032', '東京都', '世田谷区', '代沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1550033', '東京都', '世田谷区', '代田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580094', '東京都', '世田谷区', '玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580096', '東京都', '世田谷区', '玉川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580085', '東京都', '世田谷区', '玉川田園調布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580087', '東京都', '世田谷区', '玉堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570071', '東京都', '世田谷区', '千歳台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540016', '東京都', '世田谷区', '弦巻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580082', '東京都', '世田谷区', '等々力');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580091', '東京都', '世田谷区', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580092', '東京都', '世田谷区', '野毛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540003', '東京都', '世田谷区', '野沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560056', '東京都', '世田谷区', '八幡山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560042', '東京都', '世田谷区', '羽根木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580084', '東京都', '世田谷区', '東玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580081', '東京都', '世田谷区', '深沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560055', '東京都', '世田谷区', '船橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560043', '東京都', '世田谷区', '松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540005', '東京都', '世田谷区', '三宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1570062', '東京都', '世田谷区', '南烏山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1560051', '東京都', '世田谷区', '宮坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1580097', '東京都', '世田谷区', '用賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1540023', '東京都', '世田谷区', '若林');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500000', '東京都', '渋谷区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510064', '東京都', '渋谷区', '上原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500032', '東京都', '渋谷区', '鶯谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500042', '東京都', '渋谷区', '宇田川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500013', '東京都', '渋谷区', '恵比寿（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506090', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506001', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506002', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506003', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506004', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506005', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506006', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506007', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506008', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506009', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506010', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506011', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506012', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506013', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506014', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506015', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506016', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506017', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506018', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506019', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506020', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506021', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506022', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506023', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506024', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506025', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506026', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506027', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506028', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506029', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506030', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506031', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506032', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506033', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506034', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506035', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506036', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506037', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506038', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1506039', '東京都', '渋谷区', '恵比寿恵比寿ガーデンプレイス（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500021', '東京都', '渋谷区', '恵比寿西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500022', '東京都', '渋谷区', '恵比寿南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510065', '東京都', '渋谷区', '大山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500047', '東京都', '渋谷区', '神山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500031', '東京都', '渋谷区', '桜丘町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510073', '東京都', '渋谷区', '笹塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500033', '東京都', '渋谷区', '猿楽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500002', '東京都', '渋谷区', '渋谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500046', '東京都', '渋谷区', '松濤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500001', '東京都', '渋谷区', '神宮前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500045', '東京都', '渋谷区', '神泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500041', '東京都', '渋谷区', '神南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510051', '東京都', '渋谷区', '千駄ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500034', '東京都', '渋谷区', '代官山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500043', '東京都', '渋谷区', '道玄坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510063', '東京都', '渋谷区', '富ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500036', '東京都', '渋谷区', '南平台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510066', '東京都', '渋谷区', '西原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510072', '東京都', '渋谷区', '幡ヶ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500035', '東京都', '渋谷区', '鉢山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510061', '東京都', '渋谷区', '初台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500011', '東京都', '渋谷区', '東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500012', '東京都', '渋谷区', '広尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510071', '東京都', '渋谷区', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1500044', '東京都', '渋谷区', '円山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510062', '東京都', '渋谷区', '元代々木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510053', '東京都', '渋谷区', '代々木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1510052', '東京都', '渋谷区', '代々木神園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640000', '東京都', '中野区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650026', '東京都', '中野区', '新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650022', '東京都', '中野区', '江古田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650023', '東京都', '中野区', '江原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650031', '東京都', '中野区', '上鷺宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640002', '東京都', '中野区', '上高田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650032', '東京都', '中野区', '鷺宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650035', '東京都', '中野区', '白鷺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640011', '東京都', '中野区', '中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640001', '東京都', '中野区', '中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650025', '東京都', '中野区', '沼袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650027', '東京都', '中野区', '野方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640003', '東京都', '中野区', '東中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640012', '東京都', '中野区', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650024', '東京都', '中野区', '松が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650021', '東京都', '中野区', '丸山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640014', '東京都', '中野区', '南台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650034', '東京都', '中野区', '大和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1640013', '東京都', '中野区', '弥生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1650033', '東京都', '中野区', '若宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660000', '東京都', '杉並区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660004', '東京都', '杉並区', '阿佐谷南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660001', '東京都', '杉並区', '阿佐谷北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670032', '東京都', '杉並区', '天沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670021', '東京都', '杉並区', '井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680063', '東京都', '杉並区', '和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670035', '東京都', '杉並区', '今川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660011', '東京都', '杉並区', '梅里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680064', '東京都', '杉並区', '永福');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680061', '東京都', '杉並区', '大宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670051', '東京都', '杉並区', '荻窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670023', '東京都', '杉並区', '上井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670043', '東京都', '杉並区', '上荻');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680074', '東京都', '杉並区', '上高井戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680082', '東京都', '杉並区', '久我山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660003', '東京都', '杉並区', '高円寺南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660002', '東京都', '杉並区', '高円寺北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670033', '東京都', '杉並区', '清水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670022', '東京都', '杉並区', '下井草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680073', '東京都', '杉並区', '下高井戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670054', '東京都', '杉並区', '松庵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670041', '東京都', '杉並区', '善福寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680072', '東京都', '杉並区', '高井戸東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680071', '東京都', '杉並区', '高井戸西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660015', '東京都', '杉並区', '成田東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660016', '東京都', '杉並区', '成田西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670053', '東京都', '杉並区', '西荻南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670042', '東京都', '杉並区', '西荻北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680065', '東京都', '杉並区', '浜田山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680062', '東京都', '杉並区', '方南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660013', '東京都', '杉並区', '堀ノ内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670031', '東京都', '杉並区', '本天沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660014', '東京都', '杉並区', '松ノ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670052', '東京都', '杉並区', '南荻窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1680081', '東京都', '杉並区', '宮前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1670034', '東京都', '杉並区', '桃井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1660012', '東京都', '杉並区', '和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700000', '東京都', '豊島区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700014', '東京都', '豊島区', '池袋（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710014', '東京都', '豊島区', '池袋（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700011', '東京都', '豊島区', '池袋本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710043', '東京都', '豊島区', '要町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700012', '東京都', '豊島区', '上池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700004', '東京都', '豊島区', '北大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700003', '東京都', '豊島区', '駒込');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700002', '東京都', '豊島区', '巣鴨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710041', '東京都', '豊島区', '千川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710032', '東京都', '豊島区', '雑司が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710033', '東京都', '豊島区', '高田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710042', '東京都', '豊島区', '高松');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710044', '東京都', '豊島区', '千早');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710051', '東京都', '豊島区', '長崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710021', '東京都', '豊島区', '西池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700001', '東京都', '豊島区', '西巣鴨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700013', '東京都', '豊島区', '東池袋（次のビルを除く）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706090', '東京都', '豊島区', '東池袋サンシャイン６０（地階・階層不明）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706001', '東京都', '豊島区', '東池袋サンシャイン６０（１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706002', '東京都', '豊島区', '東池袋サンシャイン６０（２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706003', '東京都', '豊島区', '東池袋サンシャイン６０（３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706004', '東京都', '豊島区', '東池袋サンシャイン６０（４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706005', '東京都', '豊島区', '東池袋サンシャイン６０（５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706006', '東京都', '豊島区', '東池袋サンシャイン６０（６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706007', '東京都', '豊島区', '東池袋サンシャイン６０（７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706008', '東京都', '豊島区', '東池袋サンシャイン６０（８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706009', '東京都', '豊島区', '東池袋サンシャイン６０（９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706010', '東京都', '豊島区', '東池袋サンシャイン６０（１０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706011', '東京都', '豊島区', '東池袋サンシャイン６０（１１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706012', '東京都', '豊島区', '東池袋サンシャイン６０（１２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706013', '東京都', '豊島区', '東池袋サンシャイン６０（１３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706014', '東京都', '豊島区', '東池袋サンシャイン６０（１４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706015', '東京都', '豊島区', '東池袋サンシャイン６０（１５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706016', '東京都', '豊島区', '東池袋サンシャイン６０（１６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706017', '東京都', '豊島区', '東池袋サンシャイン６０（１７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706018', '東京都', '豊島区', '東池袋サンシャイン６０（１８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706019', '東京都', '豊島区', '東池袋サンシャイン６０（１９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706020', '東京都', '豊島区', '東池袋サンシャイン６０（２０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706021', '東京都', '豊島区', '東池袋サンシャイン６０（２１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706022', '東京都', '豊島区', '東池袋サンシャイン６０（２２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706023', '東京都', '豊島区', '東池袋サンシャイン６０（２３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706024', '東京都', '豊島区', '東池袋サンシャイン６０（２４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706025', '東京都', '豊島区', '東池袋サンシャイン６０（２５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706026', '東京都', '豊島区', '東池袋サンシャイン６０（２６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706027', '東京都', '豊島区', '東池袋サンシャイン６０（２７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706028', '東京都', '豊島区', '東池袋サンシャイン６０（２８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706029', '東京都', '豊島区', '東池袋サンシャイン６０（２９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706030', '東京都', '豊島区', '東池袋サンシャイン６０（３０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706031', '東京都', '豊島区', '東池袋サンシャイン６０（３１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706032', '東京都', '豊島区', '東池袋サンシャイン６０（３２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706033', '東京都', '豊島区', '東池袋サンシャイン６０（３３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706034', '東京都', '豊島区', '東池袋サンシャイン６０（３４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706035', '東京都', '豊島区', '東池袋サンシャイン６０（３５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706036', '東京都', '豊島区', '東池袋サンシャイン６０（３６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706037', '東京都', '豊島区', '東池袋サンシャイン６０（３７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706038', '東京都', '豊島区', '東池袋サンシャイン６０（３８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706039', '東京都', '豊島区', '東池袋サンシャイン６０（３９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706040', '東京都', '豊島区', '東池袋サンシャイン６０（４０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706041', '東京都', '豊島区', '東池袋サンシャイン６０（４１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706042', '東京都', '豊島区', '東池袋サンシャイン６０（４２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706043', '東京都', '豊島区', '東池袋サンシャイン６０（４３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706044', '東京都', '豊島区', '東池袋サンシャイン６０（４４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706045', '東京都', '豊島区', '東池袋サンシャイン６０（４５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706046', '東京都', '豊島区', '東池袋サンシャイン６０（４６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706047', '東京都', '豊島区', '東池袋サンシャイン６０（４７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706048', '東京都', '豊島区', '東池袋サンシャイン６０（４８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706049', '東京都', '豊島区', '東池袋サンシャイン６０（４９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706050', '東京都', '豊島区', '東池袋サンシャイン６０（５０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706051', '東京都', '豊島区', '東池袋サンシャイン６０（５１階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706052', '東京都', '豊島区', '東池袋サンシャイン６０（５２階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706053', '東京都', '豊島区', '東池袋サンシャイン６０（５３階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706054', '東京都', '豊島区', '東池袋サンシャイン６０（５４階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706055', '東京都', '豊島区', '東池袋サンシャイン６０（５５階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706056', '東京都', '豊島区', '東池袋サンシャイン６０（５６階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706057', '東京都', '豊島区', '東池袋サンシャイン６０（５７階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706058', '東京都', '豊島区', '東池袋サンシャイン６０（５８階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706059', '東京都', '豊島区', '東池袋サンシャイン６０（５９階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1706060', '東京都', '豊島区', '東池袋サンシャイン６０（６０階）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710022', '東京都', '豊島区', '南池袋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1700005', '東京都', '豊島区', '南大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710052', '東京都', '豊島区', '南長崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1710031', '東京都', '豊島区', '目白');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140000', '東京都', '北区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150045', '東京都', '北区', '赤羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150053', '東京都', '北区', '赤羽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150055', '東京都', '北区', '赤羽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150044', '東京都', '北区', '赤羽南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150052', '東京都', '北区', '赤羽北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150041', '東京都', '北区', '岩淵町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150051', '東京都', '北区', '浮間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140002', '東京都', '北区', '王子');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140022', '東京都', '北区', '王子本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140034', '東京都', '北区', '上十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140016', '東京都', '北区', '上中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150043', '東京都', '北区', '神谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140021', '東京都', '北区', '岸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150054', '東京都', '北区', '桐ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140005', '東京都', '北区', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150042', '東京都', '北区', '志茂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140033', '東京都', '北区', '十条台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140031', '東京都', '北区', '十条仲原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140011', '東京都', '北区', '昭和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140023', '東京都', '北区', '滝野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140014', '東京都', '北区', '田端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140012', '東京都', '北区', '田端新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140003', '東京都', '北区', '豊島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140015', '東京都', '北区', '中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140032', '東京都', '北区', '中十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1150056', '東京都', '北区', '西が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140024', '東京都', '北区', '西ケ原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140001', '東京都', '北区', '東十条');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140013', '東京都', '北区', '東田端');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1140004', '東京都', '北区', '堀船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160000', '東京都', '荒川区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160002', '東京都', '荒川区', '荒川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160011', '東京都', '荒川区', '西尾久');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160013', '東京都', '荒川区', '西日暮里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160012', '東京都', '荒川区', '東尾久');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160014', '東京都', '荒川区', '東日暮里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160001', '東京都', '荒川区', '町屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1160003', '東京都', '荒川区', '南千住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740000', '東京都', '板橋区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740044', '東京都', '板橋区', '相生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750092', '東京都', '板橋区', '赤塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750093', '東京都', '板橋区', '赤塚新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740051', '東京都', '板橋区', '小豆沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740055', '東京都', '板橋区', '泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730004', '東京都', '板橋区', '板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730002', '東京都', '板橋区', '稲荷台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740061', '東京都', '板橋区', '大原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730035', '東京都', '板橋区', '大谷口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730032', '東京都', '板橋区', '大谷口上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730031', '東京都', '板橋区', '大谷口北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730024', '東京都', '板橋区', '大山金井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730014', '東京都', '板橋区', '大山東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730033', '東京都', '板橋区', '大山西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730023', '東京都', '板橋区', '大山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730003', '東京都', '板橋区', '加賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740076', '東京都', '板橋区', '上板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730025', '東京都', '板橋区', '熊野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730037', '東京都', '板橋区', '小茂根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730034', '東京都', '板橋区', '幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730015', '東京都', '板橋区', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740043', '東京都', '板橋区', '坂下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740075', '東京都', '板橋区', '桜川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740053', '東京都', '板橋区', '清水町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740056', '東京都', '板橋区', '志村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750081', '東京都', '板橋区', '新河岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750085', '東京都', '板橋区', '大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750082', '東京都', '板橋区', '高島平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740074', '東京都', '板橋区', '東新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740071', '東京都', '板橋区', '常盤台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750083', '東京都', '板橋区', '徳丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730016', '東京都', '板橋区', '中板橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730005', '東京都', '板橋区', '仲宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740064', '東京都', '板橋区', '中台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730022', '東京都', '板橋区', '仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730026', '東京都', '板橋区', '中丸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750094', '東京都', '板橋区', '成増');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740045', '東京都', '板橋区', '西台（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750045', '東京都', '板橋区', '西台（２〜４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740052', '東京都', '板橋区', '蓮沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740046', '東京都', '板橋区', '蓮根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730013', '東京都', '板橋区', '氷川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740042', '東京都', '板橋区', '東坂下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740073', '東京都', '板橋区', '東山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740062', '東京都', '板橋区', '富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730011', '東京都', '板橋区', '双葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740041', '東京都', '板橋区', '舟渡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730001', '東京都', '板橋区', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740063', '東京都', '板橋区', '前野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750091', '東京都', '板橋区', '三園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730027', '東京都', '板橋区', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740072', '東京都', '板橋区', '南常盤台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740054', '東京都', '板橋区', '宮本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730036', '東京都', '板橋区', '向原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730012', '東京都', '板橋区', '大和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1730021', '東京都', '板橋区', '弥生町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1750084', '東京都', '板橋区', '四葉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1740065', '東京都', '板橋区', '若木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760000', '東京都', '練馬区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760005', '東京都', '練馬区', '旭丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790071', '東京都', '練馬区', '旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780061', '東京都', '練馬区', '大泉学園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780062', '東京都', '練馬区', '大泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790074', '東京都', '練馬区', '春日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770044', '東京都', '練馬区', '上石神井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770043', '東京都', '練馬区', '上石神井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790081', '東京都', '練馬区', '北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760022', '東京都', '練馬区', '向山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760004', '東京都', '練馬区', '小竹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760006', '東京都', '練馬区', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760002', '東京都', '練馬区', '桜台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770042', '東京都', '練馬区', '下石神井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770045', '東京都', '練馬区', '石神井台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770041', '東京都', '練馬区', '石神井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770052', '東京都', '練馬区', '関町東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770053', '東京都', '練馬区', '関町南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770051', '東京都', '練馬区', '関町北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770033', '東京都', '練馬区', '高野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790075', '東京都', '練馬区', '高松');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790073', '東京都', '練馬区', '田柄');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770054', '東京都', '練馬区', '立野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790076', '東京都', '練馬区', '土支田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760011', '東京都', '練馬区', '豊玉上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760013', '東京都', '練馬区', '豊玉中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760014', '東京都', '練馬区', '豊玉南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760012', '東京都', '練馬区', '豊玉北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760024', '東京都', '練馬区', '中村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760025', '東京都', '練馬区', '中村南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760023', '東京都', '練馬区', '中村北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780065', '東京都', '練馬区', '西大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780066', '東京都', '練馬区', '西大泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790082', '東京都', '練馬区', '錦');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760021', '東京都', '練馬区', '貫井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760001', '東京都', '練馬区', '練馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1760003', '東京都', '練馬区', '羽沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790085', '東京都', '練馬区', '早宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790072', '東京都', '練馬区', '光が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790084', '東京都', '練馬区', '氷川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780063', '東京都', '練馬区', '東大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770034', '東京都', '練馬区', '富士見台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1790083', '東京都', '練馬区', '平和台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1780064', '東京都', '練馬区', '南大泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770035', '東京都', '練馬区', '南田中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770031', '東京都', '練馬区', '三原台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1770032', '東京都', '練馬区', '谷原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200000', '東京都', '足立区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200012', '東京都', '足立区', '青井（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210012', '東京都', '足立区', '青井（４〜６丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200015', '東京都', '足立区', '足立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200005', '東京都', '足立区', '綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210823', '東京都', '足立区', '伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210807', '東京都', '足立区', '伊興本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210836', '東京都', '足立区', '入谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210834', '東京都', '足立区', '入谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210816', '東京都', '足立区', '梅島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230851', '東京都', '足立区', '梅田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230873', '東京都', '足立区', '扇');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200001', '東京都', '足立区', '大谷田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230844', '東京都', '足立区', '興野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200046', '東京都', '足立区', '小台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230861', '東京都', '足立区', '加賀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210055', '東京都', '足立区', '加平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210056', '東京都', '足立区', '北加平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230842', '東京都', '足立区', '栗原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200013', '東京都', '足立区', '弘道');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230872', '東京都', '足立区', '江北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210833', '東京都', '足立区', '古千谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210832', '東京都', '足立区', '古千谷本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210053', '東京都', '足立区', '佐野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230862', '東京都', '足立区', '皿沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230864', '東京都', '足立区', '鹿浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210815', '東京都', '足立区', '島根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230865', '東京都', '足立区', '新田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210051', '東京都', '足立区', '神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210057', '東京都', '足立区', '神明南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230852', '東京都', '足立区', '関原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200034', '東京都', '足立区', '千住');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200023', '東京都', '足立区', '千住曙町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200026', '東京都', '足立区', '千住旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200025', '東京都', '足立区', '千住東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200031', '東京都', '足立区', '千住大川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200037', '東京都', '足立区', '千住河原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200033', '東京都', '足立区', '千住寿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200045', '東京都', '足立区', '千住桜木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200024', '東京都', '足立区', '千住関屋町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200042', '東京都', '足立区', '千住龍田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200035', '東京都', '足立区', '千住中居町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200036', '東京都', '足立区', '千住仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200038', '東京都', '足立区', '千住橋戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200044', '東京都', '足立区', '千住緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200043', '東京都', '足立区', '千住宮元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200041', '東京都', '足立区', '千住元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200032', '東京都', '足立区', '千住柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210813', '東京都', '足立区', '竹の塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210054', '東京都', '足立区', '辰沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200011', '東京都', '足立区', '中央本町（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210011', '東京都', '足立区', '中央本町（３〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230871', '東京都', '足立区', '椿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200003', '東京都', '足立区', '東和');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210831', '東京都', '足立区', '舎人');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210837', '東京都', '足立区', '舎人公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210835', '東京都', '足立区', '舎人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200002', '東京都', '足立区', '中川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200014', '東京都', '足立区', '西綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230841', '東京都', '足立区', '西新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230843', '東京都', '足立区', '西新井栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230845', '東京都', '足立区', '西新井本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210824', '東京都', '足立区', '西伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210825', '東京都', '足立区', '西伊興町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210074', '東京都', '足立区', '西加平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210822', '東京都', '足立区', '西竹の塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210812', '東京都', '足立区', '西保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210061', '東京都', '足立区', '花畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200004', '東京都', '足立区', '東綾瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210801', '東京都', '足立区', '東伊興');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210063', '東京都', '足立区', '東保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210071', '東京都', '足立区', '東六月町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210075', '東京都', '足立区', '一ツ家');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200021', '東京都', '足立区', '日ノ出町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210076', '東京都', '足立区', '平野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210064', '東京都', '足立区', '保木間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210072', '東京都', '足立区', '保塚町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230874', '東京都', '足立区', '堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210062', '東京都', '足立区', '南花畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200047', '東京都', '足立区', '宮城');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210052', '東京都', '足立区', '六木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230853', '東京都', '足立区', '本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230854', '東京都', '足立区', '本木東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230856', '東京都', '足立区', '本木西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230855', '東京都', '足立区', '本木南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230857', '東京都', '足立区', '本木北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1230863', '東京都', '足立区', '谷在家');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200006', '東京都', '足立区', '谷中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1200022', '東京都', '足立区', '柳原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210814', '東京都', '足立区', '六月');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1210073', '東京都', '足立区', '六町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240000', '東京都', '葛飾区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250062', '東京都', '葛飾区', '青戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240022', '東京都', '葛飾区', '奥戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240003', '東京都', '葛飾区', 'お花茶屋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250042', '東京都', '葛飾区', '金町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250043', '東京都', '葛飾区', '金町浄水場');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250053', '東京都', '葛飾区', '鎌倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250061', '東京都', '葛飾区', '亀有');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240001', '東京都', '葛飾区', '小菅');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250052', '東京都', '葛飾区', '柴又');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250063', '東京都', '葛飾区', '白鳥');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240024', '東京都', '葛飾区', '新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250054', '東京都', '葛飾区', '高砂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240005', '東京都', '葛飾区', '宝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240012', '東京都', '葛飾区', '立石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250051', '東京都', '葛飾区', '新宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240002', '東京都', '葛飾区', '西亀有（１、２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250002', '東京都', '葛飾区', '西亀有（３、４丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240025', '東京都', '葛飾区', '西新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250031', '東京都', '葛飾区', '西水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250041', '東京都', '葛飾区', '東金町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240023', '東京都', '葛飾区', '東新小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240013', '東京都', '葛飾区', '東立石');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240004', '東京都', '葛飾区', '東堀切');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250033', '東京都', '葛飾区', '東水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240014', '東京都', '葛飾区', '東四つ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240021', '東京都', '葛飾区', '細田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240006', '東京都', '葛飾区', '堀切');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250032', '東京都', '葛飾区', '水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250034', '東京都', '葛飾区', '水元公園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1250035', '東京都', '葛飾区', '南水元');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1240011', '東京都', '葛飾区', '四つ木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320000', '東京都', '江戸川区', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320024', '東京都', '江戸川区', '一之江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340092', '東京都', '江戸川区', '一之江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340082', '東京都', '江戸川区', '宇喜田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320013', '東京都', '江戸川区', '江戸川（１〜３丁目、４丁目１〜１４番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340013', '東京都', '江戸川区', '江戸川（その他）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320022', '東京都', '江戸川区', '大杉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330042', '東京都', '江戸川区', '興宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330041', '東京都', '江戸川区', '上一色');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330054', '東京都', '江戸川区', '上篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340081', '東京都', '江戸川区', '北葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330051', '東京都', '江戸川区', '北小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330053', '東京都', '江戸川区', '北篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320034', '東京都', '江戸川区', '小松川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330073', '東京都', '江戸川区', '鹿骨');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330072', '東京都', '江戸川区', '鹿骨町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330061', '東京都', '江戸川区', '篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330064', '東京都', '江戸川区', '下篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340087', '東京都', '江戸川区', '清新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320021', '東京都', '江戸川区', '中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340083', '東京都', '江戸川区', '中葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320001', '東京都', '江戸川区', '新堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320023', '東京都', '江戸川区', '西一之江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340088', '東京都', '江戸川区', '西葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330057', '東京都', '江戸川区', '西小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320032', '東京都', '江戸川区', '西小松川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330055', '東京都', '江戸川区', '西篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320015', '東京都', '江戸川区', '西瑞江（２〜３丁目、４丁目３〜９番）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340015', '東京都', '江戸川区', '西瑞江（４丁目１〜２番・１０〜２７番、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340093', '東京都', '江戸川区', '二之江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320003', '東京都', '江戸川区', '春江町（１〜３丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340003', '東京都', '江戸川区', '春江町（４、５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340084', '東京都', '江戸川区', '東葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330052', '東京都', '江戸川区', '東小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320033', '東京都', '江戸川区', '東小松川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330063', '東京都', '江戸川区', '東篠崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330062', '東京都', '江戸川区', '東篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330071', '東京都', '江戸川区', '東松本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320014', '東京都', '江戸川区', '東瑞江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320035', '東京都', '江戸川区', '平井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340091', '東京都', '江戸川区', '船堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330044', '東京都', '江戸川区', '本一色');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320025', '東京都', '江戸川区', '松江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320031', '東京都', '江戸川区', '松島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330043', '東京都', '江戸川区', '松本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320011', '東京都', '江戸川区', '瑞江');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340085', '東京都', '江戸川区', '南葛西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330056', '東京都', '江戸川区', '南小岩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330065', '東京都', '江戸川区', '南篠崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1320002', '東京都', '江戸川区', '谷河内（１丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1330002', '東京都', '江戸川区', '谷河内（２丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1340086', '東京都', '江戸川区', '臨海町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920000', '東京都', '八王子市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920043', '東京都', '八王子市', '暁町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920083', '東京都', '八王子市', '旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920082', '東京都', '八王子市', '東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920032', '東京都', '八王子市', '石川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930814', '東京都', '八王子市', '泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930802', '東京都', '八王子市', '犬目町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920902', '東京都', '八王子市', '上野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920911', '東京都', '八王子市', '打越町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920024', '東京都', '八王子市', '宇津木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920915', '東京都', '八王子市', '宇津貫町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920013', '東京都', '八王子市', '梅坪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930841', '東京都', '八王子市', '裏高尾町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920056', '東京都', '八王子市', '追分町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920352', '東京都', '八王子市', '大塚');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930935', '東京都', '八王子市', '大船町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920034', '東京都', '八王子市', '大谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920062', '東京都', '八王子市', '大横町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920045', '東京都', '八王子市', '大和田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920054', '東京都', '八王子市', '小門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920025', '東京都', '八王子市', '尾崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920155', '東京都', '八王子市', '小津町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920353', '東京都', '八王子市', '鹿島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920004', '東京都', '八王子市', '加住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920914', '東京都', '八王子市', '片倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930815', '東京都', '八王子市', '叶谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930811', '東京都', '八王子市', '上壱分方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920156', '東京都', '八王子市', '上恩方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920151', '東京都', '八王子市', '上川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920373', '東京都', '八王子市', '上柚木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930801', '東京都', '八王子市', '川口町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930821', '東京都', '八王子市', '川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920913', '東京都', '八王子市', '北野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920906', '東京都', '八王子市', '北野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920912', '東京都', '八王子市', '絹ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930804', '東京都', '八王子市', '清川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930942', '東京都', '八王子市', '椚田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920023', '東京都', '八王子市', '久保山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920361', '東京都', '八王子市', '越野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930934', '東京都', '八王子市', '小比企町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920031', '東京都', '八王子市', '小宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920904', '東京都', '八王子市', '子安町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920012', '東京都', '八王子市', '左入町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930832', '東京都', '八王子市', '散田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920154', '東京都', '八王子市', '下恩方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920372', '東京都', '八王子市', '下柚木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930825', '東京都', '八王子市', '城山手');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920065', '東京都', '八王子市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930812', '東京都', '八王子市', '諏訪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930835', '東京都', '八王子市', '千人町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930931', '東京都', '八王子市', '台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930816', '東京都', '八王子市', '大楽寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920022', '東京都', '八王子市', '平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930844', '東京都', '八王子市', '高尾町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920033', '東京都', '八王子市', '高倉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920002', '東京都', '八王子市', '高月町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920011', '東京都', '八王子市', '滝山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930944', '東京都', '八王子市', '館町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920064', '東京都', '八王子市', '田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920003', '東京都', '八王子市', '丹木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930943', '東京都', '八王子市', '寺田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920073', '東京都', '八王子市', '寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920074', '東京都', '八王子市', '天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930843', '東京都', '八王子市', '廿里町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920001', '東京都', '八王子市', '戸吹町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920085', '東京都', '八王子市', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920041', '東京都', '八王子市', '中野上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920042', '東京都', '八王子市', '中野山王');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920015', '東京都', '八王子市', '中野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920374', '東京都', '八王子市', '中山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920907', '東京都', '八王子市', '長沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930824', '東京都', '八王子市', '長房町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920919', '東京都', '八王子市', '七国');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930831', '東京都', '八王子市', '並木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930803', '東京都', '八王子市', '楢原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920371', '東京都', '八王子市', '南陽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930842', '東京都', '八王子市', '西浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920917', '東京都', '八王子市', '西片倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920153', '東京都', '八王子市', '西寺方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930822', '東京都', '八王子市', '弐分方町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930941', '東京都', '八王子市', '狭間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920053', '東京都', '八王子市', '八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930845', '東京都', '八王子市', '初沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930834', '東京都', '八王子市', '東浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920351', '東京都', '八王子市', '東中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920918', '東京都', '八王子市', '兵衛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930836', '東京都', '八王子市', '日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920061', '東京都', '八王子市', '平岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920044', '東京都', '八王子市', '富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920363', '東京都', '八王子市', '別所');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920355', '東京都', '八王子市', '堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920052', '東京都', '八王子市', '本郷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920066', '東京都', '八王子市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920354', '東京都', '八王子市', '松が谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920362', '東京都', '八王子市', '松木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920021', '東京都', '八王子市', '丸山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920084', '東京都', '八王子市', '三崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920014', '東京都', '八王子市', 'みつい台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930932', '東京都', '八王子市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930846', '東京都', '八王子市', '南浅川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920364', '東京都', '八王子市', '南大沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920075', '東京都', '八王子市', '南新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920072', '東京都', '八王子市', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920916', '東京都', '八王子市', 'みなみ野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920005', '東京都', '八王子市', '宮下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920152', '東京都', '八王子市', '美山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920046', '東京都', '八王子市', '明神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930833', '東京都', '八王子市', 'めじろ台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930826', '東京都', '八王子市', '元八王子町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920051', '東京都', '八王子市', '元本郷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920063', '東京都', '八王子市', '元横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920055', '東京都', '八王子市', '八木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920016', '東京都', '八王子市', '谷野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930933', '東京都', '八王子市', '山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920375', '東京都', '八王子市', '鑓水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920071', '東京都', '八王子市', '八日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930823', '東京都', '八王子市', '横川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920081', '東京都', '八王子市', '横山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1930813', '東京都', '八王子市', '四谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1920903', '東京都', '八王子市', '万町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900000', '東京都', '立川市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900012', '東京都', '立川市', '曙町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900015', '東京都', '立川市', '泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900033', '東京都', '立川市', '一番町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900004', '東京都', '立川市', '柏町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900032', '東京都', '立川市', '上砂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900002', '東京都', '立川市', '幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900003', '東京都', '立川市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900023', '東京都', '立川市', '柴崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900031', '東京都', '立川市', '砂川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900011', '東京都', '立川市', '高松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900022', '東京都', '立川市', '錦町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900034', '東京都', '立川市', '西砂町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900021', '東京都', '立川市', '羽衣町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900013', '東京都', '立川市', '富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900014', '東京都', '立川市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900001', '東京都', '立川市', '若葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800000', '東京都', '武蔵野市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800004', '東京都', '武蔵野市', '吉祥寺本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800002', '東京都', '武蔵野市', '吉祥寺東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800003', '東京都', '武蔵野市', '吉祥寺南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800001', '東京都', '武蔵野市', '吉祥寺北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800023', '東京都', '武蔵野市', '境南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800005', '東京都', '武蔵野市', '御殿山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800022', '東京都', '武蔵野市', '境');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800021', '東京都', '武蔵野市', '桜堤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800014', '東京都', '武蔵野市', '関前');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800006', '東京都', '武蔵野市', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800013', '東京都', '武蔵野市', '西久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800012', '東京都', '武蔵野市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1800011', '東京都', '武蔵野市', '八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810000', '東京都', '三鷹市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810011', '東京都', '三鷹市', '井口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810001', '東京都', '三鷹市', '井の頭');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810015', '東京都', '三鷹市', '大沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810012', '東京都', '三鷹市', '上連雀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810003', '東京都', '三鷹市', '北野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810013', '東京都', '三鷹市', '下連雀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810004', '東京都', '三鷹市', '新川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810016', '東京都', '三鷹市', '深大寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810005', '東京都', '三鷹市', '中原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810014', '東京都', '三鷹市', '野崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1810002', '東京都', '三鷹市', '牟礼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980000', '東京都', '青梅市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980087', '東京都', '青梅市', '天ケ瀬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980023', '東京都', '青梅市', '今井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980021', '東京都', '青梅市', '今寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980088', '東京都', '青梅市', '裏宿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980086', '東京都', '青梅市', '大柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980003', '東京都', '青梅市', '小曾木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980041', '東京都', '青梅市', '勝沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980036', '東京都', '青梅市', '河辺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980081', '東京都', '青梅市', '上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980013', '東京都', '青梅市', '木野下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980005', '東京都', '青梅市', '黒沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980053', '東京都', '青梅市', '駒木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980172', '東京都', '青梅市', '沢井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980011', '東京都', '青梅市', '塩船');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980024', '東京都', '青梅市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980025', '東京都', '青梅市', '末広町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980084', '東京都', '青梅市', '住江町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980014', '東京都', '青梅市', '大門');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980085', '東京都', '青梅市', '滝ノ上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980043', '東京都', '青梅市', '千ケ瀬町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980002', '東京都', '青梅市', '富岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980051', '東京都', '青梅市', '友田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980082', '東京都', '青梅市', '仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980052', '東京都', '青梅市', '長淵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980001', '東京都', '青梅市', '成木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980044', '東京都', '青梅市', '西分町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980004', '東京都', '青梅市', '根ケ布');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980032', '東京都', '青梅市', '野上町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980063', '東京都', '青梅市', '梅郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980061', '東京都', '青梅市', '畑中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980042', '東京都', '青梅市', '東青梅');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980046', '東京都', '青梅市', '日向和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980015', '東京都', '青梅市', '吹上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980022', '東京都', '青梅市', '藤橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980171', '東京都', '青梅市', '二俣尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980083', '東京都', '青梅市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980174', '東京都', '青梅市', '御岳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980175', '東京都', '青梅市', '御岳山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980173', '東京都', '青梅市', '御岳本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980089', '東京都', '青梅市', '森下町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980031', '東京都', '青梅市', '師岡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980012', '東京都', '青梅市', '谷野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980064', '東京都', '青梅市', '柚木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980062', '東京都', '青梅市', '和田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830000', '東京都', '府中市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830003', '東京都', '府中市', '朝日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830012', '東京都', '府中市', '押立町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830021', '東京都', '府中市', '片町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830041', '東京都', '府中市', '北山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830056', '東京都', '府中市', '寿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830013', '東京都', '府中市', '小柳町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830014', '東京都', '府中市', '是政');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830054', '東京都', '府中市', '幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830051', '東京都', '府中市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830015', '東京都', '府中市', '清水が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830011', '東京都', '府中市', '白糸台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830052', '東京都', '府中市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830034', '東京都', '府中市', '住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830001', '東京都', '府中市', '浅間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830002', '東京都', '府中市', '多磨町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830053', '東京都', '府中市', '天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830043', '東京都', '府中市', '東芝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830046', '東京都', '府中市', '西原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830031', '東京都', '府中市', '西府町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830044', '東京都', '府中市', '日鋼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830036', '東京都', '府中市', '日新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830016', '東京都', '府中市', '八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830057', '東京都', '府中市', '晴見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830024', '東京都', '府中市', '日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830055', '東京都', '府中市', '府中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830033', '東京都', '府中市', '分梅町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830032', '東京都', '府中市', '本宿町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830027', '東京都', '府中市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830006', '東京都', '府中市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830026', '東京都', '府中市', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830022', '東京都', '府中市', '宮西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830023', '東京都', '府中市', '宮町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830045', '東京都', '府中市', '美好町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830042', '東京都', '府中市', '武蔵台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830004', '東京都', '府中市', '紅葉丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830025', '東京都', '府中市', '矢崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830035', '東京都', '府中市', '四谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1830005', '東京都', '府中市', '若松町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960000', '東京都', '昭島市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960025', '東京都', '昭島市', '朝日町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960033', '東京都', '昭島市', '東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960013', '東京都', '昭島市', '大神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960032', '東京都', '昭島市', '郷地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960011', '東京都', '昭島市', '上川原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960015', '東京都', '昭島市', '昭和町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960014', '東京都', '昭島市', '田中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960034', '東京都', '昭島市', '玉川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960023', '東京都', '昭島市', '築地町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960012', '東京都', '昭島市', 'つつじが丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960022', '東京都', '昭島市', '中神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960002', '東京都', '昭島市', '拝島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960031', '東京都', '昭島市', '福島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960003', '東京都', '昭島市', '松原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960004', '東京都', '昭島市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960001', '東京都', '昭島市', '美堀町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960024', '東京都', '昭島市', '宮沢町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1960021', '東京都', '昭島市', '武蔵野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820000', '東京都', '調布市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820004', '東京都', '調布市', '入間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820035', '東京都', '調布市', '上石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820007', '東京都', '調布市', '菊野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820022', '東京都', '調布市', '国領町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820026', '東京都', '調布市', '小島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820016', '東京都', '調布市', '佐須町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820014', '東京都', '調布市', '柴崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820034', '東京都', '調布市', '下石原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820012', '東京都', '調布市', '深大寺東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820013', '東京都', '調布市', '深大寺南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820011', '東京都', '調布市', '深大寺北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820017', '東京都', '調布市', '深大寺元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820002', '東京都', '調布市', '仙川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820023', '東京都', '調布市', '染地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820025', '東京都', '調布市', '多摩川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820021', '東京都', '調布市', '調布ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820036', '東京都', '調布市', '飛田給');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820006', '東京都', '調布市', '西つつじケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820032', '東京都', '調布市', '西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820031', '東京都', '調布市', '野水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820005', '東京都', '調布市', '東つつじケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820033', '東京都', '調布市', '富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820024', '東京都', '調布市', '布田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820001', '東京都', '調布市', '緑ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820015', '東京都', '調布市', '八雲台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1820003', '東京都', '調布市', '若葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940000', '東京都', '町田市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940211', '東京都', '町田市', '相原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940023', '東京都', '町田市', '旭町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950062', '東京都', '町田市', '大蔵町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940003', '東京都', '町田市', '小川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950064', '東京都', '町田市', '小野路町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940215', '東京都', '町田市', '小山ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940204', '東京都', '町田市', '小山田桜台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940212', '東京都', '町田市', '小山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950072', '東京都', '町田市', '金井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950071', '東京都', '町田市', '金井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940012', '東京都', '町田市', '金森');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940201', '東京都', '町田市', '上小山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940037', '東京都', '町田市', '木曽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940036', '東京都', '町田市', '木曽東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940033', '東京都', '町田市', '木曽町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940014', '東京都', '町田市', '高ケ坂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940202', '東京都', '町田市', '下小山田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950057', '東京都', '町田市', '真光寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950051', '東京都', '町田市', '真光寺町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940203', '東京都', '町田市', '図師町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940035', '東京都', '町田市', '忠生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940041', '東京都', '町田市', '玉川学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940001', '東京都', '町田市', 'つくし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950061', '東京都', '町田市', '鶴川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940004', '東京都', '町田市', '鶴間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940213', '東京都', '町田市', '常盤町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940021', '東京都', '町田市', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940044', '東京都', '町田市', '成瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940011', '東京都', '町田市', '成瀬が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940043', '東京都', '町田市', '成瀬台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940038', '東京都', '町田市', '根岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940034', '東京都', '町田市', '根岸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950053', '東京都', '町田市', '能ケ谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950063', '東京都', '町田市', '野津田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940013', '東京都', '町田市', '原町田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940042', '東京都', '町田市', '東玉川学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950056', '東京都', '町田市', '広袴');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950052', '東京都', '町田市', '広袴町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940032', '東京都', '町田市', '本町田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940031', '東京都', '町田市', '南大谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940002', '東京都', '町田市', '南つくし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940045', '東京都', '町田市', '南成瀬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950054', '東京都', '町田市', '三輪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950055', '東京都', '町田市', '三輪緑山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940022', '東京都', '町田市', '森野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950073', '東京都', '町田市', '薬師台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1940214', '東京都', '町田市', '矢部町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950075', '東京都', '町田市', '山崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1950074', '東京都', '町田市', '山崎町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840000', '東京都', '小金井市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840002', '東京都', '小金井市', '梶野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840005', '東京都', '小金井市', '桜町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840001', '東京都', '小金井市', '関野町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840012', '東京都', '小金井市', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840014', '東京都', '小金井市', '貫井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840015', '東京都', '小金井市', '貫井北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840011', '東京都', '小金井市', '東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840004', '東京都', '小金井市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840013', '東京都', '小金井市', '前原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1840003', '東京都', '小金井市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870000', '東京都', '小平市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870001', '東京都', '小平市', '大沼町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870031', '東京都', '小平市', '小川東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870035', '東京都', '小平市', '小川西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870032', '東京都', '小平市', '小川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870043', '東京都', '小平市', '学園東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870045', '東京都', '小平市', '学園西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870044', '東京都', '小平市', '喜平町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870034', '東京都', '小平市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870023', '東京都', '小平市', '上水新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870022', '東京都', '小平市', '上水本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870021', '東京都', '小平市', '上水南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870011', '東京都', '小平市', '鈴木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870024', '東京都', '小平市', 'たかの台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870025', '東京都', '小平市', '津田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870004', '東京都', '小平市', '天神町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870033', '東京都', '小平市', '中島町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870042', '東京都', '小平市', '仲町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870002', '東京都', '小平市', '花小金井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870003', '東京都', '小平市', '花小金井南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870041', '東京都', '小平市', '美園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870012', '東京都', '小平市', '御幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1870013', '東京都', '小平市', '回田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910000', '東京都', '日野市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910065', '東京都', '日野市', '旭が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910022', '東京都', '日野市', '新井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910021', '東京都', '日野市', '石田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910061', '東京都', '日野市', '大坂上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910034', '東京都', '日野市', '落川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910014', '東京都', '日野市', '上田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910015', '東京都', '日野市', '川辺堀之内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910001', '東京都', '日野市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910063', '東京都', '日野市', 'さくら町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910023', '東京都', '日野市', '下田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910002', '東京都', '日野市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910016', '東京都', '日野市', '神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910031', '東京都', '日野市', '高幡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910062', '東京都', '日野市', '多摩平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910051', '東京都', '日野市', '豊田（大字）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910053', '東京都', '日野市', '豊田（丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910055', '東京都', '日野市', '西平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910052', '東京都', '日野市', '東豊田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910054', '東京都', '日野市', '東平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910012', '東京都', '日野市', '日野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910003', '東京都', '日野市', '日野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910011', '東京都', '日野市', '日野本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910043', '東京都', '日野市', '平山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910064', '東京都', '日野市', '富士町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910042', '東京都', '日野市', '程久保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910024', '東京都', '日野市', '万願寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910032', '東京都', '日野市', '三沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910041', '東京都', '日野市', '南平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910013', '東京都', '日野市', '宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1910033', '東京都', '日野市', '百草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890000', '東京都', '東村山市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890002', '東京都', '東村山市', '青葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890001', '東京都', '東村山市', '秋津町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890011', '東京都', '東村山市', '恩多町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890003', '東京都', '東村山市', '久米川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890013', '東京都', '東村山市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890021', '東京都', '東村山市', '諏訪町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890026', '東京都', '東村山市', '多摩湖町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890022', '東京都', '東村山市', '野口町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890012', '東京都', '東村山市', '萩山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890024', '東京都', '東村山市', '富士見町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890014', '東京都', '東村山市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890023', '東京都', '東村山市', '美住町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1890025', '東京都', '東村山市', '廻田町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850000', '東京都', '国分寺市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850024', '東京都', '国分寺市', '泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850001', '東京都', '国分寺市', '北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850004', '東京都', '国分寺市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850036', '東京都', '国分寺市', '高木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850003', '東京都', '国分寺市', '戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850033', '東京都', '国分寺市', '内藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850005', '東京都', '国分寺市', '並木町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850013', '東京都', '国分寺市', '西恋ケ窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850035', '東京都', '国分寺市', '西町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850023', '東京都', '国分寺市', '西元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850034', '東京都', '国分寺市', '光町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850014', '東京都', '国分寺市', '東恋ケ窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850002', '東京都', '国分寺市', '東戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850022', '東京都', '国分寺市', '東元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850032', '東京都', '国分寺市', '日吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850031', '東京都', '国分寺市', '富士本');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850011', '東京都', '国分寺市', '本多');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850012', '東京都', '国分寺市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1850021', '東京都', '国分寺市', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860000', '東京都', '国立市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860013', '東京都', '国立市', '青柳');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860014', '東京都', '国立市', '石田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860012', '東京都', '国立市', '泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860001', '東京都', '国立市', '北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860004', '東京都', '国立市', '中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860005', '東京都', '国立市', '西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860002', '東京都', '国立市', '東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860003', '東京都', '国立市', '富士見台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860015', '東京都', '国立市', '矢川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1860011', '東京都', '国立市', '谷保');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970000', '東京都', '福生市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970024', '東京都', '福生市', '牛浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970012', '東京都', '福生市', '加美平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970005', '東京都', '福生市', '北田園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970003', '東京都', '福生市', '熊川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970002', '東京都', '福生市', '熊川二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970023', '東京都', '福生市', '志茂');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970021', '東京都', '福生市', '東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970011', '東京都', '福生市', '福生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970014', '東京都', '福生市', '福生二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970022', '東京都', '福生市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970004', '東京都', '福生市', '南田園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970013', '東京都', '福生市', '武蔵野台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970001', '東京都', '福生市', '横田基地内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010000', '東京都', '狛江市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010003', '東京都', '狛江市', '和泉本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010015', '東京都', '狛江市', '猪方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010005', '東京都', '狛江市', '岩戸南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010004', '東京都', '狛江市', '岩戸北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010016', '東京都', '狛江市', '駒井町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010012', '東京都', '狛江市', '中和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010011', '東京都', '狛江市', '西和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010001', '東京都', '狛江市', '西野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010014', '東京都', '狛江市', '東和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010002', '東京都', '狛江市', '東野川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2010013', '東京都', '狛江市', '元和泉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070000', '東京都', '東大和市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070033', '東京都', '東大和市', '芋窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070023', '東京都', '東大和市', '上北台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070011', '東京都', '東大和市', '清原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070002', '東京都', '東大和市', '湖畔');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070022', '東京都', '東大和市', '桜が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070003', '東京都', '東大和市', '狭山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070004', '東京都', '東大和市', '清水');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070012', '東京都', '東大和市', '新堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070032', '東京都', '東大和市', '蔵敷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070005', '東京都', '東大和市', '高木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070021', '東京都', '東大和市', '立野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070001', '東京都', '東大和市', '多摩湖');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070015', '東京都', '東大和市', '中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070016', '東京都', '東大和市', '仲原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070031', '東京都', '東大和市', '奈良橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070014', '東京都', '東大和市', '南街');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2070013', '東京都', '東大和市', '向原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040000', '東京都', '清瀬市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040002', '東京都', '清瀬市', '旭が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040024', '東京都', '清瀬市', '梅園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040013', '東京都', '清瀬市', '上清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040001', '東京都', '清瀬市', '下宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040011', '東京都', '清瀬市', '下清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040023', '東京都', '清瀬市', '竹丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040012', '東京都', '清瀬市', '中清戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040003', '東京都', '清瀬市', '中里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040004', '東京都', '清瀬市', '野塩');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040022', '東京都', '清瀬市', '松山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2040021', '東京都', '清瀬市', '元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030000', '東京都', '東久留米市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030001', '東京都', '東久留米市', '上の原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030021', '東京都', '東久留米市', '学園町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030003', '東京都', '東久留米市', '金山町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030051', '東京都', '東久留米市', '小山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030052', '東京都', '東久留米市', '幸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030043', '東京都', '東久留米市', '下里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030013', '東京都', '東久留米市', '新川町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030002', '東京都', '東久留米市', '神宝町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030012', '東京都', '東久留米市', '浅間町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030011', '東京都', '東久留米市', '大門町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030033', '東京都', '東久留米市', '滝山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030054', '東京都', '東久留米市', '中央町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030041', '東京都', '東久留米市', '野火止');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030042', '東京都', '東久留米市', '八幡町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030004', '東京都', '東久留米市', '氷川台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030014', '東京都', '東久留米市', '東本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030022', '東京都', '東久留米市', 'ひばりが丘団地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030053', '東京都', '東久留米市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030032', '東京都', '東久留米市', '前沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030023', '東京都', '東久留米市', '南沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030031', '東京都', '東久留米市', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030044', '東京都', '東久留米市', '柳窪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2030034', '東京都', '東久留米市', '弥生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080000', '東京都', '武蔵村山市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080023', '東京都', '武蔵村山市', '伊奈平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080022', '東京都', '武蔵村山市', '榎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080013', '東京都', '武蔵村山市', '大南');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080011', '東京都', '武蔵村山市', '学園');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080031', '東京都', '武蔵村山市', '岸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080034', '東京都', '武蔵村山市', '残堀');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080002', '東京都', '武蔵村山市', '神明');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080003', '東京都', '武蔵村山市', '中央');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080001', '東京都', '武蔵村山市', '中藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080035', '東京都', '武蔵村山市', '中原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080004', '東京都', '武蔵村山市', '本町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080033', '東京都', '武蔵村山市', '三ツ木（大字）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080032', '東京都', '武蔵村山市', '三ツ木（１〜５丁目）');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080021', '東京都', '武蔵村山市', '三ツ藤');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2080012', '東京都', '武蔵村山市', '緑が丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060000', '東京都', '多摩市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060041', '東京都', '多摩市', '愛宕');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060002', '東京都', '多摩市', '一ノ宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060033', '東京都', '多摩市', '落合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060015', '東京都', '多摩市', '落川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060012', '東京都', '多摩市', '貝取');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060035', '東京都', '多摩市', '唐木田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060014', '東京都', '多摩市', '乞田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060013', '東京都', '多摩市', '桜ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060042', '東京都', '多摩市', '山王下');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060024', '東京都', '多摩市', '諏訪');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060011', '東京都', '多摩市', '関戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060034', '東京都', '多摩市', '鶴牧');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060031', '東京都', '多摩市', '豊ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060036', '東京都', '多摩市', '中沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060025', '東京都', '多摩市', '永山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060003', '東京都', '多摩市', '東寺方');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060022', '東京都', '多摩市', '聖ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060023', '東京都', '多摩市', '馬引沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060032', '東京都', '多摩市', '南野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060004', '東京都', '多摩市', '百草');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060021', '東京都', '多摩市', '連光寺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060001', '東京都', '多摩市', '和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060000', '東京都', '稲城市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060801', '東京都', '稲城市', '大丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060811', '東京都', '稲城市', '押立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060803', '東京都', '稲城市', '向陽台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060822', '東京都', '稲城市', '坂浜');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060821', '東京都', '稲城市', '長峰');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060802', '東京都', '稲城市', '東長沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060823', '東京都', '稲城市', '平尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060804', '東京都', '稲城市', '百村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060812', '東京都', '稲城市', '矢野口');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2060824', '東京都', '稲城市', '若葉台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050000', '東京都', '羽村市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050001', '東京都', '羽村市', '小作台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050021', '東京都', '羽村市', '川崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050011', '東京都', '羽村市', '五ノ神');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050002', '東京都', '羽村市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050023', '東京都', '羽村市', '神明台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050024', '東京都', '羽村市', '玉川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050012', '東京都', '羽村市', '羽');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050016', '東京都', '羽村市', '羽加美');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050015', '東京都', '羽村市', '羽中');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050014', '東京都', '羽村市', '羽東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050017', '東京都', '羽村市', '羽西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050013', '東京都', '羽村市', '富士見平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050022', '東京都', '羽村市', '双葉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2050003', '東京都', '羽村市', '緑ケ丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900100', '東京都', 'あきる野市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970804', '東京都', 'あきる野市', '秋川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970828', '東京都', 'あきる野市', '秋留');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900155', '東京都', 'あきる野市', '網代');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970827', '東京都', 'あきる野市', '油平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970825', '東京都', 'あきる野市', '雨間');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900164', '東京都', 'あきる野市', '五日市');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900142', '東京都', 'あきる野市', '伊奈');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900161', '東京都', 'あきる野市', '入野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900143', '東京都', 'あきる野市', '上ノ台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970826', '東京都', 'あきる野市', '牛沼');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970821', '東京都', 'あきる野市', '小川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970822', '東京都', 'あきる野市', '小川東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900174', '東京都', 'あきる野市', '乙津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970832', '東京都', 'あきる野市', '上代継');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970824', '東京都', 'あきる野市', '切欠');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970802', '東京都', 'あきる野市', '草花');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900165', '東京都', 'あきる野市', '小中野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900153', '東京都', 'あきる野市', '小峰台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900151', '東京都', 'あきる野市', '小和田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900162', '東京都', 'あきる野市', '三内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970831', '東京都', 'あきる野市', '下代継');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970801', '東京都', 'あきる野市', '菅生');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970803', '東京都', 'あきる野市', '瀬戸岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900154', '東京都', 'あきる野市', '高尾');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900163', '東京都', 'あきる野市', '舘谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900166', '東京都', 'あきる野市', '舘谷台');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900173', '東京都', 'あきる野市', '戸倉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900152', '東京都', 'あきる野市', '留原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970814', '東京都', 'あきる野市', '二宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970815', '東京都', 'あきる野市', '二宮東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970823', '東京都', 'あきる野市', '野辺');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970811', '東京都', 'あきる野市', '原小宮');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970834', '東京都', 'あきる野市', '引田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970812', '東京都', 'あきる野市', '平沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970816', '東京都', 'あきる野市', '平沢西');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970813', '東京都', 'あきる野市', '平沢東');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900172', '東京都', 'あきる野市', '深沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1970833', '東京都', 'あきる野市', '渕上');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900144', '東京都', 'あきる野市', '山田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900171', '東京都', 'あきる野市', '養沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900141', '東京都', 'あきる野市', '横沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020000', '東京都', '西東京市', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020011', '東京都', '西東京市', '泉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880003', '東京都', '西東京市', '北原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020003', '東京都', '西東京市', '北町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020006', '東京都', '西東京市', '栄町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880014', '東京都', '西東京市', '芝久保町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020004', '東京都', '西東京市', '下保谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020023', '東京都', '西東京市', '新町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020005', '東京都', '西東京市', '住吉町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880011', '東京都', '西東京市', '田無町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020013', '東京都', '西東京市', '中町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880004', '東京都', '西東京市', '西原町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020012', '東京都', '西東京市', '東町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020021', '東京都', '西東京市', '東伏見');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020001', '東京都', '西東京市', 'ひばりが丘');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020002', '東京都', '西東京市', 'ひばりが丘北');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020014', '東京都', '西東京市', '富士町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020015', '東京都', '西東京市', '保谷町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880002', '東京都', '西東京市', '緑町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880012', '東京都', '西東京市', '南町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880013', '東京都', '西東京市', '向台町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('2020022', '東京都', '西東京市', '柳沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1880001', '東京都', '西東京市', '谷戸町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901200', '東京都', '西多摩郡瑞穂町', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901211', '東京都', '西多摩郡瑞穂町', '石畑');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901202', '東京都', '西多摩郡瑞穂町', '駒形富士山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901203', '東京都', '西多摩郡瑞穂町', '高根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901212', '東京都', '西多摩郡瑞穂町', '殿ケ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901232', '東京都', '西多摩郡瑞穂町', '長岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901233', '東京都', '西多摩郡瑞穂町', '長岡下師岡');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901231', '東京都', '西多摩郡瑞穂町', '長岡長谷部');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901234', '東京都', '西多摩郡瑞穂町', '長岡藤橋');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901201', '東京都', '西多摩郡瑞穂町', '二本木');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901221', '東京都', '西多摩郡瑞穂町', '箱根ケ崎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901222', '東京都', '西多摩郡瑞穂町', '箱根ケ崎東松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901223', '東京都', '西多摩郡瑞穂町', '箱根ケ崎西松原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901204', '東京都', '西多摩郡瑞穂町', '富士山栗原新田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901224', '東京都', '西多摩郡瑞穂町', '南平');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901213', '東京都', '西多摩郡瑞穂町', '武蔵');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1901214', '東京都', '西多摩郡瑞穂町', 'むさし野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900100', '東京都', '西多摩郡日の出町', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900181', '東京都', '西多摩郡日の出町', '大久野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900182', '東京都', '西多摩郡日の出町', '平井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900200', '東京都', '西多摩郡檜原村', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900204', '東京都', '西多摩郡檜原村', '小沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900221', '東京都', '西多摩郡檜原村', '数馬');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900203', '東京都', '西多摩郡檜原村', '神戸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900212', '東京都', '西多摩郡檜原村', '上元郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900201', '東京都', '西多摩郡檜原村', '倉掛');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900213', '東京都', '西多摩郡檜原村', '下元郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900223', '東京都', '西多摩郡檜原村', '南郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900205', '東京都', '西多摩郡檜原村', '樋里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900202', '東京都', '西多摩郡檜原村', '藤原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900222', '東京都', '西多摩郡檜原村', '人里');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900211', '東京都', '西多摩郡檜原村', '三都郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1900214', '東京都', '西多摩郡檜原村', '本宿');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980000', '東京都', '西多摩郡奥多摩町', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980213', '東京都', '西多摩郡奥多摩町', '海沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980103', '東京都', '西多摩郡奥多摩町', '梅沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980101', '東京都', '西多摩郡奥多摩町', '大丹波');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980102', '東京都', '西多摩郡奥多摩町', '川井');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980225', '東京都', '西多摩郡奥多摩町', '川野');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980224', '東京都', '西多摩郡奥多摩町', '河内');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980105', '東京都', '西多摩郡奥多摩町', '小丹波');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980222', '東京都', '西多摩郡奥多摩町', '境');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980107', '東京都', '西多摩郡奥多摩町', '白丸');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980106', '東京都', '西多摩郡奥多摩町', '棚沢');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980104', '東京都', '西多摩郡奥多摩町', '丹三郎');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980221', '東京都', '西多摩郡奥多摩町', '留浦');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980211', '東京都', '西多摩郡奥多摩町', '日原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980223', '東京都', '西多摩郡奥多摩町', '原');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1980212', '東京都', '西多摩郡奥多摩町', '氷川');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000100', '東京都', '大島町', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000102', '東京都', '大島町', '岡田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000211', '東京都', '大島町', '差木地');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000103', '東京都', '大島町', '泉津');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000104', '東京都', '大島町', '野増');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000212', '東京都', '大島町', '波浮港');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000101', '東京都', '大島町', '元町');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000301', '東京都', '利島村', '利島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000400', '東京都', '新島村', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000511', '東京都', '新島村', '式根島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000402', '東京都', '新島村', '本村');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000401', '東京都', '新島村', '若郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1000601', '東京都', '神津島村', '神津島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001100', '東京都', '三宅島三宅村', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001212', '東京都', '三宅島三宅村', '阿古');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001103', '東京都', '三宅島三宅村', '伊ケ谷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001102', '東京都', '三宅島三宅村', '伊豆');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001213', '東京都', '三宅島三宅村', '雄山');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001101', '東京都', '三宅島三宅村', '神着');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001211', '東京都', '三宅島三宅村', '坪田');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001301', '東京都', '御蔵島村', '御蔵島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001400', '東京都', '八丈島八丈町', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001401', '東京都', '八丈島八丈町', '大賀郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001621', '東京都', '八丈島八丈町', '樫立');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001622', '東京都', '八丈島八丈町', '末吉');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001623', '東京都', '八丈島八丈町', '中之郷');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001511', '東京都', '八丈島八丈町', '三根');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1001701', '東京都', '青ヶ島村', '青ヶ島村一円');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002100', '東京都', '小笠原村', '以下に掲載がない場合');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002101', '東京都', '小笠原村', '父島');
INSERT INTO postalcode(f3, f7, f8, f9)
VALUES ('1002211', '東京都', '小笠原村', '母島');
