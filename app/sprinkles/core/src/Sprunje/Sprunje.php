<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Sprunje
 *
 * Implements a versatile API for sorting, filtering, and paginating an Eloquent query builder.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Sprunje
{

    protected $classMapper;

    protected $sortable = [];

    protected $filterable = [];

    protected $options = [
        'sorts' => [],
        'filters' => [],
        'size' => 'all',
        'page' => null
    ];

    protected $query;

    /**
     * Constructor.
     *
     * @param ClassMapper $classMapper
     * @param mixed[] $options
     */
    public function __construct($classMapper, $options)
    {
        $this->classMapper = $classMapper;

        $this->options = array_replace_recursive($this->options, $options);

        // TODO: validation on input data
        
        $this->query = $this->baseQuery();
    }

    /**
     * Extend the query by providing a callback.
     */
    public function extendQuery(callable $callback)
    {
        $this->query = $callback($this->query);
    }

    /**
     * Executes the sprunje query, applying all sorts, filters, and pagination.
     *
     * Returns an array containing `count` (the total number of rows, before filtering), `count_filtered` (the total number of rows after filtering),
     * and `rows` (the filtered result set).
     * @return mixed[]
     */
    public function getResults()
    {
        // Count unfiltered total
        $total = $this->query->count();

        // Apply filters
        $this->applyFilters();

        // Count filtered total
        $totalFiltered = $this->query->count();

        // Apply sorts
        $this->applySorts();

        // Paginate
        $this->applyPagination();

        $collection = collect($this->query->get());

        // Perform any additional transformations on the dataset
        $this->applyTransformations($collection);

        // Return sprunjed results
        $result = [
            'count' => $total,
            'count_filtered' => $totalFiltered,
            'rows' => $collection->values()->toArray()
        ];

        return $result;
    }

    /**
     * Apply any filters from the options, calling a custom filter callback when appropriate.
     */
    protected function applyFilters()
    {
        foreach ($this->options['filters'] as $name => $value) {
            // Determine if a custom filter method has been defined
            $filterMethodName = 'filter'.studly_case($name);

            if (method_exists($this, $filterMethodName)) {
                $this->query = $this->$filterMethodName($this->query, $value);
            } else {
                $this->query = $this->query->like($name, $value);
            }
        }

        return $this->query;
    }

    /**
     * Apply any sorts from the options, calling a custom filter callback when appropriate.
     */
    protected function applySorts()
    {
        foreach ($this->options['sorts'] as $name => $direction) {
            // Determine if a custom filter method has been defined
            $methodName = 'sort'.studly_case($name);

            if (method_exists($this, $methodName)) {
                $this->query = $this->$methodName($this->query, $direction);
            } else {
                $this->query = $this->query->orderBy($name, $direction);
            }
        }

        return $this->query;
    }

    protected function applyPagination()
    {
        if (
            ($this->options['page'] !== null) &&
            ($this->options['size'] !== null) &&
            ($this->options['size'] != 'all')
        ) {
            $offset = $this->options['size']*$this->options['page'];
            $this->query = $this->query
                            ->skip($offset)
                            ->take($this->options['size']);
        }

        return $this->query;
    }

    /**
     * Set any transformations you wish to apply to the collection, after the query is executed.
     */
    protected function applyTransformations($collection)
    {
        return $collection;
    }

    /**
     * Set the initial query used by your Sprunje.
     */
    abstract protected function baseQuery();
}
