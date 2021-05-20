<?php
// Jamulus Recording Remote
// v0.6 - 20210423
// Vincenzo Della Mea

//STANDALONE AUTOMIX

//TODO: https://www.php.net/manual/en/function.getopt.php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(isCommandLineInterface()) {
// Bandmates names to be used for "informed" automix
// 'name' => value,
// name exactly as in Jamulus profile, 
// value: 1= left only, 0= right only, 0.5= center, etc
// Names not specified are automatically panned.

$BANDMATES=array(
	'Jimi' =>0.85,
	'Eric' =>0.45,
	'Carol' =>0.53,
	'Patti' =>0.5,	
	'Stevie'=> 0.11,
);
// File format for consolidated tracks
//TODO set bitrate
$CFORMAT="mp3"; //.wav, .opus ... any format managed by ffmpeg
//default dir is current dir for automix
$MIX="./";
$DEBUG=true;
$OPERATION='automix';
$DIRTYPE='single';
$AUDIONORMALIZATION=false;

//command line options
$optlist=array("automix","consolidate","single", "all","debug","format::","in:", "out:", "help", "normalize");

$options=getopt("",$optlist);
if(isset($options['debug'])) $DEBUG=true;
if(isset($options['consolidate'])) {$OPERATION='consolidate'; $CFORMAT='wav';}
if(isset($options['all'])) $DIRTYPE='all';
if(isset($options['format'])) $CFORMAT=$options['format'];
if(isset($options['in'])) $RECORDINGS=$options['in']; 
if(isset($options['out'])) $MIX=$options['out']; 
if(isset($options['normalize'])) $AUDIONORMALIZATION=true; 

if($DEBUG) var_dump($options);

if(isset($options['help']) || !isset($RECORDINGS)) die(automix_help());

$today=date("Ymd");

//If you need to adapt the Remote to your server, check the following commands
if(!$DEBUG) $FFMPEG_LOG="-loglevel quiet ";
$COMMANDS=array(
 "ffmpeg" => "ffmpeg $FFMPEG_LOG -hide_banner ",//-loglevel quiet
 "checkstereo" => "ffmpeg $FFMPEG_LOG -hide_banner -i ", 	
 "maxvolume" =>" $FFMPEG_LOG -hide_banner -i ",
 "zipmix" => "zip mix.zip *.mp3 ; rm $MIX/*.mp3 ",
 "ffprobe" => "ffprobe -hide_banner -show_entries stream=duration -of compact=p=0:nk=1 -v 0 ",
 );

print("AUTOMIX 0.51 - part of Jamulus Recording Remote\n");

//if($argc<3) die("php automix.php single|all path_to_recordings\n");

if($OPERATION=='automix' && $DIRTYPE=='all') {
	print("Generating automix for all the sessions in $RECORDINGS.\n");
	$sessions=glob("$RECORDINGS/Jam-*",GLOB_ONLYDIR);
	foreach($sessions as $s) {
		print("- $s \n");
		$from=$s;
		$to="/var/tmp/".basename($from)."/";
		mkdir($to);
		consolidate_tracks($from,$to, ".wav");
		$out=generate_mix($to);
		if($DEBUG) print_r($out);
		}
	}
else if($OPERATION=='automix' && $DIRTYPE=='single'){
	print("Generating automix for session $RECORDINGS.\n");
	$to="/var/tmp/".basename($RECORDINGS)."/";
	mkdir($to);
	consolidate_tracks($RECORDINGS,$to, ".wav");
	$out=generate_mix($to);
	if($DEBUG) print_r($out);
	}
else if($OPERATION=='consolidate' && $DIRTYPE=='single'){
	print("Consolidating tracks for session $RECORDINGS to $MIX .\n");
	mkdir($MIX);
	consolidate_tracks($RECORDINGS,$MIX."/", $CFORMAT);
	}
else if($OPERATION=='consolidate' && $DIRTYPE=='all'){
	print("Consolidating tracks for all the sessions in $RECORDINGS .\n");
	$sessions=glob("$RECORDINGS/Jam-*",GLOB_ONLYDIR);
	foreach($sessions as $s) {
		print("- $s \n");
		$from=$s;
		$to=$MIX."/".basename($from)."/";
		if($DEBUG) print("TO: $MIX - $from - $to. \n");
		mkdir($to);
		consolidate_tracks($from,$to, $CFORMAT);
		}
	}
