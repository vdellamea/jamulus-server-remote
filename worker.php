<?php
// Jamulus Server Remote
// v0.3 - 20201219
// Vincenzo Della Mea

// REMOTE WEB SERVICE

session_start();

include("config.php");
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
	exec("du -sh  $RECORDINGS/Jam*",$list);
	foreach($list as $line) { 
		$tmp=explode("\t",str_replace($RECORDINGS."/","",$line));
		print($tmp[1]."\t".$tmp[0]."\n");
	}
}
?>
