<?php

namespace Cashmusic\Venues;

use PDO;

class Controller {
    private $settings, $pdo, $route, $uuid;
    
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
        $this->setRoute()->setAction();

    }

    /**
     * Parses URL string to get what route is requested, and format to return
     */
    public function setRoute() {

        if(preg_match("/(.html|.php)/i", $_REQUEST['p'])){
            //one of these string found
            $this->format = "html";
        }

        $this->route = $this->getURLIdentifier($_REQUEST['p']);

        return $this;
    }

    public function setAction() {
        // explode route by '/' and determine what's being asked for
        $route = explode("/",$this->route);

        $path = $route[0];
        $identifier = $this->getURLIdentifier($route[1]) ? $this->getURLIdentifier($route[1]) : "";
        $identifier_2 = $route[2] ? $route[2] : "";

        if(preg_match("/(venue)/i", $path)){

            // if identifier is empty we're in search mode
            if (empty($identifier)) {
                $this->action = 'search';
            }

            // if identifier is numeric then we're doing GET venue id stuff
            if (is_numeric($identifier)) {
                $this->action = 'details';
                $this->uuid = $identifier;
            }
            // if identifier is not numeric then we're editing
            if (!is_numeric($identifier) && !empty($identifier)) {
                $this->action = 'edit';
                $this->uuid = $identifier_2;
            }

            // lastly if POST is set then we're in "edited" mode
            if (!empty($_POST['UUID'])) {
                $this->action = 'edited';
                $this->uuid = $_POST['UUID'];
            }
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

    private function getURLIdentifier($value) {
        return str_replace(array(
            ".html",
            ".php"
        ), "", $value);
    }
}

?>