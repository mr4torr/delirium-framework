<?php

declare(strict_types=1);

namespace Delirium\DevTools;

class ProcessManager
{
    /** @var resource|null */
    private $process = null;
    /** @var array<int, resource> */
    private array $pipes = [];

    public function __construct(
        private readonly string|array $command,
        private readonly ?string $cwd = null
    ) {
    }

    public function start(): void
    {
        $this->stop();

        $descriptors = [
            0 => ['file', 'php://stdin', 'r'],
            1 => ['file', 'php://stdout', 'w'], // Forward stdout
            2 => ['file', 'php://stderr', 'w'], // Forward stderr
        ];

        $this->process = proc_open($this->command, $descriptors, $this->pipes, $this->cwd);

        if (!is_resource($this->process)) {
            echo "[ProcessManager] Error: Failed to start process '{$this->command}'\n";
        }
    }

    public function stop(): void
    {
        if (is_resource($this->process)) {
            // Check if running
            $status = proc_get_status($this->process);
            
            if ($status['running']) {
                // Send SIGTERM
                proc_terminate($this->process, SIGTERM);
                
                // Wait a bit?
                // For live reload, we want speed.
                // But if we don't wait, we might get port usage errors.
                // Simple wait:
                usleep(1000000); // 1s
            }

            proc_close($this->process);
            $this->process = null;
        }
    }

    public function restart(): void
    {
        echo "[ProcessManager] Restarting...\n";
        $this->stop();
        $this->start();
    }
}
