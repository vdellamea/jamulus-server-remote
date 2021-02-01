<?php
// Jamulus Recording Remote
// v0.5 - 20210201
// Vincenzo Della Mea

//STANDALONE AUTOMIX

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
$MIX="./";

$today=date("Ymd");

//If you need to adapt the Remote to your server, check the following commands
$COMMANDS=array(
 "ffmpeg" => "/Users/enzo/Documents/Didattica/DTEH/video/ffmpeg -loglevel quiet ",
 "checkstereo" => "/Users/enzo/Documents/Didattica/DTEH/video/ffmpeg -i ", 	
 "zipmix" => "zip mix.zip *.mp3 ; rm $MIX/*.mp3 ",
 );

print("AUTOMIX 0.5 - part of Jamulus Recording Remote\n");

if($argc<3) die("php automix.php single|all path_to_recordings");

if($argv[1]=='all') {
	$RECORDINGS=$argv[2];
	print("Generating automix for all the sessions in $RECORDINGS.\n");
	$sessions=glob("$RECORDINGS/Jam-*",GLOB_ONLYDIR);
	foreach($sessions as $s) {
		print("- $s \n");
		$out=generate_mix($s);
		print_r($out."\n");
		}
	}
else if($argv[1]=='single'){
	print("Generating automix for session $argv[2].\n");
	$out=generate_mix($argv[2]);
	}
else die("Wrong parameters.");



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

