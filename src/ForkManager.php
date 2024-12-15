<?php

namespace Aikus\ForkManager;

class ForkManager
{
    /**
     * @var Worker[]
     */
    private array $workerPool = [];
    /**
     * @var Process[]
     */
    private array $processes = [];
    public function __construct(
        private readonly int $processRunningQty = 0,
        private ?ForkHelper $forkHelper = null,

    )
    {
        if(!$this->forkHelper) {
            $this->forkHelper = new DefaultForkHelper();
        }
    }

    public function getWorkerPoolSize(): int
    {
        return count($this->workerPool);
    }

    public function getProcessPoolSize(): int
    {
        return count($this->processes);
    }

    public function isEmpty(): bool
    {
        return $this->getProcessPoolSize() == 0 && $this->getWorkerPoolSize() == 0;
    }

    public function addWorker(Worker $worker): self
    {
        $this->workerPool[] = $worker;
        return $this;
    }

    /**
     * run all workers and return control after finish all
     * @param int $sleepTime sleep time in microseconds
     * @return $this
     */
    public function dispatch(int $sleepTime = 7500): self
    {
        while(!$this->isEmpty()) {
            $this->asyncTick();
            usleep($sleepTime);
        }
        return $this;
    }


    public function asyncTick(): self
    {
        $this->waitProcesses();
        while ($this->canStartWorker() && ($worker = $this->getNextWorker())) {
            if($this->startWorker($worker)) {
                return $this;
            }
        }
        return $this;
    }

    private function waitProcesses(): void
    {
        foreach ($this->processes as $key => $process) {
            $waitStatus = $this->forkHelper->wait($process->getForkResult());
            if($waitStatus->isFinished()) {
                $process->getWorker()->afterFinish($this, $waitStatus);
                unset($this->processes[$key]);
                $this->processes = array_values($this->processes);
                $this->workerPool = array_values($this->workerPool);
                break;
            }
        }
    }

    private function canStartWorker(): bool
    {
        return $this->processRunningQty == 0 || count($this->processes) < $this->processRunningQty;
    }

    private function getNextWorker(): ?Worker
    {
        return array_shift($this->workerPool);
    }

    private function startWorker(Worker $worker): bool
    {
        $forkResult = $this->forkHelper->fork();
        if($forkResult->isChild()) {
            $this->workerPool = [];
            $this->processes = [];
            $worker->setUp();
            $worker->run($forkResult);
            return true;
        }
        $this->processes[] = new Process($forkResult, $worker);
        return false;
    }
}