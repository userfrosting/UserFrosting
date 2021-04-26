<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Eloquent\Builder as LaravelEloquentBuilder;
use Illuminate\Database\Eloquent\Concerns\QueriesRelationships;
use UserFrosting\Support\Exception\BadRequestException;

/**
 * UserFrosting's custom Eloquent Builder Class.
 *
 * @author Alex Weissman (https://alexanderweissman.com)à
 * @todo This class overlaped `Illuminate\Database\Eloquent\Builder` trait and was adapted for it. It should be further improved and maybe made a Trait to reflect Laravel Trait.
 */
class EloquentBuilder extends LaravelEloquentBuilder
{
    use QueriesRelationships;
    
    /**
     * Find a model by its primary integer-valued key or throw an exception if
     * something other than a nonnegative integer is provided.
     *
     * @param int   $id
     * @param array $columns
     *
     * @throws \UserFrosting\Support\Exception\BadRequestException
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function findInt($id, $columns = ['*'])
    {
        if (!isset($id) || (filter_var($id, FILTER_VALIDATE_INT) === false)) {
            throw new BadRequestException();
        }

        return $this->find($id, $columns);
    }
}
