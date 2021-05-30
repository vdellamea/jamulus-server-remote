# Jamulus Recording Remote - 0.6 (2021-05-12)

A light-weight web-based interface for Jamulus headless server when installed on a Linux system. No frills, supersimple. Version 0.6 is compatible with Jamulus 3.7; users of previous versions should download version 0.4.1.

Jamulus Recording Remote allows to start and stop recordings, and at the end zip them to be downloaded via the Web. The current version is tested on Ubunto 20.04 Minimal. Installing on an already running system requires some adaptation (details in the Details section below). 

**Warning: use it at your own risk** 
*Jamulus Recording Remote has not yet been thoroughly examined for security issues, thus use it at your own risk, in particular if on a server running continuously. In particular, to be safe, Apache has to be set on https only.*

## Prerequisites
Jamulus should be installed according to official [instructions](https://jamulus.io/wiki/Server-Linux) for a headless server, i.e., with a service named `jamulus-headless`. 

The rest of this README refers to Ubuntu 20.04, but it should be easily adaptable to other Linux platforms.

## Update from previous version

If you already had the Remote + experimental automix installed, just replace the html pages. 

## Quick install

**If you have a personalised install of Jamulus already running, do not follow these instructions, but read the details at the bottom to adapt the system to your local needs.**

Be sure to have zip:

`sudo apt-get install zip`

Download the code:

`wget https://github.com/vdellamea/jamulus-server-remote/archive/main.zip`

Unzip it:

`unzip main.zip`

enter the unzipped directory:

`cd jamulus-server-remote-main`

Then run the `install-remote.sh` script to install the web-based remote. With a browser, go to the server address (IP or domain), and you will find the interface; enter the password you have set in the configuration (`/var/www/html/config.php`).

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

At each first access, the interface expects Jamulus to have *recording disabled* (there is no obvious way to determine its status from code). Thus the "toggle on/off" button is off, and the "Start new" is disabled. This also means that just one admin at a time must access the interface, to avoid mishaps. Then, the toggle button activate/disactivate recording, the Start new button start a new recording. 

<img src="screenshots/screenshot3.png" width="340" /> <img src="screenshots/screenshot4.png" width="340" />

At the end of each execution, buttons trigger a refresh of the Sessions textarea, where recordings are shown with their size. However, you may also reload to update the size of the last recording. 

At the end, you can zip all the original files of the session (as `orig-YYYY-MM-DD.zip` file). "Delete WAVs" deletes all sessions (the WAV files); "Delete ZIPs" deletes all ZIP files, so be careful. 

 
## Automix and consolidate

The *automix* will generate an automatically mixed stereo MP3 from each recording session. Of course, the automix is just a rough preview, with no level adjustment. Panning is done in two ways, discussed below.

In addition to that, a *consolidate* feature is present because needed for automix, but it is also usable independently. This is aimed at producing WAV (or other formats like mp3 and flac) files that can be used with DAWs different from Reaper and Audacity: tracks all begin at time 0, so it is easy to import them in any DAW, not only Reaper and Audacity.

After having run automix or consolidate, you may download the zipped version of the files as `mix-YYYY-MM-DD.zip` and `consolidated-YYYY-MM-DD.zip`.

Beware: you need additional storage to run the scripts (although only temporarily). 

Automix and consolidate can be run also independently from the web system, by running `php automix.php` with appropriate parameters:

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

### Automix configuration

Without any specific configuration, the system attempts to pan the tracks in a uniformly distributed way. One player: centered; two: one per side (but not totally); three: one centered, one left, one right; etc. They are ordered from left to right in alphabetical order by the profile name, and this may help to set the panning for large groups: e.g., you may ask singers to prefix their name with a number (01, 02, 03...). 

However, since the system is prevalently aimed at private servers, in config.php you may set the name of each musician/singer exactly as in their Jamulus profile, and set the position relative to left (1.0= all left, 0= all right, 0.5= center, etc). Tracks are recognised by the profile name, and thus can be panned in an informed way (e.g., drums and bass in the middle, guitars well spaced, etc). 

```
$BANDMATES=array(
	'Jimi' =>0.55,
	'Eric' =>0.45,
	'John' =>0.5,
	'Patti' =>0.5,	
	'Stevie'=> 0.3,
);
```

If you run `automix.php` independently, the same settings are to be put in the automix.php file, because it does not use config.php.

In addition to that, you may set the format for the consolidated tracks, as mp3, wav, flac (actually, any format managed by ffmpeg). Normalization is not yet good. 

```
$CFORMAT="mp3"; // also flac, wav, etc
$AUDIONORMALIZATION=false; //Experimental - not yet good
```

 *No need to check the rest if you installed from scratch as described above.*
 
# Details for adapting to different platforms

The following description is aimed at explaining what the installation script does, and it can be useful for those that want to install the interface on an already running server, or on a different distribution, or for any other reason.

## Installation
This is a motivated walk through the install-remoth script. Download the code from this repository. 

Install dependencies: this should be adapted to the package manager of your distribution (yum, dnf). Some other software might be missing, like zip/unzip, some might be already installed (e.g., acl).  
```
sudo apt-get install apache2 php libapache2-mod-php ffmpeg acl
```
E.g., for Fedora:
```
sudo dnf install httpd php ffmpeg zip
```


Prepare web document root: in principle this should be sufficient for most distributions, however you might want to put the PHP files in some subdirectory. 
```
  sudo rm /var/www/html/index.html
  sudo cp *.php /var/www/html/
```
Ownership of the files should be given to the user running apache. E.g., in Fedora it is not `www-data` but `apache`. It should be changed wherever www-data appears.
```
  sudo chown -R www-data /var/www/html/
  sudo chgrp -R www-data /var/www/html/
```

If you followed the original instructions, the jamulus user is not created with a home dir. The following aims at setting it.
```
  sudo usermod -d /home/jamulus jamulus
  sudo mkhomedir_helper jamulus
```

Then you need recording, mix and consolidate directories. These instruction should be good on any platform, except for the chgrp to www-data that might need adaptation.
```
# recording songs directory 
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
```

Finally, you have to add sudo capabilities to Apache for 2 commands. This is the tricky part. You have to give privileges to Apache for running commands as `sudo` by adding a file in `sudoers.d`. However, any mistake in doing this may result in loosing sudo privileges, thus use exclusively the `sudo visudo` command if you have to modify something, because it does syntax checks. 
The jamulus-sudoers.txt file should be adapted both regarding the user to which privileges are given, and the name of the service to be called (jamulus-headless vs jamulus or whatever you called it). It is highly suggested to use visudo to edit it, so copy it in place, then `sudo visudo /etc/sudoers.d/jamulus` . Be very careful. `visudo` does syntax checking and avoids mistakes, but if you use a different editor and make a mistake, all sudo privileges become locked. If visudo starts with the `vi` editor and you are not a nerd, try `sudo EDITOR=/bin/nano visudo /etc/sudoers.d/jamulus`.

This might not be sufficient due to extra layers of protection in the system (SELinux, default on Fedora and Centos). You might need to [disable it](https://www.cyberciti.biz/faq/disable-selinux-on-centos-7-rhel-7-fedora-linux/) or do further work to enable Apache to call systemctl, but I cannot help on this.

```
  sudo cp jamulus-sudoers.txt /etc/sudoers.d/jamulus
```
The content is 
```
www-data ALL=(ALL)NOPASSWD: /bin/systemctl kill -s SIGUSR1 jamulus
www-data ALL=(ALL)NOPASSWD: /bin/systemctl kill -s SIGUSR2 jamulus
```

Shell commands used by the Remote may need personalization if you want to adapt the scripts to an already existing installation (e.g., service name, the same as in the sudoers file. Here the current commands you can find in `worker.php` (two for sure that may need modifications are toggle and newrec, for the service name):
```php
 "toggle" => "sudo /bin/systemctl kill -s SIGUSR2 jamulus-headless ",
 "newrec" => "sudo /bin/systemctl kill -s SIGUSR1 jamulus-headless ",
 "compress" => "cd $RECORDINGS ; rm orig-$today.zip; zip -r orig-$today.zip Jam* ",
 "listrec" => "du -sh $RECORDINGS/Jam* ",
 "freespace" => "df -h --output=avail $RECORDINGS ",
 "delwav" => "rm -fr $RECORDINGS/Jam* ",
 "delzip" => "rm -fr $RECORDINGS/*.zip ",
 "ffmpeg" => "ffmpeg -loglevel quiet ",
 "checkstereo" => "ffmpeg -i ", 
 "maxvolume" =>"ffmpeg -i ",
 "ffprobe" => "ffprobe  -show_entries stream=duration -of compact=p=0:nk=1 -v 0 ",	
 "zipmix" => "rm $RECORDINGS/mix-$today.zip; cd $MIX; zip $RECORDINGS/mix-$today.zip *.mp3 ; rm $MIX/*.mp3 ",
 "cleancons" => "rm $RECORDINGS/consolidated-$today.zip",	
 "zipcons1" => "cd $CONSOLIDATED; zip -r $RECORDINGS/consolidated-$today.zip Jam* ; rm -fr Jam* ",
 "cleantmp" => "rm -fr /var/tmp/Jam-* ",
```

The service file now allows for writing in the home directory of the user, which is created when creating the user. However, this is not mandatory: if you installed everything according to official instructions, the jamulus user likely will not have a home directory. If you want to put the recordings elsewhere, check that the privileges set in the service file are adequate (and sorry, I am not able to help on this). 
These are the extras vs. the standard install:
```
Group=www-data
UMask=0002
ProtectHome=false
```





