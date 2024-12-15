<?php

namespace Aikus\ForkManager;

interface Worker
{
    public function setUp(): void;
    public function run(ForkResult $forkResult): void;
    public function afterFinish(ForkManager $manager, WaitStatus $status): void;
}