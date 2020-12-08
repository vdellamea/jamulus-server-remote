sudo adduser --system --no-create-home jamulus
sudo adduser jamulus www-data

sudo cp files/jamulus.service /etc/systemd/system/jamulus.service
sudo chmod 644 /etc/systemd/system/jamulus.service

sudo cp files/jamulus-start-stop.service /etc/systemd/system/jamulus-start-stop.service
sudo chmod 644 /etc/systemd/system/jamulus-start-stop.service

sudo cp files/jamulus-new.service /etc/systemd/system/jamulus-new.service
sudo chmod 644 /etc/systemd/system/jamulus-new.service

sudo systemctl daemon-reload

sudo systemctl start jamulus
sudo systemctl enable jamulus
sudo systemctl status jamulus
