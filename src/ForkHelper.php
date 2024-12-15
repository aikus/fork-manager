<?php

namespace Aikus\ForkManager;

interface ForkHelper
{
    public function fork(): ForkResult;
    public function wait(ForkResult $forkResult): WaitStatus;
}