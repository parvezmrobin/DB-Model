<?php

namespace DbModel;

use mysqli_result;

class Query
{

    private $host, $db, $username, $password, $port;
    /**
     * @var \mysqli $connection
     */
    private $connection;

    /**
     * Query constructor.
     * @param string $db Name of the database
     * @param string $host Database host URL
     * @param string $username
     * @param string $password
     * @param string $port
     */
    public function __construct(
        $db, $host = 'localhost', $username = 'root', $password = '', $port = '3306'
    )
    {
        $this->db = $db;
        $this->host = $host ?: 'localhost';
        $this->username = $username ?: 'root';
        $this->password = $password ?: '';
        $this->port = $port ?: '3306';
    }

    /**
     * Performs a database query
     * @param string $query query to be executed
     * @return array|boolean
     */
    public function run($query)
    {
        $result = $this->start($query);
        if (is_bool($result)) {
            if ($result === true) {
                $ret = $this->connection->insert_id;
            } else {
                $ret = false;
            }
        } else {
            $ret = $result->fetch_all(MYSQLI_BOTH);
        }

        $this->stop();

        return $ret;
    }

    /**
     * Start a database query
     * @param string $query query to be executed
     * @return bool|mysqli_result
     * @throws \Exception
     */
    public function start($query)
    {
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->db, $this->port);
        $query = $this->connection->real_escape_string($query);
        $result = $this->connection->query($query);

        if ($result === false) {
            throw new \Exception($this->connection->error . " Query: " . $query);
        }

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

    /**
     * Get the last auto increment id of last insertion
     * @return mixed
     */
    public function getLastId()
    {
        return $this->connection->insert_id;
    }


    /**
     * Get the last error
     * @return string
     */
    public function getLastError()
    {
        return $this->connection->error;
    }
}
