<?php
class FileLogger
{
    private $logFile;

    public function __construct($filename)
    {
        $this->logFile = $filename;
    }

    public function log($message)
    {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}