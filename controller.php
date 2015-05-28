<?php

session_start();
/**************************************************************
*
*       Database Connection
*
**************************************************************/
require_once('config.php');

try {
   $db = new PDO($CONNECTION, $USERNAME, $PASSWORD);
   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
   echo $e->getMessage();
   exit;
}


/**************************************************************
*
*       Routes and Formats
*
**************************************************************/
$route = $_REQUEST['p'];

$output_format = 'json';
// look for '.html' and set output format to 'html' if found
if (strpos($route,'.html') !== false) {
   $output_format = 'html';
   $route = str_replace('.html','',$route); // correct route by removing the .html after format set
}
if (strpos($route,'.php') !== false) {
   $output_format = 'html';
   $route = str_replace('.html','',$route); // correct route by removing the .html after format set
}


/*************************************************************
*
*       Parse Action
*
*************************************************************/
// explode route by '/' and determine what's being asked for
$exploded_route = explode("/",$route);

$requested_action = 'index';
if (isset($exploded_route[2])) {
   if (count($exploded_route) > 1 && $exploded_route[2] == 'process.php') {
      $requested_action = 'edited';
      $UUID = $_POST['UUID'];
   } else if (count($exploded_route) > 1 && $exploded_route[1] == 'edit') {
      $requested_action = 'edit';
      $UUID = $exploded_route[2];
   }
} else {
   if (count($exploded_route) == 1 && $exploded_route[0] == 'venues') {
      $requested_action = 'search';
   } else if (count($exploded_route) > 1 && $exploded_route[0] == 'venues') {
      $requested_action = 'details';
      $UUID = $exploded_route[1];
   }
}


/*************************************************************
*
*       Handle Routes
*
*************************************************************/

/////////////////////////////// EDITED
if ($requested_action == 'edited') {

   $venuename = $_POST['venuename'];
   $address1 = $_POST['address1'];
   $address2 = $_POST['address2'];
   $city = $_POST['city'];
   $region = $_POST['region'];
   $country = $_POST['country'];
   $postalcode = $_POST['postalcode'];
   $latitude = $_POST['latitude'];
   $longitude = $_POST['longitude'];
   $url = $_POST['url'];
   $phone = $_POST['phone'];
   $type = $_POST['type'];
   $UUID = $_POST['UUID'];

   try {
      $sql = "UPDATE venues SET name = ?, address1 = ?,address2 = ?,city = ?,region = ?,country = ?,postalcode = ?,latitude = ?, longitude = ?, url = ?,phone = ?,type = ? WHERE UUID = ?";
      $q = $db->prepare($sql);
      $q->execute(array($venuename,$address1,$address2,$city,$region,$country,$postalcode,$latitude, $longitude, $url,$phone,$type,$UUID));

   } catch(Exception $e) {
      echo $e->getMessage();
      exit;
   }
   header('Location: /venues/' . $UUID . '.html');

/////////////////////////////// EDIT
} else if ($requested_action == 'edit') {
   // load the venue with the matching UUID for editing

   try {
      $venuearray = $db->prepare('SELECT * FROM venues WHERE UUID = ?');
      $venuearray->bindParam(1, $UUID);
      $venuearray->execute();
   } catch(Exception $e) {
      echo $e->getMessage();
      exit;
   }
   // load mustache template for edit page
   $venue = $venuearray->fetch(PDO::FETCH_ASSOC);
   // output content to browser
   outputContent($venue,$output_format,'edit');

/////////////////////////////// DETAILS
} else if ($requested_action == 'details') {

   // load venue with matching UUID, this info is on the specific venue details page
   try {
      $venuearray = $db->prepare('SELECT * FROM venues WHERE UUID = ?');
      $venuearray->bindParam(1, $UUID);
      $venuearray->execute();

   } catch(Exception $e) {
      echo $e->getMessage();
      exit;
   }
   $venue = $venuearray->fetch(PDO::FETCH_ASSOC);
   if ($venue) {
      // output content to browser
      outputContent($venue,$output_format,'venue');
   } else {
      // stuff didn't work!
      echo "  404 not found!";
   }

/////////////////////////////// SEARCH
} else if ($requested_action == 'search') {
   $name = $_GET['q']; // the search term

   if(isset($name)) {
      // gets all venues with the search term in the name somewhere
      try {
         $searcharray = $db->prepare("SELECT * FROM venues WHERE name LIKE :query");
         $searcharray->execute(array(':query' => '%'.$name.'%'));
      } catch(Exception $e) {
         echo $e->getMessage();
         exit;
      }
      $search = $searcharray->fetchALL(PDO::FETCH_ASSOC);
      $search_results = array("results" => $search, "name" => $name);

      // output content to browser
      outputContent($search_results,$output_format,'search');
   }

/////////////////////////////// MAIN PAGE
} else {
   if (isset($_SESSION['access_token'])) {
      $_SESSION['access_token'] = True;
   } else {
      $_SESSION['access_token'] = False;
   }

   outputContent(array('loggedin' => $_SESSION['logged_in']),'html','mainpage');
}


/**************************************************************
*
*       Support Functions
*
**************************************************************/

function setHeaders($output_format) {
   if ($output_format == 'json') {
      header("Access-Control-Allow-Origin: *");
      header('Content-type:application/json');
   } else if ($output_format == 'html') {
      header('Content-type:text/html; charset=utf-8');
   }
}
function outputContent($data,$output_format,$template=false) {
   setHeaders($output_format);
   require_once(__DIR__.'/lib/mustache/Mustache.php');
   $mustache = new Mustache;
   if ($output_format == 'html') {
      //setMustache();
      $data["loggedin"] = $_SESSION['logged_in'];
      $template = file_get_contents(__DIR__.'/views/'.$template.'.mustache');

      echo $mustache->render($template,$data);
   } else {
      echo json_encode($data);
   }
}
?>
