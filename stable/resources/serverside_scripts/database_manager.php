<?php

class DBManager {
	private $db;
	private $realm = 'TVP';

	function __construct($accessLevel, $databaseLocation) {
		if(gettype($accessLevel) != 'string' || gettype($databaseLocation) != 'string') {
			// TODO throw error
		}
		$accessLevel = strtolower($accessLevel);
		$databaseLocation = strtolower($databaseLocation);
		switch($accessLevel) {
			case 'ohtar': // Read permissions only
				$password = 'TQIQJ4Kfwu34NInH4WA5tEqEdwxsz8ETh+Hf3wze2jI=';
				break;
			case 'arphen': // Read and write permissions
				$password = '6LRX/gqjqvDiGd2MWezSV1l16tw8p//miQ8YwtAZu3I=';
				break;
			case 'maia': // Full permissions (unimplemented until necessary)
				// Unimplemented until necessary
			default:
				// TODO throw error
		}
		if(!($databaseLocation == 'logindb' || $databaseLocation == 'forum')) {
			// TODO throw error
		}
		
		try {
			$this->db = new PDO('mysql:host=localhost;dbname=tvpx10ho_'.$databaseLocation, 'tvpx10ho_'.$accessLevel, trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'obfuscate', base64_decode($password), MCRYPT_MODE_ECB)), array(PDO::ATTR_PERSISTENT => true));
		} catch (PDOException $e) {
			//echo $e->getMessage();
			//echo mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'obfuscate', base64_decode($password), MCRYPT_MODE_ECB);
			// TODO throw error
		}
	}
	
	function __destruct() {
		$this->db = null;
	}
	
	// Equal function to prevent timing attacks
	private function slowEqual($a, $b) {
		$alength = strlen($a);
		$blength = strlen($b);
		$diff = $alength ^ $blength;
		for($i = 0; $i < $alength && $i < $blength; $i++)
			$diff |= $a[$i] ^ $b[$i];
		return $diff == 0;
	}
	
	function getUserId($username) {
		if(strlen($username) > 16 || strlen($username) < 4 || !ctype_alnum($username)) {
			return -1;
		}
		$stmt = $this->db->prepare('SELECT `userid` FROM `loginData` WHERE `username` = :username');
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return -1;
		}
		return $row['userid'];
	}
	
	function getUsername($userid) {
		$stmt = $this->db->prepare('SELECT `username` FROM `loginData` WHERE `userid` = :userid');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return NULL;
		}
		return $row['username'];
	}
	
	// Checks a challenge-response pair and returns a token if it is a match
	function verifyPassword($challenge, $response, $userid) {
		// Get login data
		$stmt = $this->db->prepare('SELECT `hmacHash` FROM `loginData` WHERE `userid` = :userid');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row || !isset($row['hmacHash'])) {
			return null;
		}

		// Generate response on the serverside
		$serverResponse = hash_hmac('sha512', $challenge, $row['hmacHash']);

		// Check password
		$isCorrect = $this->slowEqual($response, $serverResponse);

		if(!$isCorrect) {
			return null;
		}
		
		// Generate a 32 character hex string
		$token = bin2hex(openssl_random_pseudo_bytes(16));
		
		$stmt = $this->db->prepare('INSERT INTO `sessions` (`userid`, `token`) VALUES (:userid, :token)');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		if(!$stmt->execute()) {
			// Technically verification was successful, but the user needs to know that sign in failed
			return null;
		}
		
		return $token;
	}
	
	// This function is provided in case the client needs to synchronize the cnonce
	function getCnonce($userid) {
		$stmt = $this->db->prepare('SELECT `cnonce` FROM `loginData` WHERE `userid` = :userid');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return null;
		}
		
		return $row['cnonce'];
	}
	
	/* Symmetric impelmentation
	function verifyPassword($userid, $password) {
		$stmt = $this->db->prepare('SELECT passwordHash FROM loginData WHERE userid = :userid');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return false;
		}
		return password_verify($password, $row['passwordHash']);
	}*/
	
	function getUserEmail($userid) {
		$stmt = $this->db->prepare('SELECT `email` FROM `loginData` WHERE `userid` = :userid AND `emailVerified` = 1');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return null;
		}
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'i7bV4xDRxPXFFpkk4KIyzh9OERCzm1su', base64_decode($row['email']), MCRYPT_MODE_ECB));
	}
	
	function addUser($username, $password, $emailAddress) {
		$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);
		
		$message = $username.$this->realm.$password;
		$hmacHash = hash('sha512', $message);
		for($i = 1; $i < 4096; $i++) {
			$hmacHash = hash_hmac('sha512', hex2bin($hmacHash), $message);
		}
		
		$stmt = $this->db->prepare('INSERT INTO `loginData` (`username`, `passwordHash`, `hmacHash`) VALUES (:username, :passwordHash, :hmacHash)');
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->bindParam(':passwordHash', $hash, PDO::PARAM_STR);
		$stmt->bindParam(':hmacHash', $hmacHash, PDO::PARAM_STR);
		$stmt->execute();
		$uid = $this->getUserId($username);
		$emailRes = $this->setUserEmail($uid, $emailAddress);
		return !$emailRes ? -1 : $uid;
	}
	
	function setUserEmail($userid, $address) {
		if(strlen($address) > 254 || !filter_var($address, FILTER_VALIDATE_EMAIL) || $userid < 0) {
			return false;
		}
		// TODO Modify this so that verification emails can be resent
		if($this->getUserEmail($userid) === $address) {
			return true;
		}
		$encrypted_address = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, 'i7bV4xDRxPXFFpkk4KIyzh9OERCzm1su', $address, MCRYPT_MODE_ECB));
		if(!$encrypted_address) {
			return false;
		}
		$stmt = $this->db->prepare('UPDATE `loginData` SET `email` = :email, `emailVerified` = 0 WHERE `userid` = :userid');
		$stmt->bindParam(':email', $encrypted_address, PDO::PARAM_STR);
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		if(!$stmt->execute()) {
			return false;
		}
		$username = $this->getUsername($userid);
		if(is_null($username)) {
			return false;
		}
		$token = bin2hex(openssl_random_pseudo_bytes(16));
		$stmt = $this->db->prepare('INSERT INTO `emailConfirmations` (`userid`, `token`) VALUES (:userid, :token) ON DUPLICATE KEY UPDATE `token` = :token');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		if(!$stmt->execute()) {
			return false;
		}
		// TODO make message prettier
		// TODO implement email schema (http://www.bruceclay.com/blog/6-things-you-need-to-know-about-email-schema/)
		// Broken encrypted email code
		/*$start = uniqid();
		if(!file_put_contents(getenv('APP_ROOT_PATH').'tmp/'.$start.'.txt', '<html><head><title>Please activate your account</title></head><body>Mae govannen, '.$username.'!<br />Please click the link below to confirm your email address:<br /><a href="https://tvp.elementfx.com/confirm_email.php?token='.$token.'">http://tvp.elementfx.com/confirm_email.php?token='.$token.'</a></body></html>')) {
			return false;
		}
		
		$out = uniqid();
		// TODO move certs to a better place
		if (openssl_pkcs7_sign(getenv('APP_ROOT_PATH').'tmp/'.$start.'.txt', getenv('APP_ROOT_PATH').'tmp/'.$out.'.txt', getenv('APP_ROOT_PATH').'secret_stuff/webmaster@tvp.elementfx.com.crt', array(.getenv('APP_ROOT_PATH').'secret_stuff/webmaster@tvp.elementfx.com.pem', 'o8yLbhQydS3LkJj8Aak5jL8lIaxan8bC'),
			array('To' => $address,
				'From' => 'The Valar Project <noreply@tvp.elementfx.com>',
				'Subject' => 'Please activate your account',
				'MIME-Version' => '1.0',
				'Content-type' => 'text/html; charset=iso-8859-1'))) {
			exec(ini_get('sendmail_path').' < '.getenv('APP_ROOT_PATH').'tmp/'.$out.'.txt');
			return true;
		}
		return false;*/
		return mail($address, 'Please activate your account', '<html><head><title>Please activate your account</title></head><body>Mae govannen, '.$username.'!<br />Please click the link below to confirm your email address:<br /><a href="http://tvp.elementfx.com/confirm_email.php?token='.$token.'">http://tvp.elementfx.com/confirm_email.php?token='.$token.'</a></body></html>', 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n".'From: The Valar Project <noreply@tvp.elementfx.com>');
	}
	
	function confirmUserEmail($token) {
		if(strlen($token) != 32 || !ctype_alnum($token)) {
			return null;
		}
		
		$stmt = $this->db->prepare('SELECT `userid` FROM `emailConfirmations` WHERE `token` = :token');
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return null;
		}
		$userid = $row['userid'];
		
		$stmt = $this->db->prepare('UPDATE `loginData` SET `emailVerified` = 1 WHERE `userid` = :userid');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		if(!$stmt->execute()) {
			return null;
		}
		
		$stmt = $this->db->prepare('DELETE FROM `emailConfirmations` WHERE `token` = :token');
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		if(!$stmt->execute()) {
			// Non-critical error
		}
		
		return $this->getUserEmail($userid);
	}
	
	// Gets the number of recent attempts an ip has made to login
	function getAttempts($ipAddress) {
		$stmt = $this->db->prepare('SELECT SUM(`failedAttempts`) AS attempts FROM `challenges` WHERE `ipAddress` = :ip AND `creationTime`+60*60 > NOW()');
		$stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row || !isset($row['attempts']) || !is_numeric($row['attempts'])) {
			return 0;
		}
		
		return (int) $row['attempts'];
	}
	
	// Get challenge for logging in
	function getChallenge($ipAddress) {
		if(!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
			return 'null';
		}
		
		// Get all non-expired challenges for this ip address
		$stmt = $this->db->prepare('SELECT `userid`, `challenge` FROM `challenges` WHERE `ipAddress` = :ip AND `creationTime`+60*60 > NOW()');
		$stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		// If there are no such challenges, create a new one 
		if(!$row || !isset($row['challenge'])) {
			// Generate challenge
			$challenge = hash('sha256', uniqid());

			// Add entry to database
			$stmt = $this->db->prepare('INSERT INTO `challenges` (`challenge`, `ipAddress`) VALUES (:challenge, :ip)');
			$stmt->bindParam(':challenge', $challenge, PDO::PARAM_STR);
			$stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
			$result = $stmt->execute();

			// Return challenge 
			return $challenge;
		}
		else {
			return (string) $row['challenge'];
		}
	}
	
	// No longer used
	function getAuth($ipAddress) {
		if(!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
			return null;
		}
		
		$stmt = $this->db->prepare('SELECT `userid`, `opaque`, `nonce`, `cnonce` FROM `challenges` WHERE `ipAddress` = :ip AND `creationTime`+60*60 > NOW()');
		$stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row || $row['cnonce'] != 0) {
			// Generate opaque
			$opaque = hash('sha256', uniqid());
			// Generate nonce
			$nonce = hash('sha256', uniqid());
			
			// Add entry to database
			$stmt = $this->db->prepare('INSERT INTO `sessions` (`opaque`, `nonce`, `ipAddress`) VALUES (:opaque, :nonce, :ip)');
			$stmt->bindParam(':opaque', $opaque, PDO::PARAM_STR);
			$stmt->bindParam(':nonce', $nonce, PDO::PARAM_STR);
			$stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
			$result = $stmt->execute();
			
			$res = ['opaque' => $opaque, 'nonce' => $nonce, 'cnonce' => 0, 'signedIn' => FALSE];
		}
		else {
			$res = ['opaque' => $row['opaque'], 'nonce' => $row['nonce'], 'cnonce' => $row['cnonce'], 'signedIn' => (!is_null($row['userid']) && !$row['userid'] < 0)];
		}
		
		return $res;
	}
	
	function createNewRecoveryToken($userid, $address) {
		if(!$this->slowEqual($address, $this->getUserEmail($userid))) {
			return true;
		}
		
		// Get username
		$username = $this->getUsername($userid);
		if(is_null($username)) {
			return false;
		}
		
		// Generate token
		$token = md5(uniqid());
		
		// Add entry to database
		$stmt = $this->db->prepare('INSERT INTO `recovery` (`userid`, `token`) VALUES (:userid, :token)');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		if(!$stmt->execute()) {
			return false;
		}
		return mail($address, 'Account Recovery', '<html><head><title>Account Recovery</title></head><body>A request was made for the password of your account ('.$username.'). Please click the link below to reset your password:<br /><a href="http://tvp.elementfx.com/recovery_email.php?token='.$token.'">http://tvp.elementfx.com/recovery_email.php?token='.$token.'</a><br />Please be sure to do this soon, as the link will expire in 24 hours.</body></html>', 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n".'From: The Valar Project <noreply@tvp.elementfx.com>');
	}
	
	function checkRecoveryFunction($token) {
		$stmt = $this->db->prepare('SELECT `id` FROM `recovery` WHERE `token` = :token AND `requestTime`+24*60*60 < NOW()');
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return false;
		}
		return true;
	}
	
	function checkToken($userid, $token) {
		// Make sure userid is properly formatted and valid
		if(!is_numeric($userid) || $userid < 0) {
			return false;
		}
		// Make sure token is properly formatted
		if(strlen($token) != 32 || !ctype_xdigit($token)) {
			return false;
		}

		$stmt = $this->db->prepare('SELECT `lastAccessTime` FROM `sessions` WHERE `userid` = :userid AND `token` = :token AND DATE_ADD(`lastAccessTime`, INTERVAL 1 DAY) > NOW()');
		$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
		$stmt->bindParam(':token', $token, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row) {
			return false;
		}

		return true;
	}
	
	function logout() {
		
	}
}

?>