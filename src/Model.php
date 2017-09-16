<?php

namespace DbModel;

use JsonSerializable;

class Model implements JsonSerializable
{
    /**
     * @var string $host URL of database host
     * @var string $database Name of database
     * @var string $username
     * @var string $password
     * @var string $port
     */
    public static $host, $database, $username, $password, $port;
    private $data;

    /**
     * Model constructor.
     * @param $data array
     */
    public function __construct($data = [])
    {
        if (is_null($data)) {
            $this->data = [];
        } else {
            $this->data = $data;
        }
    }

    /**
     * Creates an array of Model using given data
     * @param array $arrays Array containing the arrays of data
     * @return Model[]
     */
    public static function createFromArray($arrays)
    {
        $instances = [];
        foreach ($arrays as $array) {
            $instances[] = new Model($array);
        }
        return $instances;
    }

    /**
     * Retrieves all instances of given model
     * @param string $table Name of the table
     * @param array|string $columns Columns to be selected
     * @return ModelCollection
     */
    public static function all($table, $columns = '*')
    {
        return static::where($table, '1', $columns);
    }

    /**
     * Retrieves the models that matches the given conditions
     * @param string $table Name of the table
     * @param string $conditions Condition to be applied
     * @param array|string $columns Columns to be selected
     * @param string $extra
     * @return ModelCollection
     */
    public static function where($table, $conditions = '1', $columns = '*', $extra = '')
    {
        $query = new Query(
            static::$database,
            static::$host,
            static::$username,
            static::$password,
            static::$port
        );
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $results = $query->run("SELECT $columns FROM $table WHERE $conditions $extra");

        $ret = [];
        foreach ($results as $result) {
            array_push($ret, new static($result));
        }

        return new ModelCollection($ret);
    }

    /**
     * Retrieves a single model that matches given id
     * @param string $table Name of the table
     * @param string $id id
     * @param array|string $columns Columns to be selected
     * @param string $id_col_name Name of id column
     * @return \DbModel\Model
     */
    public static function find($table, $id, $id_col_name = 'id', $columns = '*')
    {
        $query = new Query(
            static::$database,
            static::$host,
            static::$username,
            static::$password,
            static::$port
        );
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $result = $query->run("SELECT {$columns} FROM {$table} WHERE {$id_col_name} = '{$id}' LIMIT 1");
        return new static($result[0]);
    }

    /**
     * Counts the number of models in corresponding database table
     * @param string $table Name of the table
     * @param string $conditions Condition to be applied
     * @return int
     */
    public static function count($table, $conditions = '1')
    {
        return (int)static::where($table, $conditions, 'count(*) as count')[0]->count;
    }

    /**
     * Checks if a model exists or not
     * @param string $table Name of the table
     * @param string $conditions Condition to be applied
     * @return bool
     */
    public static function exists($table, $conditions = '1')
    {
        return static::where($table, $conditions, 'count(*) as count')[0]->count > 0;
    }

    /**
     * Stores a model to corresponding database table
     * @param string $table Name of table of related model
     * @return void
     */
    public function store($table)
    {
        foreach ($this->data as &$datum) {
            $datum = '"' . $datum . '"';
        }
        $keys = implode(',', array_keys($this->data));
        $values = implode(',', $this->data);
        $sql = "INSERT INTO $table($keys) VALUES($values)";

        $query = new Query(
            static::$database,
            static::$host,
            static::$username,
            static::$password,
            static::$port
        );

        $query->run($sql);
    }

    /**
     * Stores a model to corresponding database table
     * @param string $table Name of table of related model
     * @return void
     */
    public function create($table)
    {
        $this->store($table);
    }

    /**
     * Stores a model to corresponding database table
     * @param string $table Name of table of related model
     * @return void
     */
    public function save($table)
    {
        $this->store($table);
    }

