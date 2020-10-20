<?php
namespace Sbe1\Webcore;
use \PDO;

/**
 * MYSQL PDO Class
 * 
 * NOTE: Only accepts DSN as a connection argument.
 * E.G. $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
 * 
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class DatabaseMySQLPDO {
    private string $_dsn;
    private $_conn;

    /**
     * Constructor.
     * 
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @param array $options
     * @param boolean $delayconnect
     * 
     * @return self
     */
    public function __construct (string $dsn,  string $user, string $pass,
        array $options=null, $delayconnect=false) {
        $this->_dsn = $dsn;
        if (!$delayconnect) { $this->connect($user, $pass, $options); }
    }

    /**
     * Connect to the database.
     * 
     * @param string $user
     * @param string $pass
     * @param array $options
     * 
     * @return void
     */
    public function connect (string $user, string $pass, array $options=null) {
        $opts = empty($options) ? [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true
        ] : $options;
        try {
            $this->_conn = new PDO($this->_dsn, $user, $pass, $opts);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Execute a query and return the results.
     * 
     * @param string $sql
     * @param boolean $returnstmt
     * 
     * @return mixed
     */
    public function query (string $sql, $returnstmt=false) {
        $stmt = $this->_conn->query($sql);
        return $returnstmt ? $stmt : $stmt->fetchAll();
    }

    /**
     * Execute a prepared query and return the results.
     * 
     * @param string $sql
     * @param array $params
     * @param boolean $returnstmt
     * 
     * @return array
     */
    public function preparedQuery (string $sql, array $params,
        $returnstmt=false) {
        $stmt = $this->_conn->prepare($sql);
        $stmt->execute($params);
        return $returnstmt ? $stmt : $stmt->fetchAll();
    }

    /**
     * Execute query that does not return a result.
     * 
     * @param string $sql
     * 
     * @return void
     */
    public function voidQuery (string $sql) {
        $this->_conn->query($sql);
    }

    /**
     * Execute prepared query that does not return a result.
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return void
     */
    public function preparedVoidQuery (string $sql, array $params) {
        $stmt = $this->_conn->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Execute an array of queries individually.
     * 
     * @param array $queries
     * 
     * @return void
     */
    public function batchQuery (array $queries) {
        foreach ($queries as $query) {
            $this->_conn->query($query);
        }
    }

    /**
     * Execute an array of prepared queries individually.
     * 
     * @param string $sql
     * @param array $paramset
     * 
     * @return void
     */
    public function preparedBatchQuery (string $sql, array $pramsset) {
        $stmt = $this->_conn->prepare($sql);
        foreach ($paramset as $params) {
            $stmt->execute($params);
        }
    }

    /**
     * Execute a query and return a result row.
     * 
     * @param string $sql
     * 
     * @return array
     */
    public function getRow (string $sql) {
        $stmt = $this->_conn->query($sql);
        return $stmt->fetch();
    }

    /**
     * Execute a prepared query and return a result row.
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return array
     */
    public function getPreparedRow (string $sql, array $params) {
        $stmt = $this->_conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute a query and return the first column of the first result row.
     * 
     * @param string $sql
     * 
     * @return mixed
     */
    public function getOne (string $sql) {
        $stmt = $this->_conn->query($sql);
        $row = $stmt->fetch();
        return array_values($row)[0];
    }

    /**
     * Execute a prepared query and return the first column of the first row.
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return mixed
     */
    public function getPreparedOne (string $sql, array $params) {
        $stmt = $this->_conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return array_values($row)[0];
    }

    /**
     * Return the last insert id from the last insert query.
     * 
     * 
     * @return int
     */
    public function getLastInsertId () {
        $stmt = $this->_conn->query('SELECT LAST_INSERT_ID()');
        $row = $stmt->fetch();
        return $row[0];
    }
}