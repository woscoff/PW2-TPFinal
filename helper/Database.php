<?php

namespace helper;
class Database
{
    private $conn;

    public function __construct($servername, $username, $password, $dbname, $port)
    {
        $this->conn = mysqli_connect($servername, $username, $password, $dbname, $port);

        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    public function query($sql)
    {
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function queryArray($sql)
    {
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }


    public function execute($sql)
    {
        mysqli_query($this->conn, $sql);
    }

    public function prepare($sql)
    {
        $this->conn->prepare($sql);
    }

    public function getLastInsertedId()
    {
        return mysqli_insert_id($this->conn);
    }

    public function __destruct()
    {
        mysqli_close($this->conn);
    }
}
