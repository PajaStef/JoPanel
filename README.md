# JoPanel
Panel for managing Apache Virtual Hosts.


How to set it up:
1. Clone the repository

2. Setup a domain for the panel(optional) and point the document root to the panel document root.

3. Give www-data user privileges to write in these folder: /etc/apache2/sites-available/ and /var/www/html: - NOTE: these are the default directories for apache2 web server on ubuntu, if you are using it on other distros you *might* have to check this
sudo groupadd webadmins - Adding a group for users. You can name it whatevery you want.
sudo usermod -aG webadmins www-data - Adding user www-data to the group. You can also add more users if needed(eg. if another user, lets say sftp, also needs to write to one of the directories under this groups ownership add it in the group).
sudo chgrp -R webadmins /var/www/html - Changing that the group has the ownership of the /var/www/html folder
sudo chgrp -R webadmins /etc/apache2/sites-available - Changing that the group has ownership of the /etc/apache2/sites-available
sudo chmod -R 775 /var/www/html
sudo chmod -R 775 /etc/apache2/sites-available
setting correct permissions.

4. After that restart or start apache and you are ready to go!


