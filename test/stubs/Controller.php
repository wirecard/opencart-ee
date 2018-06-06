<?php

/**
 * Controller class
 */
abstract class Controller
{
    protected $registry;
    protected $config;
    protected $load;
    public $model_extension_payment_wirecard_pg_paypal;

    public function __construct($registry, $config, $loader)
    {
        $this->registry = $registry;
        $this->config = $config;
        $this->load = $loader;
        $this->model_extension_payment_wirecard_pg_paypal = new ModelExtensionPaymentWirecardPGPayPal($registry);
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
