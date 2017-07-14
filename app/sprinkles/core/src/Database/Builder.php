<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Query\Builder as LaravelBuilder;

/**
 * UFBuilder Class
 *
 * The base Eloquent data model, from which all UserFrosting data classes extend.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Builder extends LaravelBuilder
{
    /**
     * Perform a "begins with" pattern match on a specified column in a query.
     *
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function beginsWith($field, $value)
    {
        return $this->where($field, 'LIKE', "$value%");
    }

    /**
     * Perform an "ends with" pattern match on a specified column in a query.
     *
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function endsWith($field, $value)
    {
        return $this->where($field, 'LIKE', "%$value");
    }

    /**
     * Excluding certain columns in a query.
     *
     * @param $query
     * @param $value array|string The column(s) to exclude
     */
    public function exclude($value = array())
    {
        $columns = Capsule::schema()->getColumnListing($this->table);
        return $this->select( array_diff( $columns,(array) $value) );
    }

    /**
     * Perform a pattern match on a specified column in a query.
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function like($field, $value)
    {
        return $this->where($field, 'LIKE', "%$value%");
    }

    /**
     * Perform a pattern match on a specified column in a query.
     * @param $query
     * @param $field string The column to match
     * @param $value string The value to match
     */
    public function orLike($field, $value)
    {
        return $this->orWhere($field, 'LIKE', "%$value%");
    }
}
