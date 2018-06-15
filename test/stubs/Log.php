<?php

/**
 * Class Log
 */
class Log {
    private $messages = array();
    private $handle;

    public function __construct($filename) {
        $this->handle = $filename;
    }

    public function write($message) {
        $this->messages[] = date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n";
    }

    public function __destruct() {
        $this->handle = null;
    }
}