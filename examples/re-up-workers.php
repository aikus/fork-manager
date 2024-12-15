<?php


require_once "vendor/autoload.php";

$listener = function (int $id, \Aikus\ForkManager\ForkResult $result): void {
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
};

function buildWorker(): \Aikus\ForkManager\Worker
{
    static $id = 0;
    global $listener;
    return new \Aikus\ForkManager\ReturnableWorker(
        new \Aikus\ForkManager\CallableWorker($listener, [++$id])
    );
}

$manager = new Aikus\ForkManager\ForkManager(2);
$manager->addWorker(buildWorker())
    ->addWorker(buildWorker())
    ->dispatch();