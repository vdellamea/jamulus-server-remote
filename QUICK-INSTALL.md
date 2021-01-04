## Quick install
This is a no-brain sequence of commands that you may run on a fresh instance of Ubuntu 18.04 to install everything from Jamulus to the Recording remote. 
You can just copy the following lines and paste them in the server shell:

```
wget https://raw.githubusercontent.com/corrados/jamulus/master/distributions/installscripts/install4ubuntu.sh

sh install4ubuntu.sh

sudo apt-get install zip

wget https://github.com/vdellamea/jamulus-server-remote/archive/main.zip

unzip main.zip

cd jamulus-server-remote-main

sh install-service.sh

sh install-remote.sh
```

After this, the system is already up and running: you can reach it via `http://your.ip.address` where the IP address is the same you use to connect via Jamulus. 
However, **please remember to change the passwords!** You may edit the `config.php` file with the `nano` text editor:

`sudo nano /var/www/html/config.php` 

You have to modify at least:

`$ADMINPASSWORD= "your password";`

`$MUSICIANSPASSWORD= "another password";`

And eventually also:

`$SERVERNAME="Your band name";`

When you have finished, `CTRL+X` will let you save and exit (confirm with `y`).