else die(automix_help());

}


//FUNCTIONS

function generate_mix($session) {
	global $RECORDINGS;	
	global $MIX;
	global $COMMANDS;
	global $BANDMATES;
	global $DEBUG;
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
			if($numchans==0) 
				$rtmp=$offset;
			else 
				$rtmp=$rtmp+$step;

			$ltmp=1-$rtmp;
			$numchans++;
 			if($DEBUG) print("PANNING ".basename($t).": $ltmp - $rtmp \n");			
		}
		$ltmp=round($ltmp,3);
 		$rtmp=round($rtmp,3);
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
	if($DEBUG) {
		print("AUTOMIX:".$automixcommand."\n");
		print_r($out);
	}
	unset($out);
}


///////////////////////////////////////////
function consolidate_tracks($dir, $outdir, $format) {
	global $AUDIONORMALIZATION;
	global $COMMANDS;
	global $DEBUG;
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
		if($DEBUG) print("DURATION: ".$ffprobe.$t."\n");
	
		//check max volume
		$maxvolumecommand=$checkmaxvolume.$t.
		' -af "volumedetect" -f null /dev/null 2>&1 |grep max_volume';
		exec($maxvolumecommand,$outvol,$retvol);
		if($DEBUG) print("VOLUME: ".$maxvolumecommand."\n");
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
	
	if($DEBUG) print_r($tracks);
	
	foreach ($tracks as $ip=>$t){
		$trackdur=0; 
		$maxoffset=0;
		$inputs=""; $delays=""; $amix="";
		//$format contains the file extension: ffmpeg will generate in such format
		//TODO set bitrate!
		$outname=$outdir.$t['name']."-consolidated.".$format; 
		if($t['name']=='____') $outname=$outdir.$ip."-consolidated.".$format; 
		$c=0;
		
		//sort by offset to reorder when many WAVs
		//and decide whether the channel should be stereo or mono
		$orderedwavs=array();
		foreach($t['segments'] as $tr=>$s){
			$orderedwavs[$tr]=$s['offset'];
			$numberofchannels=$s['channels'];
			}
		asort($orderedwavs);
		
		$monostereo='mono';if($numberofchannels==2) $monostereo='stereo';

		$previousdur=0;
		foreach($orderedwavs as $tr=>$o){
			$s=$t['segments'][$tr];
			$inputs.="-i $tr ";
			$delay=round(1000*($s['offset']-$previousdur),0);
			$maxvolume=-$s['maxvolume'];
			
			//Volumes are all brought to 0dB
			//this tries to save who had set the volume too low
			$volumeincrease="";
			if($maxvolume>0 && $maxvolume<7)
				$volumeincrease=" ,volume=".$maxvolume."dB";
			$delays.=	
				"[$c]aformat=sample_fmts=s16:sample_rates=48000".
				":channel_layouts=$monostereo".
				",adelay=".$delay."|".$delay.$volumeincrease."[a$c]; ";
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
		
		//Audio normalization: does not give reliable results
		if($AUDIONORMALIZATION) $audionorm=", dynaudnorm=t=0.1 ";
//		if($AUDIONORMALIZATION) $audionorm=", loudnorm=tp=0.0, aresample=48000";		

		$command="ffmpeg -hide_banner $inputs $silence".
			" -filter_complex \"$delays $silencedelay $amix".
			$silenceamix."concat=n=$total:v=0:a=1 $audionorm". "\"  $outname\n";


		exec($command, $outcommand);
		if($DEBUG) {
			print("CONSOLIDATE: ".$command."\n");
			print_r($outcommand);
		}
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

function automix_help() {
?>
AUTOMIX v0.51 - part of Jamulus Recording Remote
Choose an operation:
--automix (default)
--consolidate
Is a full Recordings directory or a single session?
--single (default)
--all
Files:
--in path_to_recordings directory
--out path_to_generated (default: current dir)
Options:
--format (wav,mp3,opus) (default: mp3 for automix, wav for consolidate)
--normalize audio normalization, default off - not good yet
--debug add extra output
--help this one
<?php

}
?>


