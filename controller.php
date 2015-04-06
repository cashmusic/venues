<?php 
// set basic variables
$route = $_REQUEST['p'];
$output_format = 'json';
//$requested_action = false;


//set up Mustache
require_once('lib/mustache/Autoloader.php');
Mustache_Autoloader::register();
$mustache = new Mustache_Engine(array(
		'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views') 
));



// look for '.html' and set output format if found

if (strpos($route,'.html') !== false) {
	$output_format = 'html';
	$route = str_replace('.html','',$route); // correct route by removing the .html after format set
	
}

// explode route by '/' and determine what's being asked for
$exploded_route = explode("/",$route);

if (count($exploded_route) == 1 && $exploded_route[0] == 'venues') {
	$requested_action = 'search';
	
} else if (count($exploded_route) > 1 && $exploded_route[0] == 'venues') {
	$requested_action = 'details';
	
} 


// handle headers
function setHeaders($output_format) {
	if ($output_format == 'json') {
		header("Access-Control-Allow-Origin: *");
		header('Content-type:application/json');
	} else if ($output_format == 'html') {
		header('Content-type:text/html; charset=utf-8');
	}
}

// if we found something to do, include needed files and get doing...
if ($requested_action) {
	require_once('database.php');
	

	if ($requested_action == 'details') {
		setHeaders($output_format);
		$id = intval($exploded_route[1]);
		// make our query first
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
			
				// html output if its venues/id.html
				if (isset($venue['name'])) {
					echo "<h1>" . $venue['name'] . "</h1>";
				}
				if (isset($venue['city'])) {
					echo "<p>" . $venue['city'] . "</p>";
				}
				if (isset($venue['phone'])) {
					echo "<p>" . $venue['phone'] . "</p>";
				}
			}
		} else {
			// stuff didn't work! for now just echo out an 'oh shit note'
			echo "404 not found!";
		}
	} elseif ($requested_action == 'search') {
		
		// SEARCH PAGE!! both json and html pages will need the db, the search term
		// (which is saved as $name), and then the db is searched and returns an array
		
		require_once('database.php');
		
		
		$name = $_GET['q'];
		
	
		if(isset($name)) {
		
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
				
							// html code for the search page (venues.html?p=search)
							?>
							<html>
							<head>
							<meta charset="utf-8">
							<title>Search Venue Database</title>
							<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
							<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js" type="text/javascript"></script>
							<script src="autocomplete.js"></script>
							<link rel="stylesheet" href="style.css">
						</head>
						<body>
							<ul>
								<h2>Search for: <?php echo $name ?></h2>
								<?php
								foreach($search as $entry) {
								echo "<li>" . '<a href="venues/' . $entry["id"] . '.html">' . $entry["name"] . "</a></li>";
								}
								?>
							</ul>
		
		<?php
				}
				}
			}
		
		
		
		
		
		
		
		
		
		
	}
} else {

	//Index page! gets the db, makes an array with all venue info
	
	require_once('database.php');
	
	
	try {
		$results = $db->query('SELECT * FROM venues');
	
	
	} catch(Exception $e) {
		echo $e->getMessage();
		exit;
	
	}

	$venues = $results->fetchAll(PDO::FETCH_ASSOC);
// 	
	
// HTML code for basic index page, list of venues in db, and search form
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Venue Database</title>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js" type="text/javascript"></script>
 		<script src="autocomplete.js"></script>
 		<link rel="stylesheet" href="style.css">
	</head>
	<body>
	
<!-- 	Load mustache template -->
	<?php 
		$template = $mustache->loadTemplate('billybob');
		echo $template->render(array('whathewants' => 'taters'));
		
	?>
	
<!-- Load list of venues	 -->
		<ul>
			<?php
			foreach($venues as $venue) {
			echo "<li>" . '<a href="venues/' . $venue["id"] . '.html">' . $venue["name"] . "</a></li>";
			}
			?>
		</ul>
		<BR><BR>
		
<!-- Load search feature -->
		<p>Search the Database: 
		<form method="get" action="/venues.html">
			<input type="text" name="q" value="" placeholder="Venue Name..." id="keyword">
			<ul id="venue_list_id"></ul>
			<BR><BR>
			<input type="submit" value="search">
		</form>

		
	</body>
</html>


	
<?php 
	
}

?>