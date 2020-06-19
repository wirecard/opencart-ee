<?php

use Mockery as m;

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
	protected $currency;
	protected $transaction;
	protected $db;
	public $request;
	public $model_extension_payment_wirecard_pg_paypal;
	public $model_extension_payment_wirecard_pg_creditcard;
	public $model_extension_payment_wirecard_pg_sepact;
	public $model_extension_payment_wirecard_pg_sofortbanking;
	public $model_extension_payment_wirecard_pg_ideal;
	public $model_extension_payment_wirecard_pg_poi;
	public $model_extension_payment_wirecard_pg_pia;
	public $model_extension_payment_wirecard_pg_alipay_crossborder;
	public $model_extension_payment_wirecard_pg_upi;
	public $model_extension_payment_wirecard_pg_sepadd;
	public $model_extension_payment_wirecard_pg_ratepayinvoice;
	public $model_extension_payment_wirecard_pg_maestro;
	public $model_localization_language;
	public $model_checkout_order;
	public $controller_extension_payment_wirecard_pg_sepact;

	public function __construct($registry, $config, $loader, $session, $response, $orderModel, $url, $modelPayment, $language, $cart, $currency, $subController = null, $document = null, $customer = null, $overrideRequest = null, $transaction = null)
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
        $this->model_extension_payment_wirecard_pg_alipay_crossborder = $modelPayment;
		$this->model_extension_payment_wirecard_pg_upi = $modelPayment;
		$this->model_extension_payment_wirecard_pg_poi = $modelPayment;
		$this->model_extension_payment_wirecard_pg_pia = $modelPayment;
		$this->model_extension_payment_wirecard_pg_sepadd = $modelPayment;
		$this->model_extension_payment_wirecard_pg_ratepayinvoice = $modelPayment;
		$this->model_extension_payment_wirecard_pg_maestro = $modelPayment;
		$this->model_localisation_language = $modelPayment;
		$this->language = $language;
		$this->cart = $cart;
		$this->controller_extension_payment_wirecard_pg_sepact = $subController;
		$this->document = $document;
		$this->customer = $customer;
		$this->transaction = $transaction;

		$this->request = new stdClass();
		$this->request->post = $overrideRequest ?: [
			'fingerprint-session' => '123',
			'ideal-bic' => \Wirecard\PaymentSdk\Entity\IdealBic::INGBNL2A
		];

		$this->currency = $currency;

		$this->model_extension_payment_wirecard_pg_vault = m::mock('overload:ModelExtensionPaymentWirecardPGVault');
		$this->model_extension_payment_wirecard_pg_vault->shouldReceive('getCards');
		$this->db = new DB('mysql', 'localhost', 'username', 'password', 'test');
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
