<?php

namespace Cashmusic\Venues;

use PDO;

class Controller {
    private $settings, $pdo;

    /**
     * Gets JSON config file and PDO connection on start
     */
    function __construct()
    {
        // get connection details
        $this->settings = $this->getSettings();
        $this->getPDOConnection();

    }

    /**
     * Get contents of config/config.json or fail
     *
     * @return mixed
     */
    function getSettings() {

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
    function getPDOConnection() {
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
}

?>