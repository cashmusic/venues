
<?php


/**************************************************************
*
*				Routes and Formats
*
**************************************************************/

 
// set basic variables
$route = $_REQUEST['p'];
$output_format = 'json';
$requested_action = false;

// look for '.html' and set output format to 'html' if found

if (strpos($route,'.html') !== false) {
	$output_format = 'html';
	$route = str_replace('.html','',$route); // correct route by removing the .html after format set
	
}

if ($output_format == 'html') {
    setMustache();
}


/*************************************************************
*
*				Parse Action
*
*************************************************************/


// explode route by '/' and determine what's being asked for
$exploded_route = explode("/",$route);

if (count($exploded_route) > 1 && $exploded_route[2] == 'edit.php') {
	$requested_action = 'edited';
	
} else if (count($exploded_route) > 1 && $exploded_route[1] == 'edit') {
	$requested_action = 'edit';

} else if (count($exploded_route) == 1 && $exploded_route[0] == 'venues') {
	$requested_action = 'search';
	
} else if (count($exploded_route) > 1 && $exploded_route[0] == 'venues') {
	$requested_action = 'details';
	
} 
	

/*************************************************************
*
*				Handle Routes
*
*************************************************************/

require_once('database.php');

// if we found something to do, include needed files and get doing...
if ($requested_action) {
	setMustache();
	if ($requested_action == 'edited') {
			echo "form sent!<br>";
			
			$venuename = $_POST['venuename'];
			
			$address1 = $_POST['address1'];
			$address2 = $_POST['address2'];
			$city = $_POST['city'];
			$region = $_POST['region'];
			$country = $_POST['country'];
			$postalcode = $_POST['postalcode'];
			$url = $_POST['url'];
			$phone = $_POST['phone'];
			$type = $_POST['type'];
			$UUID = $_POST['UUID'];
			
		try {
			
			$sql = "UPDATE venues SET name = ?, 
										address1 = ?,
										address2 = ?,
										city = ?,
										region = ?,
										country = ?,
										postalcode = ?,
										url = ?,
										phone = ?,
										type = ?
									
									WHERE UUID = ?";
									
			$q = $db->prepare($sql);
			$q->execute(array($venuename, 
								$address1, 
								$address2, 
								$city, 
								$region, 
								$country,
								$postalcode,
								$url,
								$phone,
								$type,
								$UUID));
							
			echo "woodegevv!";
			
			
			
			

	
		} catch(Exception $e) {
			echo $e->getMessage();
			exit;
		
		}
	
	
	} else if ($requested_action == 'edit') {
		
		setHeaders($output_format);
		$UUID = $exploded_route[2];
		
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
		$template = $mustache->loadTemplate('edit');
		echo $template->render($venue);

	} else if ($requested_action == 'details') {
		setHeaders($output_format);
		$id = intval($exploded_route[1]);
		// load venue with matching UUID, this info is on the specific venue details page
		try {
			$venuearray = $db->prepare('SELECT * FROM venues WHERE id = ?');
			$venuearray->bindParam(1, $id);
			$venuearray->execute();
	
		} catch(Exception $e) {
			echo $e->getMessage();
			exit;
		}

		$venue = $venuearray->fetch(PDO::FETCH_ASSOC);
		
		if ($venue) {
			// we're going to switch output based on format
			if ($output_format == 'json') {
				// json output if its venues/id
				echo json_encode($venue);
			} else if ($output_format == 'html') {
				// Load mustache template for venue page
				$template = $mustache->loadTemplate('venue');
				echo $template->render($venue,'venue');	
			}
		} else {
			// stuff didn't work!
			echo "404 not found!";
		}

	} elseif ($requested_action == 'search') {
		
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
			
			if ($search) {
			
				if ($output_format == 'json') {
					// json output for search page (venues?p=search)
					echo json_encode($search);
					
				} else if ($output_format == 'html') {
					//  Load mustache template for search page
					$template = $mustache->loadTemplate('search');
					echo $template->render(array("results" => $search, "name" => $name));
				}
			} else {
				echo "No venues were found :(";

			}
		}
	}
} else {

	//Index page!
	setHeaders($output_format);
	// loads ALL venues in the db
	try {
		$results = $db->query('SELECT * FROM venues');
	} catch(Exception $e) {
		echo $e->getMessage();
		exit;
	}
	$venues = $results->fetchAll(PDO::FETCH_ASSOC);
		
	// Load mustache template for main page
	setMustache();
	$template = $mustache->loadTemplate('mainpage');
	echo $template->render(array("results" => $venues));
}

/**************************************************************
*
*				Setup Functions
*
**************************************************************/


function setMustache() {
	//set up Mustache
	require_once('lib/mustache/Autoloader.php');
	Mustache_Autoloader::register();
	$mustache = new Mustache_Engine(array(
		'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views') 
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

