<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use UserFrosting\App\Bakery\HelloCommand;
use UserFrosting\Sprinkle\SprinkleReceipe;

class MyApp implements SprinkleReceipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'My Application';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getBakeryCommands(): array
    {
        return [
            new HelloCommand(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [];
    }
}
