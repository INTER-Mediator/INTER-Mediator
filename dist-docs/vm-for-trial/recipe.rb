# Recipe file of Itamae for Ubuntu Server 14.04.1 or CentOS 6.6
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

execute "groupadd im-developer" do
  command "groupadd im-developer"
end

execute "usermod -a -G im-developer developer" do
  command "usermod -a -G im-developer developer"
end

if os[:family] == "ubuntu"
  execute "usermod -a -G im-developer www-data" do
    command "usermod -a -G im-developer www-data"
  end
elsif os[:family] == "redhat"
  package "httpd" do
    action :install
  end
  service "httpd" do
    action [ :enable, :start ]
  end
  execute "usermod -a -G im-developer apache" do
    command "usermod -a -G im-developer apache"
  end
end

if os[:family] == "redhat"
  package "postgresql-server" do
    action :install
  end
  execute "service postgresql initdb" do
    command "service postgresql initdb"
  end
  service "postgresql" do
    action [ :enable, :start ]
  end
end

user "postgres" do
  password "$6$inter-mediator$kEUWd5ZQNPEfNF7CPzRMDoHhmz67rgJTmDbUsJ3AL35vV3c5sGk9ml2kLRj.2z5BkygH7SS2E549qTB2FYs6S/"
end

if os[:family] == "ubuntu"
  file "/etc/mysql/conf.d/im.cnf" do
    content "character-set-server=utf8mb4\nskip-character-set-client-handshake\n[client]\ndefault-character-set=utf8mb4\n[mysqldump]\ndefault-character-set=utf8mb4\n[mysql]\ndefault-character-set=utf8mb4"
  end
elsif os[:family] == "redhat"
  package "mysql-server" do
    action :install
  end
  service "mysqld" do
    action [ :enable, :start ]
  end
  file "/etc/my.cnf" do
    content "[mysqld]\ndatadir=/var/lib/mysql\nsocket=/var/lib/mysql/mysql.sock\nuser=mysql\n# Disabling symbolic-links is recommended to prevent assorted security risks\nsymbolic-links=0\ncharacter-set-server=utf8\nskip-character-set-client-handshake\n\n[mysqld_safe]\nlog-error=/var/log/mysqld.log\npid-file=/var/run/mysqld/mysqld.pid\n\n[client]\ndefault-character-set=utf8\n\n[mysqldump]\ndefault-character-set=utf8\n\n[mysql]\ndefault-character-set=utf8"
  end
  execute "mysql -e \"GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by '*********';\" -u root" do
    command "mysql -e \"GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' identified by 'im4135dev';\" -u root"
  end
end

if os[:family] == "ubuntu"
  execute "aptitude update" do
    command "aptitude update"
  end

  execute "aptitude full-upgrade" do
    command "aptitude full-upgrade --assume-yes"
  end
elsif os[:family] == "redhat"
  execute "yum -y update" do
    command "yum -y update"
  end
end

package "sqlite" do
  action :install
end

package "acl" do
  action :install
end

if os[:family] == "ubuntu"
  package "libmysqlclient-dev" do
    action :install
  end
elsif os[:family] == "redhat"
  package "mysql-devel" do
    action :install
  end
end

if os[:family] == "ubuntu"
  package "php5-pgsql" do
    action :install
  end
elsif os[:family] == "redhat"
  package "php-pgsql" do
    action :install
  end
end

if os[:family] == "ubuntu"
  package "php5-sqlite" do
    action :install
  end
elsif os[:family] == "redhat"
  package "php-pdo" do
    action :install
  end
end

if os[:family] == "ubuntu"
  package "php5-curl" do
    action :install
  end
end

package "git" do
  action :install
end

if os[:family] == "redhat"
  package "epel-release" do
    action :install
  end
end
package "nodejs" do
  action :install
end

execute "update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10" do
  command "update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10"
end

package "npm" do
  action :install
end

if os[:family] == "ubuntu"
  package "libfontconfig1" do
    action :install
  end
elsif os[:family] == "redhat"
  package "fontconfig-devel" do
    action :install
  end
end

if os[:family] == "ubuntu"
  package "phpunit" do
    action :install
  end
elsif os[:family] == "redhat"
  package "php-phpunit-PHPUnit" do
    action :install
  end
end

if os[:family] == "ubuntu"
  execute "aptitude clean" do
    command "aptitude clean"
  end
end

execute "cd \"#{WEBROOT}\" && git clone https://github.com/msyk/INTER-Mediator.git" do
  command "cd \"#{WEBROOT}\" && git clone https://github.com/msyk/INTER-Mediator.git"
end

if os[:family] == "ubuntu"
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
  content "AddType \"text/html; charset=UTF-8\" .html"
end

if os[:family] == "ubuntu"
  file "#{WEBROOT}/params.php" do
    content "<?php\n$dbUser = \"web\";\n$dbPassword = \"password\";\n$dbDSN = \"mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4\";\n$dbOption = array();\n$browserCompatibility = array(\n    \"Chrome\" => \"1+\",\"FireFox\" => \"2+\",\"msie\" => \"9+\",\"Opera\" => \"1+\",\n    \"Safari\" => \"4+\",\"Trident\" => \"5+\",\n);\n$dbServer = \"192.168.56.1\";\n$dbPort = \"80\";\n$dbDataType = \"FMPro12\";\n$dbDatabase = \"TestDB\";\n$dbProtocol = \"HTTP\";\n"
  end
