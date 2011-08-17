<?php

###############################################################
# Page Password Protect 2.13
###############################################################
# Visit http://www.zubrag.com/scripts/ for updates
############################################################### 
#
# Usage:
# Set usernames / passwords below between SETTINGS START and SETTINGS END.
# Open it in browser with "help" parameter to get the code
# to add to all files being protected. 
#    Example: password_protect.php?help
# Include protection string which it gave you into every file that needs to be protected
#
# Add following HTML code to your page where you want to have logout link
# <a href="http://www.example.com/path/to/protected/page.php?logout=1">Logout</a>
#
###############################################################

/*
-------------------------------------------------------------------
SAMPLE if you only want to request login and password on login form.
Each row represents different user.

$LOGIN_INFORMATION = array(
  'zubrag' => 'root',
  'test' => 'testpass',
  'admin' => 'passwd'
);

--------------------------------------------------------------------
SAMPLE if you only want to request only password on login form.
Note: only passwords are listed

$LOGIN_INFORMATION = array(
  'root',
  'testpass',
  'passwd'
);

--------------------------------------------------------------------
*/
##################################################################
#  SETTINGS START
##################################################################
// Add login/password pairs below, like described above
// NOTE: all rows except last must have comma "," at the end of line
$LOGIN_INFORMATION = array(
	'judge1' => 'selppa',
	'judge2' => 'segnaro',
	'judge3' => 'sananab',
	'judge4' => 'sehcaep',
	'admin' => 'reunion'
);
// request login? true - show login and password boxes, false - password box only
define('USE_USERNAME', true);
// User will be redirected to this page after logout
define('LOGOUT_URL', 'http://www.example.com/');
// time out after NN minutes of inactivity. Set to 0 to not timeout
define('TIMEOUT_MINUTES', 60);
// This parameter is only useful when TIMEOUT_MINUTES is not zero
// true - timeout time from last activity, false - timeout time from login
define('TIMEOUT_CHECK_ACTIVITY', true);
##################################################################
#  SETTINGS END
##################################################################


