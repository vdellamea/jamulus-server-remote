<?php
// Jamulus Server Remote
// v0.1 - 20201206
// Vincenzo Della Mea

// CONFIGURATION FILE

$SERVERNAME="Sparse Band";
$PASSWORD= "4strings";
$RECORDINGS="/var/www/html/recording/";
$RECURL="recording/";
$DEBUG=false;

$today=date("Ymd");
$COMMANDS=array(
 "toggle" => "sudo systemctl start jamulus-start-stop ",
 "newrec" => "sudo systemctl start jamulus-new ",
 "compress" => "rm session.zip; zip -r session.zip $RECORDINGS/Jam* ",
 "compressday" => "rm $today.zip; zip -r $today.zip $RECORDINGS"."Jam-$today-* ", 
 "cleanup" => "rm -fr $RECORDINGS/Jam* ",
 "listrec" => "du -sh $RECORDINGS/* ",
 );


?>
