<?php 


session_start(); 

$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

$CONSUMER_KEY = 'GmfmWT73HaYtLsiY74Y4UA';
$CONSUMER_SECRET = 'R8JtsF9GHL6v78zg3E3TdL5GcmEfwJeGJwK29moTh0';
$OAUTH_CALLBACK = 'localhost';




require '/lib/twitteroauth/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
$token = $_REQUEST['oauth_token'];

$verifier = $_REQUEST['oauth_verifier'];


$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
if ($connection) { echo " connection! ";}



$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $OAUTH_CALLBACK));



//$request_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $verifier));

if ($request_token) { 
	echo " request token! ";
}

//$redirect_url = $connection->getAuthorizeURL($request_token);
//if ($redirect_url) { echo "redirect url!";}

$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

echo "<BR><BR>sign in <A href='" . $url . "'>here</a>";







//$content = $connection->get("account/verify_credential");

// if ($content) {

// 	echo " content! "
// }



// if (isset($_GET['msg'])) {

// 	$tweetmsg = $_GET['msg'];
// 	$tweet->post('statuses/update',array('status' => $tweetmsg));
// 	echo "your message has been sent to twitter";

// } else {
// 	echo "your message has NOT been sent to twitter";
// }





?>

