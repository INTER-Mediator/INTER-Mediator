# coding: utf-8

# Recipe file of Itamae for Ubuntu Server 20.04 LTS, AlmaLinux 8.8 and Alpine Linux 3.13
#   How to test using Serverspec 2 after provisioning ("vargrant up"):
#   - Install Ruby on the host of VM (You don't need installing Ruby on macOS usually)
#   - Install Serverspec 2 on the host of VM ("gem install serverspec")
#     See detail: https://serverspec.org/
#   - Change directory to "vm-for-trial" directory on the host of VM
#   - Run "rake spec" on the host of VM

if node[:platform] == 'alpine'
  WEBROOT = "/var/www/localhost/htdocs"
  OLDWEBROOT = "/var/www/html"
elsif node[:platform] == 'ubuntu' && node[:platform_version].to_f < 14
  WEBROOT = "/var/www"
else
  WEBROOT = "/var/www/html"
end

IMROOT = "#{WEBROOT}/INTER-Mediator"
IMSUPPORT = "#{IMROOT}/src/php/DB/Support"
IMSAMPLE = "#{IMROOT}/samples"
IMUNITTEST = "#{IMROOT}/spec/INTER-Mediator-UnitTest"
IMDISTDOC = "#{IMROOT}/dist-docs"
IMVMROOT = "#{IMROOT}/dist-docs/vm-for-trial"
IMSELINUX = "#{IMROOT}/dist-docs/selinux"
APACHEOPTCONF="/etc/apache2/sites-enabled/inter-mediator-server.conf"
SMBCONF = "/etc/samba/smb.conf"


if node[:platform] == 'alpine'
  if node[:virtualization][:system] != 'docker'
    execute 'ip addr add 192.168.56.101/24 dev eth1' do
      command 'ip addr add 192.168.56.101/24 dev eth1'
    end
    file '/etc/network/interfaces' do
      content <<-EOF
auto lo
iface lo inet loopback

auto eth0
iface eth0 inet dhcp
	hostname inter-mediator-server

auto eth1
iface eth1 inet static
	address 192.168.56.101
	netmask 255.255.255.0
EOF
    end
  end
  if node[:platform_version].to_f >= 3.10
    file '/etc/apk/repositories' do
      content <<-EOF
#/media/cdrom/apks
http://dl-cdn.alpinelinux.org/alpine/v3.10/main
http://dl-cdn.alpinelinux.org/alpine/v3.10/community
#http://dl-cdn.alpinelinux.org/alpine/edge/main
#http://dl-cdn.alpinelinux.org/alpine/edge/community
#http://dl-cdn.alpinelinux.org/alpine/edge/testing
EOF
    end
  else
    file '/etc/apk/repositories' do
      content <<-EOF
#/media/cdrom/apks
http://dl-5.alpinelinux.org/alpine/v3.8/main
http://dl-5.alpinelinux.org/alpine/v3.8/community
#http://dl-5.alpinelinux.org/alpine/edge/main
#http://dl-5.alpinelinux.org/alpine/edge/community
#http://dl-5.alpinelinux.org/alpine/edge/testing
EOF
    end
  end
  package 'shadow' do
    action :install
  end
end

if node[:platform] == 'alpine'
  package 'openrc' do
    action :install
  end
end
if node[:virtualization][:system] == 'docker' && node[:platform] == 'alpine'
  directory '/run/openrc' do
    action :create
  end
  execute 'touch /run/openrc/softlevel' do
    command 'touch /run/openrc/softlevel'
  end
end

if node[:platform] == 'alpine'
  execute 'echo "developer ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers' do
    command 'echo "developer ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers'
  end
elsif node[:platform] == 'ubuntu'
  package 'sudo' do
    action :install
  end
  file '/etc/sudoers.d/developer' do
    owner 'root'
    group 'root'
    mode '440'
    content 'developer ALL=(ALL) NOPASSWD:ALL'
  end
end

if node[:platform] == 'alpine'
  user "developer" do
    action :create
  end
  execute 'yes ********* | sudo passwd developer' do
    command 'yes im4135dev | sudo passwd developer'
  end
else
  user "developer" do
    password "$6$inter-mediator$kEUWd5ZQNPEfNF7CPzRMDoHhmz67rgJTmDbUsJ3AL35vV3c5sGk9ml2kLRj.2z5BkygH7SS2E549qTB2FYs6S/"
  end
end

if node[:platform] == 'alpine'
  execute 'addgroup im-developer' do
    command 'addgroup im-developer'
  end
  ## ToDo
  execute 'usermod -a -G im-developer developer' do
    command 'usermod -a -G im-developer developer'
  end
else
  execute 'groupadd im-developer' do
    not_if 'getent group im-developer | grep developer'
    command 'groupadd im-developer'
  end
  execute 'usermod -a -G im-developer developer' do
    command 'usermod -a -G im-developer developer'
  end
end

directory '/home/developer' do
  action :create
  owner 'developer'
  group 'developer'
end
file '/home/developer/.bashrc' do
  action :create
  owner 'developer'
  group 'developer'
end
file '/home/developer/.viminfo' do
  action :create
  owner 'developer'
  group 'developer'
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 18
    execute 'sed -i -e "s/security.ubuntu.com/archive.ubuntu.com/g" /etc/apt/sources.list' do
      command 'sed -i -e "s/security.ubuntu.com/archive.ubuntu.com/g" /etc/apt/sources.list'
    end
    execute 'sed -i -e "s/jp.archive.ubuntu.com/archive.ubuntu.com/g" /etc/apt/sources.list' do
      command 'sed -i -e "s/jp.archive.ubuntu.com/archive.ubuntu.com/g" /etc/apt/sources.list'
    end
  end
  if node[:platform_version].to_f >= 16
    execute 'rm -rf /var/lib/apt/lists/*' do
      command 'rm -rf /var/lib/apt/lists/*'
    end
    execute 'apt autoclean' do
      command 'apt autoclean'
    end
    execute 'apt clean' do
      command 'apt clean'
    end
  end
  execute 'apt update' do
    command 'apt update'
  end

  if node[:virtualization][:system] != 'docker'
    execute 'apt upgrade' do
      command 'apt upgrade --assume-yes'
    end
  end
elsif node[:platform] == 'redhat'
  execute 'yum -y update' do
    command 'yum -y update'
  end
end

if node[:platform] == 'redhat' || node[:platform] == 'ubuntu'
  package 'openssh-server' do
    action :install
  end
  if node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 24
    service 'ssh' do
      action [ :enable, :start ]
    end
  else
    service 'sshd' do
      action [ :enable, :start ]
    end
  end
end

if node[:platform] == 'alpine' || (node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 16)
  package 'curl' do
    action :install
  end
end

if node[:platform] == 'alpine'
  package 'bash' do
    action :install
  end
end
if node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  package 'postgresql' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'postgresql-server' do
    action :install
  end
  if node[:platform_version].to_f < 6
    execute 'sudo su - postgres -c "initdb --encoding=UTF8 --no-locale"' do
      command 'sudo su - postgres -c "initdb --encoding=UTF8 --no-locale"'
    end
  else
    execute 'PGSETUP_INITDB_OPTIONS="--encoding=UTF-8 --no-locale" postgresql-setup --initdb' do
      command 'PGSETUP_INITDB_OPTIONS="--encoding=UTF-8 --no-locale" postgresql-setup --initdb'
    end
  end
