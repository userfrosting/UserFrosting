<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Eloquent\Builder as LaravelEloquentBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

/**
 * UserFrosting's custom Eloquent Builder Class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class EloquentBuilder extends LaravelEloquentBuilder
{
    /**
     * Add subselect queries to sum the relations.
     *
     * @param  mixed $relations
     * @return self
     */
    public function withSum($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        return $this->withAggregate($relations, 'SUM');
    }

    /**
     * Add subselect queries to max the relations.
     *
     * @param  mixed $relations
     * @return self
     */
    public function withMax($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        return $this->withAggregate($relations, 'MAX');
    }

    /**
     * Add subselect queries to min the relations.
     *
     * @param  mixed $relations
     * @return self
     */
    public function withMin($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        return $this->withAggregate($relations, 'MIN');
    }

    /**
     * Add subselect queries to min the relations.
     *
     * @param  mixed $relations
     * @return self
     */
    public function withAvg($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        return $this->withAggregate($relations, 'AVG');
    }

    /**
     * use the MySQL aggregate functions including AVG COUNT, SUM, MAX and MIN.
     *
     * @param  array  $relations
     * @param  string $function
     * @return self
     */
    public function withAggregate($relations, $function = 'COUNT')
    {
        if (is_null($this->query->columns)) {
            $this->query->select([$this->query->from.'.*']);
        }

        $function = Str::lower($function);

        foreach ($this->parseWithRelations($relations) as $name => $constraints) {
            // First we will determine if the name has been aliased using an "as" clause on the name
            // and if it has we will extract the actual relationship name and the desired name of
            // the resulting column. This allows multiple counts on the same relationship name.
            $segments = explode(' ', $name);

            unset($alias);

            if (count($segments) == 3 && Str::lower($segments[1]) == 'as') {
                list($name, $alias) = [$segments[0], $segments[2]];
            }

            // set the default column as * or primary key
            $column = ($function == 'count') ? '*' : $this->model->getKeyName();

            if (Str::contains($name, '~')) {
                list($name, $column) = explode('~', $name);
            }

            $relation = $this->getRelationWithoutConstraints($name);

            $expression = new Expression($function.'('.$this->query->getGrammar()->wrap($column).')');

            // Here we will get the relationship aggregate query and prepare to add it to the main query
            // as a sub-select. First, we'll get the "has" query and use that to get the relation
            // count query. We will normalize the relation name then append the aggregate type as the name.
            $query = $relation->getRelationExistenceQuery(
               $relation->getRelated()->newQuery(),
               $this,
               $expression
            );

            $query->callScope($constraints);

            $query->mergeConstraintsFrom($relation->getQuery());

            // Finally we will add the proper result column alias to the query and run the subselect
            // statement against the query builder. Then we will return the builder instance back
            // to the developer for further constraint chaining that needs to take place on it.
            $column = snake_case(isset($alias) ? $alias : $name).'_'.$function;

            $this->selectSub($query->toBase(), $column);
        }

        return $this;
    }
}
