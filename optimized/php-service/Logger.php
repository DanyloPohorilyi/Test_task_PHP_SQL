<?php
class Logger{
    private $handle;
    private $path;
    public function __construct(string $path) {
        $this->path = $path;
        $this->handle = @fopen($this->path, 'a');
        if ($this->handle === false) {
            error_log("Не можу відкрити файл: {$this->path}");
            $this->handle = null;
        }
    }
    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message);
    }

    private function write(string $level, string $message): void
    {
        $ts = (new DateTime())->format('Y-m-d H:i:s');
        $line = "[$ts] [$level] $message" . PHP_EOL;
        if (is_resource($this->handle)) {
            fwrite($this->handle, $line);
        } else {
            error_log($line);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

}