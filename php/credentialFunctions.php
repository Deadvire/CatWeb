<?php
/**
 * Wrapper for class mysqli.
 * @param string $query SQL query wihout terminating semicolon or \g having its Data Manipulation Language (DML) parmeters replaced with `?` and put into ...$vars
 * @param string $types A string containing a single character for each arguments passed with ...$vars depending on the type. 's' for string, 'd' for float, 'i' for int, 'b' for BLOB
 * @param string|int|float|BLOB ...$vars
 * @return mysqli_result|string|int For errors it returns a string, for successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query will return a mysqli_result object. For other successful queries mysqli_query will return the number of affected rows.
 * @see https://php.net/manual/en/class.mysqli.php Used for the actual database comminucation.
 * @throws InvalidArgumentException If $types does not match the constrants.
 */
function DatbQuery(string $query, string $types = '', ...$vars) {
	// Ensure types doesn't contain obvious errors.
	if(preg_match('/^[idsb]*$/', $types) != 1) throw new InvalidArgumentException('string $types contains invallid characters.');
	if(strlen($types) != count($vars)) throw new InvalidArgumentException('string $types should have the same length as the number of arguments passed with ...$vars'."\n". json_encode(['$types'=>$types,'...$vars'=>$vars, 'strlen($types)'=>strlen($types), 'count($vars)'=>count($vars)]));
	// Create connection based on hardcoded values.
	$m_conn = new mysqli('127.0.0.1', 'root', '', 'catweb', 3306);
	// Check if the connection succeeded.
	if($m_conn->connect_error) return $m_conn->connect_error;
	// Get the statement object and check for errors.
	$m_prep = $m_conn->prepare($query);
	if($m_prep == false) {
		$error = $m_conn->error_list;
		$m_conn->close();
		return var_export($error, true);
	}
	// Attempt to bind parameters to their relative placeholders.
	if($types != '') {
		if(!($m_prep->bind_param($types, ...$vars))) {
			$error = $m_prep->error_list;
			$m_prep->close(); $m_conn->close();
			return var_export($error, true);
		}
	}
	// Execute the querry.
	if(!$m_prep->execute()) {
		$error = $m_prep->error_list;
		$m_prep->close(); $m_conn->close();
		return var_export($error, true);
	}
	// Get the results.
	$m_result = $m_prep->get_result();
	if($m_result == false)
		$m_result = ($m_prep->errno == 0)? $m_prep->affected_rows : var_export($m_prep->error_list, true);
	// close connection
	$m_prep->close();
	$m_conn->close();
	return $m_result;
}
/**
 * Wrapper for class mysqli.
 * @param string $query SQL query wihout terminating semicolon or \g having its Data Manipulation Language (DML) parmeters replaced with `?` and put into ...$vars
 * @param string $types A string containing a single character for each arguments passed with ...$vars depending on the type. 's' for string, 'd' for float, 'i' for int, 'b' for BLOB
 * @param string|int|float|BLOB ...$vars
 * @return mysqli_result|string|int For errors it returns a string, for successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query will return a mysqli_result object. For other successful queries mysqli_query will return the number of affected rows.
 * @see https://php.net/manual/en/class.mysqli.php Used for the actual database comminucation.
 * @throws InvalidArgumentException If $types does not match the constrants.
 */
