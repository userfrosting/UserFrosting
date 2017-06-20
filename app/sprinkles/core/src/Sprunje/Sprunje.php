<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Sprunje;

use Carbon\Carbon;
use League\Csv\Writer;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Support\Exception\BadRequestException;
use Valitron\Validator;

/**
 * Sprunje
 *
 * Implements a versatile API for sorting, filtering, and paginating an Eloquent query builder.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Sprunje
{
    /**
     * @var UserFrosting\Sprinkle\Core\Util\ClassMapper
     */
    protected $classMapper;

    /**
     * Name of this Sprunje, used when generating output files.
     *
     * @var string
     */
    protected $name = '';

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * Default HTTP request parameters
     *
     * @var array[string]
     */
    protected $options = [
        'sorts' => [],
        'filters' => [],
        'lists' => [],
        'size' => 'all',
        'page' => null,
        'format' => 'json'
    ];

    /**
     * Fields to allow filtering upon.
     *
     * @var array[string]
     */
    protected $filterable = [];

    /**
     * Fields to allow listing (enumeration) upon.
     *
     * @var array[string]
     */
    protected $listable = [];

    /**
     * Fields to allow sorting upon.
     *
     * @var array[string]
     */
    protected $sortable = [];

    /**
     * List of fields to exclude when processing an "_all" filter.
     *
     * @var array[string]
     */
    protected $excludeForAll = [];

    /**
     * Separator to use when splitting filter values to treat them as ORs.
     */
    protected $orSeparator = '||';

    /**
     * Constructor.
     *
     * @param ClassMapper $classMapper
     * @param mixed[] $options
     */
    public function __construct($classMapper, $options)
    {
        $this->classMapper = $classMapper;

        // Validation on input data
        $v = new Validator($options);
        $v->rule('array', ['sorts', 'filters', 'lists']);
        $v->rule('regex', 'sorts.*', '/asc|desc/i');
        $v->rule('regex', 'size', '/all|[0-9]+/i');
        $v->rule('integer', 'page');
        $v->rule('regex', 'format', '/json|csv/i');

        // TODO: translated rules
        if(!$v->validate()) {
            $e = new BadRequestException();
            foreach ($v->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        $this->options = array_replace_recursive($this->options, $options);

        $this->query = $this->baseQuery();
    }

    /**
     * Extend the query by providing a callback.
     *
     * @param callable $callback A callback which accepts and returns a Builder instance.
     */
    public function extendQuery(callable $callback)
    {
        $this->query = $callback($this->query);
    }

    /**
     * Run the query and build a CSV object by flattening the resulting collection.  Ignores any pagination.
     *
     * @return SplTempFileObject
     */
    public function getCsv()
    {
        // Apply filters
        $this->applyFilters();

        // Apply sorts
        $this->applySorts();

        $collection = collect($this->query->get());

        // Perform any additional transformations on the dataset
        $this->applyTransformations($collection);

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $columnNames = [];

        // Flatten collection while simultaneously building the column names from the union of each element's keys
        $collection->transform(function ($item, $key) use (&$columnNames) {
            $item = array_dot($item->toArray());
            foreach ($item as $itemKey => $itemValue) {
                if (!in_array($itemKey, $columnNames)) {
                    $columnNames[] = $itemKey;
                }
            }
            return $item;
        });

        $csv->insertOne($columnNames);

        // Insert the data as rows in the CSV document
        $collection->each(function ($item) use ($csv, $columnNames) {
            $row = [];
            foreach ($columnNames as $itemKey) {
                // Only add the value if it is set and not an array.  Laravel's array_dot sometimes creates empty child arrays :(
                // See https://github.com/laravel/framework/pull/13009
                if (isset($item[$itemKey]) && !is_array($item[$itemKey])) {
                    $row[] = $item[$itemKey];
                } else {
                    $row[] = '';
                }
            }

            $csv->insertOne($row);
        });

        return $csv;
    }


    /**
     * Get lists of values for specified fields in 'lists' option, calling a custom lister callback when appropriate.
     *
     * @return array
     */
    public function getListable()
    {
        $result = [];
        foreach ($this->listable as $name) {

            // Determine if a custom filter method has been defined
            $methodName = 'list'.studly_case($name);

            if (method_exists($this, $methodName)) {
                $result[$name] = $this->$methodName();
            } else {
                $result[$name] = $this->getColumnValues($name);
            }
        }

        return $result;
    }

    /**
     * Get the underlying QueryBuilder object.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the underlying QueryBuilder object.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
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
        $total = $this->count();

        // Apply filters
        $this->applyFilters();

        // Count filtered total
        $totalFiltered = $this->countFiltered();

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
            'rows' => $collection->values()->toArray(),
            'listable' => $this->getListable()
        ];

        return $result;
    }

    /**
     * Execute the query and build the results, and append them in the appropriate format to the response.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function toResponse(Response $response)
    {
        $format = $this->options['format'];

        if ($format == 'csv') {
            $result = $this->getCsv();

            // Prepare response
            $settings = http_build_query($this->options);
            $date = Carbon::now()->format('Ymd');
            $response = $response->withAddedHeader('Content-Disposition', "attachment;filename=$date-{$this->name}-$settings.csv");
            $response = $response->withAddedHeader('Content-Type', 'text/csv; charset=utf-8');
            return $response->write($result);
        // Default to JSON
        } else {
            $result = $this->getResults();
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Apply any filters from the options, calling a custom filter callback when appropriate.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters()
    {
        foreach ($this->options['filters'] as $name => $value) {
            // Check that this filter is allowed
            if (($name != '_all') && !in_array($name, $this->filterable)) {
                $e = new BadRequestException();
                $e->addUserMessage('VALIDATE.SPRUNJE.BAD_FILTER', ['name' => $name]);
                throw $e;
            }
            // Since we want to match _all_ of the fields, we wrap the field callback in a 'where' callback
            $this->query = $this->query->where(function ($fieldQuery) use ($name, $value) {
                return $this->buildFilterQuery($fieldQuery, $name, $value);
            });
        }

        return $this->query;
    }

    /**
     * Match any filter in `filterable`.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterAll($query, $value)
    {
        foreach ($this->filterable as $name) {
            if (studly_case($name) != 'all' && !in_array($name, $this->excludeForAll)) {
                // Since we want to match _any_ of the fields, we wrap the field callback in a 'orWhere' callback
                $query = $query->orWhere(function ($fieldQuery) use ($name, $value) {
                    return $this->buildFilterQuery($fieldQuery, $name, $value);
                });
            }
        }

        return $query;
    }

    /**
     * Build the filter query for a single field.
     *
     * @param Builder $query
     * @param string $name
     * @param mixed $value
     * @return Builder
     */
    protected function buildFilterQuery($query, $name, $value)
    {
        $methodName = 'filter'.studly_case($name);

        // Determine if a custom filter method has been defined
        if (method_exists($this, $methodName)) {
            $query = $this->$methodName($query, $value);
        } else {
            return $this->buildFilterDefaultFieldQuery($query, $name, $value);
        }

        return $query;
    }

    /**
     * Perform a 'like' query on a single field, separating the value string on the or separator and
     * matching any of the supplied values.
     *
     * @param Builder $query
     * @param string $name
     * @param mixed $value
     * @return Builder
     */
    protected function buildFilterDefaultFieldQuery($query, $name, $value)
    {
        // Default filter - split value on separator for OR queries
        // and search by column name
        $values = explode($this->orSeparator, $value);
        foreach ($values as $value) {
            $query = $query->orLike($name, $value);
        }

        return $query;
    }

    /**
     * Apply any sorts from the options, calling a custom sorter callback when appropriate.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applySorts()
    {
        foreach ($this->options['sorts'] as $name => $direction) {
            // Check that this sort is allowed
            if (!in_array($name, $this->sortable)) {
                $e = new BadRequestException();
                $e->addUserMessage('VALIDATE.SPRUNJE.BAD_SORT', ['name' => $name]);
                throw $e;
            }

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

    /**
     * Apply pagination based on the `page` and `size` options.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
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
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function applyTransformations($collection)
    {
        return $collection;
    }

    /**
     * Set the initial query used by your Sprunje.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function baseQuery();

    /**
     * Get the unpaginated count of items (before filtering) in this query.
     *
     * @return int
     */
    protected function count()
    {
        return $this->query->count();
    }

    /**
     * Get the unpaginated count of items (after filtering) in this query.
     *
     * @return int
     */
    protected function countFiltered()
    {
        return $this->query->count();
    }

    /**
     * Returns a list of distinct values for a specified column.
     * Formats results to have a "value" and "text" attribute.
     *
     * @param string $column
     * @return array
     */
    protected function getColumnValues($column)
    {
        $rawValues = $this->query->select($column)->distinct()->orderBy($column, 'asc')->get();
        $values = [];
        foreach ($rawValues as $raw) {
            $values[] = [
                'value' => $raw[$column],
                'text' => $raw[$column]
            ];
        }
        return $values;
    }
}
