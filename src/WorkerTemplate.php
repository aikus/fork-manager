<?php

namespace Aikus\ForkManager;

abstract class WorkerTemplate implements Worker
{
    public function setUp(): void
    {
    }

    public function afterFinish(ForkManager $manager, WaitStatus $status): void
    {
    }
}