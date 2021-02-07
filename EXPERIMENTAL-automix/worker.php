<?php
// Jamulus Recording Remote
// v0.4 - 20210110
// Vincenzo Della Mea

// REMOTE WEB SERVICE
session_start();
include("config.php");

$today=date("Ymd");

//If you need to adapt the Remote to your server, check the following commands
$COMMANDS=array(
 "toggle" => "sudo /bin/systemctl kill -s SIGUSR2 jamulus ",
 "newrec" => "sudo /bin/systemctl kill -s SIGUSR1 jamulus ",
 "compress" => "cd $RECORDINGS ; rm orig-$today.zip; zip -r orig-$today.zip Jam* ",
 "compressday" => "cd $RECORDINGS ; rm $today.zip; zip -r $today.zip Jam-$today-* ", 
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

 );

include("automix.php");

$stderr=""; 
if($DEBUG) {
	print_r($_POST);
print_r($_SESSION);
	$stderr=" 2>&1";
	}
if(isset($_SESSION['admin'])&& ($_SESSION['admin']==$ADMINPASSWORD)) {
	if($DEBUG) print_r($_SESSION);
	if(!isset($_POST['exec'])) die("No, thanks.");
	$out=array();
	switch  ($_POST['exec']){
	case 'toggle': 
		exec($COMMANDS['toggle'].$stderr,$out,$ret);
		break;
	case 'newrec': 
		exec($COMMANDS['newrec'].$stderr,$out,$ret);
		break;
	case 'compress': 
		exec($COMMANDS['compress'].$stderr,$out,$ret);
		break;
    	case 'compressday': 
        	exec($COMMANDS['compressday'].$stderr,$out,$ret);
        	break;
	case 'cleanwav': 
		exec($COMMANDS['delwav'].$stderr,$out,$ret);
		break;
        case 'cleanzip': 
                exec($COMMANDS['delzip'].$stderr,$out,$ret);
                break;
	case 'listrec':
		break;
	case 'automix':
		$sessions=glob("$RECORDINGS/Jam-*");
		foreach($sessions as $s) {
			$to="/var/tmp/".basename($s)."/";
			mkdir($to);
			consolidate_tracks($s,$to);	
			$out=generate_mix($to);
		}
		exec($COMMANDS['zipmix'].$stderr,$out,$ret);
		break;
        case 'consolidate':
		exec($COMMANDS['cleancons'].$stderr, $out,$ret);
                $sessions=glob("$RECORDINGS/Jam-*");
                foreach($sessions as $s) {
                        $to=$CONSOLIDATED.basename($s)."/";
                        mkdir($to);
print("CONSOLIDATE: $s $to \n\n\n");
                        consolidate_tracks($s,$to);     
		exec($COMMANDS['zipcons1'].$stderr,$out,$ret);
                }
                break;
	case 'freespace':
		exec($COMMANDS['freespace'],$freemem);
		print($freemem[1]);
		break;
	die("No, thanks.");
	}
	//if the $DEBUG variable is set in config.php, let's show the results of the call
	if($DEBUG) {print_r($ret);print("\n");print_r($out);}

	//every command will return the recording directory content
	if($_POST['exec']<>'freespace') {
	exec($COMMANDS['listrec'],$list);
	foreach($list as $line) { 
		$tmp=explode("\t",str_replace($RECORDINGS."/","",$line));
		print($tmp[1]."\t".$tmp[0]."\n");
		}
	}
}


?>

