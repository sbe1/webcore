<?php
namespace Sbe1\Webcore;
/**
 * A class for interacting with a JSON flat file database.
 *
 * Expected Structure of JSON data:
 *
 {
    "articles": [{
            "id": 1,
            "title": "An Article",
            "Author": "Fred Saberhagen",
            "content": "..."
    }, {
            "id": 2,
            "title": "Another Article",
            "author": "Mark Twain",
            "content": "..."
    }],
    "pages": [{
            "page_title": "Mini-CMS: Home",
            "page_heading": "The Homepage"
    }, {
            "page_title": "Mini-CMS: An Article",
            "page_heading": "An Article"
    }]
}
 *
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class DatabaseJSONFile {
    private static $instance;
    private $dataFile;
    private $data;

    /**
     * Private constructor.
     * 
     * @param string $dataFile - path to JSON file.
     * 
     * @return self
     */
    private function __construct (string $dataFile) {
        try {
            $this->dataFile = $dataFile;
            $this->data = json_decode(file_get_contents($this->dataFile), true);
        }
        catch (\Exception $e) { throw new \Exception($e); }
    }
    public static function getInstance ($dataFile) {
        if (self::$instance == null) {
            if (file_exists($dataFile) && is_writable($dataFile)) {
                self::$instance = new DatabaseJSONFile($dataFile);
            }
            else {
                throw new \Exception('Data file either does not exist or is not writable.');
            }
        }
        return self::$instance;
    }
    /**
     * Get all records in a collection.
     * 
     * @param string $collection
     * 
     * @return array
     */
    public function selectAll ($collection) {
        return isset($this->data[$collection]) ? $this->data[$collection] : [];
    }
    /**
     * Perform a pseudo query on a collection.
     * 
     * @param string $collection
     * @param string $where
     * @param string $operator
     * @param mixed $value
     * @param int $limit
     * 
     * @return array
     */
    public function select ($collection,$where, $operator, $value, $limit = -1) {
        $result = [];
        $i = 0;
        foreach ($this->data[$collection] as $item) {
            if (isset($item[$where])) {
                if ($this->getExpressionAnswer($item, $where, $operator, $value)) {
                    array_push($result, $item);
                    if ($limit > -1) {
                        if($i < $limit) { $i++; }
                        else { break; }
                    }
                }
            }
            else {
                break;
            }
        }
        return $result;
    }

    /**
     * Return a one column row.
     * 
     * @return mixed
     */
    public function selectOne ($collection,$where, $operator, $value) {
        foreach ($this->data[$collection] as $item) {
            if (isset($item[$where])) {
                if ($this->getExpressionAnswer($item, $where, $operator, $value)) {
                    return $item;
                }
            }
            else {
                break;
            }
        }
        return [];
    }

    /**
     * Check if a collection exists.
     * 
     * @param string $collection
     * 
     * @return boolean
     */
    public function collectionExists ($collection) {
        return isset($this->data[$collection]);
    }
    /**
     * Get count of records in a collection.
     * 
     * @param string $collection
     * 
     * @return int
     */
    public function collectionCount ($collection) {
        return count($this->data[$collection]);
    }
    /**
     * Check if a key in a record in a collection exists.
     * 
     * @param string $collection
     * @param string $key
     * 
     * @return boolean
     */
    public function keyExists ($collection, $key) {
        $found = false;
        if (isset($this->data[$collection])) {
            foreach ($this->data[$collection] as $item) {
                if (isset($item[$key])) {
                    $found = true;
                    break;
                }
            }
        }
        return $found;
    }
    /**
     * Saves data to JSON file.
     * 
     * @return mixed
     */
    public function saveData (string $key=null) {
        if (!empty($key)) { $this->data[$key] = array_values($this->data[$key]); }
        return file_put_contents($this->dataFile, json_encode((array)$this->data, JSON_OBJECT_AS_ARRAY | JSON_PRETTY_PRINT));
    }
    /**
     * Creates or overwrites a collection. There is no check to see if the collection already exists.
     * Use collectionExists() method to create your own logic.
     * 
     * @param string $collection
     * 
     * @return mixed
     */
    public function createCollection ($collection) {
        $this->data[$collection] = [];
        return $this->saveData();
    }
    /**
     * Deletes a collection.
     * 
     * @param string $collection
     * 
     * @return mixed
     */
    public function deleteCollection ($collection) {
        if (isset($this->data[$collection])) {
            unset($this->data[$collection]);
            return $this->saveData();
        }
        else { return false; }
    }
    /**
     * Creates a record in a given collection.
     * @param string $collection
     * @param array $record
     * 
     * @return mixed
     */
    public function createRecord ($collection, array $record) {
        if (isset($this->data[$collection])) {
            array_push($this->data[$collection], $record);
        }
        else {
            $this->data[$collection] = [];
            array_push($this->data[$collection], $record);
        }
        return $this->saveData();
    }
    /**
     * Update existing record in a collection.
     * 
     * @param string $collection
     * @param array $record
     * @param string $where
     * @param string $operator
     * @param mixed $value
     * 
     * @return boolean
     */
    public function updateRecord ($collection, array $record, $where, $operator, $value) {
        $found = false;
        $i = 0;
        foreach ($this->data[$collection] as $item) {
            if ($this->getExpressionAnswer($item, $where, $operator, $value)) {
                $found = true;
                $record = $item;
                unset($this->data[$collection][$i]);
                array_push($this->data[$collection], $record);
                $this->saveData();
                break;
            }
            $i++;
        }
        return $found;
    }
    /**
     * Update field in an existing record in and existing collection.
     * 
     * @param string $collection
     * @param string $field
     * @param mixed $fieldValue
     * @param string $where
     * @param string $operator
     * @param mixed $whereValue
     * 
     * @return mixed
     */
    public function updateField ($collection, $field, $fieldValue, $oldvalue) {
        $found = false;
        $i = 0;
        foreach ($this->data[$collection] as $item) {
            if (isset($item[$field]) && $item[$field] === $oldvalue) {
                $found = true;
                $item[$field] = $fieldValue;
                $record = $item;
                unset($this->data[$collection][$i]);
                array_push($this->data[$collection], $record);
                $this->saveData();
                break;
            }
            $i++;
        }
        return $found;
    }
    /**
     * Delete a record from a collection.
     * 
     * @param string $collection
     * @param string $where
     * @param string $operator
     * @param mixed $value
     * 
     * @return boolean
     */
    public function delete ($collection, $where, $operator, $value) {
        $found = false;
        $i = 0;
        foreach ($this->data[$collection] as $item) {
            if ($this->getExpressionAnswer($item, $where, $operator, $value)) {
                $found = true;
                unset($this->data[$collection][$i]);
                $this->saveData();
                break;
            }
            $i++;
        }
        return $found;
    }
    /**
     * Simple query expression evaluator.
     * 
     * @param array $item
     * @param string $where
     * @param string $operator
     * @param mixed $value
     * 
     * @return boolean
     */
    private function getExpressionAnswer (array $item, $where, $operator, $value) {
        $answer = false;
        switch ($operator) {
            case '=':
                $answer = ($item[$where] == $value);
                break;
            case '!=':
                $answer = ($item[$where] != $value);
                break;
            case '>':
                $answer = ($item[$where] > $value);
                break;
            case '<':
                $answer = ($item[$where] < $value);
                break;
            case '>=':
                $answer = ($item[$where] >= $value);
                break;
            case '<=':
                $answer = ($item[$where] <= $value);
                break;
        }
        return $answer;
    }
}