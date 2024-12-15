<?php

namespace Aikus\ForkManager;

class DefaultForkHelper implements ForkHelper
{
    /**
     * @return ForkResult
     * @throws ForkError
     */
    public function fork(): ForkResult
    {
        $parentPid = getmypid();
        $pid = pcntl_fork();
        if($pid < 0) {
            $errorCode = pcntl_errno();
            throw new ForkError(pcntl_strerror($errorCode), $errorCode);
        }
        if($pid) {
            return new ForkResult(
                false,
                $parentPid,
                childPid: $pid,
            );
        }
        return new ForkResult(
            true,
            getmypid(),
            parentPid: $parentPid
        );
    }

    /**
     * @param ForkResult $forkResult
     * @return WaitStatus
     * @throws ChildrenWorkAsParent
     * @throws ForkError
     */
    public function wait(ForkResult $forkResult): WaitStatus
    {
        if($forkResult->isChild()) {
            throw new ChildrenWorkAsParent();
        }
         $result = pcntl_waitpid($forkResult->getChildPid(), $status, WNOHANG);
        if($result < 0) {
            $errorCode = pcntl_errno();
            throw new ForkError(pcntl_strerror($errorCode), $errorCode);
        }
        return new WaitStatus(
            $result > 0,
            $status,
            $forkResult->getChildPid(),
        );
    }
}