/*
 * INTER-Mediator Sample for our software paper
 * Copyright (c) 2010-2016 INTER-Mediator Directive Committee, All rights reserved.

This schema file is for the sample of INTER-Mediator using MySQL, encoded by UTF-8.

Example:
$ mysql -u root -p < readme_schema.txt
Enter password:

*/
SET NAMES 'utf8';

/***************************** ATTENTION *****************************
 * If you execute this schema twice or more, remove # of the following 'DROP USER' line.
 *********************************************************************/
#DROP USER 'web'@'localhost';
#CREATE USER 'web'@'localhost' IDENTIFIED BY 'password';

#DROP DATABASE IF EXISTS test_db;
#CREATE DATABASE test_db CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE test_db;

DROP TABLE IF EXISTS asset;
CREATE TABLE asset	(
	asset_id	INT AUTO_INCREMENT,
	name		VARCHAR(20),
	category	VARCHAR(20),
    manifacture VARCHAR(20),
    productinfo VARCHAR(20),
	purchase    DATE,
    discard     DATE,
    memo	    TEXT,
	PRIMARY     KEY(asset_id)
)		CHARACTER SET utf8,
		COLLATE utf8_unicode_ci
		ENGINE=InnoDB;
CREATE UNIQUE INDEX asset_id ON asset (asset_id);
CREATE INDEX asset_purchase ON asset (purchase);
CREATE INDEX asset_discard ON asset (discard);

DROP TABLE IF EXISTS rent;
CREATE TABLE rent	(
	rent_id		INT AUTO_INCREMENT,
	asset_id  	INT,
	staff_id    INT,
	rentdate    DATE,
    backdate    DATE,
    memo	    TEXT,
	PRIMARY     KEY(rent_id)
)		CHARACTER SET utf8,
		COLLATE utf8_unicode_ci
		ENGINE=InnoDB;
CREATE UNIQUE INDEX rent_id ON rent (rent_id);
CREATE INDEX rent_rentdate  ON rent (rentdate);
CREATE INDEX rent_backdate  ON rent (backdate);
CREATE INDEX rent_asset_id  ON rent (asset_id);
CREATE INDEX rent_staff_id  ON rent (staff_id);

DROP TABLE IF EXISTS staff;
CREATE TABLE staff	(
	staff_id	INT  AUTO_INCREMENT,
	name		VARCHAR(20),
    section     VARCHAR(20),
    memo	    TEXT,
	PRIMARY     KEY(staff_id)
)		CHARACTER SET utf8,
		COLLATE utf8_unicode_ci
		ENGINE=InnoDB;
CREATE UNIQUE INDEX staff_id ON staff (staff_id);

DROP TABLE IF EXISTS category;
CREATE TABLE category	(
	category_id	INT  AUTO_INCREMENT,
	name		VARCHAR(20),
	PRIMARY     KEY(category_id)
)		CHARACTER SET utf8,
		COLLATE utf8_unicode_ci
		ENGINE=InnoDB;
CREATE UNIQUE INDEX category_id ON category (category_id);

INSERT asset SET asset_id=11,category='個人用',name='MacBook Air[1]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1',discard='1904-01-01';
INSERT asset SET asset_id=12,category='個人用',name='MacBook Air[2]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1',discard='1904-01-01';
INSERT asset SET asset_id=13,category='個人用',name='MacBook Air[3]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1',discard='1904-01-01';
INSERT asset SET asset_id=14,category='個人用',name='VAIO type A[1]',manifacture='ソニー',productinfo='VGN-AR85S',purchase='2008-6-12',discard='2012-2-2';
INSERT asset SET asset_id=15,category='個人用',name='VAIO type A[2]',manifacture='ソニー',productinfo='VGN-AR85S',purchase='2008-6-12',discard='1904-01-01';
INSERT asset SET asset_id=16,category='共用',name='プロジェクタ',manifacture='エプソン',productinfo='EB-460T',purchase='2010-11-23',discard='1904-01-01';
INSERT asset SET asset_id=17,category='共用',name='ホワイトボード[1]',manifacture='不明',productinfo='不明',purchase=\N,discard='2005-3-22';
INSERT asset SET asset_id=18,category='共用',name='ホワイトボード[2]',manifacture='不明',productinfo='不明',purchase=\N,discard='2005-3-22';
INSERT asset SET asset_id=19,category='共用',name='加湿器',manifacture='シャープ',productinfo='プラズマクラスター加湿器',purchase='2011-12-2',discard='1904-01-01';
INSERT asset SET asset_id=20,category='共用',name='事務室エアコン',manifacture='',productinfo='',purchase=\N,discard='1904-01-01';
INSERT asset SET asset_id=21,category='共用',name='会議室エアコン',manifacture='',productinfo='',purchase=\N,discard='1904-01-01';
INSERT asset SET asset_id=22,category='共用',name='携帯電話ドコモ',manifacture='京セラ',productinfo='P904i',purchase='2010-4-4',discard='2012-3-3';
INSERT asset SET asset_id=23,category='個人用',name='携帯電話au',manifacture='シャープ',productinfo='SH001',purchase='2012-3-3',discard='2012-10-1';
INSERT asset SET asset_id=24,category='個人用',name='携帯電話Softbank[1]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';
INSERT asset SET asset_id=25,category='個人用',name='携帯電話Softbank[2]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';
INSERT asset SET asset_id=26,category='個人用',name='携帯電話Softbank[3]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';
INSERT asset SET asset_id=27,category='個人用',name='携帯電話Softbank[4]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';
INSERT asset SET asset_id=28,category='個人用',name='携帯電話Softbank[5]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';
INSERT asset SET asset_id=29,category='個人用',name='携帯電話Softbank[6]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1',discard='1904-01-01';

