<?php
// Jamulus Server Remote
// v0.5 - 20210430
// Vincenzo Della Mea

// PROTECTED DOWNLOAD
session_start();
include("config.php");
if((isset($_SESSION['admin'])&& ($_SESSION['admin']==$ADMINPASSWORD)) ||
	(isset($_SESSION['musician'])&& ($_SESSION['musician']==$MUSICIANPASSWORD))) {

	if(isset($_GET['what']) && $_GET['what']=='consolidated') 
		$file="$RECORDINGS"."consolidated-".date("Ymd").".zip";
	else if(isset($_GET['what']) && $_GET['what']=='all') 
		$file="$RECORDINGS"."orig-".date("Ymd").".zip";
        else if(isset($_GET['what']) && $_GET['what']=='mix') 
                $file="$RECORDINGS"."mix-".date("Ymd").".zip";
	else
		die("No, thanks");
		
	if(file_exists($file)){
	header('Content-Description: Download recordings');
    	header('Content-Type: application/octet-stream');
    	header('Content-Disposition: attachment; filename="'.basename($file).'"');
    	header('Expires: 0');
    	header('Cache-Control: must-revalidate');
    	header('Pragma: public');
    	header('Content-Length: ' . filesize($file));
    	readfile($file);
    	exit;	
	}

	}
	
?>
