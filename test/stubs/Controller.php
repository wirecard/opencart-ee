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
	public $model_extension_payment_wirecard_pg_sepact;
	public $model_extension_payment_wirecard_pg_sofortbanking;
	public $model_extension_payment_wirecard_pg_ideal;
	public $model_extension_payment_wirecard_pg_masterpass;
	public $model_extension_payment_wirecard_pg_upi;
    public $model_checkout_order;
    public $controller_extension_payment_wirecard_pg_sepact;

    public function __construct($registry, $config, $loader, $session, $response, $orderModel, $url, $modelPayment, $language, $cart, $currency, $subController = null, $request = null)
    {
        $this->registry = $registry;
        $this->config = $config;
        $this->load = $loader;
        $this->session = $session;
        $this->response = $response;
        $this->model_checkout_order = $orderModel;
        $this->url = $url;
        $this->model_extension_payment_wirecard_pg_paypal = $modelPayment;
        $this->model_extension_payment_wirecard_pg_creditcard = $modelPayment;
		$this->model_extension_payment_wirecard_pg_sepact = $modelPayment;
		$this->model_extension_payment_wirecard_pg_sofortbanking = $modelPayment;
		$this->model_extension_payment_wirecard_pg_ideal = $modelPayment;
		$this->model_extension_payment_wirecard_pg_masterpass = $modelPayment;
		$this->model_extension_payment_wirecard_pg_upi = $modelPayment;
        $this->language = $language;
        $this->cart = $cart;
        $this->controller_extension_payment_wirecard_pg_sepact = $subController;

	    $this->request = new stdClass();
	    $this->request->post = [
	    	'fingerprint-session' => '123',
			'ideal-bic' => \Wirecard\PaymentSdk\Entity\IdealBic::INGBNL2A
		];

	    $this->currency = $currency;
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
