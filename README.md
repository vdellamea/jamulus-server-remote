# Jamulus Server Remote - 0.1
A light-weight web-based interface for Jamulus server when installed on a Linux system. No frills, supersimple.

Jamulus Server Remote allows to start and stop recordings, and at the end zip them to be downloaded via the Web. While in principle it can be installed on any Linux distribution, at the moent it has been tested on Ubuntu 18.04 installed on an AWS EC2 machine only. 

**Warning:** Jamulus Server Remote has not yet been thoroughly examined for security issues, thus use it at your own risk, in particular if on a server running continuously.  

## Prerequisites
Jamulus should be installed according to official [instructions](https://jamulus.io/wiki/Server-Linux) (using the installscript included in the `distributions` folder is perfectly okay), set as systemd service, and with the suggested additional services for beginning and toggling recording. 
The server must be started with the recording directory set to a directory accessible to apache (e.g., `-R /var/www/html/recordings`) and the `--norecord` option, to initially disable recording. Having the recording directory in the same directory of the PHP scripts makes things simpler.

If you do not have already installed Jamulus server, you may quickly do it as follows (for Ubuntu):

`wget https://raw.githubusercontent.com/corrados/jamulus/master/distributions/installscripts/install4ubuntu.sh`

`sh install4ubuntu.sh`

Other install scripts can be found here: https://github.com/corrados/jamulus/tree/master/distributions

The rest of this README refers to Ubuntu.

## Quick install
Be sure to have zip:

`sudo apt-get install zip`

Download the code, unzip it, `cd` to the unzipped directory.

Run the `install-service.sh` script to install and start Jamulus as systemd service, as well as the two services needed for toggling recording and starting a new one. 

Then run the `install-remote.sh` script to install the web-based remote. With a browser, go to the server address (IP or domain), and you will find the interface; enter the password you have set in the configuration.

**Warning:** To be safe, Apache has to be set on https only. 



## Configuration 

In principle, the `config.php` (under `/var/www/html`) is the only place where to put hands: password, paths, and also the real shell commands to allow for personalization. Change the following values according to your local configuration or taste:

This is at your taste: server, band name, your cat name...:

`$SERVERNAME="Your band name";`

Please change the password:

`$PASSWORD= "******";`

This is the recording directory set also in the Jamulus parameters:

`$RECORDINGS="/var/www/html/recording/";`

... and this is the same position, but to be used as URL (in this case, relative to the scripts):

`$RECURL="recording/";`

If you set this one to true, in the Session box you can see some extra output, which can help in debugging:

`$DEBUG=false;`

Commands may need personalization e.g., if the names of your start/stop services are different. Here the examples:

` "toggle" => "sudo service jamulus-start-stop  start ",`

` "newrec" => "sudo service jamulus-new start ",`

` "compress" => "rm session.zip; zip -r session.zip $RECORDINGS/Jam* ",`

` "compressday" => "rm $today.zip; zip -r $today.zip $RECORDINGS"."Jam-$today-* ", `

` "cleanup" => "rm -fr $RECORDINGS/Jam* ",`

` "listrec" => "du -sh $RECORDINGS/* ",`

## Instructions
Access to the commands is protected by the password you set in the configuration file. Musicians, at present, may directly access the recordings (however this may change).

At each first access, the interface expects Jamulus to have *recording disabled*. Thus the "toggle on/off" button is off, and the "Start new" is disabled. This also means that just one admin at a time must access the interface, to avoid mishaps. Then, the toggle button activate/disactivate recording, the Start new button start a new recording. 

At the end of each execution, buttons trigger a refresh of the Sessions textarea, where recordings are shown with their size. However, you may also reload to update the size of the last recording. 

At the end, you can zip all the sessions (as `session.zip` file), or just those of the current day (as `YYYYMMDD.zip` file). Cleanup deletes all sessions, so be careful. 



## Details
The following description is aimed at explaining what the installation script does, and it can be useful for those that want to install the interface on an already running server, or on a different distribution, or for any other reason.

Download the code from this repository; the web-based interface itself is only including 3 files. Move the 3 files in the `/var/www/html` directory (or similar place in other distributions). 

### Extending privileges
This is the tricky part. You have to give privileges to Apache for running service as sudo with the visudo command:
`sudo visudo -f /etc/sudoers.d/jamulus`
and then add the following lines:

`www-data  ALL=(ALL)NOPASSWD: /usr/sbin/service startstop-jamulus  start`

`www-data  ALL=(ALL)NOPASSWD: /usr/sbin/service newrec-jamulus start`

Followed but one or two newline.

Be very careful. `visudo` does syntax checking and avoids mistakes, but if you use a different editor and make a mistake, all sudo privileges become locked.

The recording dir should be served by apache (although not needed if only the zip is given).
Since files are written by the user `jamulus:nogroup`, and then could not be deleted by `www-data` (the user under which Apache+PHP does the job), set gid to give www-data as group to any subfolder/file: 

`mkdir /var/www/html/recording`

`sudo chown www-data recording/`

`sudo chgrp www-data recording/`

`sudo chmod g+s recording/`

`sudo setfacl -d -m g::rwx recording`