    /**
     * Updates an existing model to corresponding database table
     * @param string $table Name of table of related model
     * @param string $condition Condition to be applied
     * or array of conditions
     * @return void
     */
    public function update($table, $condition)
    {
        $keys = array_keys($this->data);
        $changes = array_map(function ($val) {
            return "$val = {$this->data[$val]}";
        }, $keys);
        $changes = implode(', ', $changes);

        $sql = "UPDATE $table SET $changes WHERE $condition";

        $query = new Query(
            static::$database,
            static::$host,
            static::$username,
            static::$password,
            static::$port
        );

        $query->run($sql);
    }

    /**
     * Deletes an existing model from corresponding database table
     * @param string $table Name of table of related model
     * @param string $condition Condition to be applied
     * @return void
     */
    public static function delete($table, $condition)
    {
        $sql = "DELETE FROM $table WHERE $condition";

        $query = new Query(
            static::$database,
            static::$host,
            static::$username,
            static::$password,
            static::$port
        );

        $query->run($sql);
    }

    /**
     * Updates an existing model to corresponding database table by primary key
     * @param string $table Name of table of related model
     * @param string $primary_key Name of the primary key
     * @return void
     */
    public function updateById($table, $primary_key = 'id')
    {
        $this->update($table, "$primary_key = \"{$this->data[$primary_key]}\"");
    }

    /**
     * Deletes an existing model from corresponding database table by primary key
     * @param string $table Name of table of related model
     * @param string $primary_key Name of the primary key
     * @return void
     */
    public function deleteById($table, $primary_key = 'id')
    {
        static::delete($table, "$primary_key = \"{$this->data[$primary_key]}\"");
    }

    /**
     * Retrieves related models using one to many relationship
     * @param string $table Name of table of related model
     * @param string $for_col Foreign key column name
     * @param string $ref_col Referenced key column name
     * @param string|array $columns Columns to be selected
     * @param string $conditions Conditions to be applied
     * @return ModelCollection
     */
    public function oneToMany($table, $for_col, $ref_col = 'id', $conditions = '1', $columns = '*')
    {
        return static::where($table, "$for_col = '{$this->data[$ref_col]}' AND ($conditions)", $columns);
    }

    /**
     * Retrieve related model using many to one relationship
     * @param string $table Name of table of related model
     * @param string $for_col Foreign key column name
     * @param string $ref_col Referenced key column name
     * @param string|array $columns Columns to be selected
     * @return Model
     */
    public function manyToOne($table, $for_col, $ref_col = 'id', $columns = '*')
    {
        return static::find($table, $this->data[$for_col], $ref_col, $columns);
    }

    /**
     * Retrieves related model using many to many relationship
     * @param string $table Name of table of related model
     * @param string $intermediate Name of the intermediate table
     * @param string $table_id Primary key of related model
     * @param string $local_id Primary key of current model
     * @param string $intr_table_ref Reference of id of related model in intermediate table
     * @param string $intr_local_ref Referenced of id of current model in intermediate table
     * @param string $columns Columns to be selected
     * @param string $conditions Condition to be applied
     * @return ModelCollection
     */
    public function manyToMany($table, $intermediate, $intr_local_ref,
                               $intr_table_ref, $local_id = 'id', $table_id = 'id',
                               $conditions = '1', $columns = '*')
    {
        $cols = explode(',', $columns);
        foreach ($cols as $index => $col) {
            $cols[$index] = $table . '.' . $col;
        }
        $columns = implode(',', $cols);

        return static::where(
            "$table INNER JOIN $intermediate ON $table.$table_id = $intermediate.$intr_table_ref",
            "$intermediate.$intr_local_ref = '{$this->data[$local_id]}' AND ($conditions)",
            $columns
        );
    }

    /**
     * PHP's magic getter function
     * @param string $name Name of attribute
     * @return string
     */
    function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * PHP's magic setter function
     * @param string $name Name of attribute
     * @param string $value Value of attribute
     */
    function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Sets a property using builder pattern
     * @param string $name Name of attribute
     * @param string $value Value of attribute
     * @return $this
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return $this->data;
    }
}
