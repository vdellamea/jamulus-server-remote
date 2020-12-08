sudo apt-get install apache2 php libapache2-mod-php zip

sudo rm /var/www/html/index.html
sudo cp *.php /var/www/html/

sudo mkdir /var/www/html/recording

sudo chown -R www-data /var/www/html/
sudo chgrp -R www-data /var/www/html/
sudo chmod g+rwx /var/www/html/recording/
sudo chmod g+s /var/www/html/recording/
sudo setfacl -d -m g::rwx /var/www/html/recording

sudo cp files/jamulus-sudoers.txt /etc/sudoers.d/jamulus