<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration as NewMigration;
use Symfony\Component\Console\Style\SymfonyStyle;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Abstract Migration class.
 *
 * @deprecated since 4.2.0 Use `UserFrosting\Sprinkle\Core\Database\Migration` instead
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Migration extends NewMigration
{
    /**
     *    Constructor
     *
     *    @param Builder $schema The schema builder
     *    @param SymfonyStyle $io The SymfonyStyle instance
     */
    public function __construct(Builder $schema = null, SymfonyStyle $io = null)
    {
        Debug::debug("`UserFrosting\System\Bakery\Migration` has been deprecated and will be removed in future versions.  Please have your `" . static::class . "` migration extend the base `UserFrosting\Sprinkle\Core\Database\Migration` class instead.");

        parent::__construct($schema);
    }
}
