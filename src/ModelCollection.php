<?php
/**
 * Created by PhpStorm.
 * User: Parvez
 * Date: 9/16/2017
 * Time: 6:23 PM
 */

namespace DbModel;


use Traversable;

class ModelCollection implements \IteratorAggregate
{
    private $models = [];

    /**
     * ModelCollection constructor.
     * @param array $models
     */
    public function __construct($models = [])
    {
        $this->models = $models;
    }

    /**
     * @param Model $model
     */
    public function addModel(Model $model)
    {
        $this->models[] = $model;
    }

    /**
     * @param $arrays
     * @return ModelCollection
     */
    public static function createFromArray($arrays)
    {
        $models = [];
        foreach ($arrays as $array) {
            $models[] = is_a($array, Model::class) ? $array : Model::createFromArray($array);
        }

        return new ModelCollection($models);
    }

    /**
     * @param $columns
     * @return array
     */
    public function only($columns)
    {
        if (is_array($columns)) {
            $instances = [];
            foreach ($this->models as $model) {
                $instance = [];
                foreach ($columns as $column) {
                    $instance[] = $model->{$column};
                }
                $instances[] = $instance;
            }
        } else {
            $instances = [];
            foreach ($this->models as $model) {
                $instances[] = $model->{$columns};
            }
        }
        return $instances;
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
        $ins = implode(",", $this->only($ref_col));
        return Model::where($table, "$for_col IN '($ins)' AND ($conditions)", $columns);
    }

    /**
     * Retrieve related model using many to one relationship
     * @param string $table Name of table of related model
     * @param string $for_col Foreign key column name
     * @param string $ref_col Referenced key column name
     * @param string|array $columns Columns to be selected
     * @return ModelCollection
     */
    public function manyToOne($table, $for_col, $ref_col = 'id', $columns = '*')
    {
        $ins = implode(",", $this->only($for_col));
        if (is_array($columns)) {
            $columns = implode(",", $columns);
        }
        return Model::where($table, "$ref_col IN ($ins)", $columns);
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
        $ins = implode(",", $this->only($local_id));
        $cols = explode(',', $columns);
        foreach ($cols as $index => $col) {
            $cols[$index] = $table . '.' . $col;
        }
        $columns = implode(',', $cols);

        return Model::where(
            "$table INNER JOIN $intermediate ON $table.$table_id = $intermediate.$intr_table_ref",
            "$intermediate.$intr_local_ref IN ($ins) AND ($conditions)",
            $columns
        );
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Iterator An instance of an object implementing <b>Iterator</b> or
     * <b>Iterator</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        foreach ($this->models as $model) {
            yield $model;
        }
    }

    public function count()
    {
        return count($this->models);
    }
}