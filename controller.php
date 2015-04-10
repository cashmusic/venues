<?php
/**************************************************************
*
*				Routes and Formats
*
**************************************************************/
// set basic variables

$route = $_REQUEST['p'];
$output_format = 'json';
$requested_action = 'index';
// look for '.html' and set output format to 'html' if found
if (strpos($route,'.html') !== false) {
   $output_format = 'html';
   $route = str_replace('.html','',$route); // correct route by removing the .html after format set
}

/*************************************************************
*
*				Parse Action
*
*************************************************************/
// explode route by '/' and determine what's being asked for
$exploded_route = explode("/",$route);
if (isset($exploded_route[1])) {
   if (isset($exploded_route[2])) {
      if (count($exploded_route) > 1 && $exploded_route[2] == 'edit.php') {
         $requested_action = 'edited';
      } else if (count($exploded_route) > 1 && $exploded_route[1] == 'edit') {
         $requested_action = 'edit';
         $UUID = $exploded_route[2];
      }
   } else {
      if (count($exploded_route) == 1 && $exploded_route[0] == 'venues') {
         $requested_action = 'search';
      } else if (count($exploded_route) > 1 && $exploded_route[0] == 'venues') {
         $requested_action = 'details';
         $UUID = intval($exploded_route[1]);
      }
   }
}

/*************************************************************
*
*				Handle Routes
*
*************************************************************/
require_once('database.php');
// if we found something to do, include needed files and get doing...

	

if ($requested_action == 'edited') {

  // run through an update statement
  try {
     $sql =  "UPDATE venues SET ";
     $sql .= "name = ?,address1 = ?,address2 = ?,city = ?,region = ?,country = ?,postalcode = ?,url = ?,phone = ?,type = ? ";
     $sql .= "WHERE UUID = ?";
     $q = $db->prepare($sql);
     $q->execute(array(
        $_POST
     ));
  } catch(Exception $e) {
     echo $e->getMessage();
     exit;
  }
  // output content to browser (we're cheating here, assuming success)
  outputContent($_POST,$output_format,'edit');
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
} else if ($requested_action == 'details') {
	echo $UUID;
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
} else if ($requested_action == 'search') {

  $name = $_GET['q']; // the search term
  if(isset($name)) {
     // gets all venues with the search term in the name somewhere
     try {
        $searcharray = $db->prepare("SELECT * FROM venues WHERE name LIKE '%" . $name . "%'");
        $searcharray->execute();
     } catch(Exception $e) {
        echo $e->getMessage();
        exit;
     }
     $search = $searcharray->fetchALL(PDO::FETCH_ASSOC);
     $search_results = array("results" => $search, "name" => $name);
     // output content to browser
     outputContent($search_results,$output_format,'search');
  }
} else {
	
	//Index page!
	// loads ALL venues in the db
	try {
  	$results = $db->query("SELECT * FROM venues");
	} catch(Exception $e) {
  		echo $e->getMessage();
  		exit;
	}
	$venues = $results->fetchAll(PDO::FETCH_ASSOC);
	// Load mustache template for main page
	

	$template = $mustache->loadTemplate('mainpage');
	echo $template->render(array("results" => $venues));
  
}



/**************************************************************
*
*				Support Functions
*
**************************************************************/
function setMustache() {
   //set up Mustache
   require_once(__DIR__.'/lib/mustache/Autoloader.php');
   Mustache_Autoloader::register();
   $mustache = new Mustache_Engine(array(
      'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/views')
   ));
}
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
   if ($output_format == 'html') {
      setMustache();
      $template = $mustache->loadTemplate($template);
      echo $template->render($data);
   } else {
      echo json_encode($data);
   }
}
?>


