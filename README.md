ForkManager is a library for simplifying work with running handlers in parallel processes.

It is assumed that this library will help automate the launch of the same type of tasks in cron or monitor child processes in daemons.
# Install

# Fast start
The easiest way to run child processes sequentially is as follows:
```php
use Aikus\ForkManager\ForkManager;
use Aikus\ForkManager\CallableWorker;
use Aikus\ForkManager\ForkResult;

$id = 0;

$manager = new ForkManager(1);
$manager->addWorker(new CallableWorker(function (ForkResult $result): void {
    $id = 1;
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
}))
->addWorker(new CallableWorker(function (ForkResult $result): void {
    $id = 2;
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
}))
->addWorker(new CallableWorker(function (ForkResult $result): void {
    $id = 3;
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
}))
->addWorker(new CallableWorker(function (ForkResult $result): void {
    $id = 4;
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
}))
->addWorker(new CallableWorker(function (ForkResult $result): void {
    $id = 5;
    $sleepTime = rand(1, 5);
    echo "start $id (pid: " . $result->getMyPid() . "). Wait $sleepTime second" . ($sleepTime != 1 ? 's' : '') . PHP_EOL;
    sleep($sleepTime);
    echo "$id is finishing" . PHP_EOL;
}))
->dispatch();
```
As a result, you will see a similar output:
```
start 1 (pid: 10215). Wait 5 seconds
1 is finishing
start 2 (pid: 10216). Wait 5 seconds
2 is finishing
start 3 (pid: 10219). Wait 5 seconds
3 is finishing
start 4 (pid: 10220). Wait 5 seconds
4 is finishing
start 5 (pid: 10224). Wait 1 second
5 is finishin
```
You can run this example yourself by running `php examples/one-by-one.php` at the root of this library.

In this example, we create a fork manager object by calling `new ForkManager(1);`. We pass him the limit of simultaneously running child processes.
Next, by calling `addWorker`, we add handlers to the manager's waiting queue. To make a worker out of an arbitrary closure, we wrapped it in a `CallableWorker` object.
To start executing handlers, you need to call `dispatch()`. This call is synchronous and will return control only after the last worker is completed.

If we want to run two (or more) workers in parallel, then we need to create a manager object with a large limit, for example `new ForkManager(2)`. In this case, the output will be something like this:
```
start 1 (pid: 12369). Wait 1 second
start 2 (pid: 12370). Wait 5 seconds
1 is finishing
start 3 (pid: 12372). Wait 3 seconds
3 is finishing
start 4 (pid: 12373). Wait 2 seconds
2 is finishing
start 5 (pid: 12374). Wait 3 seconds
4 is finishing
5 is finishing
```
You can check for yourself by running the command `php examples/parallel-two-worker.php` at the root of this library.

If you need to run all the workers at once, you can leave the constructor parameter empty or pass 0.
# Workers
## The base worker
A worker is an object that is responsible for performing work in a child process. Any worker should implement the interface below.
```php
interface Worker
{
    public function setUp(): void;
    public function run(ForkResult $forkResult): void;
    public function afterFinish(ForkManager $manager, WaitStatus $status): void;
}
```
Methods `setUp()` and `run(For Result $for Result)` are executed in a child process. The `setUp` method is called to prepare the worker in the child process (reopen the database connection, close old file descriptors from the parent process, etc).
The `run` method should perform the main useful function of the process. It receives an object of the `ForkResult` class, which contains information about the result of the fork call.

The `afterFinish' method is called in the parent process after the child process completes. Accepts as a parameter the `ForkManager` object that started and waited for the worker to complete, and the `WaitStatus` object, which contains available information about the child process - its pid, completion status, and completion flag (it is always true in this call).

You can always implement this interface for your workers, but if you need to implement only the payload without preparing the child process and processing its completion, then there are two simpler ways.
## The worker function
The easiest way is to wrap an anonymous function in an object of the `CallableWorker` class. The function is passed to the constructor of this class. This function will be executed when the `run` method is called. When calling this function, the 'ForkResult` parameter will be passed.
```php
new CallableWorker(function (ForkResult $result): void {
    //do something
}
```
## The worker template
You can also create your own worker class, making it an heir to the `WorkerTemplate` class. 'WorkerTemplate' is an abstract class that requires the implementation of only the `run` method.
```php
use Aikus\ForkManager\WorkerTemplate;
use Aikus\ForkManager\ForkResult;

class MyWorker extends WorkerTemplate{
    public function run(ForkResult $forkResult) : void
    {
        //do something
    }
}
```
## The returned worker
The library also implements a returnable worker, i.e. a worker that, after completing its child process, spawns the child process again. This is a worker of the `ReturnableWorker` class, which accepts your worker with a payload as the only parameter.
```php
use Aikus\ForkManager\ForkManager;
use Aikus\ForkManager\ReturnableWorker;
$manager = new ForkManager(10);
$manager->addWorker(new ReturnableWorker(new MyWorker()));
```
You can see for yourself how the returned workers work by running the command `php examples/re-up-workers.php` at the root of this library. The result should be something like this:
```
start 1 (pid: 27795). Wait 2 seconds
start 2 (pid: 27796). Wait 4 seconds
1 is finishing
start 1 (pid: 27798). Wait 4 seconds
2 is finishing
start 2 (pid: 27800). Wait 5 seconds
1 is finishing
start 1 (pid: 27801). Wait 4 seconds
2 is finishing
start 2 (pid: 27802). Wait 4 seconds
1 is finishing
start 1 (pid: 27803). Wait 2 seconds
^C
```
# Asynchronous operation
In the example from the "Quick Start" section, the `dispatch` call was used for the manager's work, which blocks execution until the last child process completes. Which is suitable for most use cases.
The library provides the possibility of asynchronous operation. See the example.
```php
use Aikus\ForkManager\ForkManager;
$manager = new ForkManager(3);
// add any workers
while(!$manager->isEmpty()) {
    //do something
    $manager->asyncTick();
    //do something
    usleep(5000);
}
```
The manager's `isEmpty` method returns `false` if the manager has more tasks to complete or there are incomplete child processes. `true` - if there is neither one nor the other.
The `asyncTick` method performs the minimum unit of the manager's work - it polls all existing processes for completion and calls 'afterFinish` for each completed one. Then, if possible, it launches new workers from the waiting queue.

You can see how the library works in the asynchronous version by making calls:
```shell
php examples/async-one-by-one.php
php examples/async-parallel-two-worker.php
php examples/async-re-up-workers.php
```