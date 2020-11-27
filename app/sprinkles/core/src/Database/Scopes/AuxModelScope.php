<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AuxModelScope implements Scope
{
    /**
     * @var string
     */
    protected $auxType;

    /**
     * @var array
     */
    protected $auxColumns;

    /**
     * @param string $auxType
     * @param array  $auxColumns
     */
    public function __construct($auxType, array $auxColumns = [])
    {
        $this->auxType = $auxType;
        $this->auxColumns = $auxColumns;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $primaryKeyName = $model->getQualifiedKeyName();
        $table = $model->getTable();

        $auxModel = new $this->auxType();
        $auxTable = $auxModel->getTable();

        // Determine columns to load from base table and aux table
        $builder->addSelect(
            "$table.*"
        );

        foreach ($this->auxColumns as $column) {
            $builder->addSelect("$auxTable.$column as $column");
        }

        // Join on matching aux records
        $builder->leftJoin($auxTable, function ($join) use ($auxTable, $primaryKeyName) {
            $join->on("$auxTable.id", '=', $primaryKeyName);
        });
    }
}
