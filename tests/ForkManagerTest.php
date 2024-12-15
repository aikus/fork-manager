<?php

namespace Aikus\ForkManager\Test;

use Aikus\ForkManager\ForkHelper;
use Aikus\ForkManager\ForkManager;
use Aikus\ForkManager\ForkResult;
use Aikus\ForkManager\WaitStatus;
use Aikus\ForkManager\Worker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aikus\ForkManager\ForkManager
 */
class ForkManagerTest extends TestCase
{
    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWorkerBehavior(): void
    {
        $forkHelper = $this->createStub(ForkHelper::class);
        $forkResult = new ForkResult(
            true,
            4846,
            parentPid: 6038,
        );
        $forkHelper->method("fork")->willReturn($forkResult);
        $manager = new ForkManager(1, $forkHelper);
        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())->method("run")->with($forkResult);

        $manager->addWorker($worker)
            ->dispatch();
    }

    public function testParentBehavior(): void
    {
        $forkHelper = $this->createMock(ForkHelper::class);
        $forkResult = new ForkResult(
            false,
            4846,
            childPid: 6038,
        );
        $waitStatus = new WaitStatus(
            true,
            0,
            6038
        );
        $forkHelper->expects($this->any())->method("fork")->willReturn($forkResult);
        $forkHelper->expects($this->once())->method("wait")->with($forkResult)->willReturn($waitStatus);
        $manager = new ForkManager(1, $forkHelper);
        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())->method("afterFinish")->with($manager, $waitStatus);

        $manager->addWorker($worker)
            ->dispatch();
    }
}