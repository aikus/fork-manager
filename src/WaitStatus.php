<?php

namespace Aikus\ForkManager;

class WaitStatus
{
    public function __construct(
        private readonly bool $finished,
        private readonly int $status,
        private readonly int $pid,
    )
    {
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}