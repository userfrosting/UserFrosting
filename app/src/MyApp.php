<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use UserFrosting\App\Bakery\HelloCommand;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

/**
 * The Sprinkle Recipe.
 *
 * @see https://learn.userfrosting.com/sprinkles/recipe
 */
class MyApp implements
    SprinkleRecipe,
    BakeryRecipe
{
    /**
     * Return the Sprinkle name.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'My Application';
    }

    /**
     * Return the Sprinkle dir path.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#path
     *
     * @return string
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * Return dependent sprinkles.
     *
     * First one will be loaded first, with this sprinkle being last.
     * Dependent sprinkle dependencies will also be loaded recursively.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#dependent-sprinkles
     *
     * @return class-string<SprinkleRecipe>[]
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
            AdminLTE::class,
        ];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#routes
     *
     * @return class-string<\UserFrosting\Routes\RouteDefinitionInterface>[]
     */
    public function getRoutes(): array
    {
        return [
            MyRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions class.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#services
     *
     * @return class-string<\UserFrosting\ServicesProvider\ServicesProviderInterface>[]
     */
    public function getServices(): array
    {
        return [
            MyServices::class,
        ];
    }

    /**
     * Return an array of all registered Bakery Commands.
     *
     * @see https://learn.userfrosting.com/sprinkles/recipe#bakeryrecipe
     *
     * @return class-string<\Symfony\Component\Console\Command\Command>[]
     *
     * @codeCoverageIgnore
     */
    public function getBakeryCommands(): array
    {
        return [
            HelloCommand::class,
        ];
    }
}