///////////////////////////////////////////////////////
// do not change code below
///////////////////////////////////////////////////////
// show usage example
if(isset($_GET['help'])) {
	die('Include following code into every page you would like to protect, at the very beginning (first line):<br>&lt;?php include("' . str_replace('\\','\\\\',__FILE__) . '"); ?&gt;');
}
// timeout in seconds
$timeout = (TIMEOUT_MINUTES == 0 ? 0 : time() + TIMEOUT_MINUTES * 60);
// logout?
if(isset($_GET['logout'])) {
	setcookie("verify", '', $timeout, '/'); // clear password;
	header('Location: ' . LOGOUT_URL);
	exit();
}
if(!function_exists('showLoginPasswordProtect')) {
	// show login form
	function showLoginPasswordProtect($error_msg) {
		?>
		<html>
			<head>
				<title>Enter password to access page</title>
				<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
				<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
				<!-- Added for iPhone compatibility -->
				<meta name="viewport" content="user-scaleable=no, width=320px" />
				<meta name="apple-mobile-web-app-capable" content="yes" />
				<style type="text/css">
					body {
					    min-height: 420px !important;
						background: rgb(197,204,211) url(./css/img/pinstripes.png);
						font-family: Helvetica;
						font-size: 18px;
						margin: 0 0 0 0px;
						padding: 0;
						-webkit-user-select: none;
						-webkit-text-size-adjust: none;
						/* Added to centre buttons */
						text-align: center;
					}
					div#header {
						-webkit-box-sizing: border-box;
						border-bottom: 1px solid rgb(46,55,68);
						background: rgb(109,133,163) url(./css/img/toolbar.png) repeat-x top;
						border-top: 1px solid rgb(205,213,223); 
						padding: 10px;
						margin: 0 0 0 -10px;
						height: 44px;			/* Or 44px if border-top is active */
						position:relative;
					}
					div#header h1 {
						color: #fff;
						font: bold 20px Helvetica;
						text-shadow: #2d3642 0 -1px 0;
						text-align: center;
						text-overflow: ellipsis;
						white-space: nowrap;
						overflow: hidden;
						width: 49%;
						padding: 5px 0;
						margin: 2px 0 0 -24%;
						position: absolute;
						top: 0;
						left: 50%;
					}
					h2{	
						text-align: left;
						font:bold 18px Helvetica;
						text-shadow:rgba(255,255,255,.2) 0 1px 1px;
						color:#4c566c;
						margin:10px 20px 6px;
					}
					input[type="input"], input[type="password"]{
					    color: black;
						background: #fff  url(../.png); 
					    border: 0;
					    font: normal 17px Helvetica;
					    padding: 0;
					    display: inline-block;
					    margin: 0;
					    width:100%;
					    -webkit-appearance: textarea;
						/* Additional */
						text-align: center;
					}
					input[type="submit"] {
						display:none;
					}
					ul {
					    color: black;
					    background: #fff;
					    border: 1px solid #B4B4B4;
					    font: bold 17px Helvetica;
					    padding: 0;
					    margin: 15px 10px 17px 10px;
					    -webkit-border-radius: 8px;
					}
					ul li {
						list-style-type: none;
						border-top: 1px solid #B4B4B4; 
						padding: 10px 10px 10px 10px;
					}
					li:first-child {
						border-top: 0;
						-webkit-border-top-left-radius: 8px;
						-webkit-border-top-right-radius: 8px;
					}
					li:last-child {	
						-webkit-border-bottom-left-radius: 8px;
						-webkit-border-bottom-right-radius: 8px;
					}
					a {
						margin: 0;
						font-size: 9px;
						color: #B0B0B0;
					}
				</style>
			</head>
			<body>
				<div id="header"><h1>Live Results</h1></div>
				<div>
					<h2>Enter your login details...</h2>
					<form method="post">
						<ul>
							<?php if (USE_USERNAME) echo '<li><input type="input" name="access_login" placeholder="username (case sensitive)"/></li>'; ?>
							<li><input type="password" name="access_password" placeholder="password  (case sensitive)"/></li>
						</ul>
						<input type="submit" name="Submit" value="Submit" />
						<br /><br /><font color="red"><?php echo $error_msg; ?></font><br />
					</form>
					<br />
					<a href="http://www.zubrag.com/scripts/password-protect.php" title="Download Password Protector">Powered by Password Protect</a>
				</div>
			</body>
		</html>
		<?php
	// stop at this point
		die();
	}
}
// user provided password
if (isset($_POST['access_password'])) {
	$login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
	$pass = $_POST['access_password'];
	if (!USE_USERNAME && !in_array($pass, $LOGIN_INFORMATION) || (USE_USERNAME && ( !array_key_exists($login, $LOGIN_INFORMATION) || $LOGIN_INFORMATION[$login] != $pass ) ) ) {
		showLoginPasswordProtect("Incorrect password.");
	}
	else {
		// set cookie if password was validated
		setcookie("verify", md5($login.'%'.$pass), $timeout, '/');
		// Some programs (like Form1 Bilder) check $_POST array to see if parameters passed
		// So need to clear password protector variables
		unset($_POST['access_login']);
		unset($_POST['access_password']);
		unset($_POST['Submit']);
	}
}
else {
// check if password cookie is set
	if (!isset($_COOKIE['verify'])) {
		showLoginPasswordProtect("");
	}
	// check if cookie is good
	$found = false;
	foreach($LOGIN_INFORMATION as $key=>$val) {
		$lp = (USE_USERNAME ? $key : '') .'%'.$val;
		if ($_COOKIE['verify'] == md5($lp)) {
			$found = true;
			// prolong timeout
			if (TIMEOUT_CHECK_ACTIVITY) {
				setcookie("verify", md5($lp), $timeout, '/');
			}
			break;
		}
	}
	if (!$found) {
		showLoginPasswordProtect("");
	}
}
?>
