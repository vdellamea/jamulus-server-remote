<?php // Jamulus Server Remote 
// v0.4 - 20210110 
// Vincenzo Della Mea

// CONFIGURATION FILE

$SERVERNAME="BAND NAME"; //up to you
$ADMINPASSWORD= "secret1"; // change it!
$MUSICIANPASSWORD="secret2"; // change it!
$RECORDINGS="/home/jamulus/recording/"; //change this only if you are adapting to your server
$DEBUG=true;  //in case of issues, set it at true and you will see some more infos


// AUTOMIX SETTINGS
$MIX="/home/jamulus/mix/"; //as previous
$CONSOLIDATED="/home/jamulus/consolidated/";
$CFORMAT="mp3";
$AUDIONORMALIZATION=false; //Experimental - not yet good

// Bandmates names to be used for "informed" automix
// name => left percentage (right is 1-left)
$BANDMATES=array(
	'Jimi' =>0.55,
	'Eric' =>0.45,
	'John' =>0.5,
	'Patti' =>0.5,	
	'Stevie'=> 0.3,
);

?>
