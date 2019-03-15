<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Account\Database\Models\Group;

/**
 * Seeder for the default groups
 */
class DefaultGroups extends BaseSeed
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $groups = $this->getGroups();

        foreach ($groups as $group) {
            // Don't save if already exist
            if (Group::where('slug', $group->slug)->first() == null) {
                $group->save();
            }
        }
    }

    /**
     * @return array Groups to seed
     */
    protected function getGroups()
    {
        return [
            new Group([
                'slug'        => 'terran',
                'name'        => 'Terran',
                'description' => 'The terrans are a young species with psionic potential. The terrans of the Koprulu sector descend from the survivors of a disastrous 23rd century colonization mission from Earth.',
                'icon'        => 'sc sc-terran'
            ]),
            new Group([
                'slug'        => 'zerg',
                'name'        => 'Zerg',
                'description' => 'Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.',
                'icon'        => 'sc sc-zerg'
            ]),
            new Group([
                'slug'        => 'protoss',
                'name'        => 'Protoss',
                'description' => 'The protoss, a.k.a. the Firstborn, are a sapient humanoid race native to Aiur. Their advanced technology complements and enhances their psionic mastery.',
                'icon'        => 'sc sc-protoss'
            ])
        ];
    }
}
