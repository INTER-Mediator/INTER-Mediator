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

DROP DATABASE IF EXISTS test_e_db;
CREATE DATABASE test_e_db CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE test_e_db;

DROP TABLE IF EXISTS asset;
CREATE TABLE asset	(
	asset_id	INT AUTO_INCREMENT,
	name		VARCHAR(40),
	category	VARCHAR(40),
    manifacture VARCHAR(40),
    productinfo VARCHAR(40),
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
	name		VARCHAR(40),
    section     VARCHAR(40),
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

INSERT asset SET asset_id=11,category='Individual',name='MacBook Air[1]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1','1904-01-01';
INSERT asset SET asset_id=12,category='Individual',name='MacBook Air[2]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1','1904-01-01';
INSERT asset SET asset_id=13,category='Individual',name='MacBook Air[3]',manifacture='Apple',productinfo='2012/250GB/4GB',purchase='2012-8-1','1904-01-01';
INSERT asset SET asset_id=14,category='Individual',name='VAIO type A[1]',manifacture='Sony',productinfo='VGN-AR85S',purchase='2008-6-12',discard='2012-2-2';
INSERT asset SET asset_id=15,category='Individual',name='VAIO type A[2]',manifacture='Sony',productinfo='VGN-AR85S',purchase='2008-6-12','1904-01-01';
INSERT asset SET asset_id=16,category='Shared',name='Projector',manifacture='Epson',productinfo='EB-460T',purchase='2010-11-23','1904-01-01';
INSERT asset SET asset_id=17,category='Shared',name='Whiteboard[1]',manifacture='Unknown',productinfo='Unknown',purchase=\N,discard='2005-3-22';
INSERT asset SET asset_id=18,category='Shared',name='Whiteboard[2]',manifacture='Unknown',productinfo='Unknown',purchase=\N,discard='2005-3-22';
INSERT asset SET asset_id=19,category='Shared',name='Humidifier',manifacture='Sharp Co.',productinfo='Plasma Cluster Humidifier',purchase='2011-12-2','1904-01-01';
INSERT asset SET asset_id=20,category='Shared',name='Ait Conditioner (Office)',manifacture='',productinfo='',purchase=\N,'1904-01-01';
INSERT asset SET asset_id=21,category='Shared',name='Air Conditioner (Mtg Room)',manifacture='',productinfo='',purchase=\N,'1904-01-01';
INSERT asset SET asset_id=22,category='Shared',name='Cell Phone Docomo',manifacture='Kyocera Co.',productinfo='P904i',purchase='2010-4-4',discard='2012-3-3';
INSERT asset SET asset_id=23,category='Individual',name='Cell Phone au',manifacture='Sharp Co.',productinfo='SH001',purchase='2012-3-3',discard='2012-10-1';
INSERT asset SET asset_id=24,category='Individual',name='Cell Phone Softbank[1]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';
INSERT asset SET asset_id=25,category='Individual',name='Cell Phone Softbank[2]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';
INSERT asset SET asset_id=26,category='Individual',name='Cell Phone Softbank[3]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';
INSERT asset SET asset_id=27,category='Individual',name='Cell Phone Softbank[4]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';
INSERT asset SET asset_id=28,category='Individual',name='Cell Phone Softbank[5]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';
INSERT asset SET asset_id=29,category='Individual',name='Cell Phone Softbank[6]',manifacture='Apple',productinfo='iPhone 5',purchase='2012-10-1','1904-01-01';

INSERT staff SET staff_id=101,name='Tanaka, Jiro',section='CEO';
INSERT staff SET staff_id=102,name='Yamamoto, Saburo',section='COO';
INSERT staff SET staff_id=103,name='Kitano, Rokuro',section='Manager Sales';
INSERT staff SET staff_id=104,name='Toubara, Nanami',section='Sales';
INSERT staff SET staff_id=105,name='Uchimura, Kuro',section='Sales';
INSERT staff SET staff_id=106,name='Suganuma, Kenichiro',section='Manager R&D';
INSERT staff SET staff_id=107,name='Nishimori, Yuta',section='R&D';
INSERT staff SET staff_id=108,name='Nomura, Akinori',section='R&D';
INSERT staff SET staff_id=109,name='Tsujino, Hitosh',section='R&D';

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

INSERT category SET category_id=1,name='Indivisual';
INSERT category SET category_id=2,name='Shared';
