<?php

/**
 * Controller class
 */
abstract class Controller
{
    public $response;
    protected $registry;
    protected $config;
    protected $load;
    protected $session;
    protected $url;
    protected $language;
    protected $cart;
    protected $request;
    public $model_extension_payment_wirecard_pg_paypal;
    public $model_extension_payment_wirecard_pg_creditcard;
    public $model_checkout_order;

    public function __construct($registry, $config, $loader, $session, $response, $orderModel, $url, $modelPaypal, $language, $cart)
    {
        $this->registry = $registry;
        $this->config = $config;
        $this->load = $loader;
        $this->session = $session;
        $this->response = $response;
        $this->model_checkout_order = $orderModel;
        $this->url = $url;
        $this->model_extension_payment_wirecard_pg_paypal = $modelPaypal;
        $this->model_extension_payment_wirecard_pg_creditcard = $modelPaypal;
        $this->language = $language;
        $this->cart = $cart;

	    $this->request = new stdClass();
	    $this->request->post = ['fingerprint-session' => '123'];
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
