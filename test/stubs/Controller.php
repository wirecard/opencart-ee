<?php

/**
 * Controller class
 */
abstract class Controller
{
    protected $registry;
    protected $config;
    protected $load;
    protected $session;
    protected $response;
    protected $url;
    public $model_extension_payment_wirecard_pg_paypal;
    public $model_checkout_order;

    public function __construct($registry, $config, $loader, $session, $response, $orderModel, $url, $modelPaypal)
    {
        $this->registry = $registry;
        $this->config = $config;
        $this->load = $loader;
        $this->session = $session;
        $this->response = $response;
        $this->model_checkout_order = $orderModel;
        $this->url = $url;
        $this->model_extension_payment_wirecard_pg_paypal = $modelPaypal;
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
