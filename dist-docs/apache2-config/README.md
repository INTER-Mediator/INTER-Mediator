# Apache2 Configration file

Masayuki Nii (nii@msyk.net)

## Purpose

Regardless of using INTER-Mediator,
some files in the repository don't want to publish with Apache2.
This conf file for Apache2 realizes that.

## How to Set up (Ubuntu)

* Copy the file apache-deny-files.conf to /etc/apache2/conf-available
* Execute the command ```sudo a2enconf apache-deny-files```
* Restart Apache2 with ```sudo systemctl restart apache2```

## Explanation

The conf file denies the following files and directories.

* *.yaml files
* *.sh files
* *.sql files
* .git directory
* docker directory
* private directory