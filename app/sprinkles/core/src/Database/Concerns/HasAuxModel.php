<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use UserFrosting\Sprinkle\Core\Database\Scopes\AuxModelScope;

/**
 * Implements event and linking methods for base types from which child subtypes are inherited.
 */
trait HasAuxModel
{
    /**
     * The cache of aux column names for each aux model class.
     *
     * @var array
     */
    protected static $auxColumnsCache = [];

    /**
     * The "booting" method of the model.
     */
    protected static function bootHasAuxModel()
    {
        $model = new static();

        // Load the global query scope
        static::addGlobalScope(new AuxModelScope($model->auxType, $model->getAuxColumns()));

        /**
         * Create a new aux model if necessary, and save the associated aux every time.
         */
        static::saved(function (Model $super) {
            // Link subtype object, creating it if it doesn't already exist
            // and setting the id from the parent model
            $super->linkAuxModel();

            // Save related child object
            if ($super->auxType) {
                $super->aux->save();
            }
        });
    }

    /**
     * Relationship for interacting with aux model.
     */
    public function aux()
    {
        return $this->hasOne($this->auxType, 'id');
    }

    /**
     * Copy the designated attributes from the aux model to the main model.
     */
    public function setAuxAttributes()
    {
        foreach ($this->getAuxColumns() as $column) {
            $this->attributes[$column] = $this->aux->$column;
        }

        return $this;
    }

    /**
     * If this instance doesn't already have a related aux model (either in the db on in the current object), then create one
     */
    protected function linkAuxModel()
    {
        if ($this->auxType) {
            $this->createAuxModelIfNotExists();
            $this->setAuxModelPrimaryKey();
        }

        return $this;
    }

    /**
     * Create and attach aux subtype model if it doesn't exist.
     */
    protected function createAuxModelIfNotExists()
    {
        // We can't check the relationship using exists() because this requires that the model be saved.
        // In our case, the aux model could have been set but not yet saved.
        if (is_null($this->aux)) {
            // Needed to immediately hydrate the relation.  It will actually get saved in the bootHasAuxModel method.
            $this->setRelation('aux', new $this->auxType());
        }

        return $this;
    }

    /**
     * Copy the parent id to the aux model, if the parent has an id at this point but the aux doesn't
     */
    protected function setAuxModelPrimaryKey()
    {
        if (isset($this->id) && !isset($this->aux->id)) {
            $this->aux->id = $this->id;
        }

        return $this;
    }

    /**
     * Set a given attribute on the main model or aux model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getAuxColumns())) {
            $this->linkAuxModel();

            // Set the attribute on the aux model
            $this->aux->setAttribute($key, $value);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the main model or aux model.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->getAuxColumns())) {
            if (is_null($this->aux)) {
                return;
            }

            // Get the attribute from the aux model
            return $this->aux->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Convert the model's attributes to an array, merging in aux attributes.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        $auxAttributes = is_null($this->aux) ? [] : $this->aux->attributesToArray();

        return array_merge($attributes, Arr::only($auxAttributes, $this->getAuxColumns()));
    }

    /**
     * Remove the 'aux' relationship from all array/json representations.
     *
     * @param  array $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        return array_diff_key(parent::getArrayableItems($values), array_flip(['aux']));
    }

    /**
     * Overloaded to eager-load the aux relationship on every query.
     * This avoids an N+1 issue when referencing the aux relationship in other methods.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        $with = array_merge($this->with, ['aux']);

        return $this->newModelQuery()
                    ->with($with)
                    ->withCount($this->withCount);
    }

    /**
     * Determine aux column names, if not explicitly set in model
     */
    protected function getAuxColumns()
    {
        if (isset($this->auxColumns)) {
            return $this->auxColumns;
        }

        $class = $this->auxType;

        if (!isset(static::$auxColumnsCache[$class])) {
            static::cacheAuxColumns($class);
        }

        return static::$auxColumnsCache[$class];
    }

    /**
     * Retrieve and cache column names for a specified aux class.
     *
     * @param string $class
     */
    public static function cacheAuxColumns($class)
    {
        $auxModel = new $class();

        $auxTable = $auxModel->getTable();
        $schema = $auxModel->getConnection()->getSchemaBuilder();

        $auxColumns = $schema->getColumnListing($auxTable);
        $auxColumns = array_diff($auxColumns, ['id']);

        static::$auxColumnsCache[$class] = $auxColumns;
    }
}