elsif os[:family] == "redhat"
  file "#{WEBROOT}/params.php" do
    content "<?php\n$dbUser = \"web\";\n$dbPassword = \"password\";\n$dbDSN = \"mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8\";\n$dbOption = array();\n$browserCompatibility = array(\n    \"Chrome\" => \"1+\",\"FireFox\" => \"2+\",\"msie\" => \"9+\",\"Opera\" => \"1+\",\n    \"Safari\" => \"4+\",\"Trident\" => \"5+\",\n);\n$dbServer = \"192.168.56.1\";\n$dbPort = \"80\";\n$dbDataType = \"FMPro12\";\n$dbDatabase = \"TestDB\";\n$dbProtocol = \"HTTP\";\n"
  end
end

execute "sed -E -e 's|sqlite:/tmp/sample.sq3|sqlite:/var/db/im/sample.sq3|' \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\" > \"#{IMUNITTEST}/temp\"" do
  command "sed -E -e 's|sqlite:/tmp/sample.sq3|sqlite:/var/db/im/sample.sq3|' \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\" > \"#{IMUNITTEST}/temp\""
end
execute "rm \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\"" do
  command "rm \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\""
end
execute "mv \"#{IMUNITTEST}/temp\" \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\"" do
  command "mv \"#{IMUNITTEST}/temp\" \"#{IMUNITTEST}/DB_PDO-SQLite_Test.php\""
end


# Install npm packages

execute "npm install -g buster" do
  command "npm install -g buster"
end

execute "npm install -g phantomjs" do
  command "npm install -g phantomjs"
end


# Activate DefEdit/PageEdit

execute "sed -E -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/defedit.php\" > \"#{IMSUPPORT}/temp\"" do
  command "sed -E -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/defedit.php\" > \"#{IMSUPPORT}/temp\""
end
execute "rm \"#{IMSUPPORT}/defedit.php\"" do
  command "rm \"#{IMSUPPORT}/defedit.php\""
end
execute "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/defedit.php\"" do
  command "mv \"#{IMSUPPORT}/temp\" \"#{IMSUPPORT}/defedit.php\""
end

execute "sed -E -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/pageedit.php\" > \"#{IMSUPPORT}/temp\"" do
  command "sed -E -e 's|//IM_Entry|IM_Entry|' \"#{IMSUPPORT}/pageedit.php\" > \"#{IMSUPPORT}/temp\""
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
    execute "sed -E -e 's|\('INTER-Mediator.php'\)|\('INTER-Mediator/INTER-Mediator.php'\)|' \"#{IMSAMPLE}/templates/definition_file_simple.php\" > \"#{WEBROOT}/def#{num}.php\"" do
      command "sed -E -e 's|\('INTER-Mediator.php'\)|\('INTER-Mediator/INTER-Mediator.php'\)|' \"#{IMSAMPLE}/templates/definition_file_simple.php\" > \"#{WEBROOT}/def#{num}.php\""
  end
  file "#{WEBROOT}/def#{num}.php" do
    mode "664"
  end
  execute "sed -E -e 's/definitin_file_simple.php/def#{num}.php/' \"#{IMSAMPLE}/templates/page_file_simple.html\" > \"#{WEBROOT}/page#{num}.html\"" do
    command "sed -E -e 's/definitin_file_simple.php/def#{num}.php/' \"#{IMSAMPLE}/templates/page_file_simple.html\" > \"#{WEBROOT}/page#{num}.html\""
  end
  file "#{WEBROOT}/page#{num}.html" do
    mode "664"
  end
end

execute "chmod -R g+w \"#{WEBROOT}\"" do
  command "chmod -R g+w \"#{WEBROOT}\""
end


# Import schema

if os[:family] == "redhat"
  execute "sed -E -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.txt\" > \"#{IMDISTDOC}/temp\"" do
    command "sed -E -e 's|utf8mb4|utf8|g' \"#{IMDISTDOC}/sample_schema_mysql.txt\" > \"#{IMDISTDOC}/temp\""
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

execute "echo 'im4135dev' | sudo -u postgres -S psql -c 'create database test_db;'" do
  command "echo 'im4135dev' | sudo -u postgres -S psql -c 'create database test_db;'"
end

execute "echo 'im4135dev' | sudo -u postgres -S psql -f \"#{IMDISTDOC}/sample_schema_pgsql.txt\" test_db" do
  command "echo 'im4135dev' | sudo -u postgres -S psql -f \"#{IMDISTDOC}/sample_schema_pgsql.txt\" test_db"
end

directory "/var/db/im" do
  action :create
  group "im-developer"
  mode  "775"
end
if os[:family] == "ubuntu"
  directory "/var/db/im" do
    owner "www-data"
  end
elsif os[:family] == "redhat"
  directory "/var/db/im" do
    owner "apache"
  end
end

if os[:family] == "ubuntu"
  file "/var/db/im/sample.sq3" do
    owner "www-data"
    group "im-developer"
    mode  "664"
  end
elsif os[:family] == "redhat"
  file "/var/db/im/sample.sq3" do
    owner "apache"
    group "im-developer"
    mode  "664"
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
  
if os[:family] == "redhat"
  file "/etc/sysconfig/iptables" do
    content "# Firewall configuration written by system-config-firewall\n# Manual customization of this file is not recommended.\n*filter\n:INPUT ACCEPT [0:0]\n:FORWARD ACCEPT [0:0]\n:OUTPUT ACCEPT [0:0]\n-A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT\n-A INPUT -p icmp -j ACCEPT\n-A INPUT -i lo -j ACCEPT\n-A INPUT -m state --state NEW -m tcp -p tcp --dport 22 -j ACCEPT\n-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT\n-A INPUT -j REJECT --reject-with icmp-host-prohibited\n-A FORWARD -j REJECT --reject-with icmp-host-prohibited\nCOMMIT"
  end
end
  
execute "/sbin/shutdown -h now" do
  command "/sbin/shutdown -h now"
end
