<?php

namespace DbModel;

class Query
{

    private $host, $db, $username, $password, $port;
    private $connection;

    /**
     * Query constructor.
     * @param string $db Name of the database
     * @param string $host Database host URL
     * @param string $username
     * @param string $password
     * @param string $port
     */
    public function __construct($db, $host = 'localhost', $username = 'root', $password = '', $port = '3306')
    {
        $this->db = $db;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    /**
     * Performs a database query
     * @param string $query query to be executed
     * @return array|boolean
     */
    public function run($query)
    {
        $result = $this->start($query);
        $ret = (is_bool($result)) ? $result : $result->fetch_all(MYSQLI_BOTH);
        $this->stop();

        return $ret;
    }

    /**
     * Start a database query
     * @param string $query query to be executed
     * @return mysqli_result|boolean
     */
    public function start($query)
    {
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->db, $this->port);
        $result = $this->connection->query($query);

        return $result;
    }

    /**
     * Closes existing connection
     */
    public function stop()
    {
        if ($this->connection !== null) {
            $this->connection->close();
        }
    }
}
