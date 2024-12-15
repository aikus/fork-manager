<?php

namespace Aikus\ForkManager\Test;

use Aikus\ForkManager\ForkManager;
use Aikus\ForkManager\ForkResult;
use Aikus\ForkManager\ReturnableWorker;
use Aikus\ForkManager\WaitStatus;
use Aikus\ForkManager\Worker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aikus\ForkManager\ReturnableWorker
 */
class ReturningWorkerTest extends TestCase
{
    private ?ReturnableWorker $returningWorker = null;
    private ?MockObject $realWorker = null;

    public function setUp(): void
    {
        $this->realWorker = $this->createMock(Worker::class);
        $this->returningWorker = new ReturnableWorker($this->realWorker);
    }

    public function testRun(): void
    {
        $forkResult = new ForkResult(true, 1816, parentPid: 8945);

        $this->realWorker->expects($this->once())->method('run')->with($forkResult);

        $this->returningWorker->run($forkResult);
    }

    public function testAfterFinish(): void
    {
        $waitStatus = new WaitStatus(true, 0, 31209);
        $manager = $this->createMock(ForkManager::class);

        $manager->expects($this->once())->method("addWorker")->with($this->returningWorker)->willReturn($manager);
        $this->realWorker->expects($this->once())->method("afterFinish")->with($manager, $waitStatus);

        $this->returningWorker->afterFinish($manager, $waitStatus);
    }
}