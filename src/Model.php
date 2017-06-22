<?php

namespace Database;

include "Query.php";

class Model
{
    /**
     * @var string Name of the Database
     */
    public static $database = 'db';
    private $data;

    /**
     * Model constructor.
     * @param $data array
     */
    public function __construct($data)
    {
        if (is_null($data)) {
            $this->data = [];
        } else {
            $this->data = $data;
        }
    }

    /**
     * Retrieves all instances of given model
     * @param string $table Name of the table
     * @param string $columns Columns to be selected
     * @return \Database\Model[]
     */
    public static function all($table, $columns = '*')
    {
        return static::where($table, '1', $columns);
    }

    /**
     * Retrieves the models that matches the given conditions
     * @param string $table Name of the table
     * @param string $conditions Condition to be applied
     * @param string $columns Columns to be selected
     * @return \Database\Model[]
     */
    public static function where($table, $conditions = '1', $columns = '*')
    {
        $query = new Query(static::$database);
        $results = $query->run("SELECT $columns FROM $table WHERE $conditions");

        $ret = [];
        foreach ($results as $result) {
            array_push($ret, new static($result));
        }

        return $ret;
    }

    /**
     * Retrieves a single model that matches given id
     * @param string $table Name of the table
     * @param string $id id
     * @param string $columns Columns to be selected
     * @param string $id_col_name Name of id column
     * @return \Database\Model
     */
    public static function find($table, $id, $id_col_name = 'id', $columns = '*')
    {
        $query = new Query(static::$database);
        $result = $query->run("SELECT {$columns} FROM {$table} WHERE {$id_col_name} = '{$id}' LIMIT 1");
        return new static($result[0]);
    }

    /**
     * Retrieves related models using one to many relationship
     * @param string $table Name of table of related model
     * @param string $for_col Foreign key column name
     * @param string $ref_col Referenced key column name
     * @param string $columns Columns to be selected
     * @return \Database\Model[]
     */
    public function oneToMany($table, $for_col, $ref_col = 'id', $columns = '*')
    {
        return static::where($table, "$for_col = '{$this->data[$ref_col]}'", $columns);
    }

    /**
     * Retrieve related model using many to one relationship
     * @param string $table Name of table of related model
     * @param string $for_col Foreign key column name
     * @param string $ref_col Referenced key column name
     * @param string $columns Columns to be selected
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
     * @return \Database\Model[]
     */
    public function manyToMany($table, $intermediate, $intr_table_ref, $intr_local_ref, $table_id = 'id', $local_id = 'id', $columns = '*')
    {
        $cols = explode(',', $columns);
        foreach ($cols as $index => $col) {
            $cols[$index] = $table . '.' . $col;
        }
        $columns = implode(',', $cols);

        return static::where(
            "$table INNER JOIN $intermediate ON $table.$table_id = $intermediate.$intr_table_ref",
            "$intermediate.$intr_local_ref = '{$this->data[$local_id]}'",
            $columns);
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
}
