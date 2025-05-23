# JoPanel
Panel for managing Apache Virtual Hosts.


Before setting anything up you will be needing these packages:
php, apache2, certbot for apache
FOR UBUNTU
apt install apache2 -y
now for php you have multiple options:
1. apt install php -y
2. sudo add-apt-repository ppa:ondrej/php
   sudo apt install php8.2
3. sudo apt install certbot python3-certbot-apache - this is certbot for apache, this will be used to generate SSL certificates for your website

Use the second option if you want a specific version of php

Now, how to set it up:
1. Clone the repository

2. OPTIONAL: Setup a domain for the panel and point the document root to the panel document root. You dont have to do this but then you will be accessing your panel via IP address of your server. **Make sure to protect the panel page with a .htpasswd or any authentication you want**

3. Give www-data user privileges to write in these folder: /etc/apache2/sites-available/ and /var/www/html: - NOTE: these are the default directories for apache2 web server on ubuntu, if you are using it on other distros you *might* have to check this

sudo groupadd webadmins - Adding a group for users. You can name it whatevery you want.


sudo usermod -aG webadmins www-data - Adding user www-data to the group. You can also add more users if needed(eg. if another user, lets say sftp, also needs to write to one of the directories under this groups ownership add it in the group).


sudo chgrp -R webadmins /var/www/html - Changing that the group has the ownership of the /var/www/html folder


sudo chown -R root:webadmins /etc/apache2/sites-available - Changing that the group has ownership of the /etc/apache2/sites-available

sudo chmod 2775 /var/www/html

sudo chmod -R 2775 /etc/apache2/sites-available


setting correct permissions.

5. You **must** create an example apache2 config file so the panel can copy it and put in your info, heres what /etc/apache2/sites-available/template.conf should have:
<VirtualHost *:80>
    ServerName {{DOMAIN}}
    DocumentRoot {{DOC_ROOT}}
    ErrorLog ${APACHE_LOG_DIR}/{{DOMAIN}}_error.log
    CustomLog ${APACHE_LOG_DIR}/{{DOMAIN}}_access.log combined

    <Directory {{DOC_ROOT}}/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>


6. After that restart or start apache and you are ready to go!


