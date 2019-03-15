<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Query\Builder as LaravelBuilder;

/**
 * UserFrosting's custom Query Builder Class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Builder extends LaravelBuilder
{
    /**
     * @var array List of excluded columns
     */
    protected $excludedColumns = null;

    /**
     * Perform a "begins with" pattern match on a specified column in a query.
     *
     * @param  string $field The column to match
     * @param  string $value The value to match
     * @return self
     */
    public function beginsWith($field, $value)
    {
        return $this->where($field, 'LIKE', "$value%");
    }

    /**
     * Perform an "ends with" pattern match on a specified column in a query.
     *
     * @param  string $field The column to match
     * @param  string $value The value to match
     * @return self
     */
    public function endsWith($field, $value)
    {
        return $this->where($field, 'LIKE', "%$value");
    }

    /**
     * Add columns to be excluded from the query.
     *
     * @param  array|string $column The column(s) to exclude
     * @return self
     */
    public function exclude($column)
    {
        $column = is_array($column) ? $column : func_get_args();

        $this->excludedColumns = array_merge((array) $this->excludedColumns, $column);

        return $this;
    }

    /**
     * Perform a pattern match on a specified column in a query.
     *
     * @param  string $field The column to match
     * @param  string $value The value to match
     * @return self
     */
    public function like($field, $value)
    {
        return $this->where($field, 'LIKE', "%$value%");
    }

    /**
     * Perform a pattern match on a specified column in a query.
     *
     * @param  string $field The column to match
     * @param  string $value The value to match
     * @return self
     */
    public function orLike($field, $value)
    {
        return $this->orWhere($field, 'LIKE', "%$value%");
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array                          $columns
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
            $this->removeExcludedSelectColumns();
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return collect($results);
    }

    /**
     * Remove excluded columns from the select column list.
     */
    protected function removeExcludedSelectColumns()
    {
        // Convert current column list and excluded column list to fully-qualified list
        $this->columns = $this->convertColumnsToFullyQualified($this->columns);
        $excludedColumns = $this->convertColumnsToFullyQualified($this->excludedColumns);

        // Remove any explicitly referenced excludable columns
        $this->columns = array_diff($this->columns, $excludedColumns);

        // Replace any remaining wildcard columns (*, table.*, etc) with a list
        // of fully-qualified column names
        $this->columns = $this->replaceWildcardColumns($this->columns);

        $this->columns = array_diff($this->columns, $excludedColumns);
    }

    /**
     * Find any wildcard columns ('*'), remove it from the column list and replace with an explicit list of columns.
     *
     * @param  array $columns
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
     * @param  array $columns
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
     * @param  string $table
     * @return array
     */
    protected function getQualifiedColumnNames($table = null)
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        return $this->convertColumnsToFullyQualified($schema->getColumnListing($table), $table);
    }

    /**
     * Fully qualify any unqualified columns in a list with this builder's table name.
     *
     * @param  array  $columns
     * @param  string $table
     * @return array
     */
    protected function convertColumnsToFullyQualified($columns, $table = null)
    {
        if (is_null($table)) {
            $table = $this->from;
        }

        array_walk($columns, function (&$item, $key) use ($table) {
            if (strpos($item, '.') === false) {
                $item = "$table.$item";
            }
        });

        return $columns;
    }
}
