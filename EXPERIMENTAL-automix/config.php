<?php 
// Jamulus Recording Remote
// v0.5 - 20210201
// Vincenzo Della Mea

// CONFIGURATION FILE

$SERVERNAME="BAND OR SERVER NAME"; //up to you
$ADMINPASSWORD= "secret"; // change it!
$MUSICIANPASSWORD="alsosecret"; // change it!
$RECORDINGS="/home/jamulus/recording/"; //change this only if you are adapting to your server
$DEBUG=false;  //in case of issues, set it at true and you will see some more infos


// AUTOMIX SETTINGS
$MIX="/home/jamulus/mix/"; //as above

// Bandmates names to be used for "informed" automix
// 'name' => value,
// name exactly as in Jamulus profile, 
// value: 1= left only, 0= right only, 0.5= center, etc
// Names not specified are automatically panned.

$BANDMATES=array(
	'Jimi' =>1.0, //all left
	'Gwen' =>0.45, //slightly right
	'Carol' =>0.5, //center
	'Stevie' =>0.0, //all right
	'Stewart' => 0.5, //center
);

?>
