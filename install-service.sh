echo -n "JAMULUS SERVICE INSTALLATION. Are you sure? This script will overwrite the /etc/systemd/system/jamulus.service, if it exists. (y/n)? "
read answer
if [ "$answer" != "${answer#[Yy]}" ] ;then

  sudo adduser --system jamulus
  sudo adduser jamulus www-data

  sudo cp files/jamulus.service /etc/systemd/system/jamulus.service
  sudo chmod 644 /etc/systemd/system/jamulus.service

  sudo systemctl daemon-reload

  sudo systemctl start jamulus
  sudo systemctl enable jamulus
  sudo systemctl status jamulus

else
    echo -n "JAMULUS SERVICE INSTALLATION canceled."
fi
