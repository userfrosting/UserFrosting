<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use stdClass;
use Mockery as m;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use UserFrosting\Sprinkle\Core\Database\Relations\HasManySyncable;

class DatabaseSyncableTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider syncMethodListProvider
     */
     /*
    public function testSyncMethodSyncsIntermediateTableWithGivenArray($list)
    {
        $relation = $this->getMockBuilder('Illuminate\Database\Eloquent\Relations\BelongsToMany')->setMethods(['attach', 'detach'])->setConstructorArgs($this->getRelationArguments())->getMock();

        $query = m::mock('stdClass');
        $query->shouldReceive('from')->once()->with('user_role')->andReturn($query);
        $query->shouldReceive('where')->once()->with('user_id', 5)->andReturn($query);
        $relation->getQuery()->shouldReceive('getQuery')->andReturn($mockQueryBuilder = m::mock('StdClass'));
        $mockQueryBuilder->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('pluck')->once()->with('role_id')->andReturn(new BaseCollection([5, 2, 3]));
        $relation->expects($this->once())->method('attach')->with($this->equalTo('x'), $this->equalTo([]), $this->equalTo(false));
        $relation->expects($this->once())->method('detach')->with($this->equalTo([5]));
        $relation->getRelated()->shouldReceive('touches')->andReturn(false);
        $relation->getParent()->shouldReceive('touches')->andReturn(false);

        //print_r($list);
        $this->assertEquals(['attached' => ['x'], 'detached' => [5], 'updated' => []], $relation->sync($list));
    }

    public function getRelationArguments()
    {
        $parent = m::mock('Illuminate\Database\Eloquent\Model');
        $parent->shouldReceive('getKey')->andReturn(5);
        $parent->shouldReceive('getCreatedAtColumn')->andReturn('created_at');
        $parent->shouldReceive('getUpdatedAtColumn')->andReturn('updated_at');
        $builder = m::mock('Illuminate\Database\Eloquent\Builder');
        $related = m::mock('Illuminate\Database\Eloquent\Model');
        $builder->shouldReceive('getModel')->andReturn($related);
        $related->shouldReceive('getTable')->andReturn('roles');
        $related->shouldReceive('getKeyName')->andReturn('id');
        $related->shouldReceive('newPivot')->andReturnUsing(function () {
            $reflector = new ReflectionClass('Illuminate\Database\Eloquent\Relations\Pivot');
            return $reflector->newInstanceArgs(func_get_args());
        });
        $builder->shouldReceive('join')->once()->with('user_role', 'roles.id', '=', 'user_role.role_id');
        $builder->shouldReceive('where')->once()->with('user_role.user_id', '=', 5);
        return [$builder, $parent, 'user_role', 'user_id', 'role_id', 'relation_name'];
    }

    public function syncMethodListProvider()
    {
        return [
            [[2, 3, 'x']],
            [['2', '3', 'x']],
        ];
    }
    */

    /**
     * @dataProvider syncMethodHasManyListProvider
     */
    public function testSyncMethod($list)
    {
        $relation = $this->getRelation();

        // Simulate determination of related key from builder
        $relation->getRelated()->shouldReceive('getKeyName')->andReturn('id');

        // Simulate fetching of current relationships (1,2,3)
        $query = m::mock('stdClass');
        $relation->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('pluck')->once()->with('id')->andReturn(new BaseCollection([1, 2, 3]));

        // Test deletions of items removed from relationship (1)
        $relation->getRelated()->shouldReceive('withoutGlobalScopes')->andReturn($query);
        $query->shouldReceive('whereIn')->with('id', [1])->andReturn($query);
        $query->shouldReceive('delete')->andReturn($query);

        // Test updates to existing items in relationship (2,3)
        $relation->getRelated()->shouldReceive('withoutGlobalScopes')->andReturn($query);        
        $query->shouldReceive('where')->with('id', 2)->andReturn($query);
        $query->shouldReceive('update')->with(['id' => 2, 'species' => 'Tyto'])->andReturn($query);
        $query->shouldReceive('where')->with('id', 3)->andReturn($query);
        $query->shouldReceive('update')->with(['id' => 3, 'species' => 'Megascops'])->andReturn($query);

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
