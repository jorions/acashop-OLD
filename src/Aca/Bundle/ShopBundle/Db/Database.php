<?php

namespace Aca\Bundle\ShopBundle\Db;

// Becuase we are in a namespace, we are no longer in the default global namespace, so using core classes requires that we either "use" them
// here or continuously refer to them using the syntax like "new \msyqli" - the \ is necessary because it refers to the absolute namespace location
use \mysqli;
use \Exception;

class Database
{
    /**
     * Username of the database login
     * @var string
     */
    protected $username;

    /**
     * Password of the database login
     * @var string
     */
    protected $password;

    /**
     * Host of the database
     * @var string
     */
    protected $host;

    /**
     * Port of the database
     * @var int
     */
    protected $port;

    /**
     * Name of the database
     * @var string
     */
    protected $databaseName;

    /**
     * MYSQL database
     * @var mysqli
     */
    protected $db;

    public function __construct()
    {
        $this->username = 'root';
        $this->password = 'root';
        $this->host = 'localhost';
        //$this->port = 3306;
        $this->databaseName = 'acashop';

        // Connect to the DB
        $this->db = new mysqli($this->host, $this->username, $this->password, $this->databaseName);

        // Connection error handling
        if($this->db->connect_errno) {
            throw new Exception(
                "Oh no! Failed to connnect to MySQL<br>" . $this->db->connect_error);
        }
    }

    /**
     * Accept a SQL query and return any matching rows
     * @param string $query SQL query
     * @return array
     */
    public function fetchRows($query)
    {
        // Query the database
        $result = $this->db->query($query);

        return $result;
    }

    /**
     * Get many rows from the DB
     * @param string $query SQL query
     * @return array Assoc array of data from DB
     */
    public function fetchRowMany($query)
    {
        $return = [];
        $result = $this->db->query($query);
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
        return $return;
    }
}