end
if node[:platform] == 'alpine'
  if node[:virtualization][:system] == 'docker'
    user "postgres" do
      action :create
    end
  end
  execute 'yes ********* | sudo passwd postgres' do
    command 'yes im4135dev | sudo passwd postgres'
  end
  execute 'echo "*********" | sudo /etc/init.d/postgresql setup' do
    command 'echo "im4135dev" | sudo /etc/init.d/postgresql setup'
  end
  if node[:virtualization][:system] != 'docker'
    service 'postgresql' do
      action [ :enable, :start ]
    end
  else
    if node[:virtualization][:system] == 'docker' && node[:platform] == 'alpine'
      directory '/run/postgresql' do
        action :create
        owner 'postgres'
        group 'postgres'
      end
    end
    service 'postgresql' do
      action [ :enable ]
    end
    execute 'sudo su - postgres -c "pg_ctl start -D /var/lib/postgresql/11/data -l /var/log/postgresql/postgresql.log"' do
      command 'sudo su - postgres -c "pg_ctl start -D /var/lib/postgresql/11/data -l /var/log/postgresql/postgresql.log"'
    end
  end
else
  service 'postgresql' do
    action [ :enable, :start ]
  end
  user 'postgres' do
    password '$6$inter-mediator$kEUWd5ZQNPEfNF7CPzRMDoHhmz67rgJTmDbUsJ3AL35vV3c5sGk9ml2kLRj.2z5BkygH7SS2E549qTB2FYs6S/'
  end
end

if node[:platform] == 'alpine'
  package 'mariadb-client' do
    action :install
  end
  package 'mariadb' do
    action :install
  end
  if node[:virtualization][:system] == 'docker' && node[:platform] == 'alpine'
    directory '/run/mysqld' do
      action :create
      owner 'mysql'
      group 'mysql'
    end
  end
  file '/etc/mysql/my.cnf' do
    content <<-EOF
[mysqld]
datadir=/var/lib/mysql
socket=/run/mysqld/mysqld.sock
user=mysql
# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0
character-set-server=utf8mb4
skip-character-set-client-handshake

[mysqld_safe]
#log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

[client]
default-character-set=utf8mb4

[mysqldump]
default-character-set=utf8mb4

[mysql]
default-character-set=utf8mb4
EOF
  end
  execute 'sed -i "s/^skip-networking/#skip-networking/" /etc/my.cnf.d/mariadb-server.cnf' do
    command 'sed -i "s/^skip-networking/#skip-networking/" /etc/my.cnf.d/mariadb-server.cnf'
  end
  if node[:virtualization][:system] != 'docker'
    execute '/etc/init.d/mariadb setup' do
      command '/etc/init.d/mariadb setup'
    end
    service 'mariadb' do
      action [ :enable, :start ]
    end
    execute 'mysqladmin -u root password "*********"' do
      command 'mysqladmin -u root password "im4135dev"'
    end
  else
    service 'mariadb' do
      action [ :enable ]
    end
    execute '/usr/bin/mysql_install_db --user=mysql && cd /usr; /usr/bin/mysqld_safe --datadir=/var/lib/mysql --syslog --nowatch; sleep 10; mysqladmin -u root password "*********"' do
      command '/usr/bin/mysql_install_db --user=mysql && cd /usr; /usr/bin/mysqld_safe --datadir=/var/lib/mysql --syslog --nowatch; sleep 10; mysqladmin -u root password "im4135dev"'
    end
  end
elsif node[:platform] == 'ubuntu'
  package 'mysql-server' do
    action :install
  end
  if node[:platform_version].to_f < 20
    file '/etc/mysql/conf.d/im.cnf' do
      content <<-EOF
[mysqld]
character-set-server=utf8mb4
skip-character-set-client-handshake

[client]
default-character-set=utf8mb4

[mysqldump]
default-character-set=utf8mb4

[mysql]
default-character-set=utf8mb4
EOF
    end
  else
    file '/etc/mysql/mysql.conf.d/im.cnf' do
      content <<-EOF
[mysqld]
character-set-server=utf8mb4
skip-character-set-client-handshake

[client]
default-character-set=utf8mb4

[mysqldump]
default-character-set=utf8mb4

[mysql]
default-character-set=utf8mb4
EOF
    end
    execute "chown -R mysql:mysql /etc/mysql/mysql.conf.d" do
      command "chown -R mysql:mysql /etc/mysql/mysql.conf.d"
    end
  end
  service 'mysql' do
    action [ :enable, :start ]
  end
  execute 'mysqladmin -u root password "*********"' do
    command 'mysqladmin -u root password "im4135dev"'
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
    execute 'curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash' do
      command 'curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash'
    end
    package 'epel-release' do
      action :install
    end
    package 'MariaDB-server' do
      action :install
    end
    service 'mariadb' do
      action [ :enable, :start ]
    end
    execute 'mariadb-admin -u root password "*********"' do
      command 'mariadb-admin -u root password "im4135dev"'
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
end

if node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 16
  package 'sqlite3' do
    action :install
  end
else
  package 'sqlite' do
    action :install
  end
end

if node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 16 && node[:platform_version].to_f < 18
  execute 'curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -' do
    command 'curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -'
  end
  package 'software-properties-common' do
    action :install
  end
  package 'apt-transport-https' do
    action :install
  end
  execute 'sudo add-apt-repository "$(curl https://packages.microsoft.com/config/ubuntu/16.04/mssql-server-2017.list)"' do
    command 'sudo add-apt-repository "$(curl https://packages.microsoft.com/config/ubuntu/16.04/mssql-server-2017.list)"'
  end
  execute 'sudo apt-get update' do
    command 'sudo apt-get update'
  end
  package 'mssql-server' do
    action :install
  end
  execute 'sudo /opt/mssql/bin/mssql-conf set telemetry.customerfeedback false' do
    command 'sudo /opt/mssql/bin/mssql-conf set telemetry.customerfeedback false'
  end
  if node[:virtualization][:system] == 'docker'
    execute 'sudo ACCEPT_EULA="Y" MSSQL_PID="Developer" MSSQL_LCID=1041 MSSQL_SA_PASSWORD="**********" /opt/mssql/bin/mssql-conf setup' do
      command 'sudo ACCEPT_EULA="Y" MSSQL_PID="Developer" MSSQL_LCID=1041 MSSQL_SA_PASSWORD="im4135devX" /opt/mssql/bin/mssql-conf setup'
    end
    service 'mssql-server' do
      action [ :enable, :start ]
    end
  end
end

if node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 16
  package 'software-properties-common' do
    action :install
  end
  execute 'LC_ALL=C.UTF-8 sudo add-apt-repository ppa:ondrej/php -y' do
    command 'LC_ALL=C.UTF-8 sudo add-apt-repository ppa:ondrej/php -y'
  end
  execute 'sudo apt-get update' do
    command 'sudo apt-get update'
  end
#elsif node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 18
#  execute 'sudo apt-get update' do
#    command 'sudo apt-get update'
#  end
end

package 'acl' do
  action :install
end

if node[:platform] == 'alpine'
  package 'php7' do
    action :install
  end
  package 'php7-apache2' do
    action :install
  end
  package 'php7-curl' do
    action :install
  end
  package 'php7-pdo' do
    action :install
  end
  package 'php7-pdo_mysql' do
    action :install
  end
  package 'php7-pdo_pgsql' do
    action :install
  end
  package 'php7-pdo_sqlite' do
    action :install
  end
  package 'php7-openssl' do
    action :install
  end
  package 'php7-dom' do
    action :install
  end
  package 'php7-json' do
    action :install
  end
  package 'php7-bcmath' do
    action :install
  end
  package 'php7-phar' do
    action :install
  end
  package 'php7-mbstring' do
    action :install
  end
  package 'php7-xml' do
    action :install
  end
  package 'php7-xmlwriter' do
    action :install
  end
  package 'php7-tokenizer' do
    action :install
  end
  package 'php-ldap' do
    action :install
  end
  package 'php7-simplexml' do
    action :install
  end
  package 'php7-session' do
    action :install
  end
  package 'php7-mysqli' do
    action :install
  end
  package 'composer' do
    action :install
  end
  package 'libbsd' do
    action :install
  end
