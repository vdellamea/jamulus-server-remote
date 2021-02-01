<?php
// Jamulus Recording Remote
// v0.5 - 20210201
// Vincenzo Della Mea

// REMOTE WEB SERVICE
session_start();
include("config.php");
$today=date("Ymd");

//If you need to adapt the Remote to your server, check the following commands
$COMMANDS=array(
 "toggle" => "sudo /bin/systemctl kill -s SIGUSR2 jamulus ",
 "newrec" => "sudo /bin/systemctl kill -s SIGUSR1 jamulus ",
 "compress" => "cd $RECORDINGS ; rm session.zip; zip -r session.zip Jam* ",
 "compressday" => "cd $RECORDINGS ; rm $today.zip; zip -r $today.zip Jam-$today-* ", 
 "listrec" => "du -sh $RECORDINGS/Jam* ",
 "freespace" => "df -h --output=avail $RECORDINGS ",
 "delwav" => "rm -fr $RECORDINGS/Jam* ",
 "delzip" => "rm -fr $RECORDINGS/*.zip ",
 "ffmpeg" => "ffmpeg -loglevel quiet ",
 "checkstereo" => "ffmpeg -i ", 	
 "zipmix" => "rm $RECORDINGS/mix.zip; cd $MIX; zip $RECORDINGS/mix.zip *.mp3 ; rm $MIX/*.mp3 ",
 );



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
		$sessions=glob("$RECORDINGS/Jam-$today*",GLOB_ONLYDIR);
		foreach($sessions as $s) $out=generate_mix($s);
		exec($COMMANDS['zipmix'].$stderr,$out,$ret);
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

//FUNCTIONS

function generate_mix($session) {
	global $RECORDINGS;	
	global $MIX;
	global $COMMANDS;
	global $BANDMATES;
	$ffmpeg=$COMMANDS['ffmpeg'];
	$checkstereo=$COMMANDS['checkstereo'];
	$filename=basename($session);
	$dir=glob($session."/*.wav");
	$chantotal=0;
	$unknowns=0;
	//scan tracks and check mono/stereo
	foreach($dir as $t) {
		exec($checkstereo.$t." 2>&1 | grep Guessed",$out,$rec);
		$stereo=substr(trim($out[0]),-6)=="stereo";
		$tracks[$t]=$stereo;
		$chantotal1=$chantotal+1;
		if(!known(basename($t))) $unknowns++;
		
		if($stereo) {	
			$channels[$t]=array("c".$chantotal,"c".$chantotal1);
			$chantotal=$chantotal+2;
		}
		else 
			{
			$channels[$t]=array("c".$chantotal,"c".$chantotal);
			$chantotal=$chantotal+1;
		}
		unset($out);
	}
	$clients=sizeof($tracks);
	
	//STEREO PANNING
	$offset=0.5/$unknowns;
	$step=$offset*2;
	$lmult=-0.5; 
//	print("----> $unknowns $offset $step \n");
// 	$step=1/($unknowns-1);
// 	$lmult=1;$rmult=0;
	$numchans=0;
	foreach($channels as $t=>$chans){
		$inputs.="-i $t ";
		if(known(basename($t))) {
//			print("KNOWN:".basename($t)." \n");
			$ltmp=$BANDMATES[known_name(basename($t))]; 	
			$rtmp=1-$ltmp;		
//			print("$ltmp $rtmp \n")	;
		}
		else {
//			print("UNKNOWN: $numchans ".basename($t)." \n");
			if(($numchans==0) && ($unknowns%2)){
				$ltmp=0.5; 
				$rtmp=0.5;
				$lmult=-0.5+$offset;
//				print("B0\n");
				$unknowns--;
			} else if(($numchans==0)&& ($unknowns%2==0)){
				$lmult=$lmult+$offset;
				$rtmp=$lmult+1; $ltmp=1-$rtmp;
//				print("B1\n");
				$numchans++;
			}
			else if($numchans%2){
				$rtmp=$ltmp; $ltmp=1-$rtmp;
//				print("B2\n");
				$numchans++;
			}
			else if($numchans%2==0){
				$lmult=$lmult+$step;
				$ltmp=$lmult+1;$rtmp=1-$ltmp;
//				print("B3\n");
				$numchans++;
			}

			$ltmp=round($ltmp,3);$rtmp=round($rtmp,3);
//			print("$ltmp $rtmp lmult $lmult\n")	;
		
		}
		$left.=$ltmp."*".$chans[0]."+";
		$right.=$rtmp."*".$chans[1]."+";
	}

	$left=substr($left,0,-1);
	$right=substr($right,0,-1);
//print("EXEC:"."$ffmpeg $inputs -filter_complex \"amerge=inputs=$clients,pan=stereo|FL<$left|FR<$right"."[a]\" -map \"[a]\" $MIX"."$filename.mp3\n");
exec("$ffmpeg $inputs -filter_complex \"amerge=inputs=$clients,pan=stereo|FL<$left|FR<$right"."[a]\" -map \"[a]\" $MIX"."$filename.mp3\n",$out,$ret);
return $out;
}

function known($track) {
	global $BANDMATES;
	$ret=false;
	foreach($BANDMATES as $m=>$n) 
		if($ret=(substr($track,0,strlen($m))==$m)) 	
			break;
	return $ret;
}

function known_name($track) {
	global $BANDMATES;
	$ret=false;
	foreach($BANDMATES as $m=>$n) {
		$ret=substr($track,0,strlen($m));
		if($ret==$m) break;
		}
	return $ret;
}

?>