function DatbQuery_3(mysqli &$conn = null, string $query, string $types = '', ...$vars) {
	// Ensure types doesn't contain obvious errors.
	if(preg_match('/^[idsb]*$/', $types) != 1) throw new InvalidArgumentException('string $types contains invallid characters.');
	if(strlen($types) != count($vars)) throw new InvalidArgumentException('string $types should have the same length as the number of arguments passed with ...$vars'."\n". json_encode(['$types'=>$types,'...$vars'=>$vars, 'strlen($types)'=>strlen($types), 'count($vars)'=>count($vars)]));
	// Ensure connection
	$m_close = false;
	if($conn == null) {
		$conn = new mysqli('127.0.0.1', 'root', '', 'catweb', 3306);
		$m_close = true;
	}
	// Check if the connection succeeded.
	if($conn->connect_error) return $conn->connect_error;
	// Get the statement object and check for errors.
	$m_prep = $conn->prepare($query);
	if($m_prep == false) {
		$error = $conn->error_list;
		if($m_close) $conn->close();
		return var_export($error, true);
	}
	// Attempt to bind parameters to their relative placeholders.
	if($types != '') {
		if(!($m_prep->bind_param($types, ...$vars))) {
			$error = $m_prep->error_list;
			$m_prep->close(); if($m_close) $conn->close();
			return var_export($error, true);
		}
	}
	// Execute the querry.
	if(!$m_prep->execute()) {
		$error = $m_prep->error_list;
		$m_prep->close(); if($m_close) $conn->close();
		return var_export($error, true);
	}
	// Get the results.
	$m_result = $m_prep->get_result();
	if($m_result == false)
		$m_result = ($m_prep->errno == 0)?
			$m_prep->affected_rows :
			var_export($m_prep->error_list, true);
	// close connection
	$m_prep->close();
	if($m_close) $conn->close();
	return $m_result;
}
/** Check the user credentials en permissions.
 * @param int|string $username If using a token use an `int` else it should be a `string`.
 * @param string $pwd Password or token to be validated.
 * @return int|string Int representing permission level or a String containing an error message.
*/
function getPerms($username, string $pwd) {
	/** @param string $m_iv A non-NULL Initialization Vector.*/
	$m_iv = "0000000000000069";
	// Autentication with password
	if(is_string($username)) {
		// Get `pwd` to verify the given password with. `ID` so we know what user we have and `perms` for their permission level.
		$m_result = DatbQuery('SELECT `ID`, `pwd`, `perms` FROM `site_users` WHERE `email`=?', 's', $username);
		if(!is_object($m_result))
			return 'Database request failed at SELECT `pwd`';
		$m_result = $m_result->fetch_assoc();
		if(!is_array($m_result) || !password_verify(($pwd . $username), $m_result['pwd']))
			return 'Incorrecte gebruikersnaam, wachtwoord combination.';
		$permLevel = $m_result['perms'];
		// Create a login token as we should not store the password in the session.
		$m_ID = intval($m_result['ID']);
		$m_token = random_int(0, 16777215);
		$m_result = DatbQuery("UPDATE `site_users` SET `token`=?, `tokenTime` = NOW() WHERE `ID`=?", 'ii', $m_token, $m_ID);
		if($m_result !== 1)
			return 'Database request failed at UPDATE `users` SET `token`';
		// Store `ID` of the user and their token.
		$_SESSION['ID'] = $m_ID;
		$_SESSION['loginToken'] = $m_token;
		// Using a encrypted username as the Key Encryption Key. The Data Encryption Key is never put in $_SESSION
		$_SESSION['pwdKey'] = openssl_encrypt($username, 'aes-256-cbc-hmac-sha256', $pwd, 0, $m_iv);
		// Autentication with token
	} else {
		// Get the token.
		$m_result = DatbQuery('SELECT `token`, TIMESTAMPDIFF(MINUTE, `tokenTime`, NOW()) as `timeDif`, `perms` FROM `site_users` WHERE `ID`=?', 'i', $username);
		if(!is_object($m_result))
			return 'Database request failed at SELECT `token`';
		$m_result = $m_result->fetch_assoc();
		$permLevel = $m_result['perms'];
		if(!is_array($m_result)) {
			unset($_SESSION['loginToken']);
			return 'Invallid/expired loginToken.';
		}
		// Ensure the token is the same as the one given and ensure it has not expired.
		if($m_result['token'] != $pwd || $m_result['timeDif'] > 15) {
			DatbQuery('UPDATE IGNORE `users` SET `token`=NULL, `tokenTime`=NULL WHERE `ID`=?', 'i', $username);
			unset($_SESSION['loginToken']);
			return 'Invallid/expired loginToken.';
		}
	}
	return $permLevel;
}
/**
 * @see https://security.stackexchange.com/a/182008 How we handle autentication and encryption.
 * @return array<int,string>|null [encrypted_userKey, userKey]
 */
