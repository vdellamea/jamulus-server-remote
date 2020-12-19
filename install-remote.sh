sudo apt-get install apache2 php libapache2-mod-php


sudo rm /var/www/html/index.html
sudo cp *.php /var/www/html/
sudo chown -R www-data /var/www/html/
sudo chgrp -R www-data /var/www/html/

sudo mkdir /home/jamulus/recording
sudo chmod g+rwx /home/jamulus/recording/
sudo chmod g+s /home/jamulus/recording/
sudo setfacl -d -m g::rwx /home/jamulus/recording/

sudo cp files/jamulus-sudoers.txt /etc/sudoers.d/jamulus
