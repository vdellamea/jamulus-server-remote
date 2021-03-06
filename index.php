<?php
// Jamulus Server Remote
// v0.5 - 20210430
// Vincenzo Della Mea
// CSS and HTML updates by Simon White

// INTERFACE
session_start();
if(isset($_POST['logout'])) {
	$_SESSION = array();
	session_destroy();
}

include("config.php");
if($DEBUG) {
        print_r($_POST);
        print_r($_SESSION);
        }
?>
<!doctype html>

<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content ="width=device-width,initial-scale=1,user-scalable=yes" />

    <title>Jamulus Recording Remote</title>
    <style type="text/css">
    	* { font-family: "Open Sans", sans-serif;  }
    	body {background-color: #62a7c6; 
    		line-height:100%
    		}
	textarea {font-size: 110%; 
		padding: 0.3em;
		}
	button {color: white; 
		font-size: larger;
		padding: 0 0.4em;
		border-radius: 5px;}
	button:disabled,button[disabled]{
  			border: 1px solid #999999;
  			background-color: #cccccc;
  			color: #666666;
			border-radius: 5px;
		}
	#reloadbutton {/* font-size: smaller; */}
	.line { margin-top: 0.5em; }
	.lm { margin-left: 1em; }
    </style>

  </head>
<body>
<?php
print("<h1>$SERVERNAME</h1>\n");
if(
	(isset($_SESSION['admin'])&& ($_SESSION['admin']==$ADMINPASSWORD)) ||
	(isset($_POST['apwd'])&& ($_POST['apwd']==$ADMINPASSWORD))
	) {
		$_SESSION['admin']=$ADMINPASSWORD;
?>
<h2>Jamulus Recording Remote</h2>
<h3>Recording</h3>  
<p id="recarea">
<button type="button" id="togglebutton"
	style="background-color: grey"
	onclick="sendtoggle()">Toggle on/off</button>

<button type="button" id="newbutton" disabled="disabled"
	style="background-color: orange"
	onclick="sendcommand('newrec',this)">Start new</button>
</p>

<h3>Sessions</h3>
<div class="filelist">
<button type="button" id="reloadbutton" 
        style="background-color: navy"
        onclick="sendcommand('listrec', this)">Refresh list</button>
<span class="lm">Free: </span><span id="freespace">-</span>
</div>
<div class="line">
<textarea id="log" cols="40" rows="12"></textarea>
</div>
<h3>Finish</h3>
<p>
<div class="line">
<button type="button" id="compressbutton" 
	style="background-color: navy" 
	onclick="sendcommand('compress', this)">Zip WAVs</button>

<button type="button" id="consolidate" 
        style="background-color: navy"
        onclick="sendcommand('consolidate', this)">Consolidate</button>
        
<button type="button" id="automix" 
        style="background-color: navy"
        onclick="sendcommand('automix', this)">Automix</button>

</div>
<div class="line">
<button type="button" id="cleanbutton" title="Careful: this destroys all session files" 
	style="background-color: navy"
	onclick="sendcommand('cleanwav',this)">Delete WAVs</button>
<button type="button" id="cleanzips" 
	title="Careful: this destroys all zip files!"
        style="background-color: navy"
        onclick="sendcommand('cleanzip',this)">Delete ZIPs</button>

</div>
<h3>Download</h3>
<div><a href="download.php?what=all">Original tracks</a>&nbsp;|&nbsp; 
<a  class="lm" href="download.php?what=consolidated">Consolidated</a>&nbsp;|&nbsp;  
<a  class="lm" href="download.php?what=mix">Mixed</a> 
 
</div>

<script>
var endpoint="worker.php";

sendcommand('listrec', null);

function sendtoggle() {
  var xhttp = new XMLHttpRequest();
  var params="exec=toggle";
  var current=document.getElementById("togglebutton").style.backgroundColor;
  var next='red';
  if(current=='red') {
	next='grey';
        document.getElementById("newbutton").disabled=true;
	document.getElementById("compressbutton").disabled=false;

document.getElementById("consolidate").disabled=false;

document.getElementById("automix").disabled=false;
	document.getElementById("cleanbutton").disabled=false;
        document.getElementById("cleanzips").disabled=false;
	}
	else {
        document.getElementById("newbutton").disabled=false;
        document.getElementById("compressbutton").disabled=true;       
        document.getElementById("consolidate").disabled=true;
    document.getElementById("automix").disabled=true;
        document.getElementById("cleanbutton").disabled=true;
        document.getElementById("cleanzips").disabled=true;
	}
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("togglebutton").style.backgroundColor = next;
      document.getElementById("log").innerHTML = this.responseText;
	document.getElementById("togglebutton").disabled=false;
    }
  };
  document.getElementById("togglebutton").disabled=true;
  xhttp.open("POST", endpoint, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function sendcommand(command, btn){
  var xhttp = new XMLHttpRequest();
  var params="exec="+command;
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("log").innerHTML = this.responseText;
    checkfreemem();
      if(btn) btn.disabled=false;
    
    }
  };
  if(btn) btn.disabled=true;
  xhttp.open("POST", "worker.php", true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

function checkfreemem(){
  var xhttp = new XMLHttpRequest();
  var params="exec=freespace";
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("freespace").innerHTML = this.responseText;
    }
  };
  xhttp.open("POST", "worker.php", true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

</script>

<?php
}
else 
if(
        (isset($_SESSION['musician'])&& ($_SESSION['musician']==$MUSICIANPASSWORD)) ||
        (isset($_POST['mpwd'])&& ($_POST['mpwd']==$MUSICIANPASSWORD))
        ) {
                $_SESSION['musician']=$MUSICIANPASSWORD;
                unset($_SESSION['admin']);
		
?>
<h3>Files</h3>
<p><a href="download.php?what=all">Zipped WAVs</a> </p>
<p>
<a href="download.php?what=consolidated">Consolidated files</a>  
</p>
<p><a href="download.php?what=mix">Mixed MP3</a></p>
<?php
}
else {
?>
<h3>Musicians</h3>
<form action="index.php" method="post">
<input type="password" name="mpwd" />
<input type="submit" name="login" value="login" />
</p>
<h3>Admin</h3>
<form action="index.php" method="post">
<input type="password" name="apwd" />
<input type="submit" name="login" value="login" />
</form>

<?php
} 

?>
<hr />
<form action="index.php" method="post">
<input type="submit" name="logout" value="logout" />
</form>
<address class="line">
<a href="https://github.com/vdellamea/jamulus-server-remote/">Jamulus Recording Remote</a>
</address>
  </body>
</html>
