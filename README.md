# Job Queue

Work in progress library to easily push jobs on to a queue so they can be processed outside of the web request. Currently only supports Beanstalk queues.

## Usage

Firstly you need to create an instance of a Queue.
```
$pheanstalk = new Pheanstalk\Pheanstalk('localhost');
$queue = new JobQueue\BeanstalkQueue($pheanstalk, 'email_queue');
```

### Creating jobs

You can then push Jobs on to the Queue.
```
$job = new \JobQueue\Job('EmailClient', ['client_id' => $id]);
$queue->push($job);
```

This assumes that you have an `EmailClient` class with a method named `handle` that takes an argument of client_id.

### Processing the queue

This will listen for new jobs to be pushed to the queue, and then execute them.
```
$runner = new JobQueue\JobRunner();
$worker = new JobQueue\QueueWorker($queue, $runner);

$worker->processQueue();
```

To just process the next job you can do this.
```
$worker->processNextJob();
```

You can see what the QueueWorker is doing by passing in a logger that implements psr/log.
```
$runner = new JobQueue\JobRunner();
$logger = new Monolog\Logger('queue');
$worker = new JobQueue\QueueWorker($queue, $runner, $logger);
```