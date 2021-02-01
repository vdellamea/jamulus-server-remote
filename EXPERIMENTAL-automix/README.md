# AUTOMIX feature

These scripts add to an **automix** feature to an installation of Jamulus Recording Remote, based on the [ffmpeg](https://ffmpeg.org) package. The automix will generate an automatically mixed stereo MP3 from each recording session. Of course, the automix is just a rough preview, with no level adjustment, and will be correctly generated only when all musicians are present from the beginning to the end of a session (that is, tracks are presumed to be synchronised). Panning is done in two ways, discussed below in the Configuration section.

I did not yet include them in the regular distribution because they should be tested, however you can already try it following the steps below. Only the PHP files should be changed. However, one of the scripts is made to be run from command line and, while written in PHP, does not need the web interface. 
First of all,
- install ffmpeg:
```
sudo apt-get install ffmpeg
```
- download the PHP files.

You can already test the system this way:
- `php automix.php single /path/to/recordings/Jam-XXXXXXXX/` creates one mixed mp3 from the tracks of the Jam-XXXXXXXX section.
- `php automix.php all /path/to/recordings/` creates one mixed mp3 from each session inside the recordings directory.


- backup your current PHP files in /var/www/html: 
```
cd /var/www/html
sudo zip /home/ubuntu/backup.zip *.php
```
- create a directory for collecting the mixed files and set the privileges to make it writable by the web server (similar to the recording directory):
```
sudo mkdir /home/jamulus/mix
sudo chgrp www-data /home/jamulus/mix/
sudo chmod g+rwx /home/jamulus/mix/
sudo chmod g+s /home/jamulus/mix/
sudo setfacl -d -m g::rwx /home/jamulus/mix/
```
- copy the new PHP files to /var/www/html . Please note that, if you do not want to rewrite your personalizations in config.php, you may avoid overwriting it: you have just to copy the last part of the new config.php at the end of your file. 

Everything should be ready now and usable. In the web interface there is a "automix" button that generates an automix for each session of the current day, and zips them in a single file that can be downloaded with the "Today's mix" link. The mix will be deleted with the "Delete zips" button.

## Configuration
Without any specific configuration, the system attempts to pan the tracks in a uniformly distributed way (I hope). One player: centered; two: on per side (but not totally); three: one centered, one left, one right; etc. 

However, since the system is prevalently aimed at private servers, in config.php you may set the name of each musician/singer exactly as in their Jamulus profile, and set the position relative to left (1.0= all left, 0= all right, 0.5= center, etc). Tracks are recognised by the name, and thus can be panned in an informed way (e.g., drums and bass in the middle, guitars well spaced, etc). This can be also done inside automix.php, which does not read the config.php file. 
