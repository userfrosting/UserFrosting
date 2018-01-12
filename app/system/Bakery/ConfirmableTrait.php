<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Closure;

/**
 * Confirmable Trait. Include method used to confirm action
 *
 * @author Louis Charette
 */
trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     * This method only asks for confirmation in production.
     *
     * @param  string  $warning
     * @param  \Closure|bool|null  $callback
     * @return bool
     */
    public function confirmToProceed($force = false, $warning = 'Application In Production Mode!', $callback = null)
    {
        // Use default callback if argument is null
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;

        // Process callback to determine if we should ask for confirmation
        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;

        if ($shouldConfirm & !$force) {

            // Display warning
            $this->io->warning($warning);

            // Ask confirmation
            $confirmed = $this->io->confirm('Do you really wish to run this command?', false);

            if (! $confirmed) {
                $this->io->comment('Command Cancelled!');
                return false;
            }
        }

        return true;
    }

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback()
    {
        return function () {
            return $this->isProduction();
        };
    }
}
