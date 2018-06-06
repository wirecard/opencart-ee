<?php

/**
 * Controller class
 */
abstract class Controller
{
    protected $registry;
    protected $config;

    public function __construct($registry, $config)
    {
        $this->registry = $registry;
        $this->config = $config;
    }

    public function get($key)
    {
        return $this->registry->get($key);
    }

    public function set($key, $value)
    {
        $this->registry->set($key, $value);
    }
}
