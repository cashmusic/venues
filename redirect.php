<?php 
session_start();
include 'config.php';
require 'twitteroauth-master/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;


// //1. Get request token.
$tconnection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);

$request_token = $tconnection->oauth('oauth/request_token', array('oauth_callback' => $OAUTH_CALLBACK));


// // // 2. Keep the request token in the user's session.
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];


// // // 3. Redirect the user to Twitter for authorization.


$url = $tconnection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

header('Location:'.$url);  

?>