function createPass(string $username, string $pwd, ?string $pwd_old = null, ?string $encryptedKey_old = null): ?array {
	$m_iv = "0000000000000069";
	// Derive old password key from old password and new password key from new password.
	/** @var ?string $m_pwdkey_old Old Encryption Key*/
	$m_pwdKey_old = openssl_encrypt($username, 'aes-256-cbc-hmac-sha256', $pwd_old, 0, $m_iv);
	/** @var ?string $m_pwdkey_new New Key Encryption Key*/
	$m_pwdkey_new = openssl_encrypt($username, 'aes-256-cbc-hmac-sha256', $pwd, 0, $m_iv);
	// Decrypt user-key using old key
	/** @var ?string $m_userKey Data Encryption Key*/
	$m_userKey = (isset($a_oldEncryptedkey))? openssl_decrypt($encryptedKey_old, 'aes-256-cbc-hmac-sha256', $m_pwdKey_old, 0, $m_iv) : random_bytes(60);
	if($m_userKey === null || is_bool($m_pwdkey_new)) return null;
	// Encrypt user-key with new key
	$m_encrypted_userKey = openssl_encrypt($m_userKey, 'aes-256-cbc-hmac-sha256', $m_pwdkey_new, 0, $m_iv);
	if($m_encrypted_userKey === false) return null;
	return [$m_encrypted_userKey, $m_userKey];
}
/** How to get the encrypted data
 * @see https://security.stackexchange.com/a/182008 How we handle autentication and encryption.
 * @return array<string,string|false|null>|string Array with the decoded data from the database with false on failure or a string with error message.
*/
function getInfo() {
	$id = $_SESSION['ID'];
	$pwdKey = $_SESSION['pwdKey'];
	$m_iv = "0000000000000069";
	$m_result = DatbQuery('SELECT `encryptedkey`, `username`, `street`, `postcode`, `city`, `country` FROM `site_users` WHERE `ID`=?', 'i', $id);
	if(!is_object($m_result))
		return 'Database request mislukt at SELECT `encryptedkey`';
	$m_result = $m_result->fetch_assoc();
	$m_userKey = openssl_decrypt($m_result['encryptedkey'], 'aes-256-cbc-hmac-sha256', $pwdKey, 0, $m_iv);
	if(!is_string($m_userKey)) return 'Decryption failed';
	// We put the data in a relative array decrypting it first if it is not null.
	/** @var array<string,(string|false|null)> $decryptedData */
	$decryptedData = [
		'username'	=> ($m_result['username'])?	openssl_decrypt($m_result['nameFirst'],	'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv) : null,
		'street'		=> ($m_result['street'])?		openssl_decrypt($m_result['street'],		'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv) : null,
		'postcode'	=> ($m_result['postcode'])?	openssl_decrypt($m_result['postcode'],		'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv) : null,
		'city'		=> ($m_result['city'])?			openssl_decrypt($m_result['city'],			'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv) : null,
		'country'	=> ($m_result['country'])?		openssl_decrypt($m_result['country'],		'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv) : null
	];
	return $decryptedData;
}
/** Update/change user info
 * @see https://security.stackexchange.com/a/182008 How we handle autentication and encryption.
 */
function setInfo(int $id, string $pwdKey, ?string $nameFirst = null, ?string $nameLast = null, ?string $street = null, ?string $postcode = null, ?string $city = null, ?string $country = null): ?string {
	$m_iv = "0000000000000069";
	$m_result = DatbQuery('SELECT `encryptedkey` FROM `site_users` WHERE `ID`=?', 'i', $id);
	if(!is_object($m_result))
		return 'Database request mislukt at SELECT `email`';
	$m_result = $m_result->fetch_assoc();
	$m_userKey = openssl_decrypt($m_result['encryptedkey'], 'aes-256-cbc-hmac-sha256', $pwdKey, 0, $m_iv);
	// We basically go over all given arguments and change those that are set.
	if(isset($nameFirst))
		DatbQuery('UPDATE `site_users` SET `nameFirst`=? WHERE `ID`=?', 'si', openssl_encrypt($nameFirst, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($nameLast))
		DatbQuery('UPDATE `site_users` SET `nameLast`=? WHERE `ID`=?', 'si', openssl_encrypt($nameLast, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($street))
		DatbQuery('UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($street, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($postcode) && preg_match('/^\d{4}[A-Z]{2}$/', $postcode))
		DatbQuery('UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($postcode, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($city))
		DatbQuery('UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($city, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($country) && preg_match('/^[A-Z]{2}$/', $country))
		DatbQuery('UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($country, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	return null;
}
/** Update/change user info
 * @see https://security.stackexchange.com/a/182008 How we handle autentication and encryption.
 */
function setInfo2(int $id, string $pwdKey, ?string $nameFirst = null, ?string $nameLast = null, ?string $street = null, ?string $postcode = null, ?string $city = null, ?string $country = null): ?string {
	$m_iv = "0000000000000069";
	$m_conn = new mysqli('127.0.0.1', 'root', '', 'catweb', 3306);
	// Check if the connection succeeded.
	if($m_conn->connect_error) return $m_conn->connect_error;
	$m_result = DatbQuery_3($m_conn, 'SELECT `encryptedkey` FROM `site_users` WHERE `ID`=?', 'i', $id);
	if(!is_object($m_result))
		return 'Database request mislukt at SELECT `email`';
	$m_result = $m_result->fetch_assoc();
	$m_userKey = openssl_decrypt($m_result['encryptedkey'], 'aes-256-cbc-hmac-sha256', $pwdKey, 0, $m_iv);
	if($m_userKey == false) return 'Decryption failed';
	$m_results = [];
	// We basically go over all given arguments and change those that are set.
	if(isset($nameFirst))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `nameFirst`=? WHERE `ID`=?', 'si', openssl_encrypt($nameFirst, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($nameLast))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `nameLast`=? WHERE `ID`=?', 'si', openssl_encrypt($nameLast, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($street))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($street, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($postcode) && preg_match('/^\d{4}[A-Z]{2}$/', $postcode))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($postcode, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($city))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($city, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	if(isset($country) && preg_match('/^[A-Z]{2}$/', $country))
		$m_results[] = DatbQuery_3($m_conn, 'UPDATE `site_users` SET `street`=? WHERE `ID`=?', 'si', openssl_encrypt($country, 'aes-256-cbc-hmac-sha256', $m_userKey, 0, $m_iv), $id);
	$m_conn->close();
	return null;
}
/**
 * Create a new account with encrypted personal details.
 * @return null|string null on success. Error message on failure.
*/
function createAccount(string $email, string $pwd, ?string $username, ?string $street, ?string $postcode, ?string $city, ?string $country): ?string {
	// Verify contents
	if(!preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $email)) return 'Incorrect e-mail format';
	if(!preg_match('/^\d{4}[A-Z]{2}$/', $postcode)) return 'Incorrect postcode format';
	if(!preg_match('/^[A-Z]{2}$/', $country)) return 'Land code moet in ISO 3166-1 alpha-2 format';
	$m_iv = "0000000000000069";
	$m_pass = createPass($email, $pwd);
	if($m_pass === null) return 'Encryptie mislukt';
	$m_vars = [
		$email,
		password_hash($pwd . $email, '2y'),	// Hash to verify if the password is correct.
		$m_pass[0],	// encrypted_userKey
		// Data encrypted with userKey
		($username)?	openssl_encrypt($username,	'aes-256-cbc-hmac-sha256', $m_pass[1], 0, $m_iv) : null,
		($street)?		openssl_encrypt($street,	'aes-256-cbc-hmac-sha256', $m_pass[1], 0, $m_iv) : null,
		($postcode)?	openssl_encrypt($postcode,	'aes-256-cbc-hmac-sha256', $m_pass[1], 0, $m_iv) : null,
		($city)?			openssl_encrypt($city,		'aes-256-cbc-hmac-sha256', $m_pass[1], 0, $m_iv) : null,
		($country)?		openssl_encrypt($country,	'aes-256-cbc-hmac-sha256', $m_pass[1], 0, $m_iv) : null
	];
	if(array_search(false, $m_vars) !== false) return 'Encryptie mislukt';
	$m_return = DatbQuery('INSERT INTO `site_users` (`email`, `pwd`, `encryptedkey`, `username`, `street`, `postcode`, `city`, `country`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 'ssssssss', ...$m_vars);
	if(is_string($m_return)) return $m_return;
	return null;
}