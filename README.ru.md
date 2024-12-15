ForkManager - библиотека для упрощения работы с запуском обработчиков в параллельных процессах.
 
Предполагается, что данная библиотека будет помогать автоматизировать запуск однотипных задач в cron'ах или следить за дочерними процессами в демонах.

# Установка
# Быстрый старт
Самый простой способ запустить последовательно работу дочерних процессах такой:
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
В результате вы увидите подобный вывод:
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
Вы можете сами выполнить данный пример выполнив `php examples/one-by-one.php` в корне этой библиотеке.

В данном примере мы создаём объект менеджера форков вызовом `new ForkManager(1);`. Мы ему передаём лимит одновременно запущенных дочерних процессов.
Далее вызовами `addWorker` мы добавляем обработчики в осередь ожидания менеджера. Чтобы сделать из произвольного замыкания воркер, мы его обернули в объект `CallableWorker`.
Для начала выполнения обработчиков необходимо вызвать `dispatch()`. Этот вызов синхронный и вернёт управление только после того, как последний воркер завершиться.

Если мы хотим запустить параллельно два (или более) воркеров, то необходимо создавать объект менеджера с большим лимитом, например `new ForkManager(2)`. В этом случае будет примерно такой вывод:
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
Вы можете сами проверить выполнив команду `php examples/parallel-two-worker.php` в корне этой библиотеке.

Если необходимо запустить все воркеры сразу, то можно оставить параметр конструктора пустым или передать 0.
# Воркеры
## Базовый воркер
Воркер, это объект, который отвечает за выполнение работы в дочернем процессе. Любой воркер должен реализовывать интерфейс ниже.
```php
interface Worker
{
    public function setUp(): void;
    public function run(ForkResult $forkResult): void;
    public function afterFinish(ForkManager $manager, WaitStatus $status): void;
}
```
Методы `setUp()` и `run(ForkResult $forkResult)` выполняются в дочернем процессе. Метод `setUp` вызывается для подготовки воркера в дочернем процессе (переоткрыть подключение к БД, закрыть старые файловые дескрипторы из родительского процесса etc).
Метод `run` должен выполнять основную полезную функцию процесса. Он получает объект класса `ForkResult`, который в себе содержит информацию о результате выполнения вызова fork.

Метод `afterFinish` вызывается в родительском процессе после завершения дочернего. Принимает в качестве параметра объект `ForkManager`, который запустил и дождался завершения воркера, и объект `WaitStatus`, который содержит доступную информацию о дочернем процессе - его pid, статус завершения, и флаг завершения (он всегда true в этом вызове).

Вы всегда можете реализовать этот интерфейс для своих воркеров, но если нужно реализовать только полезную нагрузку без подготовки дочернего процесса и обработки его завершения, то есть два более простых способа.

## Функция-воркер
Самый простой способ - это завернуть анонимную функцию в объект класса `CallableWorker`. Функция передаётся в конструктор данного класса. Эта функция будет выполняться при вызове метода `run`. При вызове этой функции будет передан параметр `ForkResult`.
```php
new CallableWorker(function (ForkResult $result): void {
    //do something
}
```

## Воркер-шаблон
Так же можно создать свой собственный класс воркера, сделав его наследником класса `WorkerTemplate`. `WorkerTemplate` - является абстрактным классом, требующим реализации только метода `run`. 
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
## Возвращаемый воркер
В библиотеке реализован так же возвращаемый воркер. Т.е. воркер, который после завершения своего дочернего процесса снова порождает дочерний процесс. Это воркер класса `ReturnableWorker`, который в качестве единственного параметра принимает ваш воркер с полезной нагрузкой.
```php
use Aikus\ForkManager\ForkManager;
use Aikus\ForkManager\ReturnableWorker;
$manager = new ForkManager(10);
$manager->addWorker(new ReturnableWorker(new MyWorker()));
```
Вы можете сами посмотреть как работают возвращаемые воркеры выполнив команду `php examples/re-up-workers.php` в корне этой библиотеки. В результате должен получиться примерно такой результат:
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

# Асинхронная работа
В примере из радела "Быстрый старт" для работы менеджера был использован вызов `dispatch`, который блокирует выполнение до тех пор, пока не завершит работу последний дочерний процесс. Что подходит для большинства вариантов использование.
В библиотеке предусмотрена возможность асинхронной работы. См. пример.
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
Метод менеджера `isEmpty` возвращает `false`, если у менеджера есть ещё задачи на выполнение или есть не завершённые дочерние процессы. `true` - если нет ни того, ни другого.
Метод `asyncTick` проводит минимальную единицу работы менеджера - опрашивает все существующие процессы на предмет завершения и вызывает `afterFinish` у каждого завершённого. Затем, если есть такая возможность, запускает новые воркеры из очереди ожидания.

Вы можете посмотреть как работает библиотека в асинхронном варианте, сделав вызовы:
```shell
php examples/async-one-by-one.php
php examples/async-parallel-two-worker.php
php examples/async-re-up-workers.php
```