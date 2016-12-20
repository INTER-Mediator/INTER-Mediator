require 'spec_helper'

if ENV['CIRCLECI']
  class Docker::Container
    def remove(options={}); end
    alias_method :delete, :remove
  end
end

if os[:family] == 'alpine'
  WEBROOT = "/var/www/localhost/htdocs"
else
  WEBROOT = "/var/www/html"
end

#describe package('ruby'), :if => os[:virtualization][:system] == 'docker' do
#  it { should be_installed }
#end

describe package('apache2'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('httpd'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('openssh'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('openssh-server'), :if => os[:family] == 'ubuntu' || os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('mariadb-client'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('mariadb'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('mysql-server'), :if => os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f < 7) do
  it { should be_installed }
end
describe package('mariadb-server'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  it { should be_installed }
end

describe package('postgresql'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('postgresql-server'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe service('apache2'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' do
  it { should be_enabled }
  it { should be_running }
end
describe service('httpd'), :if => os[:family] == 'redhat' do
  it { should be_enabled }
  it { should be_running }
end
describe service('org.apache.httpd'), :if => os[:family] == 'darwin' do
  it { should be_enabled }
  it { should be_running }
end

describe port(80) do
  it { should be_listening }
end

describe service('ssh'), :if => os[:family] == 'ubuntu' do
  it { should be_enabled }
end
describe service('sshd'), :if => os[:family] == 'alpine' || os[:family] == 'redhat' do
  it { should be_enabled }
end

describe service('ssh'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_running }
end
describe service('sshd'), :if => os[:family] == 'alpine' || (os[:family] == 'ubuntu' && os[:release].to_f < 16) || os[:family] == 'redhat' do
  it { should be_running }
end

describe service('mysql'), :if => os[:family] == 'ubuntu' do
  it { should be_enabled }
end
describe service('mysqld'), :if => os[:family] == 'redhat' && os[:release].to_f < 7 do
  it { should be_enabled }
end
describe service('mariadb'), :if => os[:family] == 'alpine' || (os[:family] == 'redhat' && os[:release].to_f >= 7) do
  it { should be_enabled }
end

describe service('mysqld'), :if => (os[:family] == 'ubuntu' && os[:release].to_f < 16) || (os[:family] == 'redhat' && os[:release].to_f < 7) do
  it { should be_running }
end
describe service('mysql'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_running }
end
describe service('mariadb'), :if => os[:family] == 'alpine' || (os[:family] == 'redhat' && os[:release].to_f >= 7) do
  it { should be_running }
end

describe service('postgresql') do
  it { should be_enabled }
end

describe service('postgres'), :if => (os[:family] == 'ubuntu' && os[:release].to_f < 16) || (os[:family] == 'redhat' && os[:release].to_f < 7) do
  it { should be_running }
end
describe service('postgresql'), :if => os[:family] == 'alpine' || (os[:family] == 'ubuntu' && os[:release].to_f >= 16) || (os[:family] == 'redhat' && os[:release].to_f >= 7) do
  it { should be_running }
end

describe group('im-developer') do
  it { should exist }
end

describe user('developer') do
  it { should exist }
  it { should belong_to_group 'im-developer' }
end

describe user('www-data'), :if => os[:family] == 'ubuntu' do
  it { should exist }
  it { should belong_to_group 'im-developer' }
end
describe user('apache'), :if => os[:family] == 'alpine' || os[:family] == 'redhat' do
  it { should exist }
  it { should belong_to_group 'im-developer' }
end

describe user('postgres') do
  it { should exist }
end

describe file('/etc/mysql/my.cnf'), :if => os[:family] == 'alpine' do
  it { should be_file }
  its(:content) { should match /[mysqld]/ }
  its(:content) { should match /socket=\/run\/mysqld\/mysqld.sock/ }
  its(:content) { should match /character-set-server=utf8mb4/ }
  its(:content) { should match /skip-character-set-client-handshake/ }
  its(:content) { should match /[client]/ }
  its(:content) { should match /[mysqldump]/ }
  its(:content) { should match /[mysql]/ }
end
describe file('/etc/mysql/conf.d/im.cnf'), :if => os[:family] == 'ubuntu' do
  it { should be_file }
  its(:content) { should match /[mysqld]/ }
  its(:content) { should match /character-set-server=utf8mb4/ }
  its(:content) { should match /skip-character-set-client-handshake/ }
  its(:content) { should match /[client]/ }
  its(:content) { should match /[mysqldump]/ }
  its(:content) { should match /[mysql]/ }
end
describe file('/etc/my.cnf'), :if => os[:family] == 'redhat' && os[:release].to_f < 7 do
  it { should be_file }
  its(:content) { should match /[mysqld]/ }
  its(:content) { should match /socket=\/var\/lib\/mysql\/mysql.sock/ }
  its(:content) { should match /character-set-server=utf8/ }
  its(:content) { should match /skip-character-set-client-handshake/ }
  its(:content) { should match /[client]/ }
  its(:content) { should match /[mysqldump]/ }
  its(:content) { should match /[mysql]/ }
end
describe file('/etc/my.cnf.d/im.cnf'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  it { should be_file }
  its(:content) { should match /[mysqld]/ }
  its(:content) { should match /socket=\/var\/lib\/mysql\/mysql.sock/ }
  its(:content) { should match /character-set-server=utf8mb4/ }
  its(:content) { should match /skip-character-set-client-handshake/ }
  its(:content) { should match /[client]/ }
  its(:content) { should match /[mysqldump]/ }
  its(:content) { should match /[mysql]/ }
end

describe package('sqlite'), :if => os[:family] == 'alpine' || (os[:family] == 'ubuntu' && os[:release].to_f < 16) || os[:family] == 'redhat' do
  it { should be_installed }
end
describe package('sqlite3'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end

describe package('acl'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' || os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('php5'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-apache2'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-curl'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-pdo'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-pdo_mysql'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-pdo_pgsql'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-pdo_sqlite'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-openssl'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-dom'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-json'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-bcmath'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('php5-phar'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe file('/usr/local/bin/phpunit'), :if => os[:family] == 'alpine' do
  it { should be_file }
end
describe package('libmysqlclient-dev'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('php'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end
describe package('php7.0-mbstring'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end
describe package('php-mbstring'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end
describe package('php7.0-bcmath'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end
describe package('php-pear'), :if => os[:family] == 'redhat' && os[:release].to_f < 6 do
  it { should be_installed }
end
describe package('gcc'), :if => os[:family] == 'redhat' && os[:release].to_f < 6 do
  it { should be_installed }
end
describe package('pcre-devel'), :if => os[:family] == 'redhat' && os[:release].to_f < 6 do
  it { should be_installed }
end
describe package('php-mysql'), :if => os[:family] == 'redhat' && os[:release].to_f < 7 do
  it { should be_installed }
end
describe package('mysql-devel'), :if => os[:family] == 'redhat' && os[:release].to_f < 7 do
  it { should be_installed }
end
describe package('mariadb-devel'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  it { should be_installed }
end
describe package('php-mysqlnd'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  it { should be_installed }
end

describe package('php5-mysql'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-mysql'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end
describe package('php-mysql'), :if => os[:family] == 'readhat' do
  it { should be_installed }
end

describe package('php5-pgsql'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-pgsql'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end
describe package('php-pgsql'), :if => os[:family] == 'readhat' do
  it { should be_installed }
end

describe package('php5-sqlite'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-sqlite3'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end
describe package('php-pdo'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('php-common'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('php5-curl'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-curl'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end

describe package('php5-gd'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-gd'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end

describe package('php5-xmlrpc'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-xmlrpc'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end

describe package('php5-intl'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_installed }
end
describe package('php7.0-intl'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_installed }
end

describe package('git'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' || os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('nodejs'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f >= 6) do
  it { should be_installed }
end

describe file('/usr/bin/node'), :if => os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f >= 6) do
  it { should be_file }
end

describe package('nodejs-legacy'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('npm'), :if => os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f >= 6) do
  it { should be_installed }
end

describe package('buster'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f >= 6) do
  it { should be_installed.by('npm').with_version('0.7.18') }
end

describe package('bzip2'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  it { should be_installed }
end

describe package('xvfb'), :if => os[:family] == 'alpine' || os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('dbus'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('firefox'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end

describe package('chromium'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('libgudev'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end

describe package('phantomjs'), :if => os[:family] == 'ubuntu' || (os[:family] == 'redhat' && os[:release].to_f >= 6) do
  it { should be_installed.by('npm').with_version('2.1.7') }
end

describe package('fontconfig-dev'), :if => os[:family] == 'alpine' do
  it { should be_installed }
end
describe package('libfontconfig1'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('fontconfig-devel'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('phpunit'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end
describe package('php-phpunit-PHPUnit'), :if => os[:family] == 'redhat' && os[:release].to_f >= 6 do
  it { should be_installed }
end

describe package('samba') do
  it { should be_installed }
end

describe package('language-pack-ja'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('fbterm'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('unifont'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('linux-generic-lts-xenial'), :if => os[:family] == 'ubuntu' && os[:release].to_i == 14 do
  it { should be_installed }
end

describe package('linux-image-generic-lts-xenial'), :if => os[:family] == 'ubuntu' && os[:release].to_i == 14 do
  it { should be_installed }
end

describe file('/etc/apache2/mods-enabled/headers.load'), :if => os[:family] == 'ubuntu' do
  it { should be_file }
end

describe file('/etc/apache2/sites-enabled/inter-mediator-server.conf'), :if => os[:family] == 'ubuntu' do
  it { should be_file }
  its(:content) { should match /#Header add Content-Security-Policy "default-src 'self'"/ }
end

describe file(WEBROOT + '/INTER-Mediator') do
  it { should be_directory }
end

describe command('git --git-dir=' + WEBROOT + '/INTER-Mediator/.git status | grep -o "Changes not staged for commit:"') do
  its(:stdout) { should match // }
end

describe file(WEBROOT + '/index_original.html'), :if => os[:family] == 'ubuntu' do
  it { should_not be_file }
end

describe file(WEBROOT + '/INTER-Mediator/INTER-Mediator-Support') do
  it { should be_directory }
end

describe file(WEBROOT + '/INTER-Mediator/INTER-Mediator-UnitTest') do
  it { should be_directory }
end

describe file(WEBROOT + '/INTER-Mediator/INTER-Mediator-UnitTest/DB_PDO-SQLite_Test.php') do
  it { should be_file }
  its(:content) { should match /sqlite:\/var\/db\/im\/sample.sq3/ }
end

describe file(WEBROOT + '/index.html') do
  it { should_not be_file }
end

describe file(WEBROOT + '/index.php') do
  it { should be_symlink }
end

describe file(WEBROOT + '/INTER-Mediator/dist-docs/vm-for-trial/index.html') do
  it { should be_file }
  its(:content) { should match /<meta http-equiv="refresh" content="0; URL=http:\/\/192.168.56.101\/INTER-Mediator\/dist-docs\/vm-for-trial\/index.php">/ }
end

describe command('diff -c ' + WEBROOT + '/index.php ' + WEBROOT + '/INTER-Mediator/dist-docs/vm-for-trial/index.php') do
  its(:stdout) { should match // }
end

describe file(WEBROOT + '/.htaccess') do
  it { should be_file }
  its(:content) { should match /AddType "text\/html; charset=UTF-8" .html/ }
end

describe file(WEBROOT + '/params.php') do
  it { should be_file }
  its(:content) { should match /\$dbUser = 'web';/ }
  its(:content) { should match /\$dbOption = array\(\);/ }
  its(:content) { should match /\$dbServer = '192.168.56.1';/ }
  its(:content) { should match /\$generatedPrivateKey = <<<EOL/ }
end
describe file(WEBROOT + '/params.php'), :if => os[:family] == 'alpine' do
  its(:content) { should match /\$dbDSN = 'mysql:unix_socket=\/var\/run\/mysqld\/mysqld.sock;dbname=test_db;charset=utf8mb4';/ }
end
describe file(WEBROOT + '/params.php'), :if => os[:family] == 'ubuntu' do
  its(:content) { should match /\$dbDSN = 'mysql:unix_socket=\/var\/run\/mysqld\/mysqld.sock;dbname=test_db;charset=utf8mb4';/ }
end
describe file(WEBROOT + '/params.php'), :if => os[:family] == 'redhat' && os[:release].to_f < 7 do
  its(:content) { should match /\$dbDSN = 'mysql:unix_socket=\/var\/lib\/mysql\/mysql.sock;dbname=test_db;charset=utf8';/ }
end
describe file(WEBROOT + '/params.php'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  its(:content) { should match /\$dbDSN = 'mysql:unix_socket=\/var\/lib\/mysql\/mysql.sock;dbname=test_db;charset=utf8mb4';/ }
end

describe file(WEBROOT + '/INTER-Mediator/INTER-Mediator-Support/defedit.php') do
  it { should be_file }
end

describe file(WEBROOT + '/INTER-Mediator/INTER-Mediator-Support/pageedit.php') do
  it { should be_file }
end

describe file(WEBROOT + '/INTER-Mediator/dist-docs/vm-for-trial/dbupdate.sh') do
  it { should be_file }
  it { should be_mode 664 }
end

describe command('date -d "`cat ' + WEBROOT + '/INTER-Mediator/dist-docs/readme.txt  | grep TestDB | cut -d"(" -f2 | cut -d")" -f1 | cut -d":" -f2`" +"%Y-%m-%d" | grep -o `git --git-dir=' + WEBROOT + '/INTER-Mediator/.git log -1 --date=short --pretty=format:"%cd" -- -p dist-docs/TestDB.fmp12` | wc -l') do
  its(:stdout) { should match /1/ }
end

range = 1..40
range.each{|num|
  describe file(WEBROOT + '/def' + "%02d" % num + '.php') do
    it { should be_file }
    it { should be_mode 664 }
    its(:content) { should match /require_once\('INTER-Mediator\/INTER-Mediator.php'\);/ }
  end

  describe file(WEBROOT + '/page' + "%02d" % num + '.html') do
    it { should be_file }
    it { should be_mode 664 }
    its(:content) { should match /<!DOCTYPE html>/ }
  end
}

describe command('mysql -u root --password=im4135dev test_db -e \'SHOW TABLES\'') do
  its(:stdout) { should match /cor_way_kind/ }
end

describe command('echo "im4135dev" | sudo -u postgres -S psql -c \'\\l\'') do
  its(:stdout) { should match /test_db/ }
end

describe file('/var/lib/pgsql/data/pg_hba.conf'), :if => os[:family] == 'redhat' do
  it { should be_owned_by 'postgres' }
  it { should be_grouped_into 'postgres' }
  it { should be_mode 600 }
  its(:content) { should match /host    all         all         ::1\/128               trust/ }
end

describe file('/var/db/im') do
  it { should be_directory }
  it { should be_grouped_into 'im-developer' }
  it { should be_mode 775 }
end
describe file('/var/db/im'), :if => os[:family] == 'ubuntu' do
  it { should be_owned_by 'www-data' }
end
describe file('/var/db/im'), :if => os[:family] == 'redhat' do
  it { should be_owned_by 'apache' }
end

describe file('/var/db/im/sample.sq3') do
  it { should be_file }
  it { should be_grouped_into 'im-developer' }
  it { should be_mode 664 }
end
describe file('/var/db/im/sample.sq3'), :if => os[:family] == 'ubuntu' do
  it { should be_owned_by 'www-data' }
end
describe file('/var/db/im/sample.sq3'), :if => os[:family] == 'redhat' do
  it { should be_owned_by 'apache' }
end

describe command('sqlite3 /var/db/im/sample.sq3 ".tables"') do
  its(:stdout) { should match /cor_way_kind/ }
end

describe command('getfacl ' + WEBROOT), :if => os[:family] == 'ubuntu' || os[:family] == 'redhat' do
  its(:stdout) { should match /^group:im-developer:rwx$/ }
  its(:stdout) { should match /^default:group:im-developer:rwx$/ }
end

describe file(WEBROOT) do
  it { should be_directory }
  it { should be_mode 775 }
  it { should be_owned_by 'developer' }
  it { should be_grouped_into 'im-developer' }
end

describe file('/home/developer') do
  it { should be_directory }
  it { should be_owned_by 'developer' }
  it { should be_grouped_into 'developer' }
end

describe file('/etc/php5/apache2/php.ini'), :if => os[:family] == 'ubuntu' && os[:release].to_f < 16 do
  it { should be_file }
  its(:content) { should match /max_execution_time = 120/ }
  its(:content) { should match /max_input_time = 120/ }
  its(:content) { should match /memory_limit = 256M/ }
  its(:content) { should match /post_max_size = 100M/ }
  its(:content) { should match /upload_max_filesize = 100M/ }
end
describe file('/etc/php/7.0/apache2/php.ini'), :if => os[:family] == 'ubuntu' && os[:release].to_f >= 16 do
  it { should be_file }
  its(:content) { should match /max_execution_time = 120/ }
  its(:content) { should match /max_input_time = 120/ }
  its(:content) { should match /memory_limit = 256M/ }
  its(:content) { should match /post_max_size = 100M/ }
  its(:content) { should match /upload_max_filesize = 100M/ }
end

describe file('/etc/samba/smb.conf') do
  it { should be_file }
  its(:content) { should match /hosts allow = 192.168.56. 127./ }
  if os[:family] == 'alpine'
    its(:content) { should match /path = \/var\/www\/localhost\/htdocs/ }
  else
    its(:content) { should match /path = \/var\/www\/html/ }
  end
  its(:content) { should match /guest ok = no/ }
  its(:content) { should match /browseable = yes/ }
  its(:content) { should match /read only = no/ }
  its(:content) { should match /create mask = 0664/ }
  its(:content) { should match /directory mask = 0775/ }
  its(:content) { should match /force group = im-developer/ }
end

describe file('/home/developer') do
  it { should be_directory }
end

describe file('/home/developer/.bashrc') do
  it { should be_file }
  it { should be_owned_by 'developer' }
  it { should be_grouped_into 'developer' }
end

describe file('/home/developer/.viminfo') do
  it { should be_file }
  it { should be_owned_by 'developer' }
  it { should be_grouped_into 'developer' }
end

describe file('/etc/default/keyboard'), :if => os[:family] == 'ubuntu' do
  its(:content) { should match /XKBMODEL="pc105"/ }
  its(:content) { should match /XKBLAYOUT="jp"/ }
end

describe file('/etc/default/locale'), :if => os[:family] == 'ubuntu' do
  its(:content) { should match /LANG="ja_JP.UTF-8"/ }
end

describe file('/etc/sysconfig/iptables'), :if => os[:family] == 'redhat' && os[:release].to_f >= 6 && os[:release].to_f < 7 do
  its(:content) { should match /-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT/ }
end
describe file('/etc/firewalld/zones/public.xml'), :if => os[:family] == 'redhat' && os[:release].to_f >= 7 do
  its(:content) { should match /<service name="http"\/>/ }
end

describe file('/etc/local.d/buster-server.start'), :if => os[:family] == 'alpine' do
  it { should be_file }
  it { should be_mode 755 }
  its(:content) { should match /export DISPLAY=:99.0/ }
  its(:content) { should match /Xvfb :99 -screen 0 1024x768x24 &/ }
  its(:content) { should match /\/usr\/bin\/buster-server &/ }
  its(:content) { should match /firefox http:\/\/localhost:1111\/capture > \/dev\/null &/ }
end
describe file('/etc/rc.local'), :if => os[:family] == 'ubuntu' do
  it { should be_file }
  its(:content) { should match /\/usr\/local\/bin\/buster-server &/ }
  its(:content) { should match /\/usr\/local\/bin\/phantomjs \/usr\/local\/lib\/node_modules\/buster\/script\/phantom.js http:\/\/localhost:1111\/capture > \/dev\/null &/ }
end

#describe service('buster-server'), :if => os[:family] == 'ubuntu' do
#  it { should be_running }
#end
