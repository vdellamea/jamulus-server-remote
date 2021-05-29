echo -n "JAMULUS RECORDING REMOTE INSTALLATION v0.6. Are you sure?  (y/n)? "
read answer
if [ "$answer" != "${answer#[Yy]}" ] ;then

  sudo apt-get install apache2 php libapache2-mod-php ffmpeg acl

# prepare web document root
  sudo rm /var/www/html/index.html
  sudo cp *.php /var/www/html/
  sudo chown -R www-data /var/www/html/
  sudo chgrp -R www-data /var/www/html/

# home dir for the jamulus user
  sudo usermod -d /home/jamulus jamulus
  sudo mkhomedir_helper jamulus

# recording directory
  sudo mkdir /home/jamulus/recording
  sudo chgrp www-data /home/jamulus/recording/
  sudo chmod g+rwx /home/jamulus/recording/
  sudo chmod g+s /home/jamulus/recording/
  sudo setfacl -d -m g::rwx /home/jamulus/recording/

# mixed songs directory  
  sudo mkdir /home/jamulus/mix
  sudo chgrp www-data /home/jamulus/mix/
  sudo chmod g+rwx /home/jamulus/mix/
  sudo chmod g+s /home/jamulus/mix/
  sudo setfacl -d -m g::rwx /home/jamulus/mix/

# consolidated tracks directory
  sudo mkdir /home/jamulus/consolidated
  sudo chgrp www-data /home/jamulus/consolidated/
  sudo chmod g+rwx /home/jamulus/consolidated/
  sudo chmod g+s /home/jamulus/consolidated/
  sudo setfacl -d -m g::rwx /home/jamulus/consolidated/

# add sudo capabilities to Apache for 2 commands
  sudo cp jamulus-sudoers.txt /etc/sudoers.d/jamulus

else
    echo -n "JAMULUS RECORDING REMOTE INSTALLATION canceled."
fi
