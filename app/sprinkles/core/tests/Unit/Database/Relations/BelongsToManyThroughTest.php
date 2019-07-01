<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Database\Relations;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Database\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;

/**
 * Tests the BelongsToManyThrough relation.
 */
class BelongsToManyThroughTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testPaginatedQuery()
    {
        // Creates a real BelongsToManyThrough object
        $relation = $this->getRelation();

        // We need to define a mock base query, because Eloquent\Builder will pass through many calls
        // to this underlying Query\Builder object.
        $baseQuery = m::mock(QueryBuilder::class);
        $builder = m::mock(EloquentBuilder::class, [$baseQuery])->makePartial();

        $related = $relation->getRelated();
        $related->shouldReceive('getQualifiedKeyName')->once()->andReturn('users.id');

        $builder->shouldReceive('withGlobalScope')->once()->andReturnSelf();

        $builder->shouldReceive('limit')->once()->with(2)->andReturnSelf();
        $builder->shouldReceive('offset')->once()->with(1)->andReturnSelf();

        // Mock the collection generated by the constrained query
        $collection = m::mock('Illuminate\Database\Eloquent\Collection');
        $collection->shouldReceive('pluck')->once()->with('id')->andReturn($collection);
        $collection->shouldReceive('toArray')->once()->andReturn([1, 2]);
        $builder->shouldReceive('get')->once()->andReturn($collection);

        // Test the final modification to the original unpaginated query
        $builder->shouldReceive('whereIn')->once()->with('users.id', [1, 2])->andReturnSelf();

        $paginatedQuery = $relation->getPaginatedQuery($builder, 2, 1);
    }

    /**
     * Set up and simulate base expectations for arguments to relationship.
     */
    protected function getRelation()
    {
        // We simulate a BelongsToManyThrough relationship that gets all related users for a specified permission(s).
        $builder = m::mock(EloquentBuilder::class);
        $related = m::mock('Illuminate\Database\Eloquent\Model');
        $related->shouldReceive('getKey')->andReturn(1);
        $related->shouldReceive('getTable')->andReturn('users');
        $related->shouldReceive('getKeyName')->andReturn('id');
        // Tie the mocked builder to the mocked related model
        $builder->shouldReceive('getModel')->andReturn($related);

        // Mock the intermediate role->permission BelongsToMany relation
        $intermediateRelationship = m::mock(BelongsToMany::class);
        $intermediateRelationship->shouldReceive('getTable')->once()->andReturn('permission_roles');
        $intermediateRelationship->shouldReceive('getQualifiedRelatedKeyName')->once()->andReturn('permission_roles.role_id');
        // Crazy pivot query stuff
        $newPivot = m::mock('\Illuminate\Database\Eloquent\Relations\Pivot');
        $newPivot->shouldReceive('getForeignKey')->andReturn('permission_id');
        $intermediateRelationship->shouldReceive('newExistingPivot')->andReturn($newPivot);

        // Expectations for joining the main relation - users to roles
        $builder->shouldReceive('join')->once()->with('role_users', 'users.id', '=', 'role_users.user_id');

        // Expectations for joining the intermediate relation - roles to permissions
        $builder->shouldReceive('join')->once()->with('permission_roles', 'permission_roles.role_id', '=', 'role_users.role_id');
        $builder->shouldReceive('where')->once()->with('permission_id', '=', 1);

        // Now we set up the relationship with the related model.
        return new BelongsToManyThrough(
            $builder,
            $related,
            $intermediateRelationship,
            'role_users',
            'role_id',
            'user_id',
            'relation_name'
        );
    }
}

class EloquentBelongsToManyModelStub extends Model
{
    protected $guarded = [];
}
