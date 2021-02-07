<?php
// Jamulus Recording Remote
// v0.5 - 20210207
// Vincenzo Della Mea

//STANDALONE AUTOMIX


if(isCommandLineInterface()) {
// Bandmates names to be used for "informed" automix
// 'name' => value,
// name exactly as in Jamulus profile, 
// value: 1= left only, 0= right only, 0.5= center, etc
// Names not specified are automatically panned.

$BANDMATES=array(
	'Alex' =>0.55,
	'CP__' =>0.45,
	'Andrea' =>0.5,
	'Enzo' =>0.5,	
	'Luca_DG'=> 0.3,
	'Daniele'=> 0.9,
	'gigio'=> 0.1,
);
$MIX="./";

$today=date("Ymd");

//If you need to adapt the Remote to your server, check the following commands
$COMMANDS=array(
 "ffmpeg" => "ffmpeg -loglevel quiet -hide_banner ",//-loglevel quiet
 "checkstereo" => "ffmpeg -i ", 	
 "maxvolume" =>"ffmpeg -i ",
 "zipmix" => "zip mix.zip *.mp3 ; rm $MIX/*.mp3 ",
 "ffprobe" => "ffprobe  -show_entries stream=duration -of compact=p=0:nk=1 -v 0 ",
 );

print("AUTOMIX 0.5 - part of Jamulus Recording Remote\n");

if($argc<3) die("php automix.php single|all path_to_recordings\n");

if($argv[1]=='all') {
	$RECORDINGS=$argv[2];
	print("Generating automix for all the sessions in $RECORDINGS.\n");
	$sessions=glob("$RECORDINGS/Jam-*",GLOB_ONLYDIR);
	foreach($sessions as $s) {
		print("- $s \n");
		$from=$s;
		$to="/var/tmp/".basename($from)."/";
		mkdir($to);
		consolidate_tracks($from,$to);
		$out=generate_mix($to);
		print_r($out);
		}
	}
else if($argv[1]=='single'){
	print("Generating automix for session $argv[2].\n");
	$from=$argv[2];
	$to="/var/tmp/".basename($from)."/";
	mkdir($to);
	consolidate_tracks($from,$to);
	$out=generate_mix($to);
	print_r($out);
	}
else die("Wrong parameters.");

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
	
	//scan tracks, check known clients, check mono/stereo
	foreach($dir as $t) {
		//Is it a known client or not? 
		if(!known(basename($t))) $unknowns++;
		
		exec($checkstereo.$t." 2>&1 | grep Guessed",$out,$rec);
		$stereo=substr(trim($out[0]),-6)=="stereo";
		$tracks[$t]=$stereo;
		$chantotal1=$chantotal+1;
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
	if($unknowns>0) $offset=0.5/$unknowns;
	$step=$offset*2;
	$lmult=-0.5; 
	$numchans=0;
	foreach($channels as $t=>$chans){
		$inputs.="-i $t ";
		//if client is known, use the value from config
		if(known(basename($t))) {
			$ltmp=$BANDMATES[known_name(basename($t))]; 	
			$rtmp=1-$ltmp;		
		}
		else {
			//first one of odd tracks: centered
			if(($numchans==0) && ($unknowns%2)){
				$ltmp=0.5; 
				$rtmp=0.5;
				$lmult=-0.5+$offset;
				$unknowns--;
			} 
			//first one of even tracks: left
			else if(($numchans==0)&& ($unknowns%2==0)){
				$lmult=$lmult+$offset;
				$rtmp=$lmult+1; 
				$ltmp=1-$rtmp;
				$numchans++;
			}
			//odd: move to right
			else if($numchans%2){
				$rtmp=$ltmp; 
				$ltmp=1-$rtmp;
				$numchans++;
			}
			//even: change position on left
			else if($numchans%2==0){
				$lmult=$lmult+$step;
				$ltmp=$lmult+1;
				$rtmp=1-$ltmp;
				$numchans++;
			}

			$ltmp=round($ltmp,3);
			$rtmp=round($rtmp,3);
		
		}
		$left.=$ltmp."*".$chans[0]."+";
		$right.=$rtmp."*".$chans[1]."+";
	}

	// remove trailing '+'
	$left=substr($left,0,-1);
	$right=substr($right,0,-1);

	$volumeplus=$clients;
	$automixcommand=
		"$ffmpeg $inputs -filter_complex \"amerge=inputs=". $clients. 
		",volume=$volumeplus"."dB,pan=stereo|FL<$left|FR<$right".
		"[a]\" -map \"[a]\" $MIX"."$filename.mp3\n";
	
	exec($automixcommand,$out,$ret);
	//return $out;
	print("EXEC:".$automixcommand);

}


///////////////////////////////////////////
function consolidate_tracks($dir, $outdir) {
	global $AUDIONORMALIZATION;
	global $COMMANDS;
	$ffprobe=$COMMANDS['ffprobe'];
	$checkmaxvolume=$COMMANDS['maxvolume'];

	// scan .lof file to read track offsets in seconds
	$lof=file($dir."/".basename($dir).".lof");
	foreach($lof as $f){
		$tmp=explode(" ",trim($f));
		$offset[str_replace("\"","",$tmp[1])]=$tmp[3];
	}

	$list=glob($dir."/*.wav");

	$maxduration=0;//this will become the total length of the session
	
	foreach($list as $t){
		$tmp=explode("-",substr(basename($t),0,-4)); 
		//check duration of each track
		exec($ffprobe.$t,
		$out);
		$duration=$out[0];unset($out);	
		print("FFPROBE:".$ffprobe.$t."\n");
	
		//check max volume
		$maxvolumecommand=$checkmaxvolume.$t.
		' -af "volumedetect" -f null /dev/null 2>&1 |grep max_volume';
		exec($maxvolumecommand,$outvol,$retvol);
		print($maxvolumecommand."\n");
		$outvol2=explode(":",trim($outvol[0]));
		$outvol2=explode(" ",substr($outvol2[1],0,-2)); 
		$maxvolume=$outvol2[1]; 
		unset($outvol); 
		unset($outvol2);
	
		//if a client name is available, use it
		if(!isset($tracks[$tmp[1]]['name'])) $tracks[$tmp[1]]['name']='____';
		if($tmp[0]<>'____') $tracks[$tmp[1]]['name']=$tmp[0];

		// real duration is offset + duration
		$newdur=$offset[basename($t)]+$duration;
		// look for the longest
		if($newdur>$maxduration) $maxduration=$newdur;

		$tracks[$tmp[1]]['segments'][$t]=
		array(	'frame'=>$tmp[2],
				'channels'=>$tmp[3],
				'offset'=>$offset[basename($t)],
				'duration'=>$duration,
				'newdur'=>$newdur,
				'maxvolume'=>$maxvolume,
			);
	}
	
	print_r($tracks);
	foreach ($tracks as $ip=>$t){
		$trackdur=0; 
		$maxoffset=0;
		$inputs=""; $delays=""; $amix="";
		$outname=$outdir.$t['name']."-consolidated.wav"; 
		if($t['name']=='____') $outname=$outdir.$ip."-consolidated.wav"; 
		$c=0;
		
		//sort by offset to reorder when many WAVs
		//and decide whether the channel should be stereo or mono
		$orderedwavs=array();
		foreach($t['segments'] as $tr=>$s){
			$orderedwavs[$tr]=$s['offset'];
			$numberofchannels=$s['channels'];
			}
		asort($orderedwavs);
		//print_r($orderedwavs);
		
		$monostereo='mono';if($numberofchannels==2) $monostereo='stereo';
		$previousdur=0;
		foreach($orderedwavs as $tr=>$o){
			$s=$t['segments'][$tr];
			$inputs.="-i $tr ";
			//$delay=round(1000*$s['offset'],0);
			$delay=round(1000*($s['offset']-$previousdur),0);
			$maxvolume=-$s['maxvolume'];
			$volumeincrease="";
			if($maxvolume>0 && $maxvolume<7)
				$volumeincrease=" ,volume=".$maxvolume."dB";
			$delays.=
			"[$c]aformat=sample_fmts=s16:sample_rates=48000:cl=$monostereo,adelay=".
				$delay."|".$delay.$volumeincrease."[a$c]; ";
//			",volume=".$volumeincrease."[a$c]; ";
			$amix.="[a$c]";
			$c++;
			if($s['offset']>$maxoffset) {
				$trackdur=($s['offset']+$s['duration']);
				$maxoffset=$s['offset'];
			}
			$previousdur=$s['offset']+$s['duration'];
		}
		
		
		$total=$c;
		$silence="";$silencedelay="";$silenceamix="";
		$missingtime=round($maxduration-$previousdur,3);
		$trackdur=round(1000*$trackdur,0);
		// if the consolidated track is shorter than the maximum duration,
		// add a silence track that lasts as the maximum
		if($missingtime>0) {
			$total=$c+1;
			$silence=" -f lavfi -i anullsrc=r=48000 ";
			$tracksilence=round($maxduration,3);
			$silencedelay="[$c]atrim=duration=".$missingtime."[a$c];";
			$silenceamix="[a$c]";
		}
		
		if($AUDIONORMALIZATION==true) $audionorm=", dynaudnorm=t=0.1 ";
//		if($AUDIONORMALIZATION==true) $audionorm=", loudnorm=tp=0.0, aresample=48000";		
		$command="ffmpeg -hide_banner $inputs $silence".
			" -filter_complex \"$delays $silencedelay $amix".
			$silenceamix."concat=n=$total:v=0:a=1 $audionorm". "\"  $outname\n";

		print($command."\n");
		exec($command, $outcommand);
		exec("ls -l /var/tmp", $tmpout);
		print_r($tmpout);
		unset($outcommand); 
	}
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

function isCommandLineInterface()
{
    return (php_sapi_name() === 'cli');
}

?>


