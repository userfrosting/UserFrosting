<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Sprunje;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Builder as Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * SprunjeTest class.
 * Tests a basic Sprunje.
 */
class SprunjeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testSprunjeApplyFiltersDefault()
    {
        $sprunje = new SprunjeStub([
            'filters' => [
                'species' => 'Tyto',
            ],
        ]);

        $builder = $sprunje->getQuery();

        // Need to mock the new Builder instance that Laravel spawns in the where() closure.
        // See https://stackoverflow.com/questions/20701679/mocking-callbacks-in-laravel-4-mockery
        $builder->shouldReceive('newQuery')->andReturn(
                $subBuilder = m::mock(Builder::class, function ($subQuery) {
                    $subQuery->makePartial();
                    $subQuery->shouldReceive('orLike')->with('species', 'Tyto')->once()->andReturn($subQuery);
                })
            );

        $sprunje->applyFilters($builder);
    }

    public function testSprunjeApplySortsDefault()
    {
        $sprunje = new SprunjeStub([
            'sorts' => [
                'species' => 'asc',
            ],
        ]);

        $builder = $sprunje->getQuery();
        $builder->shouldReceive('orderBy')->once()->with('species', 'asc');
        $sprunje->applySorts($builder);
    }

    public function testSprunjeApplyTransformations(): void
    {
        $sprunje = new SprunjeStub([]);

        $builder = $sprunje->getQuery();
        $builder->shouldReceive('count')->andReturn(2);
        $builder->shouldReceive('get')->andReturn([
            ['id' => '1', 'name' => 'Foo'],
            ['id' => '2', 'name' => 'Bar'],
        ]);

        $result = $sprunje->getModels();

        $this->assertSame([
            ['id' => '1', 'name' => 'FooFoo'],
            ['id' => '2', 'name' => 'BarBar'],
        ], $result[2]->toArray());
    }
}

class SprunjeStub extends Sprunje
{
    protected $filterable = [
        'species',
    ];

    protected $sortable = [
        'species',
    ];

    public function __construct($options)
    {
        $classMapper = new ClassMapper();
        parent::__construct($classMapper, $options);
    }

    protected function baseQuery()
    {
        // We use a partial mock for Builder, because we need to be able to run some of its actual methods.
        // For example, we need to be able to run the `where` method with a closure.
        $builder = m::mock(Builder::class);
        $builder->makePartial();

        return $builder;
    }

    protected function applyTransformations($collection)
    {
        $collection = $collection->map(function ($item, $key) {
            $item['name'] = $item['name'] . $item['name'];

            return $item;
        });

        return $collection;
    }
}

class SprunjeTestModelStub extends Model
{
    protected $table = 'table';
}
