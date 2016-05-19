<?php

namespace Cashmusic\Venues;

class Controller {
    public $action = 'index';
    public $format = "json";
    public $parameter, $results;
    protected $settings, $db;
    public $debug = false;

    /**
     * Gets JSON config file and PDO connection on start
     */
    function __construct()
    {
        // get connection details
        $this->settings = $this->getSettings();
        $this->debug = $this->settings['debug'];

        // get the PDO connection
        $this->getDatabaseConnection();

        // route parsing for action, intended route, etc
        $this->setFormat()->setAction()->handleRoute();
    }

    /**
     * Get contents of config/config.json or fail
     * @return mixed
     */
    private function getSettings() {

        if (file_exists(CASH_VENUE_ROOT.'/config/config.json')) {
            try {
                $settings = json_decode(file_get_contents(CASH_VENUE_ROOT.'/config/config.json'),true);
            } catch (\Exception $e) {
                echo "Error reading the config.json file. (".$e->getMessage().")";
            }

            return $settings;
        }

        echo "Couldn't find a config.json file.";

    }

    /**
     * Setup the DB wrapper with the settings we got from the JSON
     */
    private function getDatabaseConnection() {
        if (!$this->debug) {
            $pdo_settings = $this->settings['database'];
        } else {
            $pdo_settings = $this->settings['debug_database'];
        }


        try {
            $this->db = new DatabaseWrapper($pdo_settings);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

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
            $parameter = $this->getParameter($_REQUEST['parameter']) ? $this->getParameter($_REQUEST['parameter']) : "";
        }

        if ($path == "venues"){
            $this->action = 'search';
            $this->parameter = $parameter;

            // we should abort this route if the search parameter is less than three characters long.
            if (strlen($this->parameter) < 3) header("Location: /");
        }

        if ($path == "venue"){
            $this->action = 'details';
            $this->parameter = $parameter;
        }

        return $this;
    }

    /**
     * Get venue ID or search parameter from URL, without the file ending
     * @param $value
     * @return string
     */
    private function getParameter($value) {
        return str_replace(array(
            ".html",
            ".php"
        ), "", $value);
    }

    /**
     * Parses URL string to get what route is requested, and format to return
     * @return $this
     */
    public function setFormat() {

        if(preg_match("/(.html|.php)/i", $_SERVER['REQUEST_URI'])){
            //one of these string found
            $this->format = "html";
        }

        return $this;
    }

    /**
     * The actual route controller method
     * @return $this
     */
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

    /**
     * Get array of search results based on $this->parameter query
     * @return $this
     */
    private function searchVenues() {
        $name = urldecode($this->parameter); // the search term

        if(isset($name)) {
            // gets all venues with the search term in the name somewhere

            $search = $this->db->query("SELECT * FROM venues WHERE name LIKE :query ORDER BY name ASC", array(':query' => '%'.$name.'%'));

            $this->results = array(
                "results" => $search,
                "name" => $name
            );
        }

        return $this;
    }

    /**
     * Get venue details based on uuid pulled from $this->parameter
     * @return $this
     */
    private function getVenueDetails() {

        // load venue with matching UUID, this info is on the specific venue details page
        $venue_details = $this->db->query("SELECT * FROM venues WHERE UUID = :uuid LIMIT 1", array(':uuid' => $this->parameter));
        $venue = $venue_details[0];

        if ($venue) {
            // output content to browser
            if ($venue['creationdate']) {
                $venue['creationdate'] = $this->prettifyDate($venue['creationdate']);
            }
            if ($venue['modificationdate']) {
                $venue['modificationdate'] = $this->prettifyDate($venue['creationdate']);
            }

            // encoding this onto the $venue array so we don't do it with AJAX on the template
            $json_encoded_values = json_encode($venue);
            $venue['api_response'] = $json_encoded_values;

            $this->results = $venue;

            return $this;

        } else {
            // stuff didn't work!
            echo "  404 not found!";
        }
    }

    /**
     * Formats date strings for humans
     * @param $date
     * @return bool|string
     */
    private function prettifyDate($date) {
        return date("F j, Y", strtotime($date));
    }

    /**
     * Show index page
     * @return $this
     */
    private function getIndex() {
        $this->format = "html";
        $this->results = array();
        return $this;
    }

}

?>