elsif node[:platform] == 'ubuntu'
  package 'libmysqlclient-dev' do
    action :install
  end
  if node[:platform_version].to_f >= 20
    package 'php8.4' do
      action :install
    end
    package 'php8.4-cli' do
      action :install
    end
    package 'libapache2-mod-php8.4' do
      action :install
    end
    package 'php8.4-xml' do
      action :install
    end
    package 'php8.4-mbstring' do
      action :install
    end
    package 'php8.4-bcmath' do
      action :install
    end
    package 'php8.4-ldap' do
      action :install
    end
  elsif node[:platform_version].to_f >= 18
    package 'php' do
      action :install
    end
    package 'php-cli' do
      action :install
    end
    package 'libapache2-mod-php' do
      action :install
    end
    package 'php-xml' do
      action :install
    end
    package 'php-mbstring' do
      action :install
    end
    package 'php-bcmath' do
      action :install
    end
    package 'php-ldap' do
      action :install
    end
  elsif node[:platform_version].to_f >= 16
    package 'php7.2' do
      action :install
    end
    package 'php7.2-cli' do
      action :install
    end
    package 'libapache2-mod-php7.2' do
      action :install
    end
    package 'php7.2-dom' do
      action :install
    end
    package 'php7.2-mbstring' do
      action :install
    end
    package 'php7.2-bcmath' do
      action :install
    end
    package 'php7.2-ldap' do
      action :install
    end
  end
  execute 'curl -sS https://getcomposer.org/installer | php; sudo mv composer.phar /usr/local/bin/composer; sudo chmod +x /usr/local/bin/composer;' do
    command 'curl -sS https://getcomposer.org/installer | php; sudo mv composer.phar /usr/local/bin/composer; sudo chmod +x /usr/local/bin/composer;'
  end
elsif node[:platform] == 'redhat' && node[:platform_version].to_f >= 8
  execute 'dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm' do
    command 'dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm'
  end
  execute 'dnf -y module reset php && dnf -y module enable php:remi-8.4' do
    command 'dnf -y module reset php && dnf -y module enable php:remi-8.4'
  end
  execute 'dnf -y install php php-mbstring php-mysqlnd php-pdo php-pgsql php-xml php-bcmath php-process php-zip php-gd php-ldap php-intl' do
    command 'dnf -y install php php-mbstring php-mysqlnd php-pdo php-pgsql php-xml php-bcmath php-process php-zip php-gd php-ldap php-intl'
  end
  package 'glibc-langpack-ja' do
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
  execute 'curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer;' do
    command 'curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer;'
  end
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 16
    package 'php5-mysql' do
      action :install
    end
  elsif node[:platform_version].to_f < 18
    package 'php7.2-mysql' do
      action :install
    end
  elsif node[:platform_version].to_f < 20
    package 'php-mysql' do
      action :install
    end
  else
    package 'php8.4-mysql' do
      action :install
    end
  end
elsif node[:platform] == 'redhat'
  if node[:platform_version].to_f < 7
    package 'php-mysql' do
      action :install
    end
    package 'mysql-devel' do
      action :install
    end
  elsif node[:platform_version].to_f < 8
    package 'mariadb-devel' do
      action :install
    end
  end
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 16
    package 'php5-pgsql' do
      action :install
    end
  elsif node[:platform_version].to_f < 18
    package 'php7.2-pgsql' do
      action :install
    end
  elsif node[:platform_version].to_f < 20
    package 'php-pgsql' do
      action :install
    end
  else
    package 'php8.4-pgsql' do
      action :install
    end
  end
elsif node[:platform] == 'redhat'
  package 'php-pgsql' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 16
    package 'php5-sqlite' do
      action :install
    end
  elsif node[:platform_version].to_f < 18
    package 'php7.2-sqlite3' do
      action :install
    end
  elsif node[:platform_version].to_f < 20
    package 'php-sqlite3' do
      action :install
    end
  else
    package 'php8.4-sqlite3' do
      action :install
    end
  end
elsif node[:platform] == 'redhat'
  package 'php-pdo' do
    action :install
  end
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 16
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
  elsif node[:platform_version].to_f < 18
    package 'php7.2-curl' do
      action :install
    end
    package 'php7.2-gd' do
      action :install
    end
    package 'php7.2-xmlrpc' do
      action :install
    end
    package 'php7.2-intl' do
      action :install
    end
  elsif node[:platform_version].to_f < 20
    package 'php-curl' do
      action :install
    end
    package 'php-gd' do
      action :install
    end
    package 'php-xmlrpc' do
      action :install
    end
    package 'php-intl' do
      action :install
    end
  else
    package 'php8.4-curl' do
      action :install
    end
    package 'php8.4-gd' do
      action :install
    end
    package 'php8.4-xmlrpc' do
      action :install
    end
    package 'php8.4-intl' do
      action :install
    end
  end
end

if node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  package 'apache2' do
    action :install
  end
  if node[:platform] == 'alpine'
    package 'apache2-proxy' do
      action :install
    end
    execute 'sed -i "s/^LoadModule lbmethod_/#LoadModule lbmethod_/" /etc/apache2/conf.d/proxy.conf' do
      command 'sed -i "s/^LoadModule lbmethod_/#LoadModule lbmethod_/" /etc/apache2/conf.d/proxy.conf'
    end
  end
  if node[:virtualization][:system] == 'docker' && node[:platform] == 'alpine'
    directory '/run/apache2/' do
      action :create
      owner 'apache'
      group 'apache'
    end
    file '/etc/apache2/conf.d/im.conf' do
      content <<-EOF
LoadModule slotmem_shm_module modules/mod_slotmem_shm.so
EOF
    end
    service 'apache2' do
      action [ :enable ]
    end
    execute 'httpd' do
      command 'httpd'
    end
  else
    service 'apache2' do
      action [ :enable, :start ]
    end
  end
elsif node[:platform] == 'redhat'
  package 'httpd' do
    action :install
  end
  service 'httpd' do
    action [ :enable, :start ]
  end
end
if node[:platform] == 'alpine'
  execute 'usermod -a -G im-developer apache' do
    command 'usermod -a -G im-developer apache'
  end
elsif node[:platform] == 'redhat'
  execute 'usermod -a -G im-developer apache' do
    command 'usermod -a -G im-developer apache'
  end
elsif node[:platform] == 'ubuntu'
  execute 'usermod -a -G im-developer www-data' do
    command 'usermod -a -G im-developer www-data'
  end
end

package 'nodejs' do
  action :install
end

if node[:platform] == 'redhat' && node[:platform_version].to_f < 8
  execute 'update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10' do
    command 'update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10'
  end
end
if node[:platform] == 'alpine'
  package 'nodejs-npm' do
    action :install
  end
end
if node[:platform] == 'ubuntu' || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  package 'npm' do
    action :install
  end
end
if (node[:platform] == 'ubuntu' && node[:platform_version].to_f < 22) || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6 && node[:platform_version].to_f < 8)
  if (node[:platform] == 'ubuntu' && node[:platform_version].to_f < 22) || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 7 && node[:platform_version].to_f < 8)
    execute 'npm install -g n --before 2025-09-14' do
      command 'npm install -g n --before 2025-09-14'
    end
    execute 'n stable' do
      command 'n stable'
    end
    execute 'ln -sf /usr/local/bin/node /usr/bin/node' do
      command 'ln -sf /usr/local/bin/node /usr/bin/node'
    end
    execute 'ln -sf /usr/local/bin/npm /usr/bin/npm' do
      command 'ln -sf /usr/local/bin/npm /usr/bin/npm'
    end
  end
  if node[:platform] == 'ubuntu' && node[:platform_version].to_f < 18
    execute 'apt-get purge -y nodejs npm' do
      command 'apt-get purge -y nodejs npm'
    end
  end
end

if node[:platform] == 'alpine'
  package 'fontconfig-dev' do
    action :install
  end
elsif node[:platform] == 'ubuntu'
  package 'libfontconfig1' do
    action :install
  end
elsif node[:platform] == 'redhat'
  package 'fontconfig-devel' do
    action :install
  end
end

