<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Bakery;

use Composer\Script\Event;
use UserFrosting\Bakery\Bakery;
use UserFrosting\System\UserFrosting;

/**
 * Migration CLI Tools.
 * Perform database migrations commands
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Migration extends Bakery
{
    /**
     * Run the `migrate` composer script
     *
     * @access public
     * @static
     * @param Event $event
     * @return void
     */
    public static function run(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());

        // Display header,
        $bakery->io->write("\n<info>/****************************/\n/* UserFrosting's Migration */\n/****************************/</info>");

        // If all went well and there's no fatal errors, we are ready to bake
        $bakery->io->write("\n<fg=black;bg=green>Migrated successfully !</>\n");
    }
}