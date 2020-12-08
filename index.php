<?php
// Jamulus Server Remote
// v0.1 - 20201206
// Vincenzo Della Mea

// INTERFACE
session_start();

include("config.php");

?>
<!doctype html>

<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content ="width=device-width,initial-scale=1,user-scalable=yes" />

    <title>Jamulus Server Remote</title>
    <style type="text/css">
    	* { font-family: sans-serif;  }
    	body {background-color: #62a7c6; 
    		line-height:80%
    		}
		textarea {font-size: 110%; }
    	button {color: white; font-size: larger;}
		button:disabled,button[disabled]{
  			border: 1px solid #999999;
  			background-color: #cccccc;
  			color: #666666;
		}
	#reloadbutton {font-size: smaller; }
    </style>

  </head>
<body>
	<h2>Jamulus Server Remote</h2>
<?php
print("<h1>$SERVERNAME</h1>\n");
if(
	(isset($_SESSION['me'])&& ($_SESSION['me']==$PASSWORD)) ||
	(isset($_POST['pwd'])&& ($_POST['pwd']==$PASSWORD))
	) {
		$_SESSION['me']=$PASSWORD;
?>
    
<h3>Recording</h3>  
<p id="recarea">
<button type="button" id="togglebutton"
	style="background-color: grey"
	onclick="sendtoggle()">Toggle on/off</button>

<button type="button" id="newbutton" disabled="disabled"
	style="background-color: orange"
	onclick="sendcommand('newrec',this)">Start new</button>
</p>

<h3>Sessions
<button type="button" id="reloadbutton" 
        style="background-color: navy"
        onclick="sendcommand('listrec', this)">Reload</button>
</h3>
<p>
<textarea id="log" cols="40" rows="12"></textarea>
</p>
<h3>Finish</h3>
<p>
<button type="button" id="compressbutton" 
	style="background-color: navy" 
	onclick="sendcommand('compress', this)">Zip all</button>

<button type="button" id="compressday" 
        style="background-color: navy"
        onclick="sendcommand('compressday', this)">Zip today</button>

<button type="button" id="cleanbutton" title="Careful: this destroys all session files" 
	style="background-color: navy"
	onclick="sendcommand('cleanup',this)">Cleanup</button>
</p>
<p><a href="session.zip">Zipped everything</a>  
<a href="<?php echo date("Ymd") ?>.zip">Today's zip</a>  
<a href="<?php echo $RECURL ?>">All files</a>
</p>

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
	document.getElementById("compressday").disabled=false;
	document.getElementById("cleanbutton").disabled=false;
	}
	else {
        document.getElementById("newbutton").disabled=false;
        document.getElementById("compressbutton").disabled=true;
        document.getElementById("compressday").disabled=true;
        document.getElementById("cleanbutton").disabled=true;

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
      if(btn) btn.disabled=false;

    }
  };
  if(btn) btn.disabled=true;
  xhttp.open("POST", "worker.php", true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(params);
}

</script>

<?php
}
else if(!isset($_POST['pwd'])){
?>
<h3>Musicians</h3>
<p><a href="session.zip">Zipped everything</a> 
<a href="<?php echo date("Ymd") ?>.zip">Today's zip</a>  
<a href="<?php echo $RECURL ?>">All files</a>
</p>
<h3>Admin</h3>
<form action="index.php" method="post">
<input type="password" name="pwd" />
<input type="submit" name="login" value="login" />
</form>

<?php
} 

?>
<hr />
<address>
VDM 2020
</address>
  </body>
</html>