INSERT staff SET staff_id=101,name='田中次郎',section='代表取締役社長';
INSERT staff SET staff_id=102,name='山本三郎',section='専務取締役';
INSERT staff SET staff_id=103,name='北野六郎',section='営業部長';
INSERT staff SET staff_id=104,name='東原七海',section='営業部';
INSERT staff SET staff_id=105,name='内村久郎',section='営業部';
INSERT staff SET staff_id=106,name='菅沼健一郎',section='開発部長';
INSERT staff SET staff_id=107,name='西森裕太',section='開発部';
INSERT staff SET staff_id=108,name='野村顕昭',section='開発部';
INSERT staff SET staff_id=109,name='辻野均',section='開発部';

INSERT rent	SET asset_id=22,staff_id=101,rentdate='2010-4-4',backdate='2012-3-3';
INSERT rent	SET asset_id=23,staff_id=101,rentdate='2012-3-3',backdate='2012-10-1';
INSERT rent	SET asset_id=24,staff_id=101,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=25,staff_id=102,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=26,staff_id=103,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=27,staff_id=106,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=28,staff_id=107,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=29,staff_id=108,rentdate='2012-10-1',backdate=\N;
INSERT rent	SET asset_id=14,staff_id=106,rentdate='2008-6-12',backdate='2012-2-2';
INSERT rent	SET asset_id=15,staff_id=106,rentdate='2008-6-12',backdate='2011-3-31';
INSERT rent	SET asset_id=15,staff_id=109,rentdate='2011-4-6',backdate=\N;
INSERT rent	SET asset_id=11,staff_id=107,rentdate='2012-8-1',backdate=\N;
INSERT rent	SET asset_id=12,staff_id=108,rentdate='2012-8-1',backdate=\N;
INSERT rent	SET asset_id=13,staff_id=109,rentdate='2012-8-1',backdate=\N;
INSERT rent	SET asset_id=16,staff_id=109,rentdate='2010-11-29',backdate='2010-11-29';
INSERT rent	SET asset_id=16,staff_id=105,rentdate='2010-12-29',backdate='2010-12-29';
INSERT rent	SET asset_id=16,staff_id=103,rentdate='2011-2-28',backdate='2011-3-29';
INSERT rent	SET asset_id=16,staff_id=104,rentdate='2011-5-29',backdate='2011-6-3';
INSERT rent	SET asset_id=16,staff_id=109,rentdate='2011-8-9',backdate='2011-8-31';
INSERT rent	SET asset_id=16,staff_id=102,rentdate='2011-9-29',backdate='2011-9-30';
INSERT rent	SET asset_id=16,staff_id=101,rentdate='2011-12-2',backdate='2011-12-9';
INSERT rent	SET asset_id=16,staff_id=108,rentdate='2012-1-29',backdate='2012-1-31';
INSERT rent	SET asset_id=16,staff_id=108,rentdate='2012-4-29',backdate='2012-5-10';
INSERT rent	SET asset_id=16,staff_id=109,rentdate='2012-6-29',backdate='2012-7-29';

INSERT category SET category_id=1,name='個人用';
INSERT category SET category_id=2,name='共用';
