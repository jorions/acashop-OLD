<?php

namespace Aca\Bundle\ShopBundle\Db;

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
        // Use new \msyqli instead of just new mysqli because that makes the reference use the absolute namespace, not the relative namespace
        $this->db = new \mysqli($this->host, $this->username, $this->password, $this->databaseName);

        // Connection error handling
        if($this->db->connect_errno) {
            echo "Oh no! Failed to connnect to MySQL<br>";
            echo $this->db->connect_error;
            exit();
        }
    }

    /**
     * Accept a SQL query and return any matching rows
     * @param $query
     * @return array
     */
    public function fetchRows($query)
    {
        // Query the database
        $result = $this->db->query($query);

        return $result;
    }
}