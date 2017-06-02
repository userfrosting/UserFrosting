<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;


use Mockery as m;
use UserFrosting\Tests\TestCase;
//use UserFrosting\Tests\DatabaseMigrations;
use UserFrosting\Tests\DatabaseTransactions;
use UserFrosting\Sprinkle\Core\Database\Builder as Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * SprunjeTest class.
 * Tests a basic Sprunje.
 *
 * @extends TestCase
 */
class SprunjeTest extends TestCase
{
    function testGetQueryMethod()
    {
        $sprunje = new SprunjeStub([]);
        $query = $sprunje->getQuery();

        $this->assertEquals('select * from "table"', $query->toSql());
    }

    function testFilterMethod()
    {
        $sprunje = new SprunjeWithFiltersStub([
            'filters' => [
                'species' => 'Tyto'
            ]
        ]);

        $query = $sprunje->applyFilters();

        $this->assertEquals('select * from "table" where ("species" LIKE ?)', $query->toSql());
    }
}

class SprunjeStub extends Sprunje
{
    public function __construct($options)
    {
        $classMapper = new ClassMapper();
        parent::__construct($classMapper, $options);
    }
    
    public function applyFilters()
    {
        return parent::applyFilters();
    }
    
    protected function baseQuery()
    {
        $grammar = new \Illuminate\Database\Query\Grammars\Grammar;
        $processor = m::mock('Illuminate\Database\Query\Processors\Processor');
        $builder = new Builder(m::mock('Illuminate\Database\ConnectionInterface'), $grammar, $processor);

        return $builder->from('table');
    }
}

class SprunjeWithFiltersStub extends SprunjeStub
{
    protected $filterable = [
        'species'
    ];
}

class SprunjeTestModelStub extends Model
{
    protected $table = 'table';
}

