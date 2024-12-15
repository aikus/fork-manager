<?php

namespace Aikus\ForkManager;

class ForkResult
{
    public function __construct(
        private readonly bool $isChild,
        private readonly int $myPid,
        private readonly ?int $parentPid = null,
        private readonly ?int $childPid = null,
    )
    {
    }

    /**
     * @return int|null
     */
    public function getChildPid(): ?int
    {
        return $this->childPid;
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->isChild;
    }

    /**
     * @return int
     */
    public function getMyPid(): int
    {
        return $this->myPid;
    }

    /**
     * @return int|null
     */
    public function getParentPid(): ?int
    {
        return $this->parentPid;
    }
}
