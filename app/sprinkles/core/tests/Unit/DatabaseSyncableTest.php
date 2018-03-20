<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use stdClass;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use UserFrosting\Sprinkle\Core\Database\Relations\HasManySyncable;

class DatabaseSyncableTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider syncMethodHasManyListProvider
     */
    public function testSyncMethod($list)
    {
        $relation = $this->getRelation();

        // Simulate determination of related key from builder
        $relation->getRelated()->shouldReceive('getKeyName')->once()->andReturn('id');

        // Simulate fetching of current relationships (1,2,3)
        $query = m::mock(stdClass::class);
        $relation->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('pluck')->once()->with('id')->andReturn(new Collection([1, 2, 3]));

        // withoutGlobalScopes will get called exactly 3 times
        $relation->getRelated()->shouldReceive('withoutGlobalScopes')->times(3)->andReturn($query);

        // Test deletions of items removed from relationship (1)
        $query->shouldReceive('whereIn')->once()->with('id', [1])->andReturn($query);
        $query->shouldReceive('delete')->once()->andReturn($query);

        // Test updates to existing items in relationship (2,3)
        $query->shouldReceive('where')->once()->with('id', 2)->andReturn($query);
        $query->shouldReceive('update')->once()->with(['id' => 2, 'species' => 'Tyto'])->andReturn($query);
        $query->shouldReceive('where')->once()->with('id', 3)->andReturn($query);
        $query->shouldReceive('update')->once()->with(['id' => 3, 'species' => 'Megascops'])->andReturn($query);

        // Test creation of new items ('x')
        $model = $this->expectCreatedModel($relation, [
            'id' => 'x'
        ]);
        $model->shouldReceive('getAttribute')->with('id')->andReturn('x');

        $this->assertEquals(['created' => ['x'], 'deleted' => [1], 'updated' => [2,3]], $relation->sync($list));
    }

    /**
     * Set up and simulate base expectations for arguments to relationship.
     */
    protected function getRelation()
    {
        $builder = m::mock('Illuminate\Database\Eloquent\Builder');
        $builder->shouldReceive('whereNotNull')->with('table.foreign_key');
        $builder->shouldReceive('where')->with('table.foreign_key', '=', 1);
        $related = m::mock('Illuminate\Database\Eloquent\Model');
        $builder->shouldReceive('getModel')->andReturn($related);
        $parent = m::mock('Illuminate\Database\Eloquent\Model');
        $parent->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $parent->shouldReceive('getCreatedAtColumn')->andReturn('created_at');
        $parent->shouldReceive('getUpdatedAtColumn')->andReturn('updated_at');
        return new HasManySyncable($builder, $parent, 'table.foreign_key', 'id');
    }

    public function syncMethodHasManyListProvider()
    {
        return [
            // First test set
            [
                // First argument
                [
                    [
                        'id' => 2,
                        'species' => 'Tyto'
                    ],
                    [
                        'id' => 3,
                        'species' => 'Megascops'
                    ],
                    [
                        'id' => 'x'
                    ]
                ]
            ]
            // Additional test sets here
        ];
    }

    protected function expectNewModel($relation, $attributes = null)
    {
        $relation->getRelated()->shouldReceive('newInstance')->once()->with($attributes)->andReturn($model = m::mock(Model::class));
        $model->shouldReceive('setAttribute')->with('foreign_key', 1)->andReturn($model);
        return $model;
    }

    protected function expectCreatedModel($relation, $attributes)
    {
        $model = $this->expectNewModel($relation, $attributes);
        $model->shouldReceive('save')->andReturn($model);
        return $model;
    }
}
