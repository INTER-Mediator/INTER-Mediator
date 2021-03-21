The SELinux policy files for INTER-Mediator are included in this directory.
You could apply to them with the following command.

sudo semodule -i inter-mediator.pp

The inter-mediator.te file is the Types Enforcement file, i.e. the text file containing definitions with collecting
the audit2allow command. The .pp file is compiled it.

Most possible operations are done when we collect 'deny' information, but they are not all for you. In same case,
for example you want to set the document root of apache to another directory from the default one, you have to
apply to allow your demands.

If you want to write files on your server for accepting uploaded files, you have to add permission to the directory
of storing uploaded files. The example of command for setting the '/var/www/files' directory as the 'media-root-dir':

sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/files(/.*)?"
sudo semanage fcontext -l | grep /var/www/files # Cheking to show the above values
sudo restorecon -R /var/www/files               # Apply above settings to files

If you think some definitions are not appropriate, you can modify the .te file, compile it and apply it.
I'd like to refer about the detail instructions to any site as like:

https://access.redhat.com/documentation/en-US/red_hat_enterprise_linux/6/html/security-enhanced_linux/index
