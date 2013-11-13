<?php header('Content-type: application/json');
/**
 * Callback for Opauth
 * 
 * This file (callback.php) provides an example on how to properly receive auth response of Opauth.
 * 
 * Basic steps:
 * 1. Fetch auth response based on callback transport parameter in config.
 * 2. Validate auth response
 * 3. Once auth response is validated, your PHP app should then work on the auth response 
 *    (eg. registers or logs user in to your site, save auth data onto database, etc.)
 * 
 */


/**
 * Define paths
 */
define('CONF_FILE', dirname(__FILE__).'/'.'opauth.conf.php');
define('OPAUTH_LIB_DIR', dirname(__FILE__).'/lib/Opauth/');

/**
* Load config
*/
if (!file_exists(CONF_FILE)) {
	trigger_error('Config file missing at '.CONF_FILE, E_USER_ERROR);
	exit();
}
require CONF_FILE;

/**
 * Instantiate Opauth with the loaded config but not run automatically
 */
require OPAUTH_LIB_DIR.'Opauth.php';
$Opauth = new Opauth( $config, false );

	
/**
* Fetch auth response, based on transport configuration for callback
*/
$response = null;

switch($Opauth->env['callback_transport']) {
	case 'session':
		session_start();
		$response = $_SESSION['opauth'];
		unset($_SESSION['opauth']);
		break;
	case 'post':
		$response = unserialize(base64_decode( $_POST['opauth'] ));
		break;
	case 'get':
		$response = unserialize(base64_decode( $_GET['opauth'] ));
		break;
	default:
		//echo '<strong style="color: red;">Error: </strong>Unsupported callback_transport.'."<br>\n";
		break;
}

/**
 * Check if it's an error callback
 */
if (array_key_exists('error', $response)) {
	//echo '<strong style="color: red;">Authentication error: </strong> Opauth returns error auth response.'."<br>\n";
}

/**
 * Auth response validation
 * 
 * To validate that the auth response received is unaltered, especially auth response that 
 * is sent through GET or POST.
 */
else{
	if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
		//echo '<strong style="color: red;">Invalid auth response: </strong>Missing key auth response components.'."<br>\n";
	} elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
		//echo '<strong style="color: red;">Invalid auth response: </strong>'.$reason.".<br>\n";
	} else {
		//echo '<strong style="color: green;">OK: </strong>Auth response is validated.'."<br>\n";

		/**
		 * It's all good. Go ahead with your application-specific authentication logic
		 */
	}
}

if(isset($response["auth"])){
	//echo json_encode($response);
	require_once "../connection.php";

	global $pdo;

	$p = "SELECT COUNT(*) AS count, ID, name FROM users WHERE name='".$response["auth"]["info"]["nickname"]."'";
	$r = $pdo->prepare($p);
	$rr = $r->execute();
	$rr = $r->fetchAll();
	if($rr[0]["count"]==0){
		$insert = "INSERT INTO users (`ID`,`name`,`mail`,`provider`,`token`,`secret`) VALUES (NULL, :name, :mail, :provider, :token, :secret)";
		$ready = $pdo->prepare($insert);
		$result = $ready->execute(prepareData($response));
		$userid = $pdo->lastInsertId();
	}
	else{
		$userid = $rr[0]["ID"];
		$username = $rr[0]["name"];
	}

	if($userid!=0){
		echo json_encode($userid);
		$_SESSION['userid'] = $userid;
		$_SESSION['username'] = $username;
	}
	else{
		echo json_encode(false);
		session_unset();
		session_destroy();
	}
	/* Redirect to a different page in the current directory that was requested */
	$host  = $_SERVER['HTTP_HOST'];
	$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/../backend.php");
}

function prepareData($object){
	$result = array(
		":name" => $object["auth"]["info"]["nickname"],
		":mail" => "",
		":provider" => "Twitter",
		":token" => $object["auth"]["credentials"]["token"],
		":secret" => $object["auth"]["credentials"]["secret"]
	);

	return $result;
}