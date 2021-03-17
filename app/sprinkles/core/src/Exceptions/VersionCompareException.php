<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions;

use LogicException;

/**
 * Version Compare Exception. Used when a version contraint is not matched.
 */
class VersionCompareException extends LogicException
{
    /** @var string */
    protected $version;

    /** @var string */
    protected $contraint;

    /**
     * @return self
     */
    public function setContraint(string $contraint)
    {
        $this->contraint = $contraint;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return self
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    public function getContraint(): string
    {
        return $this->contraint;
    }
}