if node[:platform] == 'alpine' || node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 20
  package 'ca-certificates' do
    action :install
  end
  execute 'update-ca-certificates' do
    command 'update-ca-certificates'
  end
end
package 'wget' do
  action :install
end
if node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  if node[:platform] == 'ubuntu' && node[:platform_version].to_f < 18
    execute 'wget https://phar.phpunit.de/phpunit-6.phar -P /tmp' do
      command 'wget https://phar.phpunit.de/phpunit-6.phar -P /tmp'
    end
    execute 'mv /tmp/phpunit-6.phar /usr/local/bin/phpunit' do
      command 'mv /tmp/phpunit-6.phar /usr/local/bin/phpunit'
    end
  else
    execute 'wget https://phar.phpunit.de/phpunit-9.phar -P /tmp' do
      command 'wget https://phar.phpunit.de/phpunit-9.phar -P /tmp'
    end
    execute 'mv /tmp/phpunit-9.phar /usr/local/bin/phpunit' do
      command 'mv /tmp/phpunit-9.phar /usr/local/bin/phpunit'
    end
  end
  execute 'chmod +x /usr/local/bin/phpunit' do
    command 'chmod +x /usr/local/bin/phpunit'
  end
elsif node[:platform] == 'redhat'
  execute 'wget https://phar.phpunit.de/phpunit-8.phar -P /tmp' do
    command 'wget https://phar.phpunit.de/phpunit-8.phar -P /tmp'
  end
  execute 'mv /tmp/phpunit-8.phar /usr/local/bin/phpunit' do
    command 'mv /tmp/phpunit-8.phar /usr/local/bin/phpunit'
  end
end
execute 'chmod +x /usr/local/bin/phpunit' do
  command 'chmod +x /usr/local/bin/phpunit'
end

package 'samba' do
  action :install
end

if node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 16
  service 'smbd' do
    action [ :enable, :start ]
  end
else
  if node[:platform] == 'redhat' && node[:platform_version].to_f >= 7
    service 'smb' do
      action [ :enable, :start ]
    end
  else
    if node[:virtualization][:system] != 'docker'
      service 'samba' do
        action [ :enable, :start ]
      end
    else
      service 'samba' do
        action [ :enable ]
      end
      execute 'smbd' do
        command 'smbd'
      end
    end
  end
end

if node[:platform] == 'ubuntu'
  package 'language-pack-ja' do
    action :install
  end
  package 'fbterm' do
    action :install
  end
  package 'unifont' do
    action :install
  end
end

if node[:platform] == 'alpine'
  package 'python' do
    action :install
  end
end

package 'git' do
  action :install
end

if node[:platform] == 'ubuntu'
  if node[:platform_version].to_i == 14
    package 'linux-generic-lts-xenial' do
      action :install
    end
    package 'linux-image-generic-lts-xenial' do
      action :install
    end
  end
  execute 'apt-get clean' do
    command 'apt-get clean'
  end
end

if node[:platform] == 'redhat' && node[:platform_version].to_f >= 8
  execute 'RESULT=`id vagrant 2>/dev/null`; if [ "$RESULT" != "" ]; then mariadb -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'*********\';" -u root ; fi' do
    command 'RESULT=`id vagrant 2>/dev/null`; if [ "$RESULT" != "" ]; then mariadb -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'im4135dev\';" -u root ; fi'
  end
else
  execute 'RESULT=`id vagrant 2>/dev/null`; if [ "$RESULT" != "" ]; then mysql -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'*********\';" -u root ; fi' do
    command 'RESULT=`id vagrant 2>/dev/null`; if [ "$RESULT" != "" ]; then mysql -e "GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' identified by \'im4135dev\';" -u root ; fi'
  end
end

if node[:platform] == 'ubuntu'
  execute 'a2enmod headers' do
    command 'a2enmod headers'
  end
  file "#{APACHEOPTCONF}" do
    content '#Header add Content-Security-Policy "default-src \'self\'"'
  end
end

execute "if [ ! -e /var/www/html/INTER-Mediator ]; then cd \"#{WEBROOT}\" && git clone https://github.com/INTER-Mediator/INTER-Mediator.git && cd INTER-Mediator && git checkout master && git remote add upstream https://github.com/INTER-Mediator/INTER-Mediator.git; fi" do
  command "if [ ! -e /var/www/html/INTER-Mediator ]; then cd \"#{WEBROOT}\" && git clone https://github.com/INTER-Mediator/INTER-Mediator.git && cd INTER-Mediator && git checkout master && git remote add upstream https://github.com/INTER-Mediator/INTER-Mediator.git; fi"
end

if node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  execute "rm -f \"#{WEBROOT}/index.html\"" do
    command "rm -f \"#{WEBROOT}/index.html\""
  end
end

if node[:platform] == 'redhat' || node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  execute "chown -R developer:im-developer \"#{WEBROOT}\"" do
    command "chown -R developer:im-developer \"#{WEBROOT}\""
  end
end
if node[:platform] == 'redhat' || node[:platform] == 'ubuntu'
  execute "chown developer:im-developer /var/www" do
    command "chown developer:im-developer /var/www"
  end
  execute "chmod 775 /var/www" do
    command "chmod 775 /var/www"
  end
end
#execute "cd \"#{IMSUPPORT}\" && git clone https://github.com/codemirror/CodeMirror.git" do
#  command "cd \"#{IMSUPPORT}\" && git clone https://github.com/codemirror/CodeMirror.git"
#end

execute "cd \"#{WEBROOT}\" && ln -s \"#{IMVMROOT}/index.php\" index.php" do
  command "cd \"#{WEBROOT}\" && ln -s \"#{IMVMROOT}/index.php\" index.php"
end

file "#{WEBROOT}/.htaccess" do
  content 'AddType "text/html; charset=UTF-8" .html'
end

if node[:platform] == 'alpine'
  MYSQLSOCKPATH = "/run/mysqld/mysqld.sock"
elsif node[:platform] == 'ubuntu'
  MYSQLSOCKPATH = "/var/run/mysqld/mysqld.sock"
