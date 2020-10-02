<?php
namespace Sbe1\Webcore;

/**
 * MySQLi class
 *
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class DatabaseMySQLi {
    private $_mysqli;
    private $_prepare_types = ['integer'=>'i','double'=>'d','string'=>'s'];
    private $_allowed_types = ['i','s','d'];

    /**
     * Constructor.
     * 
     * @return self
     */
    public function __construct (string $host, string $db,
        string $user, string $pass=null, string $charset=null) {
        $cs = empty($charset) ? 'utf8' : $charset;
        $this->_mysqli = new mysqli($host, $user, $pass, $db);
        $this->_mysqli->set_charset($cs);
    }

    /**
     * Returns MySQLi connection.
     * 
     * @return object
     */
    public function getConnection () {
        return $this->_mysqli;
    }

    /**
     * Execute a query with no result.
     * 
     * @return void
     */
    public function voidQuery (string $sql, $params=[]) {
        $stmt = $this->prepareStatement($sql, $params);
        $stmt->execute();
    }

    public function query (string $sql) {
        $stmt = $this->_mysqli->query($sql);
        if ($stmt->num_rows > 1) {
            $result = [];
            while ($row = $stmt->fetch_assoc()) {
                $result[] = $row;
            }
            return $result;
        }
        else {
            return $stmt->fetch_assoc();
        }
    }

    /**
     * Return the first column of the first row of a result.
     * 
     * @return mixed
     */
    public function getOne (string $sql, $params=[]) {
        $stmt = $this->prepareStatement($sql, $params);
        $one = null;
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $one = $result->fetch_array();
            }
        }
        return $one[0];
    }

    /**
     * Execute a query and return a single row of a query result.
     * 
     * @return array
     */
    public function fetchRow (string $sql, $params=[]) {
        $stmt = $this->prepareStatement($sql, $params);
        $rows = array();
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                return $result->fetch_assoc();
            }
        }
        return $rows;
    }

    /**
     * Execute a query and return the result as an array of objects.
     * 
     * @return array
     */
    public function fetchObject (string $sql, $params=[]) {
        $stmt = $this->prepareStatement($sql, $params);
        $rows = array();
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_object()) {
                    $rows[] = $row;
                }
                $result->free();
            }
        }
        return $rows;
    }

    /**
     * Execute a query and return the result as an associative array.
     * 
     * @return array
     */
    public function fetchAssoc (string $sql, $params=[]) {
        $stmt = $this->prepareStatement($sql, $params);
        $rows = array();
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
        }
        return $rows;
    }

    /**
     * Execute a query and return one coumn from the result rows as an array.
     * 
     * @return array
     */
    public function fetchColumn (string $sql, string $column, $params=[]) {
        $rows = $this->fetchAssoc($sql,$params);
        $list = array();
        if ($rows) {
            foreach ($rows as $r) {
                $list[] = $r[$column];
            }
        }
        return $list;
    }

    /**
     * Prepare a query and return its MySQLi statement object.
     * 
     * @return object
     */
    private function prepareStatement (string $sql, $params=[]) {
        $stmt = $this->_mysqli->prepare($sql);
        if ($params) {
            foreach ($params as $p) {
                $tmp = $this->_prepare_types[gettype($p)];
                $t = (empty($tmp) || !in_array($tmp, $this->_allowed_types)) ? 's' : $tmp;
                $stmt->bind_param($t, $p);
            }
        }
        return $stmt;
    }

    /**
     * Return the last insert ID.
     * 
     * @return int
     */
    private function getLastInsertId () {
        return $this->mysqli->insert_id;
    }

}