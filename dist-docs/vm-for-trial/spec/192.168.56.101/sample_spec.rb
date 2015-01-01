require 'spec_helper'

describe package('httpd'), :if => os[:family] == 'redhat' do
  it { should be_installed }
end

describe package('apache2'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('openssh-server'), :if => os[:family] == 'ubuntu' do
 it { should be_installed }
end

describe package('mysql-server'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('postgresql'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe service('httpd'), :if => os[:family] == 'redhat' do
  it { should be_enabled }
  it { should be_running }
end

describe service('apache2'), :if => os[:family] == 'ubuntu' do
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

describe service('sshd'), :if => os[:family] == 'ubuntu' do
  it { should be_running }
end

describe service('mysql'), :if => os[:family] == 'ubuntu' do
  it { should be_enabled }
end

describe service('mysqld'), :if => os[:family] == 'ubuntu' do
  it { should be_running }
end

describe service('postgres'), :if => os[:family] == 'ubuntu' do
  it { should be_enabled }
  it { should be_running }
end

describe group('im-developer') do
  it { should exist }
end

describe user('developer') do
  it { should exist }
  it { should belong_to_group 'im-developer' }
end

describe user('www-data') do
  it { should exist }
  it { should belong_to_group 'im-developer' }
end

describe user('postgres') do
  it { should exist }
end

describe file('/etc/mysql/conf.d/im.cnf') do
  it { should be_file }
  its(:content) { should match /[mysqld]/ }
  its(:content) { should match /character-set-server=utf8mb4/ }
  its(:content) { should match /skip-character-set-client-handshake/ }
  its(:content) { should match /[client]/ }
  its(:content) { should match /[mysqldump]/ }
  its(:content) { should match /[mysql]/ }
end

describe package('sqlite'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('acl'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('libmysqlclient-dev'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('php5-pgsql'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('php5-sqlite'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('php5-curl'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('git'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe package('nodejs'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe file('/usr/bin/node'), :if => os[:family] == 'ubuntu' do
  it { should be_file }
end

describe package('npm'), :if => os[:family] == 'ubuntu' do
it { should be_installed }
end

describe package('phpunit'), :if => os[:family] == 'ubuntu' do
  it { should be_installed }
end

describe file('/var/www/html/INTER-Mediator') do
  it { should be_directory }
end

describe file('/var/www/html/index_original.html') do
  it { should be_file }
end

describe file('/var/www/html/INTER-Mediator/INTER-Mediator-Support') do
  it { should be_directory }
end

describe file('/var/www/html/INTER-Mediator/INTER-Mediator-UnitTest') do
  it { should be_directory }
end

describe file('/var/www/html/INTER-Mediator/INTER-Mediator-UnitTest/DB_PDO-SQLite_Test.php') do
  it { should be_file }
  its(:content) { should match /sqlite:\/var\/db\/im\/sample.sq3/ }
end

describe file('/var/www/html/index.html') do
  it { should be_symlink }
end

describe command('diff -c /var/www/html/index.html /var/www/html/vm-for-trial/index.html') do
  its(:stdout) { should match // }
end

describe file('/var/www/html/.htaccess') do
  it { should be_file }
  its(:content) { should match /AddType "text\/html; charset=UTF-8" .html/ }
end

describe file('/var/www/html/params.php') do
  it { should be_file }
  its(:content) { should match /\$dbUser = "web";/ }
  its(:content) { should match /\$dbDSN = "mysql:unix_socket=\/var\/run\/mysqld\/mysqld.sock;dbname=test_db;charset=utf8mb4";/ }
  its(:content) { should match /\$dbOption = array\(\);/ }
  its(:content) { should match /\$dbServer = "192.168.56.1";/ }
end

describe file('/var/www/html/INTER-Mediator/INTER-Mediator-Support/defedit.php') do
  it { should be_file }
  its(:content) { should_not match /\/\/IM_Entry/ }
end

describe file('/var/www/html/INTER-Mediator/INTER-Mediator-Support/pageedit.php') do
  it { should be_file }
  its(:content) { should_not match /\/\/IM_Entry/ }
end

range = 1..40
range.each{|num|
  describe file('/var/www/html/def' + "%02d" % num + '.php') do
    it { should be_file }
    it { should be_mode 664 }
  end
}

range = 1..40
range.each{|num|
  describe file('/var/www/html/page' + "%02d" % num + '.html') do
    it { should be_file }
    it { should be_mode 664 }
  end
}

describe command('mysql -u root --password=im4135dev test_db -e \'SHOW TABLES\'') do
  its(:stdout) { should match /cor_way_kind/ }
end

describe command('echo "im4135dev" | sudo -u postgres -S psql -c \'\\l\'') do
  its(:stdout) { should match /test_db/ }
end

describe file('/var/db/im') do
  it { should be_directory }
  it { should be_owned_by 'www-data' }
end

describe file('/var/db/im/sample.sq3') do
  it { should be_file }
  it { should be_owned_by 'www-data' }
  it { should be_grouped_into 'im-developer' }
end

describe command('sqlite3 /var/db/im/sample.sq3 --batch \'.tables\'') do
  its(:stdout) { should match /cor_way_kind/ }
end

describe command('getfacl /var/www/html'), :if => os[:family] == 'ubuntu' || os[:family] == 'redhat' do
  its(:stdout) { should match /group:im-developer:rw-/ }
end

describe file('/var/www/html') do
  it { should be_directory }
  it { should be_mode 775 }
  it { should be_owned_by 'developer' }
  it { should be_grouped_into 'im-developer' }
end