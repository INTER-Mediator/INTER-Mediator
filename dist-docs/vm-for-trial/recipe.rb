# Recipe file of Itamae for Ubuntu Server 14.04.2, CentOS 6.6 and CentOS 7
#   How to test using Serverspec 2 after provisioning ("vargrant up"):
#   - Install Ruby on the host of VM (You don't need installing Ruby on OS X usually)
#   - Install Serverspec 2 on the host of VM ("gem install serverspec")
#     See detail: http://serverspec.org/
#   - Change directory to "vm-for-trial" directory on the host of VM
#   - Run "rake spec" on the host of VM

WEBROOT = "/var/www/html"

IMROOT = "#{WEBROOT}/INTER-Mediator"
IMSUPPORT = "#{IMROOT}/INTER-Mediator-Support"
IMSAMPLE = "#{IMROOT}/Samples"
IMUNITTEST = "#{IMROOT}/INTER-Mediator-UnitTest"
IMDISTDOC = "#{IMROOT}/dist-docs"
IMVMROOT = "#{IMROOT}/dist-docs/vm-for-trial"
SMBCONF = "/etc/samba/smb.conf"


if node[:platform] == 'redhat' && node[:platform_version].to_f < 6
  #file '/etc/resolv.conf' do
  #  content 'nameserver 192.168.1.1'
  #end
end

execute 'groupadd im-developer' do
  command 'groupadd im-developer'
end

execute 'usermod -a -G im-developer developer' do
  command 'usermod -a -G im-developer developer'
end

if node[:platform] == 'ubuntu'
  execute 'usermod -a -G im-developer www-data' do
    command 'usermod -a -G im-developer www-data'
  end
elsif node[:platform] == 'redhat'
  package 'httpd' do
    action :install
  end
  service 'httpd' do
    action [ :enable, :start ]
  end
  execute 'usermod -a -G im-developer apache' do
    command 'usermod -a -G im-developer apache'
  end
end

if node[:platform] == 'redhat'
  package 'postgresql-server' do
    action :install
  end
  if node[:platform_version].to_f < 6
    execute 'sudo su - postgres -c "initdb --encoding=UTF8 --no-locale"' do
      command 'sudo su - postgres -c "initdb --encoding=UTF8 --no-locale"'
    end
  else
    execute 'service postgresql initdb' do
      command 'service postgresql initdb'
    end
  end
  service 'postgresql' do
    action [ :enable, :start ]
  end
end

user 'postgres' do
  password '$6$inter-mediator$kEUWd5ZQNPEfNF7CPzRMDoHhmz67rgJTmDbUsJ3AL35vV3c5sGk9ml2kLRj.2z5BkygH7SS2E549qTB2FYs6S/'
end

if node[:platform] == 'ubuntu'
  file '/etc/mysql/conf.d/im.cnf' do
    content <<-EOF
character-set-server=utf8mb4
skip-character-set-client-handshake

[client]
default-character-set=utf8mb4

[mysqldump]
default-character-set=utf8mb4

[mysql]
default-character-set=utf8mb4'
EOF
  end
elsif node[:platform] == 'redhat'
  if node[:platform_version].to_f < 7
    package 'mysql-server' do
      action :install
    end
    service 'mysqld' do
      action [ :enable, :start ]
    end
    file '/etc/my.cnf' do
      content <<-EOF
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
user=mysql
# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0
character-set-server=utf8
skip-character-set-client-handshake

