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
    public function tearDown()
    {
        m::close();
    }

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

        $sprunje->applyFilters();
        $query = $sprunje->getQuery();

        $this->assertEquals('select * from "table" where ("species" LIKE ?)', $query->toSql());
    }

    function testSortMethod()
    {
        $sprunje = new SprunjeWithSortsStub([
            'sorts' => [
                'species' => 'asc'
            ]
        ]);

        $sprunje->applySorts();
        $query = $sprunje->getQuery();

        $this->assertEquals('select * from "table" order by "species" asc', $query->toSql());
    }

    function testSprunjeCallsBuilderWhereMethod()
    {
        $connection = $this->getMockBuilder('Illuminate\Database\ConnectionInterface')->getMock();
        $grammar = new \Illuminate\Database\Query\Grammars\Grammar;
        $processor = $this->getMockBuilder('Illuminate\Database\Query\Processors\Processor')->getMock();
        $builder = $this->getMockBuilder(Builder::class)
                    ->setConstructorArgs([$connection, $grammar, $processor])
                    ->setMethods(['orLike'])
                    ->getMock();
        
        $builder->expects($this->once())->method('where');
        //$builder->expects($this->once())->method('orLike');
        /*
        $connection = m::mock('Illuminate\Database\ConnectionInterface');
        $grammar = new \Illuminate\Database\Query\Grammars\Grammar;
        $processor = m::mock('Illuminate\Database\Query\Processors\Processor');
        $builder = m::mock(Builder::class, [$connection, $grammar, $processor])
            ->makePartial();
            
        $builder->shouldReceive('from')->atLeast()->times(0);
        $builder->shouldReceive('where')->atLeast()->times(1);
        */

        $sprunje = new SprunjeWithFiltersStub([
            'filters' => [
                'species' => 'Tyto'
            ]
        ]);

        $sprunje->setQuery($builder);
        $sprunje->applyFilters();
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
        $connection = m::mock('Illuminate\Database\ConnectionInterface');
        $grammar = new \Illuminate\Database\Query\Grammars\Grammar;
        $processor = m::mock('Illuminate\Database\Query\Processors\Processor');
        $builder = new Builder($connection, $grammar, $processor);
        
        return $builder->from('table');
    }
}

class SprunjeWithFiltersStub extends SprunjeStub
{
    protected $filterable = [
        'species'
    ];
}

class SprunjeWithSortsStub extends SprunjeStub
{
    protected $sortable = [
        'species'
    ];
}

class SprunjeTestModelStub extends Model
{
    protected $table = 'table';
}

