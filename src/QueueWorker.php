<?php

namespace JobQueue;

use Psr\Log\LoggerInterface;

class QueueWorker
{
    private $queue;

    private $runner;

    private $logger;

    public function __construct(Queue $queue, JobRunner $runner, LoggerInterface $logger = null)
    {
        $this->queue = $queue;
        $this->runner = $runner;
        $this->logger = $logger;
    }

    /**
     * Continuously loop over queue, processing jobs
     */
    public function processQueue()
    {
        do {
            $this->processNextJob();
        } while (true);
    }

    /**
     * Process the next job from the queue
     */
    public function processNextJob()
    {
        $this->log("Checking for new job...");

        if ($job = $this->queue->fetchNextJob()) {
            $this->executeJob($job);
        }
    }

    private function executeJob($job)
    {
        $this->log('Executing job ' . $job->getId());

        if ($this->runner->runJob($job)) {

            $this->queue->delete($job);

        } else {

            $this->log("Job failed");

            if ($this->queue->countReserves($job) < 3) {
                $this->log("Putting back in queue");

                $this->queue->release($job);
            } else {
                $this->log("Putting job on hold");

                $this->queue->bury($job);
            }
        }
    }

    private function log($message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }
}