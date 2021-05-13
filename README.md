# Jamulus Recording Remote - 0.6 (2021-05-12)
# INSTRUCTIONS ARE NOT YET fully UPDATED FOR THIS VERSION! BE CAREFUL .


A light-weight web-based interface for Jamulus headless server when installed on a Linux system. No frills, supersimple. Version 0.6 is compatible with Jamulus 3.7; users of previous versions should download version 0.4.1.

Jamulus Recording Remote allows to start and stop recordings, and at the end zip them to be downloaded via the Web. The current version is tested on Ubunto 20.04 Minimal. Installing on an already running system requires some adaptation (details in the Details section below). 

**Warning: use it at your own risk** 
*Jamulus Recording Remote has not yet been thoroughly examined for security issues, thus use it at your own risk, in particular if on a server running continuously. In particular, to be safe, Apache has to be set on https only.*

## Prerequisites
Jamulus should be installed according to official [instructions](https://jamulus.io/wiki/Server-Linux) for a headless server, i.e., with a service named `jamulus-headless`. 

The rest of this README refers to Ubuntu 20.04, but it should be easily adaptable to other Linux platforms.

## Quick install

**If you have a personalised install of Jamulus already running, do not follow these instructions, but read the details at the bottom to adapt the system to your local needs.**

The below commands are also summarized in https://github.com/vdellamea/jamulus-server-remote/blob/main/QUICK-INSTALL.md for super-quick installation **on a fresh installation** on Ubuntu 20.04.

Be sure to have zip:

`sudo apt-get install zip`

Download the code:

`wget https://github.com/vdellamea/jamulus-server-remote/archive/main.zip`

Unzip it:

`unzip main.zip`

enter the unzipped directory:

`cd jamulus-server-remote-main`

Then run the `install-remote.sh` script to install the web-based remote. With a browser, go to the server address (IP or domain), and you will find the interface; enter the password you have set in the configuration.

The service section of the service description should appear as below: some of the fields are different from the standard one, some are new. If you are in a new installation, just copy the `jamulus-headless.service` provided here in the right place.
```
[Service]
Type=simple
User=jamulus
Group=www-data
UMask=0002
NoNewPrivileges=true
ProtectSystem=true
ProtectHome=false
Nice=-20
IOSchedulingClass=realtime
IOSchedulingPriority=0
```

## Configuration 

In principle, the `config.php` (under `/var/www/html`) is the only place where to put hands: password, paths, and also the real shell commands to allow for personalization. Change the following values according to your local configuration or taste:

This is at your taste: server, band name, your cat name...:

`$SERVERNAME="Your band name";`

Please change the passwords:

`$ADMINPASSWORD= "******";`

`$MUSICIANSPASSWORD= "******";`



If you set this one to true, in the Session box you can see some extra output, which can help in debugging:

`$DEBUG=false;`

## Usage
Access to the commands is protected by the password you set in the configuration file. Musicians too need to enter a password to access zipped files.

<img src="screenshots/screenshot1.png" width="340" /> <img src="screenshots/screenshot2.png" width="340" />

At each first access, the interface expects Jamulus to have *recording disabled*. Thus the "toggle on/off" button is off, and the "Start new" is disabled. This also means that just one admin at a time must access the interface, to avoid mishaps. Then, the toggle button activate/disactivate recording, the Start new button start a new recording. 

<img src="screenshots/screenshot3.png" width="340" /> <img src="screenshots/screenshot4.png" width="340" />

At the end of each execution, buttons trigger a refresh of the Sessions textarea, where recordings are shown with their size. However, you may also reload to update the size of the last recording. 

At the end, you can zip all the sessions (as `session.zip` file), or just those of the current day (as `YYYYMMDD.zip` file). "Delete WAVs" deletes all sessions (the WAV files); "Delete ZIPs" deletes all ZIP files, so be careful. 

 *No need to check the rest if you installed from scratch as described above.*
 
## Automix and consolidate

The automix will generate an automatically mixed stereo MP3 from each recording session. Of course, the automix is just a rough preview, with no level adjustment. Panning is done in two ways, discussed below.

In addition to that, a consolidate feature is present because needed for automix, but it is also usable independently. This is aimed at producing WAV (or other formats) files that can be used with DAWs different from Reaper and Audacity: tracks all begin at time 0, so it is easy to import them.

Beware: you need additional storage to run the scripts (only temporarily). I did not yet include them in the regular distribution because they should be tested, however you can already try it following the steps below, if you already have the Recording Remote installed. Only the PHP files should be changed.

Automix and consolidate can be run also inependently from the web system, by running `php automix.php` with appropriate parameters:

--automix (default) / --consolidate

Is a full Recordings directory or a single session?

--single (default) / --all

Files:

--in path_to_recordings directory

--out path_to_generated (default: current dir)

Options:

--format (wav,mp3,opus, ...) (default: mp3 for automix, wav for consolidate)

--normalize audio normalization, default off - not good yet

--debug add extra output

--help this one

 
# Details for adapting to an already installed service
The following description is aimed at explaining what the installation script does, and it can be useful for those that want to install the interface on an already running server, or on a different distribution, or for any other reason.

Download the code from this repository; the web-based interface itself is only including 4 files. Move the 4 files in the `/var/www/html` directory (or similar place in other distributions). 

### The commands

Commands may need personalization if you want to adapt the scripts to an already existing installation. Here the current commands you can find in `worker.php`:
```php
 "toggle" => "sudo /bin/systemctl kill -s SIGUSR2 jamulus ",
 "newrec" => "sudo /bin/systemctl kill -s SIGUSR1 jamulus ",
 "compress" => "cd $RECORDINGS ; rm session.zip; zip -r session.zip Jam* ",
 "compressday" => "cd $RECORDINGS ; rm $today.zip; zip -r $today.zip Jam-$today-* ", 
 "listrec" => "du -sh $RECORDINGS/Jam* ",
 "freespace" => "df -h --output=avail $RECORDINGS ",
 "delwav" => "rm -fr $RECORDINGS/Jam* ",
 "delzip" => "rm -fr $RECORDINGS/*.zip ",
```

### The service 
The service file now allows for writing in the home directory of the user, which is created when creating the user. However, this is not mandatory: if you installed everything according to official instructions, the jamulus user likely will not have a home directory. 

### Extending privileges
This is the tricky part. You have to give privileges to Apache for running commands as `sudo` by modifying the `sudoers` file or, better, adding a file in `sudoers.d`. However, any mistake in doing this may result in loosing sudo privileges, thus use exclusively the `sudo visudo` command if you have to modify something, because it does syntax checks. E.g.:

`sudo visudo -f /etc/sudoers.d/jamulus`

and then add lines like these for each command you want to give sudo privileges to www-data:

`www-data ALL=(ALL)NOPASSWD: /bin/systemctl kill -s SIGUSR1 jamulus`

`www-data ALL=(ALL)NOPASSWD: /bin/systemctl kill -s SIGUSR2 jamulus`

Followed but one or two newline.

Be very careful. `visudo` does syntax checking and avoids mistakes, but if you use a different editor and make a mistake, all sudo privileges become locked.

Since files are written by the user `jamulus`, and then could not be deleted by `www-data` (the user under which Apache+PHP does the job), set gid to give www-data as group to any subfolder/file: 

`mkdir /home/jamulus/recording`

`sudo chown www-data /home/jamulus/recording/`

`sudo chgrp www-data /home/jamulus/recording/`

`sudo chmod g+s /home/jamulus/recording/`

`sudo setfacl -d -m g::rwx /home/jamulus/recording/`

In the above commands, you may substitute the `/home/jamulus/recording/` directory with your own. Remember to change it also in `config.php` and in `jamulus.service` (however, the latter might not be needed if you already set it up for your installation). 

