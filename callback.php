<?php 

session_start();
include 'config.php';
require 'twitteroauth-master/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
// 4. Get access token.


$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];


if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    echo "Abort! Something is wrong.";
} 

$twitconnection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);


$access_token = $twitconnection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

$_SESSION['access_token'] = $access_token;

// 5. Discard request token.

unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']); 


if (isset($access_token)) {

	$_SESSION['logged_in'] = 'true';
	
	header('Location: /'); 

} else {
	echo "no";
}
?>