<?php
include('pageContent.php');

// TODO Better division of permissions
// Can't use dbReader because things need to be inserted into sessions
$dbm = new DBManager('arphen', 'logindb');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>Sign In - The Valar Project</title>
  <?php head(); ?>
  <style>
.noTop {
 border-top: none !important;
}

.noBottom {
 border-bottom: none !important;
}
  </style>
  <script type="text/javascript" src="/resources/scripts/sha512.js"></script>
  <script type="text/javascript">
var challenge = "<?php echo isset($_COOKIE['challenge']) ? $_COOKIE['challenge'] : $dbm->getChallenge($_SERVER['REMOTE_ADDR']); ?>";
if(challenge == "null") challenge = null;

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function submitSignIn() {
	var realm = "TVP";
	
	var username = $("#username").val();
	verifyUsername();
	
	var password = $("#password").val();
	verifyPassword();

	if($("#signin_form input").hasClass("invalid")) return;

	$("#signin_feedback").html("Logging in, please be patient as this can take several seconds.").show();

	if(challenge == null) {
		$("#signin_feedback").html("Sorry, sign in has failed. Please try again.");
		return;
	}

	var message = username + realm + password;

	// Apply hmac 4096 times, outputting hex in the iteration
	var shaObj = new jsSHA("SHA-512", "TEXT");
	shaObj.update(message);
	var subhash = "";
	subhash = shaObj.getHash("BYTES");
	for(var i = 1; i < 4095; i++) {
		shaObj = new jsSHA("SHA-512", "BYTES");
		shaObj.setHMACKey(message, "TEXT");
		shaObj.update(subhash);
		subhash = shaObj.getHMAC("BYTES");
	}
	shaObj = new jsSHA("SHA-512", "BYTES");
	shaObj.setHMACKey(message, "TEXT");
	shaObj.update(subhash);
	subhash = shaObj.getHMAC("HEX");

	console.log("subhash = " + subhash);

	shaObj = new jsSHA("SHA-512", "TEXT");
	shaObj.setHMACKey(subhash, "TEXT");
	shaObj.update(challenge);
	var hash = shaObj.getHMAC("HEX");

	$.post("/resources/serverside_scripts/login_manager.php", { op: "signin", username: username, challenge: challenge, response: hash }, function(data) {
		if(data.status == "success" && data.result != null) {
			createCookie("token", data.result, 7);
			createCookie("username", username, 7);
			// TODO redirect to profile
			$("#signin_feedback").html("You are now signed in as " + username);
		}
		else {
			$("#signin_feedback").html("Sorry, sign in has failed. Please try again.");
		}
	}, "json").fail(function() {
		$("#signin_feedback").html("Sorry, sign in has failed. Please try again.");
	}).always(function() {
		eraseCookie("challenge");
		$.post("/resources/serverside_scripts/login_manager.php", { op: "get_challenge" }, function(data) {
			if(data.status == "success" && data.result != null) {
				createCookie("challenge", data.result, 1);
			}
		}, "json");
	});
}

function verifyUsername() {
	var username = $("#username").val();
	var username_pattern = new RegExp("^[A-Za-z0-9]*$");
	if(username == "") {
		$("#username_feedback").html("Username cannot be empty.");
		$("#username").addClass("invalid");
	}
	else if(username.length < 4) {
		$("#username_feedback").html("Usernames must be at least 4 characters long.");
		$("#username").addClass("invalid");
	}
	else if(username.length > 16) {
		$("#username_feedback").html("Usernames must be no longer than 16 characters long.");
		$("#username").addClass("invalid");
	}
	else if(!username_pattern.test(username)) {
		$("#username_feedback").html("Usernames must be made up of only letters and numbers.");
		$("#username").addClass("invalid");
	}
	else {
		$("#username_feedback").html("");
		$("#username").removeClass("invalid");
	}
}

function verifyPassword() {
	var password = $("#password").val();
	var password_feedback = $("#password_feedback");
	// TODO double check to make sure escapes are working properly
	var password_pattern = new RegExp("^[A-Za-z0-9!\"#$%&'()\*+,-\./:;<=>\?@[\\\]\^_`{|}~]*$");
	if(password.length < 6) {
		$("#password_feedback").html("Passwords must be at least 6 characters long.");
		$("#password").addClass("invalid");
	}
	else if(password.length > 72) {
		$("#password_feedback").html("Passwords must be no longer than 72 characters long.");
		$("#password").addClass("invalid");
	}
	else if(!password_pattern.test(password)) {
		// TODO Come up with better styling to separate the special characters.
		$("#password_feedback").html("Passwords must made up of letters, numbers, and these special characters: !\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~.");
		$("#password").addClass("invalid");
	}
	else {
		$("#password").removeClass("invalid");
	}
}

$(document).ready(function() {
	if(readCookie("challenge") == null) {
		createCookie("challenge", challenge, 1);
	}
	/*if(auth.signedIn) {
		$("#signin_form").hide();
		// TODO add logout option
		$("#pageContents").append("You are already signed in.");
		return;
	}*/
	$("#signin_form").submit(function(e) { submitSignIn(); e.preventDefault(); });
});
  </script>
 </head>
 <body>
  <?php bodyStart(); ?>
  <form id="signin_form">
   <h1>Sign In</h1>
   <p id="signin_feedback"></p>
   <table>
    <tbody>
     <tr>
      <td><label for="username">Username:&nbsp;</label></td>
      <td><div class="text-wrapper noBottom"><input type="text" name="username" id="username" /></div></td>
      <td id="username_feedback"></td>
     </tr>
    <tr>
      <td><label for="password">Password:&nbsp;</label></td>
      <td><div class="text-wrapper noTop noBottom"><input type="password" name="password" id="password" /></div></td>
      <td id="password_feedback"></td>
     </tr>
    </tbody>
   </table>
   <p><a class="hiddenLink">Forgot password</a></p>
   <button id="signin_submit" type="submit">Sign In</button>
  </form>
  <a href="register.php">Don't have an account? Register here!</a>
  <?php bodyEnd(); ?>
 </body>
</html>