<?php

namespace Aikus\ForkManager;

class ReturnableWorker implements Worker
{
    public function __construct(private readonly Worker $worker)
    {
    }

    public function run(ForkResult $forkResult): void
    {
        $this->worker->run($forkResult);
    }

    public function afterFinish(ForkManager $manager, WaitStatus $status): void
    {
        $this->worker->afterFinish($manager, $status);
        $manager->addWorker($this);
    }

    public function setUp(): void
    {
        $this->worker->setUp();
    }
}