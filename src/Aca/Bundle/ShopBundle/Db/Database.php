<?php

namespace Aca\Bundle\ShopBundle\Db;

class Database
{
    public function __construct()
    {
        $username = 'root';
        $password = 'root';
        $host = 'localhost';
        $port = 3306;

        // Connect to the DB?
        // go to php.net and investiage mysqli family of functions
    }

    // THis method will accept a SQL query and return any matching rows
    public function fetchRows($query)
    {
        // This will come from the db
        return array('user_id' => 4);
    }
}