[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

[client]
default-character-set=utf8

[mysqldump]
default-character-set=utf8

[mysql]
default-character-set=utf8
EOF
    end
  else
    package 'mariadb-server' do
      action :install
    end
    service 'mariadb' do
      action [ :enable, :start ]
    end
    file '/etc/my.cnf.d/im.cnf' do
      content <<-EOF
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
user=mysql
# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0
character-set-server=utf8mb4
skip-character-set-client-handshake

[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

[client]
default-character-set=utf8mb4

[mysqldump]
default-character-set=utf8mb4

[mysql]
default-character-set=utf8mb4
EOF
    end
  end
  execute 'mysql -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'*********\';" -u root' do
    command 'mysql -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'im4135dev\';" -u root'
  end
end

if node[:platform] == 'ubuntu'
  execute 'echo "set grub-pc/install_devices /dev/sda" | debconf-communicate' do
    command 'echo "set grub-pc/install_devices /dev/sda" | debconf-communicate'
  end

  execute 'aptitude update' do
    command 'aptitude update'
  end

  execute 'aptitude full-upgrade' do
    command 'aptitude full-upgrade --assume-yes'
  end
elsif node[:platform] == 'redhat'
  execute 'yum -y update' do
    command 'yum -y update'
  end
end

package 'sqlite' do
  action :install
end

package 'acl' do
  action :install
end

if node[:platform] == 'ubuntu'
  package 'libmysqlclient-dev' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'php' do
    action :install
  end
  if node[:platform_version].to_f < 6
    package 'php-mbstring' do
      action :install
    end
    package 'php-pear' do
      action :install
    end
    package 'gcc' do
      action :install
    end
    execute 'pecl install json' do
      command 'pecl install json'
    end
    file '/etc/php.d/json.ini' do
      content 'extension=json.so'
    end
    package 'pcre-devel' do
      action :install
    end
    directory '/usr/include/php/ext/pcre' do
      action :create
    end
    execute 'curl -L https://raw.githubusercontent.com/php/php-src/PHP-5.2/ext/pcre/php_pcre.h > /tmp/php_pcre.h' do
      command 'curl -L https://raw.githubusercontent.com/php/php-src/PHP-5.2/ext/pcre/php_pcre.h > /tmp/php_pcre.h'
    end
    execute 'mv /tmp/php_pcre.h /usr/include/php/ext/pcre/' do
      command 'mv /tmp/php_pcre.h /usr/include/php/ext/pcre/'
    end
    execute 'pecl install channel://pecl.php.net/filter-0.11.0' do
      command 'pecl install channel://pecl.php.net/filter-0.11.0'
    end
    file '/etc/php.d/filter.ini' do
      content 'extension=filter.so'
    end
    #execute 'pecl install timezonedb' do
    #  command 'pecl install timezonedb'
    #end
    #file '/etc/php.d/timezonedb.ini' do
    #  content 'extension=timezonedb.so'
    #end
  end
  if node[:platform_version].to_f < 7
    package 'php-mysql' do
      action :install
    end
    package 'mysql-devel' do
      action :install
    end
  else
    package 'mariadb-devel' do
      action :install
    end
    package 'php-mysqlnd' do
      action :install
    end
  end
end

if node[:platform] == 'ubuntu'
  package 'php5-pgsql' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'php-pgsql' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  package 'php5-sqlite' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'php-pdo' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  package 'php5-curl' do
    action :install
  end
  package 'php5-gd' do
    action :install
  end
  package 'php5-xmlrpc' do
    action :install
  end
  package 'php5-intl' do
    action :install
  end
end

if node[:platform] == 'redhat'
  package 'epel-release' do
    action :install
  end
end
if node[:platform] == 'ubuntu' || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  package 'nodejs' do
    action :install
  end
end

execute 'update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10' do
  command 'update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10'
end

if node[:platform] == 'ubuntu' || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  package 'npm' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  package 'libfontconfig1' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'fontconfig-devel' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  package 'phpunit' do
    action :install
  end
elsif node[:platform] == 'redhat' && node[:platform_version].to_f >= 6
  package 'php-phpunit-PHPUnit' do
    action :install
  end
end

package 'samba' do
  action :install
end

package 'git' do
  action :install
end

if node[:platform] == 'ubuntu'
  execute 'aptitude clean' do
    command 'aptitude clean'
  end
end

execute "cd \"#{WEBROOT}\" && git clone https://github.com/INTER-Mediator/INTER-Mediator.git" do
  command "cd \"#{WEBROOT}\" && git clone https://github.com/INTER-Mediator/INTER-Mediator.git"
end

if node[:platform] == 'ubuntu'
  execute "mv \"#{WEBROOT}/index.html\" \"#{WEBROOT}/index_original.html\"" do
    command "mv \"#{WEBROOT}/index.html\" \"#{WEBROOT}/index_original.html\""
  end
end

execute "cd \"#{IMSUPPORT}\" && git clone https://github.com/codemirror/CodeMirror.git" do
  command "cd \"#{IMSUPPORT}\" && git clone https://github.com/codemirror/CodeMirror.git"
end

execute "cd \"#{WEBROOT}\" && ln -s \"#{IMVMROOT}/index.html\" index.html" do
  command "cd \"#{WEBROOT}\" && ln -s \"#{IMVMROOT}/index.html\" index.html"
end

file "#{WEBROOT}/.htaccess" do
  content 'AddType "text/html; charset=UTF-8" .html'
end

if node[:platform] == 'ubuntu'
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4';
$dbOption = array();
$browserCompatibility = array(
    'Chrome' => '1+',
    'FireFox' => '2+',
    'msie' => '9+',
    'Opera' => '1+',
    'Safari' => '4+',
    'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
$passPhrase = '';
$generatedPrivateKey = <<<EOL
-----BEGIN RSA PRIVATE KEY-----
MIIBOwIBAAJBAKihibtt92M6A/z49CqNcWugBd3sPrW3HF8TtKANZd1EWQ/agZ65
H2/NdL8H6zCgmKpYFTqFGwlYrnWrsbD1UxcCAwEAAQJAWX5pl1Q0D7Axf6csBg1M
3V5u3qlLWqsUXo0ZtjuGDRgk5FsJOA9bkxfpJspbr2CFkodpBuBCBYpOTQhLUc2H
MQIhAN1stwI2BIiSBNbDx2YiW5IVTEh/gTEXxOCazRDNWPQJAiEAwvZvqIQLexer
TnKj7q+Zcv4G2XgbkhtaLH/ELiA/Fh8CIQDGIC3M86qwzP85cCrub5XCK/567GQc
GmmWk80j2KpciQIhAI/ybFa7x85Gl5EAS9F7jYy9ykjeyVyDHX0liK+V1355AiAG
jU6zr1wG9awuXj8j5x37eFXnfD/p92GpteyHuIDpog==
-----END RSA PRIVATE KEY-----
EOL;
EOF
  end
elsif node[:platform] == 'redhat' && node[:platform_version].to_f < 7
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=test_db;charset=utf8';
$dbOption = array();
$browserCompatibility = array(
    'Chrome' => '1+',
    'FireFox' => '2+',
    'msie' => '9+',
    'Opera' => '1+',
    'Safari' => '4+',
    'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
EOF
  end
elsif node[:platform] == 'redhat' && node[:platform_version].to_f >= 7
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=test_db;charset=utf8mb4';
$dbOption = array();
$browserCompatibility = array(
    'Chrome' => '1+',
    'FireFox' => '2+',
    'msie' => '9+',
    'Opera' => '1+',
    'Safari' => '4+',
    'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
EOF
  end
end

execute "sed -e 's|sqlite:/tmp/sample.sq3|sqlite:/var/db/im/sample.sq3|' \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\" > \"#{IMUNITTEST}/temp\"" do
  command "sed -e 's|sqlite:/tmp/sample.sq3|sqlite:/var/db/im/sample.sq3|' \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\" > \"#{IMUNITTEST}/temp\""
end
execute "rm \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\"" do
  command "rm \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\""
end
execute "mv \"#{IMUNITTEST}/temp\" \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\"" do
  command "mv \"#{IMUNITTEST}/temp\" \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\""
end

if node[:platform] == 'redhat'
  execute 'service httpd restart' do
    command 'service httpd restart'
  end
end


# Install npm packages

if node[:platform] == 'ubuntu' || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  execute 'npm install -g buster' do
    command 'npm install -g buster'
  end

  if node[:platform] == 'redhat' && node[:platform_version].to_f >= 7
    package 'bzip2' do
      action :install  # for phantomjs
    end
  end

  execute 'npm install -g phantomjs' do
    command 'npm install -g phantomjs'
  end
end


# Activate DefEdit/PageEdit

execute "sed -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/defedit.php\" > \"#{IMSUPPORT}/temp\"" do
  command "sed -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/defedit.php\" > \"#{IMSUPPORT}/temp\""
end
execute "rm \"#{IMSUPPORT}/defedit.php\"" do
  command "rm \"#{IMSUPPORT}/defedit.php\""
end
execute "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/defedit.php\"" do
  command "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/defedit.php\""
end

execute "sed -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/pageedit.php\" > \"#{IMSUPPORT}/temp\"" do
  command "sed -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/pageedit.php\" > \"#{IMSUPPORT}/temp\""
end
execute "rm \"#{IMSUPPORT}/pageedit.php\"" do
  command "rm \"#{IMSUPPORT}/pageedit.php\""
end
execute "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/pageedit.php\"" do
  command "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/pageedit.php\""
end


# Copy Templates

for num in 1..40 do
  num = "%02d" % num
    execute "sed -e \"s|\('INTER-Mediator.php'\)|\('INTER-Mediator/INTER-Mediator.php'\)|\" \"#{IMSAMPLE}/templates/definition_file_simple.php\" > \"#{WEBROOT}/def#{num}.php\"" do
      command "sed -e \"s|\('INTER-Mediator.php'\)|\('INTER-Mediator/INTER-Mediator.php'\)|\" \"#{IMSAMPLE}/templates/definition_file_simple.php\" > \"#{WEBROOT}/def#{num}.php\""
  end
  file "#{WEBROOT}/def#{num}.php" do
    action :nothing
    mode '664'
  end
  execute "sed -e 's/definitin_file_simple.php/def#{num}.php/' \"#{IMSAMPLE}/templates/page_file_simple.html\" > \"#{WEBROOT}/page#{num}.html\"" do
    command "sed -e 's/definitin_file_simple.php/def#{num}.php/' \"#{IMSAMPLE}/templates/page_file_simple.html\" > \"#{WEBROOT}/page#{num}.html\""
  end
  file "#{WEBROOT}/page#{num}.html" do
    action :nothing
    mode '664'
  end
end

execute "chmod -R g+w \"#{WEBROOT}\"" do
  command "chmod -R g+w \"#{WEBROOT}\""
end


# Import schema

if node[:platform] == 'redhat'
  if node[:platform_version].to_f >= 6
    execute 'setenforce 0' do
      command 'setenforce 0'
    end
    file '/etc/selinux/config' do
      owner 'root'
      group 'root'
      mode '644'
      content <<-EOF
# This file controls the state of SELinux on the system.
# SELINUX= can take one of these three values:
#     enforcing - SELinux security policy is enforced.
#     permissive - SELinux prints warnings instead of enforcing.
#     disabled - No SELinux policy is loaded.
SELINUX=disabled
# SELINUXTYPE= can take one of these two values:
#     targeted - Targeted processes are protected,
#     mls - Multi Level Security protection.
SELINUXTYPE=targeted


EOF
    end
    if node[:platform_version].to_f >= 6 && node[:platform_version].to_f < 7
      file '/etc/sysconfig/iptables' do
        content <<-EOF
# Firewall configuration written by system-config-firewall
# Manual customization of this file is not recommended.
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
-A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
-A INPUT -p icmp -j ACCEPT
-A INPUT -i lo -j ACCEPT
-A INPUT -m state --state NEW -m tcp -p tcp --dport 22 -j ACCEPT
-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT
-A INPUT -j REJECT --reject-with icmp-host-prohibited
-A FORWARD -j REJECT --reject-with icmp-host-prohibited
COMMIT
EOF
      end
      execute 'service iptables restart' do
        command 'service iptables restart'
      end
    else
      execute 'firewall-cmd --zone=public --add-service=http --permanent' do
        command 'firewall-cmd --zone=public --add-service=http --permanent'
      end
      execute 'firewall-cmd --reload' do
        command 'firewall-cmd --reload'
      end
    end
  end
  #execute 'setenforce 1' do
  #  command 'setenforce 1'
  #end
end

if node[:platform] == 'redhat'
  execute "sed -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.txt\" > \"#{IMDISTDOC}/temp\"" do
    command "sed -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.txt\" > \"#{IMDISTDOC}/temp\""
  end
  execute "rm \"#{IMDISTDOC}/sample_schema_mysql.txt\"" do
    command "rm \"#{IMDISTDOC}/sample_schema_mysql.txt\""
  end
  execute "mv \"#{IMDISTDOC}/temp\" \"#{IMDISTDOC}/sample_schema_mysql.txt\"" do
    command "mv \"#{IMDISTDOC}/temp\" \"#{IMDISTDOC}/sample_schema_mysql.txt\""
  end
end

execute "mysql -u root --password=im4135dev < \"#{IMDISTDOC}/sample_schema_mysql.txt\"" do
  command "mysql -u root --password=im4135dev < \"#{IMDISTDOC}/sample_schema_mysql.txt\""
end

execute 'echo "im4135dev" | sudo -u postgres -S psql -c "create database test_db;"' do
  command 'echo "im4135dev" | sudo -u postgres -S psql -c "create database test_db;"'
end

execute "echo 'im4135dev' | sudo -u postgres -S psql -f \"#{IMDISTDOC}/sample_schema_pgsql.txt\" test_db" do
  command "echo 'im4135dev' | sudo -u postgres -S psql -f \"#{IMDISTDOC}/sample_schema_pgsql.txt\" test_db"
end

if node[:platform] == 'redhat'
  file '/var/lib/pgsql/data/pg_hba.conf' do
    owner 'postgres'
    group 'postgres'
    mode '600'
    content <<-EOF
# PostgreSQL Client Authentication Configuration File
# ===================================================
#
# Refer to the "Client Authentication" section in the
# PostgreSQL documentation for a complete description
# of this file.  A short synopsis follows.
#
# This file controls: which hosts are allowed to connect, how clients
# are authenticated, which PostgreSQL user names they can use, which
# databases they can access.  Records take one of these forms:
#
# local      DATABASE  USER  METHOD  [OPTIONS]
# host       DATABASE  USER  CIDR-ADDRESS  METHOD  [OPTIONS]
# hostssl    DATABASE  USER  CIDR-ADDRESS  METHOD  [OPTIONS]
# hostnossl  DATABASE  USER  CIDR-ADDRESS  METHOD  [OPTIONS]
#
# (The uppercase items must be replaced by actual values.)
#
# The first field is the connection type: "local" is a Unix-domain socket,
# "host" is either a plain or SSL-encrypted TCP/IP socket, "hostssl" is an
# SSL-encrypted TCP/IP socket, and "hostnossl" is a plain TCP/IP socket.
#
# DATABASE can be "all", "sameuser", "samerole", a database name, or
# a comma-separated list thereof.
#
# USER can be "all", a user name, a group name prefixed with "+", or
# a comma-separated list thereof.  In both the DATABASE and USER fields
# you can also write a file name prefixed with "@" to include names from
# a separate file.
#
# CIDR-ADDRESS specifies the set of hosts the record matches.
# It is made up of an IP address and a CIDR mask that is an integer
# (between 0 and 32 (IPv4) or 128 (IPv6) inclusive) that specifies
# the number of significant bits in the mask.  Alternatively, you can write
# an IP address and netmask in separate columns to specify the set of hosts.
#
# METHOD can be "trust", "reject", "md5", "password", "gss", "sspi", "krb5",
# "ident", "pam", "ldap" or "cert".  Note that "password" sends passwords
# in clear text; "md5" is preferred since it sends encrypted passwords.
#
# OPTIONS are a set of options for the authentication in the format
# NAME=VALUE. The available options depend on the different authentication
# methods - refer to the "Client Authentication" section in the documentation
# for a list of which options are available for which authentication methods.
#
# Database and user names containing spaces, commas, quotes and other special
# characters must be quoted. Quoting one of the keywords "all", "sameuser" or
# "samerole" makes the name lose its special character, and just match a
# database or username with that name.
#
# This file is read on server startup and when the postmaster receives
# a SIGHUP signal.  If you edit the file on a running system, you have
# to SIGHUP the postmaster for the changes to take effect.  You can use
# "pg_ctl reload" to do that.

# Put your actual configuration here
# ----------------------------------
#
# If you want to allow non-local connections, you need to add more
# "host" records. In that case you will also need to make PostgreSQL listen
# on a non-local interface via the listen_addresses configuration parameter,
# or via the -i or -h command line switches.
#



# TYPE  DATABASE    USER        CIDR-ADDRESS          METHOD

# "local" is for Unix domain socket connections only
local   all         all                               trust
# IPv4 local connections:
host    all         all         127.0.0.1/32          trust
# IPv6 local connections:
host    all         all         ::1/128               trust
EOF
  end
  service 'postgresql' do
    action [ :restart ]
  end
end

directory '/var/db/im' do
  action :create
  group 'im-developer'
  mode '775'
end
if node[:platform] == 'ubuntu'
  directory '/var/db/im' do
    owner 'www-data'
  end
elsif node[:platform] == 'redhat'
  directory '/var/db/im' do
    owner 'apache'
  end
end

if node[:platform] == 'ubuntu'
  file '/var/db/im/sample.sq3' do
    owner 'www-data'
    group 'im-developer'
    mode '664'
  end
elsif node[:platform] == 'redhat'
  file '/var/db/im/sample.sq3' do
    owner 'apache'
    group 'im-developer'
    mode '664'
  end
end

execute "sqlite3 /var/db/im/sample.sq3 < \"#{IMDISTDOC}/sample_schema_sqlite.txt\"" do
  command "sqlite3 /var/db/im/sample.sq3 < \"#{IMDISTDOC}/sample_schema_sqlite.txt\""
end

execute "setfacl --recursive --modify g:im-developer:rw \"#{WEBROOT}\"" do
  command "setfacl --recursive --modify g:im-developer:rw \"#{WEBROOT}\""
end

execute "chown -R developer:im-developer \"#{WEBROOT}\"" do
  command "chown -R developer:im-developer \"#{WEBROOT}\""
end

execute "chmod -R g+w \"#{WEBROOT}\"" do
  command "chmod -R g+w \"#{WEBROOT}\""
end

execute 'chown -R developer:developer /home/developer' do
  command 'chown -R developer:developer /home/developer'
end

file "#{SMBCONF}" do
  owner 'root'
  group 'root'
  mode '644'
  content <<-EOF
#
# Sample configuration file for the Samba suite for Debian GNU/Linux.
#
#
# This is the main Samba configuration file. You should read the
# smb.conf(5) manual page in order to understand the options listed
# here. Samba has a huge number of configurable options most of which
# are not shown in this example
#
# Some options that are often worth tuning have been included as
# commented-out examples in this file.
#  - When such options are commented with ";", the proposed setting
#    differs from the default Samba behaviour
#  - When commented with "#", the proposed setting is the default
#    behaviour of Samba but the option is considered important
#    enough to be mentioned here
#
# NOTE: Whenever you modify this file you should run the command
# "testparm" to check that you have not made any basic syntactic
# errors.

#======================= Global Settings =======================

[global]

## Browsing/Identification ###

# Change this to the workgroup/NT-domain name your Samba server will part of
   workgroup = WORKGROUP

# server string is the equivalent of the NT Description field
	server string = %h server (Samba, Ubuntu)

# Windows Internet Name Serving Support Section:
# WINS Support - Tells the NMBD component of Samba to enable its WINS Server
#   wins support = no

# WINS Server - Tells the NMBD components of Samba to be a WINS Client
# Note: Samba can be either a WINS Server, or a WINS Client, but NOT both
;   wins server = w.x.y.z

# This will prevent nmbd to search for NetBIOS names through DNS.
   dns proxy = no

#### Networking ####
   hosts allow = 192.168.56. 127.
# The specific set of interfaces / networks to bind to
# This can be either the interface name or an IP address/netmask;
# interface names are normally preferred
;   interfaces = 127.0.0.0/8 eth0

# Only bind to the named interfaces and/or networks; you must use the
# 'interfaces' option above to use this.
# It is recommended that you enable this feature if your Samba machine is
# not protected by a firewall or is a firewall itself.  However, this
# option cannot handle dynamic or non-broadcast interfaces correctly.
;   bind interfaces only = yes



#### Debugging/Accounting ####

# This tells Samba to use a separate log file for each machine
# that connects
   log file = /var/log/samba/log.%m

# Cap the size of the individual log files (in KiB).
   max log size = 1000

# If you want Samba to only log through syslog then set the following
# parameter to 'yes'.
#   syslog only = no

# We want Samba to log a minimum amount of information to syslog. Everything
# should go to /var/log/samba/log.{smbd,nmbd} instead. If you want to log
# through syslog you should set the following parameter to something higher.
   syslog = 0

# Do something sensible when Samba crashes: mail the admin a backtrace
   panic action = /usr/share/samba/panic-action %d


####### Authentication #######

# Server role. Defines in which mode Samba will operate. Possible
# values are "standalone server", "member server", "classic primary
# domain controller", "classic backup domain controller", "active
# directory domain controller".
#
# Most people will want "standalone sever" or "member server".
# Running as "active directory domain controller" will require first
# running "samba-tool domain provision" to wipe databases and create a
# new domain.
   server role = standalone server

# If you are using encrypted passwords, Samba will need to know what
# password database type you are using.
   passdb backend = tdbsam

   obey pam restrictions = yes

# This boolean parameter controls whether Samba attempts to sync the Unix
# password with the SMB password when the encrypted SMB password in the
# passdb is changed.
   unix password sync = yes

# For Unix password sync to work on a Debian GNU/Linux system, the following
# parameters must be set (thanks to Ian Kahan <<kahan@informatik.tu-muenchen.de> for
# sending the correct chat script for the passwd program in Debian Sarge).
   passwd program = /usr/bin/passwd %u
   passwd chat = *Enter\\snew\\s*\\spassword:* %n\\n *Retype\\snew\\s*\\spassword:* %n\\n *password\\supdated\\ssuccessfully* .

# This boolean controls whether PAM will be used for password changes
# when requested by an SMB client instead of the program listed in
# 'passwd program'. The default is 'no'.
   pam password change = yes

# This option controls how unsuccessful authentication attempts are mapped
# to anonymous connections
   map to guest = bad user

########## Domains ###########

#
# The following settings only takes effect if 'server role = primary
# classic domain controller', 'server role = backup domain controller'
# or 'domain logons' is set
#

# It specifies the location of the user's
# profile directory from the client point of view) The following
# required a [profiles] share to be setup on the samba server (see
# below)
;   logon path = \\\\%N\\profiles\\%U
# Another common choice is storing the profile in the user's home directory
# (this is Samba's default)
#   logon path = \\\\%N\\%U\\profile

# The following setting only takes effect if 'domain logons' is set
# It specifies the location of a user's home directory (from the client
# point of view)
;   logon drive = H:
#   logon home = \\\\%N\\%U

# The following setting only takes effect if 'domain logons' is set
# It specifies the script to run during logon. The script must be stored
# in the [netlogon] share
# NOTE: Must be store in 'DOS' file format convention
;   logon script = logon.cmd

# This allows Unix users to be created on the domain controller via the SAMR
# RPC pipe.  The example command creates a user account with a disabled Unix
# password; please adapt to your needs
; add user script = /usr/sbin/adduser --quiet --disabled-password --gecos "" %u

# This allows machine accounts to be created on the domain controller via the
# SAMR RPC pipe.
# The following assumes a "machines" group exists on the system
; add machine script  = /usr/sbin/useradd -g machines -c "%u machine account" -d /var/lib/samba -s /bin/false %u

# This allows Unix groups to be created on the domain controller via the SAMR
# RPC pipe.
; add group script = /usr/sbin/addgroup --force-badname %g

############ Misc ############

# Using the following line enables you to customise your configuration
# on a per machine basis. The %m gets replaced with the netbios name
# of the machine that is connecting
;   include = /home/samba/etc/smb.conf.%m

# Some defaults for winbind (make sure you're not using the ranges
# for something else.)
;   idmap uid = 10000-20000
;   idmap gid = 10000-20000
;   template shell = /bin/bash

# Setup usershare options to enable non-root users to share folders
# with the net usershare command.

# Maximum number of usershare. 0 (default) means that usershare is disabled.
;   usershare max shares = 100

# Allow users who've been granted usershare privileges to create
# public shares, not just authenticated ones
   usershare allow guests = yes

#======================= Share Definitions =======================

# Un-comment the following (and tweak the other settings below to suit)
# to enable the default home directory shares. This will share each
# user's home directory as \\\\server\\username
;[homes]
;   comment = Home Directories
;   browseable = no

# By default, the home directories are exported read-only. Change the
# next parameter to 'no' if you want to be able to write to them.
;   read only = yes

# File creation mask is set to 0700 for security reasons. If you want to
# create files with group=rw permissions, set next parameter to 0775.
;   create mask = 0700

# Directory creation mask is set to 0700 for security reasons. If you want to
# create dirs. with group=rw permissions, set next parameter to 0775.
;   directory mask = 0700

# By default, \\\\server\\username shares can be connected to by anyone
# with access to the samba server.
# Un-comment the following parameter to make sure that only "username"
# can connect to \\\\server\\username
# This might need tweaking when using external authentication schemes
;   valid users = %S

# Un-comment the following and create the netlogon directory for Domain Logons
# (you need to configure Samba to act as a domain controller too.)
;[netlogon]
;   comment = Network Logon Service
;   path = /home/samba/netlogon
;   guest ok = yes
;   read only = yes

# Un-comment the following and create the profiles directory to store
# users profiles (see the "logon path" option above)
# (you need to configure Samba to act as a domain controller too.)
# The path below should be writable by all users so that their
# profile directory may be created the first time they log on
;[profiles]
;   comment = Users profiles
;   path = /home/samba/profiles
;   guest ok = no
;   browseable = no
;   create mask = 0600
;   directory mask = 0700

[printers]
   comment = All Printers
   browseable = no
   path = /var/spool/samba
   printable = yes
   guest ok = no
   read only = yes
   create mask = 0700

# Windows clients look for this share name as a source of downloadable
# printer drivers
[print$]
   comment = Printer Drivers
   path = /var/lib/samba/printers
   browseable = yes
   read only = yes
   guest ok = no
# Uncomment to allow remote administration of Windows print drivers.
# You may need to replace 'lpadmin' with the name of the group your
# admin users are members of.
# Please note that you also need to set appropriate Unix permissions
# to the drivers directory for these users to have write rights in it
;   write list = root, @lpadmin

[webroot]
   comment = Apache Root Directory
   path = /var/www/html
   guest ok = no
   browseable = yes
   read only = no
   create mask = 0664
   directory mask = 0775
   force group = im-developer
EOF
end

execute '( echo im4135dev; echo im4135dev ) | sudo smbpasswd -s -a developer' do
  command '( echo im4135dev; echo im4135dev ) | sudo smbpasswd -s -a developer'
end

if node[:platform] == 'ubuntu'
  file '/etc/rc.local' do
    owner 'root'
    group 'root'
    mode '755'
    content <<-EOF
#!/bin/sh -e
#
# rc.local
#
# This script is executed at the end of each multiuser runlevel.
# Make sure that the script will "exit 0" on success or any other
# value on error.
#
# In order to enable or disable this script just change the execution
# bits.
#
# By default this script does nothing.

/usr/local/bin/buster-server &
/bin/sleep 5
/usr/local/bin/phantomjs /usr/local/lib/node_modules/buster/script/phantom.js http://localhost:1111/capture > /dev/null &
exit 0
EOF
  end
end
