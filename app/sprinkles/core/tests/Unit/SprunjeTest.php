<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use Illuminate\Database\Capsule\Manager as DB;
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
    protected $schemaName = 'integration';

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Boot database
        $this->ci->db;
    }

    public function tearDown()
    {
        m::close();
    }

    function testSprunjeApplyFiltersDefault()
    {
        $sprunje = new SprunjeStub([
            'filters' => [
                'species' => 'Tyto'
            ]
        ]);

        $builder = $sprunje->getQuery();

        // Need to mock the new Builder instance that Laravel spawns in the where() closure.
        // See https://stackoverflow.com/questions/20701679/mocking-callbacks-in-laravel-4-mockery
        $builder->shouldReceive('newQuery')->andReturn(
                $subBuilder = m::mock(Builder::class, function ($subQuery) {
                    $subQuery->makePartial();
                    $subQuery->shouldReceive('from')->with('table')->once()->andReturn($subQuery);
                    $subQuery->shouldReceive('orLike')->with('species', 'Tyto')->once()->andReturn($subQuery);
                })
            );

        $sprunje->applyFilters();
    }

    function testSprunjeApplySortsDefault()
    {
        $sprunje = new SprunjeStub([
            'sorts' => [
                'species' => 'asc'
            ]
        ]);

        $builder = $sprunje->getQuery();
        $builder->shouldReceive('orderBy')->once()->with('species', 'asc');
        $sprunje->applySorts();
    }

}

class SprunjeStub extends Sprunje
{
    protected $filterable = [
        'species'
    ];

    protected $sortable = [
        'species'
    ];

    public function __construct($options)
    {
        $classMapper = new ClassMapper();
        parent::__construct($classMapper, $options);
    }

    /**
     * Allows us to test calls on a protected method.
     */
    public function applyFilters()
    {
        return parent::applyFilters();
    }

    protected function baseQuery()
    {
        // We use a partial mock for Builder, because we need to be able to run some of its actual methods.
        // For example, we need to be able to run the `where` method with a closure.
        $connection = DB::connection('integration');
        $builder = m::mock(Builder::class, [$connection]);

        $builder->makePartial();
        return $builder->from('table');
    }
}

class SprunjeTestModelStub extends Model
{
    protected $table = 'table';
}

