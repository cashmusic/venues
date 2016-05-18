<?php
/**
 * PDO Wrapper Class
 * We're basically just abstracting this in case we do some fancy pants stuff later.
 */
namespace Cashmusic\Venues;

use PDO;

class DatabaseWrapper
{
    public $db, $error;

    function __construct($pdo_settings, $connection_type)
    {
        if ( !empty($pdo_settings['username']) && !empty($pdo_settings['password']) ) {

            // build the connection string
            $pdo_connection = "$connection_type:host=" . $pdo_settings['host'] . ";dbname=" . $pdo_settings['database'];

            // create the connection to pass back to the host class
            try {
                $this->db = new PDO($pdo_connection, $pdo_settings['username'], $pdo_settings['password']);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

    }

    /**
     * Very basic query method
     * @param $query
     * @param $params
     * @return array|bool
     */
    public function query($query, $params) {
        try {
            $search = $this->db->prepare($query);
            $search->execute($params);
        } catch(Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $search->fetchAll(PDO::FETCH_ASSOC);
    }

}