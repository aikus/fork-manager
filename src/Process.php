<?php

namespace Aikus\ForkManager;

class Process
{
    public function __construct(
        private readonly ForkResult $forkResult,
        private readonly Worker $worker,
    )
    {
    }

    /**
     * @return ForkResult
     */
    public function getForkResult(): ForkResult
    {
        return $this->forkResult;
    }

    /**
     * @return Worker
     */
    public function getWorker(): Worker
    {
        return $this->worker;
    }
}