<?php

namespace Cashmusic\Venues;

use PDO;

class Controller {
    private $settings, $pdo, $parameter, $results;
    private $debug = false;
    protected $action = 'index';
    protected $format = "json";

    /**
     * Gets JSON config file and PDO connection on start
     */
    function __construct()
    {
        // get connection details
        $this->settings = $this->getSettings();

        // get the PDO connection, not the best implementation but it works for nwo
        $this->getPDOConnection();

        // route parsing for action, intended route, etc
        $this->setFormat()->setAction()->handleRoute();
    }

    /**
     * Parses URL string to get what route is requested, and format to return
     */
    public function setFormat() {

        if(preg_match("/(.html|.php)/i", $_SERVER['REQUEST_URI'])){
            //one of these string found
            $this->format = "html";
        }

        return $this;
    }


    /**
     * Set action and identifier based on string matches in the request URI.
     *
     * @return $this
     */
    public function setAction() {
        // explode route by '/' and determine what's being asked for
        $path = "";
        if (!empty($_REQUEST['path'])) {
            $path = $_REQUEST['path'];
        }

        $parameter = "";
        if (!empty($_REQUEST['parameter'])) {
            $parameter = $this->getVenueParameter($_REQUEST['parameter']) ? $this->getVenueParameter($_REQUEST['parameter']) : "";
        }

        if($path == "venues"){
            $this->action = 'search';
            $this->parameter = $parameter;
        }

        if($path == "venue"){
            $this->action = 'details';
            $this->parameter = $parameter;
        }

        return $this;
    }

    public function handleRoute() {
        switch ($this->action) {
            case "search":
                $this->searchVenues()->renderView();
                break;

            case "details":
                $this->getVenueDetails()->renderView();
                break;

            case "index":
                $this->getIndex()->renderView();
                break;

            default:
                $this->getIndex()->renderView();

        }

        return $this;
    }

    /**
     * Get contents of config/config.json or fail
     *
     * @return mixed
     */
    private function getSettings() {

        if (file_exists(CASH_VENUE_ROOT.'/config/config.json')) {
            try {
                $settings = json_decode(file_get_contents(CASH_VENUE_ROOT.'/config/config.json'),true);
            } catch (\Exception $e) {
                echo "Error reading the config.json file.";
            }

            return $settings;
        }

        echo "Couldn't find a config.json file.";

    }

    /**
     * Not great PDO stuff, to start
     */
    private function getPDOConnection() {
        $pdo_settings = $this->settings['mysql'];
        if ( !empty($pdo_settings['username']) && !empty($pdo_settings['password']) ) {

            // build the connection string
            $pdo_connection = "mysql:host=" . $pdo_settings['host'] . ";dbname=" . $pdo_settings['database'];

            //TODO: needs to be better exception handling here
            try {
                $this->pdo = new PDO($pdo_connection, $pdo_settings['username'], $pdo_settings['password']);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        }
    }

    private function getVenueParameter($value) {
        return str_replace(array(
            ".html",
            ".php"
        ), "", $value);
    }

    private function searchVenues() {
        $name = urldecode($this->parameter); // the search term

        if(isset($name)) {
            // gets all venues with the search term in the name somewhere
            try {
                $search = $this->pdo->prepare("SELECT * FROM venues WHERE name LIKE :query ORDER BY name ASC");
                $search->execute(array(':query' => '%'.$name.'%'));
            } catch(Exception $e) {
                echo $e->getMessage();
                exit;
            }

            $this->results = array(
                "results" => $search->fetchALL(PDO::FETCH_ASSOC),
                "name" => $name
            );
        }

        return $this;
    }

    private function getVenueDetails() {

        // load venue with matching UUID, this info is on the specific venue details page
        try {
            $venue_details = $this->pdo->prepare('SELECT * FROM venues WHERE UUID = ?');
            $venue_details->bindParam(1, $this->parameter);
            $venue_details->execute();

        } catch(Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $venue = $venue_details->fetch(PDO::FETCH_ASSOC);

        if ($venue) {
            // output content to browser
            if ($venue['creationdate']) {
                $venue['creationdate'] = date("F j, Y", strtotime($venue['creationdate']) );
            }
            if ($venue['modificationdate']) {
                $venue['modificationdate'] = date("F j, Y", strtotime($venue['modificationdate']));
            }

            $this->results = $venue;

            return $this;

        } else {
            // stuff didn't work!
            echo "  404 not found!";
        }
    }

    private function getIndex() {
        $this->format = "html";
        $this->results = array();
        return $this;
    }

    /**
     * Render mustache template for this view, or return JSON
     *
     * @param string|bool $template
     * @return $this
     */
    private function renderView($template=false) {
        if ($this->format == 'json') {
            header("Access-Control-Allow-Origin: *");
            header('Content-type:application/json');
            echo json_encode($this->results);
        }

        if ($this->format == 'html') {
            header('Content-type:text/html; charset=utf-8');

            $renderer = new \Mustache_Engine(array(
                'loader' => new \Mustache_Loader_FilesystemLoader(CASH_VENUE_ROOT . '/views'),
            ));
            echo $renderer->render($this->action, $this->results);

        }

        return $this;
    }
}

?>