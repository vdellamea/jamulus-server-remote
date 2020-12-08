<?php
// Jamulus Server Remote
// v0.1 - 20201206
// Vincenzo Della Mea

// REMOTE WEB SERVICE
session_start();

include("config.php");
$stderr=""; 
if($DEBUG) {
	print_r($_POST);
	$stderr=" 2>&1";
	}
if(isset($_SESSION['me'])&& ($_SESSION['me']==$PASSWORD)) {
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
	case 'cleanup': 
		exec($COMMANDS['cleanup'].$stderr,$out,$ret);
		break;
	case 'listrec':
		break;
	die("No, thanks.");
	}
	//if the $DEBUG variable is set in config.php, let's show the results of the call
	if($DEBUG) {print_r($ret);print("\n");print_r($out);}

	//every command will return the recording directory content
	exec("du -sh  $RECORDINGS/*",$list);
	foreach($list as $line) { 
		$tmp=explode("\t",str_replace($RECORDINGS."/","",$line));
		print($tmp[1]."\t".$tmp[0]."\n");
	}
}
?>
