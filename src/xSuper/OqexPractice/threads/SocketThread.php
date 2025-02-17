<?php
declare(strict_types=1);

namespace xSuper\OqexPractice\threads;

use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\Thread;
use xSuper\OqexPractice\ui\menu\CustomInventory;
use function usleep;

class SocketThread extends Thread
{
    private string $host;
    private int $port;

    private ThreadSafeArray $receive;

    private bool $isRunning;

    public function __construct(protected SleeperHandlerEntry $sleeperHandlerEntry, string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->receive = new ThreadSafeArray();

        $this->isRunning = false;
    }

    public function onRun(): void
    {
        $socket = stream_socket_server("tcp://" . $this->host . ':' . $this->port, $errno, $errstr);
        if (!$socket) {
            die("$errstr ($errno)");
        }

        echo "server started\n";

        $client = null;
        $buffer = "";

        $notifier = $this->sleeperHandlerEntry->createNotifier();

        while ($this->isRunning) {
            usleep(25000);
            if ($client === null) {
                $client = @stream_socket_accept($socket);
                if ($client === false) {
                    continue;
                }
                echo "client connected\n";
            }

            if ($client === false) {
                $client = null;
                echo "client disconnected\n";
                continue;
            }

            $data = fread($client, 1024);
            if ($data === false || $data === '') {
                echo "client disconnected\n";
                fclose($client);
                $client = null;
                continue;
            }

            $buffer .= $data;
            if (strpos($buffer, "\n") === false) {
                continue;
            }

            $explosion = explode("\n", $buffer);
            $json = array_shift($explosion);
            $buffer = implode("\n", $explosion);

            $this->receive[] = $json;
            $notifier->wakeupSleeper();
        }
    }

    public function start($options = 0): bool
    {
        $this->isRunning = true;
        return parent::start($options);
    }

    public function quit(): void
    {
        $this->synchronized(
            function (): void {
                $this->isRunning = false;
                $this->notify();
            }
        );
        parent::quit();
    }

    public function getReceive(): ?string
    {
        return $this->receive->shift();
    }
}