end
if node[:platform] == 'ubuntu'
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=#{MYSQLSOCKPATH};dbname=test_db;charset=utf8mb4';
$dbOption = [];
$browserCompatibility = array(
    'Chrome' => '71+',
    'FireFox' => '69+',
    'Safari' => '11+',
    'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
$activateClientService = false;
$preventSSAutoBoot = false;
$serviceServerPort = '11478';
$stopSSEveryQuit = false;
$notUseServiceServer = true;
$messages['default'][9999] = "Changed";
$messages['ja'][9999] = "変更した";
$defaultTimezone = 'Asia/Tokyo';
EOF
  end
elsif node[:platform] == 'alpine'
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=#{MYSQLSOCKPATH};dbname=test_db;charset=utf8mb4';
$dbOption = [];
$browserCompatibility = array(
  'Chrome' => '71+',
  'FireFox' => '69+',
  'Safari' => '11+',
  'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
$activateClientService = false;
$preventSSAutoBoot = false;
$serviceServerPort = '11478';
$stopSSEveryQuit = false;
$notUseServiceServer = true;
$messages['default'][9999] = "Changed";
$messages['ja'][9999] = "変更した";
$defaultTimezone = 'Asia/Tokyo';
EOF
  end
elsif node[:platform] == 'redhat' && node[:platform_version].to_f >= 7
  file "#{WEBROOT}/params.php" do
    content <<-EOF
<?php
$dbUser = 'web';
$dbPassword = 'password';
$dbDSN = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=test_db;charset=utf8mb4';
$dbOption = [];
$browserCompatibility = array(
  'Chrome' => '71+',
  'FireFox' => '69+',
  'Safari' => '11+',
  'Trident' => '5+',
);
$dbServer = '192.168.56.1';
$dbPort = '80';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
$preventSSAutoBoot = false;
$serviceServerPort = '11478';
$stopSSEveryQuit = false;
$notUseServiceServer = true;
$messages['default'][9999] = "Changed";
$messages['ja'][9999] = "変更した";
$activateClientService = false;
$defaultTimezone = 'Asia/Tokyo';
EOF
  end
end

if node[:virtualization][:system] != 'docker'
  file "#{WEBROOT}/params.php" do
    action :edit
    block do |content|
      content << "$webServerName = ['192.168.56.101'];\n"
      content << "$serviceServerHost = '192.168.56.101';\n"
      content << "$serviceServerConnect = 'http://192.168.56.101';\n"
    end
  end
else
  file "#{WEBROOT}/params.php" do
    action :edit
    block do |content|
      content << "$webServerName = [''];\n"
      content << "$serviceServerHost = 'localhost';\n"
      content << "$serviceServerConnect = 'http://localhost';\n"
    end
  end
end


if node[:platform] == 'ubuntu'
  user "fmserver" do
    action :create
  end
  execute 'groupadd fmsadmin' do
    command 'groupadd fmsadmin'
  end
  execute 'usermod -a -G fmsadmin fmserver' do
    command 'usermod -a -G fmsadmin fmserver'
  end
  execute 'a2enmod rewrite' do
    command 'a2enmod rewrite'
  end
  file '/etc/apache2/sites-enabled/filemaker.conf' do
    content <<-EOF
RewriteEngine on
RewriteRule ^/admin-console(.*) http://127.0.0.1:16001/admin-console$1 [P,L]
RewriteRule ^/assets/(.*) http://127.0.0.1:16001/assets/$1 [P,L]
RewriteRule ^/(.*)-es(.*).js http://127.0.0.1:16001/$1-es$2.js [P,L]
RewriteRule ^/scripts.(.*).js http://127.0.0.1:16001/scripts.$1.js [P,L]
RewriteRule ^/styles.(.*).css http://127.0.0.1:16001/styles.$1.css [P,L]
RewriteCond %{SERVER_PORT} 80
RewriteRule ^/socket\.io/(.*) ws://127.0.0.1:16001/socket\.io/$1 [P,L]
RewriteRule ^/fmi/admin/(.*) http://127.0.0.1:16001/fmi/admin/$1 [P,L]
RewriteRule ^/fmi/data/(.*) http://127.0.0.1:3000/fmi/data/$1 [P,L]
EOF
  end
  directory '/opt/FileMaker/FileMaker Server/Data/Databases' do
    action :create
    owner 'fmserver'
    group 'fmsadmin'
  end
  execute 'cp -p /var/www/html/INTER-Mediator/dist-docs/TestDB.fmp12 "/opt/FileMaker/FileMaker Server/Data/Databases/"' do
    command 'cp -p /var/www/html/INTER-Mediator/dist-docs/TestDB.fmp12 "/opt/FileMaker/FileMaker Server/Data/Databases/"'
  end
  execute 'chown fmserver:fmsadmin "/opt/FileMaker/FileMaker Server/Data/Databases/TestDB.fmp12"' do
    command 'chown fmserver:fmsadmin "/opt/FileMaker/FileMaker Server/Data/Databases/TestDB.fmp12"'
  end
  package 'unzip' do
    action :install
  end
  service 'apache2' do
    action [ :restart ]
  end
end

if node[:platform] == 'alpine'
  execute "ln -s \"#{WEBROOT}\" \"#{OLDWEBROOT}\"" do
    command "ln -s \"#{WEBROOT}\" \"#{OLDWEBROOT}\""
  end
end

# Copy Samples

# directory "#{IMSAMPLE}" do
#   action :create
#   group 'im-developer'
#   mode '775'
# end
# execute "cd \"#{IMROOT}\"; rm -rf samples/*" do
#   command "cd \"#{IMROOT}\"; rm -rf samples/*"
# end
# execute "cd \"#{IMROOT}\"; git clone https://github.com/INTER-Mediator/INTER-Mediator_Samples samples; mkdir -p samples/vendor/inter-mediator; cd samples/vendor/inter-mediator; ln -s ../../../ inter-mediator" do
#   command "cd \"#{IMROOT}\"; git clone https://github.com/INTER-Mediator/INTER-Mediator_Samples samples; mkdir -p samples/vendor/inter-mediator; cd samples/vendor/inter-mediator; ln -s ../../../ inter-mediator"
# end

# Install php/js libraries

if node[:platform] == 'redhat' && node[:platform_version].to_f >= 7 && node[:platform_version].to_f < 8
  # Node.js 18 requires glibc 2.18
  execute "yum groupinstall \"Development tools\" -y" do
    command "yum groupinstall \"Development tools\" -y"
  end
  execute "wget https://ftp.gnu.org/gnu/glibc/glibc-2.18.tar.gz && tar zxvf glibc-2.18.tar.gz && cd glibc-2.18 && mkdir build && cd build && ../configure --prefix=/opt/glibc-2.18 && make -j4 && make install && export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/opt/glibc-2.18/lib" do
    command "wget https://ftp.gnu.org/gnu/glibc/glibc-2.18.tar.gz && tar zxvf glibc-2.18.tar.gz && cd glibc-2.18 && mkdir build && cd build && ../configure --prefix=/opt/glibc-2.18 && make -j4 && make install && export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/opt/glibc-2.18/lib"
  end
end

execute "su - developer -c 'cd \"#{IMROOT}\" && /usr/local/bin/composer update --with-all-dependencies'" do
  command "su - developer -c 'cd \"#{IMROOT}\" && /usr/local/bin/composer update --with-all-dependencies'"
end

# Install npm packages

if (node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 14 && node[:platform_version].to_f < 20) || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  execute 'npm install -g buster --unsafe-perm --before 2025-09-14' do
    command 'npm install -g buster --unsafe-perm --before 2025-09-14'
  end
end

if (node[:platform] == 'ubuntu' && node[:platform_version].to_f >= 14) || (node[:platform] == 'redhat' && node[:platform_version].to_f >= 6)
  if node[:platform] == 'redhat' && node[:platform_version].to_f >= 7
    package 'bzip2' do
      action :install  # for phantomjs
    end
  end

  if node[:platform] != 'alpine'
    execute 'npm install -g phantomjs-prebuilt --unsafe-perm --before 2025-09-14' do
      command 'npm install -g phantomjs-prebuilt --unsafe-perm --before 2025-09-14'
    end
  end
end

if node[:platform] == 'alpine' || node[:platform] == 'ubuntu'
  package 'xvfb' do
    action :install
  end
end

if node[:platform] == 'alpine' && node[:virtualization][:system] != 'docker'
  #package 'virtualbox-additions-grsec' do
  #  action :install
  #end
  #package 'virtualbox-guest-additions' do
  #  action :install
  #end
  #package 'virtualbox-guest-modules-grsec' do
  #  action :install
  #end
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

# Import schema

if node[:platform] == 'redhat'
  if node[:platform_version].to_f >= 6
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
      if node[:virtualization][:system] != 'docker'
        package 'firewalld' do
          action :install
        end
        service 'firewalld' do
          action [ :enable, :start ]
        end
        if node[:platform_version].to_f >= 8
          # Work-around Steps for AlmaxLinux 8
          execute 'sed -i -e "s/FirewallBackend=nftables/FirewallBackend=iptables/g" /etc/firewalld/firewalld.conf' do
            command 'sed -i -e "s/FirewallBackend=nftables/FirewallBackend=iptables/g" /etc/firewalld/firewalld.conf'
          end
          package 'iptables-services' do
            action :install
          end
          execute 'systemctl restart firewalld.service' do
            command 'systemctl restart firewalld.service'
          end
          execute 'firewall-cmd --zone=public --add-service=http --permanent' do
            command 'firewall-cmd --zone=public --add-service=http --permanent'
          end
          execute 'firewall-cmd --zone=public --add-service=samba --permanent' do
            command 'firewall-cmd --zone=public --add-service=samba --permanent'
          end
          execute 'firewall-cmd --reload' do
            command 'firewall-cmd --reload'
          end
        end
      end
    end
  end
end

if node[:platform] == 'redhat'
  execute "sed -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.sql\" > \"#{IMDISTDOC}/temp\"" do
    command "sed -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.sql\" > \"#{IMDISTDOC}/temp\""
  end
  execute "rm \"#{IMDISTDOC}/sample_schema_mysql.sql\"" do
    command "rm \"#{IMDISTDOC}/sample_schema_mysql.sql\""
  end
  execute "mv \"#{IMDISTDOC}/temp\" \"#{IMDISTDOC}/sample_schema_mysql.sql\"" do
    command "mv \"#{IMDISTDOC}/temp\" \"#{IMDISTDOC}/sample_schema_mysql.sql\""
  end
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
# Refer to the "Client Authentication" section in the PostgreSQL
# documentation for a complete description of this file.  A short
# synopsis follows.
#
# This file controls: which hosts are allowed to connect, how clients
# are authenticated, which PostgreSQL user names they can use, which
# databases they can access.  Records take one of these forms:
#
# local      DATABASE  USER  METHOD  [OPTIONS]
# host       DATABASE  USER  ADDRESS  METHOD  [OPTIONS]
# hostssl    DATABASE  USER  ADDRESS  METHOD  [OPTIONS]
# hostnossl  DATABASE  USER  ADDRESS  METHOD  [OPTIONS]
#
# (The uppercase items must be replaced by actual values.)
#
# The first field is the connection type: "local" is a Unix-domain
# socket, "host" is either a plain or SSL-encrypted TCP/IP socket,
# "hostssl" is an SSL-encrypted TCP/IP socket, and "hostnossl" is a
# plain TCP/IP socket.
#
# DATABASE can be "all", "sameuser", "samerole", "replication", a
# database name, or a comma-separated list thereof. The "all"
# keyword does not match "replication". Access to replication
# must be enabled in a separate record (see example below).
#
# USER can be "all", a user name, a group name prefixed with "+", or a
# comma-separated list thereof.  In both the DATABASE and USER fields
# you can also write a file name prefixed with "@" to include names
# from a separate file.
#
# ADDRESS specifies the set of hosts the record matches.  It can be a
# host name, or it is made up of an IP address and a CIDR mask that is
# an integer (between 0 and 32 (IPv4) or 128 (IPv6) inclusive) that
# specifies the number of significant bits in the mask.  A host name
# that starts with a dot (.) matches a suffix of the actual host name.
# Alternatively, you can write an IP address and netmask in separate
# columns to specify the set of hosts.  Instead of a CIDR-address, you
# can write "samehost" to match any of the server's own IP addresses,
# or "samenet" to match any address in any subnet that the server is
# directly connected to.
#
# METHOD can be "trust", "reject", "md5", "password", "scram-sha-256",
# "gss", "sspi", "ident", "peer", "pam", "ldap", "radius" or "cert".
# Note that "password" sends passwords in clear text; "md5" or
# "scram-sha-256" are preferred since they send encrypted passwords.
#
# OPTIONS are a set of options for the authentication in the format
# NAME=VALUE.  The available options depend on the different
# authentication methods -- refer to the "Client Authentication"
# section in the documentation for a list of which options are
# available for which authentication methods.
#
# Database and user names containing spaces, commas, quotes and other
# special characters must be quoted.  Quoting one of the keywords
# "all", "sameuser", "samerole" or "replication" makes the name lose
# its special character, and just match a database or username with
# that name.
#
# This file is read on server startup and when the server receives a
# SIGHUP signal.  If you edit the file on a running system, you have to
# SIGHUP the server for the changes to take effect, run "pg_ctl reload",
# or execute "SELECT pg_reload_conf()".
#
# Put your actual configuration here
# ----------------------------------
#
# If you want to allow non-local connections, you need to add more
# "host" records.  In that case you will also need to make PostgreSQL
# listen on a non-local interface via the listen_addresses
# configuration parameter, or via the -i or -h command line switches.



# TYPE  DATABASE        USER            ADDRESS                 METHOD

# "local" is for Unix domain socket connections only
local   all             all                                     peer
# IPv4 local connections:
host    all             all             127.0.0.1/32            trust
# IPv6 local connections:
host    all             all             ::1/128                 trust
# Allow replication connections from localhost, by a user with the
# replication privilege.
local   replication     all                                     peer
host    replication     all             127.0.0.1/32            ident
host    replication     all             ::1/128                 ident
EOF
  end
  #service 'postgresql' do
  #  action [ :restart ]
  #end
  execute 'systemctl reload postgresql.service' do
    command 'systemctl reload postgresql.service'
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

execute "echo \"y\" | bash \"#{IMVMROOT}/dbupdate.sh\"" do
  command "echo \"y\" | bash \"#{IMVMROOT}/dbupdate.sh\""
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

if node[:platform] == 'alpine' || node[:platform] == 'redhat'
  execute "setfacl --recursive --modify g:im-developer:rwx,d:g:im-developer:rwx \"#{WEBROOT}\"" do
    command "setfacl --recursive --modify g:im-developer:rwx,d:g:im-developer:rwx \"#{WEBROOT}\""
  end
end

if node[:platform] == 'redhat'
  execute "chown -R apache:im-developer \"#{WEBROOT}\"" do
    command "chown -R apache:im-developer \"#{WEBROOT}\""
  end
  execute "chown -R apache:im-developer /usr/share/httpd" do
    command "chown -R apache:im-developer /usr/share/httpd"
  end
else
  execute "chown -R developer:im-developer \"#{WEBROOT}\"" do
    command "chown -R developer:im-developer \"#{WEBROOT}\""
  end
end

execute "chmod -R a=rX,u+w,g+w \"#{WEBROOT}\"" do
  command "chmod -R a=rX,u+w,g+w \"#{WEBROOT}\""
end

execute "git config --global --add safe.directory \"#{WEBROOT}/INTER-Mediator\"" do
  command "git config --global --add safe.directory \"#{WEBROOT}/INTER-Mediator\""
end

execute "cd \"#{WEBROOT}\" && cd INTER-Mediator && git checkout ." do
  command "cd \"#{WEBROOT}\" && cd INTER-Mediator && git checkout ."
end

execute "chmod 664 #{WEBROOT}/*.html" do
  command "chmod 664 #{WEBROOT}/*.html"
end

execute "chmod 664 #{WEBROOT}/*.php" do
  command "chmod 664 #{WEBROOT}/*.php"
end

execute "chmod 775 \"#{IMVMROOT}/dbupdate.sh\"" do
  command "chmod 775 \"#{IMVMROOT}/dbupdate.sh\""
end

execute "chmod 755 \"#{IMVMROOT}/index.php\"" do
  command "chmod 755 \"#{IMVMROOT}/index.php\""
end

execute 'chown -R developer:developer /home/developer' do
  command 'chown -R developer:developer /home/developer'
end

if node[:platform] == 'alpine'
  file '/etc/apache2/conf.d/im.conf' do
    content <<-EOF
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule slotmem_shm_module modules/mod_slotmem_shm.so
RewriteEngine on
RewriteRule ^/fmi/rest/(.*) http://192.168.56.1/fmi/rest/$1 [P,L]
RewriteRule ^/fmi/xml/(.*)  http://192.168.56.1/fmi/xml/$1 [P,L]
EOF
  end
end

if node[:platform] == 'alpine'
  execute 'cat /etc/php7/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php7/php.ini.tmp && mv /etc/php7/php.ini.tmp /etc/php7/php.ini' do
    command 'cat /etc/php7/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php7/php.ini.tmp && mv /etc/php7/php.ini.tmp /etc/php7/php.ini'
  end
end
if node[:platform] == 'ubuntu'
  if node[:platform_version].to_f < 16
    execute 'cat /etc/php5/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php5/apache2/php.ini.tmp && mv /etc/php5/apache2/php.ini.tmp /etc/php5/apache2/php.ini' do
      command 'cat /etc/php5/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php5/apache2/php.ini.tmp && mv /etc/php5/apache2/php.ini.tmp /etc/php5/apache2/php.ini'
    end
  elsif node[:platform_version].to_f < 18
    execute 'cat /etc/php/7.2/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/7.2/apache2/php.ini.tmp && mv /etc/php/7.2/apache2/php.ini.tmp /etc/php/7.2/apache2/php.ini' do
      command 'cat /etc/php/7.2/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/7.2/apache2/php.ini.tmp && mv /etc/php/7.2/apache2/php.ini.tmp /etc/php/7.2/apache2/php.ini'
    end
  elsif node[:platform_version].to_f < 20
    execute 'cat /etc/php/8.0/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/8.0/apache2/php.ini.tmp && mv /etc/php/8.0/apache2/php.ini.tmp /etc/php/8.0/apache2/php.ini' do
      command 'cat /etc/php/8.0/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/8.0/apache2/php.ini.tmp && mv /etc/php/8.0/apache2/php.ini.tmp /etc/php/8.0/apache2/php.ini'
    end
  else
    execute 'cat /etc/php/8.4/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/8.4/apache2/php.ini.tmp && mv /etc/php/8.4/apache2/php.ini.tmp /etc/php/8.4/apache2/php.ini' do
      command 'cat /etc/php/8.4/apache2/php.ini | sed -e "s/max_execution_time = 30/max_execution_time = 120/g" | sed -e "s/max_input_time = 60/max_input_time = 120/g" | sed -e "s/memory_limit = 128M/memory_limit = 256M/g" | sed -e "s/post_max_size = 8M/post_max_size = 100M/g" | sed -e "s/upload_max_filesize = 2M/upload_max_filesize = 100M/g" > /etc/php/8.4/apache2/php.ini.tmp && mv /etc/php/8.4/apache2/php.ini.tmp /etc/php/8.4/apache2/php.ini'
    end
  end
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
# Most people will want "standalone server" or "member server".
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

[global]
   security = user
   passdb backend = tdbsam
   max protocol = SMB3
   min protocol = SMB2
   ea support = yes
   unix extensions = no
   browseable = no
   hosts allow = 192.168.56. 127.

[webroot]
   comment = Apache Root Directory
   path = #{WEBROOT}
   guest ok = no
   browseable = yes
   read only = no
   create mask = 0664
   directory mask = 0775
   force group = im-developer

[developer]
   comment = Home Directory
   path = /home/developer
   guest ok = no
   browseable = yes
   read only = no
   create mask = 0664
   directory mask = 0775
   force group = im-developer
EOF
end

execute '( echo *********; echo ********* ) | smbpasswd -s -a developer' do
  command '( echo im4135dev; echo im4135dev ) | smbpasswd -s -a developer'
end


# SELinux
if node[:platform] == 'redhat' && node[:virtualization][:system] != 'docker'
  package 'policycoreutils' do
    action :install
  end
  package 'libselinux-utils' do
    action :install
  end
  if node[:platform_version].to_f >= 8
    execute 'dnf makecache --refresh' do
      command 'dnf makecache --refresh'
    end
  end
  if node[:platform_version].to_f >= 8
    package 'python3-policycoreutils' do
      action :install
    end
    package 'policycoreutils-python-utils' do
      action :install
    end
  else
    package 'policycoreutils-python' do
      action :install
    end
  end
  execute 'setsebool -P samba_export_all_rw 1' do
    command 'setsebool -P samba_export_all_rw 1'
  end
  execute "cd \"#{IMSELINUX}\" && semodule -i inter-mediator.pp" do
    command "cd \"#{IMSELINUX}\" && semodule -i inter-mediator.pp"
  end
  execute "semanage fcontext -a -t httpd_sys_rw_content_t \"/var/www/html/(.*).html\"" do
    command "semanage fcontext -a -t httpd_sys_rw_content_t \"/var/www/html/(.*).html\""
  end
  execute "restorecon \"/var/www/html/*.html\"" do
    command "restorecon \"/var/www/html/*.html\""
  end
  execute "semanage fcontext -a -t httpd_sys_rw_content_t \"/var/www/html/(.*).php\"" do
    command "semanage fcontext -a -t httpd_sys_rw_content_t \"/var/www/html/(.*).php\""
  end
  execute "restorecon \"/var/www/html/*.php\"" do
    command "restorecon \"/var/www/html/*.php\""
  end
end


if node[:platform] == 'ubuntu'
  execute 'mysql -e "install plugin validate_password soname \'validate_password.so\';"' do
    command 'mysql -e "install plugin validate_password soname \'validate_password.so\';"'
  end
  execute 'mysql -e "set global validate_password_policy=LOW;"' do
    command 'mysql -e "set global validate_password_policy=LOW;"'
  end
  execute 'mysql -e "ALTER USER \'root\'@\'localhost\' IDENTIFIED WITH mysql_native_password BY \'*********\';"' do
    command 'mysql -e "ALTER USER \'root\'@\'localhost\' IDENTIFIED WITH mysql_native_password BY \'im4135dev\';"'
  end
end


if node[:platform] == 'ubuntu'
  file '/etc/default/keyboard' do
    owner 'root'
    group 'root'
    mode '644'
    content <<-EOF
# Check /usr/share/doc/keyboard-configuration/README.Debian for
# documentation on what to do after having modified this file.

# The following variables describe your keyboard and can have the same
# values as the XkbModel, XkbLayout, XkbVariant and XkbOptions options
# in /etc/X11/xorg.conf.

XKBMODEL="pc105"
XKBLAYOUT="jp"
XKBVARIANT=""
XKBOPTIONS=""

# If you don't want to use the XKB layout on the console, you can
# specify an alternative keymap.  Make sure it will be accessible
# before /usr is mounted.
# KMAP=/etc/console-setup/defkeymap.kmap.gz
EOF
  end
  package 'locales' do
    action :install
  end
  file '/etc/default/locale' do
    owner 'root'
    group 'root'
    mode '644'
    content 'LANG="ja_JP.UTF-8"'
  end
  execute 'locale-gen en_US.UTF-8' do
    command 'locale-gen en_US.UTF-8'
  end
  execute 'locale-gen en_GB.UTF-8' do
    command 'locale-gen en_GB.UTF-8'
  end
  execute '/usr/sbin/update-locale LANG="ja_JP.UTF-8"' do
    command '/usr/sbin/update-locale LANG="ja_JP.UTF-8"'
  end

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

export DISPLAY=:99.0
#/usr/local/bin/buster-server &
#/bin/sleep 5
#/usr/local/bin/phantomjs /usr/local/lib/node_modules/buster/script/phantom.js http://localhost:1111/capture > /dev/null &
/usr/bin/Xvfb :99 -screen 0 1024x768x24 -extension RANDR > /dev/null 2>&1 &
/bin/sleep 5
#firefox http://localhost:1111/capture > /dev/null &
google-chrome-stable --no-sandbox --headless --remote-debugging-port=9222 http://localhost:1111/capture > /dev/null &
exit 0
EOF
  end

  execute 'chmod u+s /usr/bin/fbterm' do
    command 'chmod u+s /usr/bin/fbterm'
  end

  if node[:platform_version].to_f < 16
    execute 'dpkg-reconfigure -f noninteractive keyboard-configuration' do
      command 'dpkg-reconfigure -f noninteractive keyboard-configuration'
    end
  end
end


# Install Selenium WebDriver
if node[:platform] == 'alpine'
  package 'dbus' do
    action :install
  end
  package 'firefox-esr' do
    action :install
  end
  package 'chromium' do
    action :install
  end
  package 'libgudev' do
    action :install
  end
elsif node[:platform] == 'ubuntu'
  package 'curl' do
    action :install
  end
  package 'x11-xkb-utils' do
    action :install
  end
  package 'xfonts-100dpi' do
    action :install
  end
  package 'xfonts-75dpi' do
    action :install
  end
  package 'xfonts-cyrillic' do
    action :install
  end
  package 'xfonts-scalable' do
    action :install
  end
  package 'xserver-xorg-core' do
    action :install
  end
  package 'dbus-x11' do
    action :install
  end
  package 'libfontconfig1-dev' do
    action :install
  end
  package 'libexif12' do
    action :install
  end
  package 'xbase-clients' do
    action :install
  end
  if node[:platform_version].to_f < 16
    package 'ruby2.0' do
      action :install
    end
    package 'ruby2.0-dev' do
      action :install
    end
    execute 'gem2.0 install rspec --no-ri --no-rdoc' do
      command 'gem2.0 install rspec --no-ri --no-rdoc'
    end
    execute 'gem2.0 install bundler --no-ri --no-rdoc' do
      command 'gem2.0 install bundler --no-ri --no-rdoc'
    end
    execute 'gem2.0 install ffi -v "1.9.18" --no-ri --no-rdoc' do
      command 'gem2.0 install ffi -v "1.9.18" --no-ri --no-rdoc'
    end
    execute 'gem2.0 install childprocess -v "0.9.0" --no-ri --no-rdoc' do
      command 'gem2.0 install childprocess -v "0.9.0" --no-ri --no-rdoc'
    end
    execute 'gem2.0 install selenium-webdriver -v "3.142.2" --no-ri --no-rdoc' do
      command 'gem2.0 install selenium-webdriver -v "3.142.2" --no-ri --no-rdoc'
    end
  elsif node[:platform_version].to_f < 18
    package 'ruby2.3' do
      action :install
    end
    package 'ruby2.3-dev' do
      action :install
    end
    execute 'gem2.3 install rspec --no-ri --no-rdoc' do
      command 'gem2.3 install rspec --no-ri --no-rdoc'
    end
    execute 'gem2.3 install bundler --no-ri --no-rdoc' do
      command 'gem2.3 install bundler --no-ri --no-rdoc'
    end
    execute 'gem2.3 install ffi --no-ri --no-rdoc' do
      command 'gem2.3 install ffi --no-ri --no-rdoc'
    end
    execute 'gem2.3 install childprocess -v "0.9.0" --no-ri --no-rdoc' do
      command 'gem2.3 install childprocess -v "0.9.0" --no-ri --no-rdoc'
    end
    execute 'gem2.3 install selenium-webdriver -v "3.142.2" --no-ri --no-rdoc' do
      command 'gem2.3 install selenium-webdriver -v "3.142.2" --no-ri --no-rdoc'
    end
  else
    package 'ruby' do
      action :install
    end
    package 'ruby-dev' do
      action :install
    end
    package 'libffi-dev' do
      action :install
    end
    execute 'gem install rspec -N' do
      command 'gem install rspec -N'
    end
    execute 'gem install bundler -N' do
      command 'gem install bundler -N'
    end
    execute 'gem install ffi -N' do
      command 'gem install ffi -N'
    end
    execute 'gem install childprocess -N' do
      command 'gem install childprocess -N'
    end
    execute 'gem install selenium-webdriver -N' do
      command 'gem install selenium-webdriver -N'
    end
  end

  if node[:platform_version].to_f < 24
    package 'firefox' do
      action :install
    end
    execute 'curl -L https://github.com/mozilla/geckodriver/releases/download/v0.36.0/geckodriver-v0.36.0-linux64.tar.gz > /tmp/geckodriver-v0.36.0-linux64.tar.gz; cd /usr/bin/; tar xzvf /tmp/geckodriver-v0.36.0-linux64.tar.gz' do
      command 'curl -L https://github.com/mozilla/geckodriver/releases/download/v0.36.0/geckodriver-v0.36.0-linux64.tar.gz > /tmp/geckodriver-v0.36.0-linux64.tar.gz; cd /usr/bin/; tar xzvf /tmp/geckodriver-v0.36.0-linux64.tar.gz'
    end
  end

  package 'libasound2t64' do
    action :install
  end
  package 'libatk-bridge2.0-0' do
    action :install
  end
  package 'libatk1.0-0' do
    action :install
  end
  package 'libatspi2.0-0' do
    action :install
  end
  package 'libcairo2' do
    action :install
  end
  package 'libgtk-3-0' do
    action :install
  end
  package 'libnspr4' do
    action :install
  end
  package 'libnss3' do
    action :install
  end
  package 'libpango-1.0-0' do
    action :install
  end
  package 'libxdamage1' do
    action :install
  end
  package 'libxkbcommon0' do
    action :install
  end
  package 'fonts-liberation' do
    action :install
  end
  execute 'wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb' do
    command 'wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb'
  end
  execute 'dpkg -i google-chrome-stable_current_amd64.deb' do
    command 'dpkg -i google-chrome-stable_current_amd64.deb'
  end
  execute 'rm -f /usr/local/bin/chromedriver' do
    command 'rm -f /usr/local/bin/chromedriver'
  end
end


if node[:platform] == 'alpine'
  file '/etc/local.d/buster-server.start' do
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

export DISPLAY=:99.0
Xvfb :99 -screen 0 1024x768x24 &
/bin/sleep 5
#/usr/bin/buster-server &
#/bin/sleep 5
firefox http://localhost:1111/capture > /dev/null &
#chromium-browser --no-sandbox http://localhost:1111/capture > /dev/null &
/bin/sleep 5
exit 0
EOF
  end
  execute 'rc-update add local default' do
    command 'rc-update add local default'
  end
  if node[:virtualization][:system] == 'docker'
    execute '/etc/local.d/buster-server.start' do
      command '/etc/local.d/buster-server.start'
    end
  end
end


if node[:platform] == 'alpine'
  execute "\"#{IMROOT}\"/dist-docs/installfiles.sh -2" do
    command "\"#{IMROOT}\"/dist-docs/installfiles.sh -2"
  end
  execute "cd \"#{IMROOT}\";composer install" do
    command "cd \"#{IMROOT}\";composer install"
  end
  execute "cd \"#{IMROOT}\";npm install --before 2025-09-14" do
    command "cd \"#{IMROOT}\";npm install --before 2025-09-14"
  end
end


if node[:platform] == 'alpine'
  execute "chmod 755 \"#{WEBROOT}\"/INTER-Mediator/node_modules/jest/bin/jest.js" do
    command "chmod 755 \"#{WEBROOT}\"/INTER-Mediator/node_modules/jest/bin/jest.js"
  end
end
#if node[:platform] == 'ubuntu' && node[:virtualization][:system] == 'docker'
#  execute 'sudo /etc/rc.local &' do
#      command 'sudo /etc/rc.local &'
#  end
#end
if node[:virtualization][:system] != 'docker'
  if node[:platform] == 'redhat' || node[:platform] == 'ubuntu'
    service 'smb' do
      action [ :stop ]
    end
    service 'postgresql' do
      action [ :stop ]
    end
    service 'mariadb' do
      action [ :stop ]
    end
  end
  if node[:platform] == 'redhat'
    service 'httpd' do
      action [ :stop ]
    end
  elsif node[:platform] == 'ubuntu'
    service 'apache2' do
      action [ :stop ]
    end
  end
  if node[:platform] == 'redhat' || node[:platform] == 'ubuntu'
    execute '/var/www/html/INTER-Mediator/node_modules/.bin/forever stopall' do
      command '/var/www/html/INTER-Mediator/node_modules/.bin/forever stopall'
    end
    execute 'rm -f mitamae-x86_64-linux; rm -f recipe.rb; /sbin/shutdown -h +1' do
      command 'rm -f mitamae-x86_64-linux; rm -f recipe.rb; /sbin/shutdown -h +1'
    end
  else
    execute 'rm -f mitamae-x86_64-linux; rm -f recipe.rb; poweroff' do
      command 'rm -f mitamae-x86_64-linux; rm -f recipe.rb; poweroff'
    end
  end
end
