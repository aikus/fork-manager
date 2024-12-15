<?php

namespace Aikus\ForkManager;

class CallableWorker extends WorkerTemplate
{
    private $callback;
    private array $params;

    public function __construct(callable $callback, array $params = [])
    {
        $this->callback = $callback;
        $this->params = $params;
    }

    public function run(ForkResult $forkResult): void
    {
        $params = array_values($this->params);
        $params[] = $forkResult;
        call_user_func_array($this->callback, $params);
    }
}