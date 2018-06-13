<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

include_once(DIR_SYSTEM . 'library/autoload.php');
require_once __DIR__ . '/../../../../model/extension/payment/wirecard_pg/helper/additional_information_helper.php';
require_once __DIR__ . '/../../../../model/extension/payment/wirecard_pg/handler/notification_handler.php';
require __DIR__ . '/../../../../model/extension/payment/wirecard_pg/helper/pg_order_manager.php';

use Wirecard\PaymentSdk\Config\Config;

/**
 * Class ControllerExtensionPaymentGateway
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
abstract class ControllerExtensionPaymentGateway extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg_';
	const PATH = 'extension/payment/wirecard_pg';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $pluginVersion = '1.0.0';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $prefix = 'payment_wirecard_pg_';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type;

	/**
	 * @var Config
	 * @since 1.0.0
	 */
	protected $paymentConfig;

	/**
	 * @var Model
	 * @since 1.0.0
	 */
	protected $model;

	/**
	 * @var \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	protected $transaction;

	/**
	 * Basic index method
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function index()
	{
		$this->load->model('checkout/order');

		$this->load->language(self::PATH);
		$this->load->language(self::ROUTE . $this->type);
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['active'] = $this->getConfigVal('status');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['additional_info'] = $this->getConfigVal('additional_info');
		$data['action'] = $this->url->link(self::ROUTE . $this->type . '/confirm', '', true);
		$sessionId = $this->getConfigVal('merchant_account_id') . '_' . $this->createSessionString($order);
		$data['session_id'] = substr($sessionId, 0, 127);
		$data['type'] = $this->type;

		return $this->load->view(self::PATH, $data);
	}

	/**
	 * Default confirm order method
	 *
	 * @since 1.0.0
	 */
	public function confirm()
	{
		$json = array();

		if ($this->session->data['payment_method']['code'] == 'wirecard_pg_' . $this->type) {
			$this->load->language(self::PATH);
			$this->load->model('checkout/order');
			$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			$this->load->model('checkout/order');
			$currency = [
				'currency_code' => $order['currency_code'],
				'currency_value' => $order['currency_value']
			];

			$amount = new \Wirecard\PaymentSdk\Entity\Amount( $order['total'], $order['currency_code']);
			$this->paymentConfig = $this->getConfig();
			$this->transaction->setRedirect($this->getRedirects());
			$this->transaction->setNotificationUrl($this->getNotificationUrl());
			$this->transaction->setAmount($amount);

			$additionalHelper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config);
			$this->transaction = $additionalHelper->setIdentificationData($this->transaction, $order);
			if ($this->getConfigVal('descriptor')) {
				$this->transaction->setDescriptor($additionalHelper->createDescriptor($order));
			}

			if ($this->getConfigVal('shopping_basket')) {
				$this->transaction = $additionalHelper->addBasket(
					$this->transaction,
					$this->cart->getProducts(),
					$this->session->data['shipping_method'],
					$currency,
					$order['total']
				);
			}

			if ($this->getConfigVal('additional_info')) {
				$this->transaction = $additionalHelper->setAdditionalInformation($this->transaction, $order);
			}

			if (isset($this->request->post['fingerprint-session'])) {
				$device = new \Wirecard\PaymentSdk\Entity\Device();
				$device->setFingerprint($this->request->post['fingerprint-session']);
				$this->transaction->setDevice($device);
			}

			$model = $this->getModel();

			if (!$this->cart->hasStock()) {
				$json['redirect'] = $this->url->link('checkout/checkout');
			} else {
				$result = $model->sendRequest($this->paymentConfig, $this->transaction, $this->getConfigVal('payment_action'));
				if (!isset($this->session->data['error'])) {
					//Save pending order
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
				}
				$json['redirect'] = $result;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Create payment specific config
	 *
	 * @return Config
	 * @since 1.0.0
	 */
	public function getConfig()
	{
		$baseUrl = $this->getConfigVal('base_url');
		$httpUser = $this->getConfigVal('http_user');
		$httpPassword = $this->getConfigVal('http_password');

		$config = new Config($baseUrl, $httpUser, $httpPassword);
		$config->setShopInfo('OpenCart', VERSION);
		$config->setPluginInfo('Wirecard_PaymentGateway', $this->pluginVersion);

		return $config;
	}

	/**
	 *  Handle notification
	 *
	 * @since 1.0.0
	 */
	public function notify()
	{
		$payload = file_get_contents('php://input');

		$notificationHandler = new NotificationHandler();
		$response = $notificationHandler->handleNotification( $this->getConfig(), $payload);

		if ($response) {
			$orderManager = new PGOrderManager($this->registry);
			$orderManager->createNotifyOrder($response, $this);
		} else {
			//write log wit error ?
		}
	}

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.0.0
	 */
	public function getModel()
	{
		$this->load->model('extension/payment/wirecard_pg/gateway');

		return $this->model_extension_payment_wirecard_pg_gateway;
	}

	/**
	 * Handle response
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function response() {
		$orderManager = new PGOrderManager($this->registry);
		try {
			$transactionService = new \Wirecard\PaymentSdk\TransactionService($this->getConfig());
			$result = $transactionService->handleResponse($_REQUEST);
		} catch (Exception $exception) {
			$this->session->data['error'] = 'An error occurred during checkout process';
			$this->response->redirect($this->url->link('checkout/checkout'));
		}
		if($result instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			$orderManager->createResponseOrder($result, $this);
			$this->response->redirect($this->url->link('checkout/success'));
		} else {
			$this->session->data['error'] = 'An error occurred during checkout process';
			$this->response->redirect($this->url->link('checkout/checkout'));
		}
	}

	/**
	 * Create notification url
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function getNotificationUrl()
	{
		return $this->url->link(
			self::ROUTE . $this->type . '/notify', '', 'SSL'
		);
	}

	/**
	 * Create payment specific redirects
	 *
	 * @return \Wirecard\PaymentSdk\Entity\Redirect
	 * @since 1.0.0
	 */
	protected function getRedirects()
	{
		$redirectUrls = new \Wirecard\PaymentSdk\Entity\Redirect(
			$this->url->link(self::ROUTE . $this->type . '/response', '', 'SSL'),
			$this->url->link(self::ROUTE . $this->type . '/response', '', 'SSL'),
			$this->url->link(self::ROUTE. $this->type . '/response', '', 'SSL')
		);

		return $redirectUrls;
	}

	/**
	 * Get configuration value per fieldname
	 *
	 * @param string $field
	 * @return bool|string
	 * @since 1.0.0
	 */
	protected function getConfigVal($field)
	{
		return $this->config->get($this->prefix . $this->type . '_' . $field);
	}

	/**
	 * Create Device Session RandomString
	 *
	 * @param array $order
	 * @return string
	 * @since 1.0.0
	 */
	protected function createSessionString($order)
	{
		$consumer_id = $order['customer_id'];
		$timestamp = microtime();
		$session = md5($consumer_id . "_" . $timestamp);

		return $session;
	}

	/**
	 * Get payment type
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getType()
	{
		return $this->type;
	}
}
