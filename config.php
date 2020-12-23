<?php 
// Jamulus Server Remote 
// v0.3 - 20201219 
// Vincenzo Della Mea

// CONFIGURATION FILE

$SERVERNAME="Sparse Band"; 
$ADMINPASSWORD= "4strings"; // change it!
$MUSICIANPASSWORD="6strings"; // change it!
$RECORDINGS="/home/jamulus/recording/"; //the same set in jamulus.service
$ARCHIVE="/home/jamulus/recording/"; //not yet used
$DEBUG=false; 

$today=date("Ymd");
$COMMANDS=array(
 "toggle" => "sudo /bin/systemctl kill -s SIGUSR2 jamulus ",
 "newrec" => "sudo /bin/systemctl kill -s SIGUSR1 jamulus ",
 "compress" => "cd $RECORDINGS ; rm session.zip; zip -r session.zip Jam* ",
 "compressday" => "cd $RECORDINGS ; rm $today.zip; zip -r $today.zip Jam-$today-* ", 
 "cleanup" => "rm -fr $RECORDINGS/Jam* ", // "rm -fr $RECORDINGS/* " to delete zips too
 "listrec" => "du -sh $RECORDINGS/Jam* ",
 );


?>
