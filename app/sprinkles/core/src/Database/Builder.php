<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder as LaravelBuilder;

/**
 * UFBuilder Class
 *
 * The base Eloquent data model, from which all UserFrosting data classes extend.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Builder extends LaravelBuilder
{
    protected $excludedColumns = null;

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
     * Add columns to be excluded from the query.
     *
     * @param $value array|string The column(s) to exclude
     * @return $this
     */
    public function exclude($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->excludedColumns = array_merge((array) $this->excludedColumns, $column);

        return $this;
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

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        // Exclude any explicitly excluded columns
        if (!is_null($this->excludedColumns)) {
            $this->columns = $this->replaceWildcardColumns($this->columns);

            $this->columns = array_diff($this->columns, $this->excludedColumns);
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return collect($results);
    }

    /**
     * Find any wildcard columns ('*'), remove it from the column list and replace with an explicit list of columns.
     *
     * @param array $columns
     * @return array
     */
    protected function replaceWildcardColumns(array $columns)
    {
        $wildcardTables = $this->findWildcardTables($columns);

        foreach ($wildcardTables as $wildColumn => $table) {
            $schemaColumns = $this->getQualifiedColumnNames($table);

            // Remove the `*` or `.*` column and replace with the individual schema columns 
            $columns = array_diff($columns, [$wildColumn]);
            $columns = array_merge($columns, $schemaColumns);
        }

        return $columns;
    }

    /**
     * Return a list of wildcard columns from the list of columns, mapping columns to their corresponding tables.
     *
     * @param array $columns
     * @return array
     */
    protected function findWildcardTables(array $columns)
    {
        $tables = [];

        foreach ($columns as $column) {
            if ($column == '*') {
                $tables[$column] = $this->from;
                continue;
            }

            if (substr($column, -1) == '*') {
                $tableName = explode('.', $column)[0];
                if ($tableName) {
                    $tables[$column] = $tableName;
                }
            }
        }

        return $tables;
    }

    /**
     * Gets the fully qualified column names for a specified table.
     *
     * @param string $table
     * @return array
     */
    protected function getQualifiedColumnNames($table = null)
    {
        if (is_null($table)) {
            $table = $this->from;
        }

        $schema = $this->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        // Don't modify column names if the table is the query's base table
        if ($table == $this->from) {
            return $columns;
        }

        array_walk ($columns, function (&$item, $key) use ($table) {
            $item = "$table.$item";
        });

        return $columns;
    }
}
