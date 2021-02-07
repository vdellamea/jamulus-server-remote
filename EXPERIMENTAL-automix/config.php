<?php // Jamulus Server Remote 
// v0.4 - 20210110 
// Vincenzo Della Mea

// CONFIGURATION FILE

$SERVERNAME="Musica Permanente"; //up to you
$ADMINPASSWORD= "secret1"; // change it!
$MUSICIANPASSWORD="secret2"; // change it!
$RECORDINGS="/home/jamulus/recording/"; //change this only if you are adapting to your server
$DEBUG=true;  //in case of issues, set it at true and you will see some more infos


// AUTOMIX SETTINGS
$MIX="/home/jamulus/mix/"; //as previous
$CONSOLIDATED="/home/jamulus/consolidated/";
$AUDIONORMALIZATION=false;

// Bandmates names to be used for "informed" automix
// name => left percentage (right is 1-left)
$BANDMATES=array(
	'Alex' =>0.55,
	'CP__' =>0.45,
	'Andrea' =>0.5,
	'Enzo' =>0.5,	
	'Luca_DG'=> 0.3,
	'Daniele'=> 0.9,
	'gigio'=> 0.1,
);

?>
