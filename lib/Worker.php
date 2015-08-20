<?php
namespace ShellQueue;
use PhpAmqpLib\Connection\AMQPConnection;

class Worker
{
    /**
     * Number of threads to start
     *
     * @var integer
     */
    private $number_of_threads;
    private $host;
    private $user;
    private $password;

    public function __construct($host, $user, $password, $number_of_threads)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->number_of_threads = $number_of_threads;
    }

    public function run()
    {
        for ($i = 1; $i <= $this->number_of_threads; ++ $i) {
            $pid = pcntl_fork();

            if (! $pid) {
                $connection = new AMQPConnection($this->host, 5672, $this->user, $this->password);
                $channel = $connection->channel();
                $channel->queue_declare('shell-queue', false, false, false, false);
                $callback = function ($msg) {
                    echo " Running Job \n";
                    $cmd = $msg->body;
                    $result = shell_exec($cmd);
                    echo " Finished Job \n";
                };

                $channel->basic_consume('shell-queue', '', false, true, false, false, $callback);

                while (count($channel->callbacks)) {
                    $channel->wait();
                }
            }
        }

        while (pcntl_waitpid(0, $status) != - 1) {
            $status = pcntl_wexitstatus($status);
            echo "Child $status completed\n";
        }
    }
}