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

    public function addModel(Model $model)
    {
        $this->models[] = $model;
    }

    public function createFromArray($arrays)
    {
        $models = [];
        foreach ($arrays as $array) {
            $models[] = Model::createFromArray($array);
        }

        return new ModelCollection($models);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        foreach ($this->models as $model) {
            yield $model;
        }
    }
}