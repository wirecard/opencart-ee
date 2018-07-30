<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

include_once(DIR_SYSTEM . 'library/autoload.php');

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

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
	protected $payment_config;

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
	 * @var string
	 * @since 1.0.0
	 */
	protected $operation;


	/**
	 * Sets the operation that is currently being executed.
	 *
	 * @param $operation
	 * @since 1.0.0
	 */
	public function setOperation($operation) {
		$this->operation = $operation;
	}

	/**
	 * Get a logger instance
	 *
	 * @return PGLogger
	 * @since 1.0.0
	 */
	public function getLogger() {
		return new PGLogger($this->config);
	}

	/**
	 * Basic index method
	 *
	 * @param array $data
	 * @return mixed
	 * @since 1.0.0
	 */
	public function index($data = null) {
		$this->load->model('checkout/order');

		$this->load->language(self::PATH);
		$this->load->language(self::ROUTE . $this->type);
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['active'] = $this->getShopConfigVal('status');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['additional_info'] = $this->getShopConfigVal('additional_info');
		$data['action'] = $this->url->link(self::ROUTE . $this->type . '/confirm', '', true);
		$session_id = $this->getShopConfigVal('merchant_account_id') . '_' . $this->createSessionString($order);
		$data['session_id'] = substr($session_id, 0, 127);
		$data['type'] = $this->type;
		$data['vault_enabled'] = $this->getShopConfigVal('vault');
		$data['customer_logged_in'] = $this->customer->isLogged();

		return $this->load->view(self::PATH, $data);
	}

	/**
	 * Default confirm order method
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		$json = array();

		if ($this->session->data['payment_method']['code'] == 'wirecard_pg_' . $this->type) {
			$this->prepareTransaction();
			$model = $this->getModel();
			$json['redirect'] = $this->url->link('checkout/checkout');

			if ($this->cart->hasStock()) {
				$result = $model->sendRequest($this->payment_config, $this->transaction, $this->getShopConfigVal('payment_action'));
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
	 * Fill transaction with data
	 *
	 * @since 1.0.0
	 */
	public function prepareTransaction() {
		$this->load->language(self::PATH);
		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config);
		$precision = $this->getPrecision($order);
		$currency = [
			'currency_code' => $order['currency_code'],
			'currency_value' => $order['currency_value'],
			'precision' => $precision
		];
		$total = $additional_helper->convert($order['total'], $currency);
		$amount = new \Wirecard\PaymentSdk\Entity\Amount(number_format($total, $precision), $order['currency_code']);
		$this->payment_config = $this->getConfig($currency);
		$this->transaction->setRedirect($this->getRedirects($this->session->data['order_id']));
		$this->transaction->setNotificationUrl($this->getNotificationUrl());
		$this->transaction->setAmount($amount);

		$this->transaction = $additional_helper->setIdentificationData($this->transaction, $order);
		if ($this->getShopConfigVal('descriptor')) {
			$this->transaction->setDescriptor($additional_helper->createDescriptor($order));
		}

		if ($this->getShopConfigVal('shopping_basket')) {
			$this->transaction = $additional_helper->addBasket(
				$this->transaction,
				$this->cart->getProducts(),
				$this->session->data['shipping_method'],
				$currency,
				$order['total']
			);
		}

		if ($this->getShopConfigVal('additional_info')) {
			$this->transaction = $additional_helper->setAdditionalInformation($this->transaction, $order);
			$this->transaction = $additional_helper->addBasket(
				$this->transaction,
				$this->cart->getProducts(),
				$this->session->data['shipping_method'],
				$currency,
				$order['total']
			);
		}

		if (isset($this->request->post['fingerprint-session'])) {
			$device = new \Wirecard\PaymentSdk\Entity\Device();
			$device->setFingerprint($this->request->post['fingerprint-session']);
			$this->transaction->setDevice($device);
		}
	}

	/**
	 * Get precision for current currency from order
	 *
	 * @param array $order
	 * @return int
	 * @since 1.0.0
	 */
	public function getPrecision( $order ) {
		if ($this->type == 'sepadd') {
			return 2;
		}

		$currency_value = floatval( $order['currency_value'] );
		$precision = strlen(substr(strrchr($currency_value, "."), 1));
		return $precision;
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return Config
	 * @since 1.0.0
	 */
	public function getConfig($currency = null) {
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$base_url = $this->getShopConfigVal('base_url');
		$http_user = $this->getShopConfigVal('http_user');
		$http_password = $this->getShopConfigVal('http_password');

		$config = new Config($base_url, $http_user, $http_password);
		$config->setShopInfo($basic_info->getShopName(), $basic_info->getShopVersion());
		$config->setPluginInfo($basic_info->getName(), $basic_info->getVersion());

		return $config;
	}

	/**
	 *  Handle notification
	 *
	 * @since 1.0.0
	 */
	public function notify() {
		$payload = file_get_contents('php://input');

		$notification_handler = new NotificationHandler();
		$response = $notification_handler->handleNotification($this->getConfig(), $this->getLogger(), $payload);

		// All errors are already caught and handled in handleNotification.
		// So there's no need to check for an else here.
		if ($response) {
			if ($this->isIgnorableMasterpassResult($response)) {
				return;
			}

			$order_manager = new PGOrderManager($this->registry);
			$order_manager->createNotifyOrder($response, $this);
		}
	}

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.0.0
	 */
	public function getModel() {
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
		$order_manager = new PGOrderManager($this->registry);
		$delete_cancel = $this->getShopConfigVal('delete_cancel_order');
		$this->load->language('extension/payment/wirecard_pg');

		$logger = $this->getLogger();

		try {
			$transaction_service = new \Wirecard\PaymentSdk\TransactionService($this->getConfig(), $logger);
			$result = $transaction_service->handleResponse($_REQUEST);

			return $this->processResponse($result, $logger);

		} catch (\InvalidArgumentException $exception) {
			$logger->error(__METHOD__ . ':' . 'Invalid argument set: ' . $exception->getMessage());
			$this->session->data['error'] = $exception->getMessage();
			$this->response->redirect($this->url->link('checkout/checkout'));

			return;
		} catch (MalformedResponseException $exception) {
			$was_cancelled = isset($_REQUEST['cancelled']);

			if ($was_cancelled) {
				$this->session->data['error'] = $this->language->get('order_cancelled');
				$logger->warning('Order was cancelled');
				$order_manager->updateCancelFailureOrder($_REQUEST['orderId'], 'cancelled', $delete_cancel);
				$this->response->redirect($this->url->link('checkout/checkout'));

				return;
			}

			$logger->error( __METHOD__ . ':' . 'Response is malformed: ' . $exception->getMessage());
			$this->session->data['error'] = $exception->getMessage();

			$this->response->redirect($this->url->link('checkout/checkout'));
		}
	}

	/**
	 * Create notification url
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function getNotificationUrl() {
		return $this->url->link(
			self::ROUTE . $this->type . '/notify', '', 'SSL'
		);
	}

	/**
	 * Create payment specific redirects
	 *
	 * @param int $order_id
	 * @return \Wirecard\PaymentSdk\Entity\Redirect
	 * @since 1.0.0
	 */
	protected function getRedirects($order_id) {
		return new \Wirecard\PaymentSdk\Entity\Redirect(
			$this->url->link(self::ROUTE . $this->type . '/response', '', 'SSL'),
			$this->url->link(self::ROUTE . $this->type . '/response&cancelled=1&orderId=' . $order_id, '', 'SSL'),
			$this->url->link(self::ROUTE . $this->type . '/response', '', 'SSL')
		);
	}

	/**
	 * Get configuration value per fieldname
	 *
	 * @param string $field
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function getShopConfigVal($field) {
		return $this->config->get($this->prefix . $this->type . '_' . $field);
	}

	/**
	 * Create Device Session RandomString
	 *
	 * @param array $order
	 * @return string
	 * @since 1.0.0
	 */
	protected function createSessionString($order) {
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
	public function getType() {
		return $this->type;
	}

	/**
	 * Process the response data
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse | \Wirecard\PaymentSdk\Response\FormInteractionResponse |
	 * \Wirecard\PaymentSdk\Response\FailureResponse $result
	 * @param Logger $logger
	 * @return bool | array
	 */
	public function processResponse($result, $logger) {
		$order_manager = new PGOrderManager($this->registry);
		$delete_failure = $this->getShopConfigVal('delete_failure_order');

		if ($result instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			if (!$this->isIgnorableMasterpassResult($result)) {
				$order_manager->createResponseOrder($result, $this);
			}

			if ('creditcard' == $this->type && isset($this->session->data['save_card'])) {
				unset($this->session->data['save_card']);

				$vault = $this->getVault();
				$vault->saveCard($this->customer, $result);
			}

			if ('pia' == $this->type && isset($this->session->data['order_id'])) {
				return $this->generateSuccessPage($result);
			}

			$this->response->redirect($this->url->link('checkout/success'));

			return true;
		} elseif ($result instanceof \Wirecard\PaymentSdk\Response\FormInteractionResponse) {
			$this->load->language('information/static');

			$data = [
				'url' => $result->getUrl(),
				'method' => $result->getMethod(),
				'form_fields' => $result->getFormFields(),
				'redirect_text' => $this->language->get('redirect_text'),
			];

			$data = array_merge($this->getCommonBlocks(), $data);
			$this->response->setOutput($this->load->view('extension/payment/wirecard_interaction_response', $data));
		} elseif ($result instanceof \Wirecard\PaymentSdk\Response\FailureResponse) {
			$errors = '';

			foreach ($result->getStatusCollection()->getIterator() as $item) {
				$errors .= $item->getDescription() . "<br>\n";
				$logger->error($item->getDescription());
			}

			$this->session->data['error'] = $errors;
			$order_manager->updateCancelFailureOrder($result->getCustomFields()->get('orderId'), 'failed', $delete_failure);
			$this->response->redirect($this->url->link('checkout/checkout'));

			return false;
		} else {
			$this->session->data['error'] = $this->language->get('order_error');
			$this->response->redirect($this->url->link('checkout/checkout'));

			return false;
		}
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return null
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return null;
	}


	/**
	 * Get payment controller
	 *
	 * @param string $type
	 * @return ControllerExtensionPaymentGateway
	 * @since 1.0.0
	 */
	public function getController($type) {
		return $this->load->controller('extension/payment/wirecard_pg_' . $type);
	}

	/**
	 * Create cancel transaction
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @param string $operation
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		$this->transaction->setParentTransactionId($parentTransaction['transaction_id']);
		$this->transaction->setAmount($amount);

		return $this->transaction;
	}

	/**
	 * Get common blocks for building a template
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getCommonBlocks() {
		$data = [
			'continue' => $this->url->link('common/home'),
			'column_left' => $this->load->controller('common/column_left'),
			'column_right' => $this->load->controller('common/column_right'),
			'content_top' => $this->load->controller('common/content_top'),
			'content_bottom' => $this->load->controller('common/content_bottom'),
			'footer' => $this->load->controller('common/footer'),
			'header' => $this->load->controller('common/header'),
		];

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf(
				$this->language->get('text_customer'),
				$this->url->link('account/account', '', true),
				$this->url->link('account/order', '', true),
				$this->url->link('account/download', '', true),
				$this->url->link('information/contact')
			);
		} else {
			$data['text_message'] = sprintf(
				$this->language->get('text_guest'),
				$this->url->link('information/contact')
			);
		}

		return $data;
	}

	/**
	 * @param \Wirecard\PaymentSdk\Response\Response
	 * @return bool
	 * @since 1.1.0
	 */
	public function isIgnorableMasterpassResult($result) {
		try {
			return 'masterpass' == $result->getPaymentMethod() &&
				(\Wirecard\PaymentSdk\Transaction\Transaction::TYPE_DEBIT == $result->getTransactionType() ||
					\Wirecard\PaymentSdk\Transaction\Transaction::TYPE_AUTHORIZATION == $result->getTransactionType());
		} catch(Exception $e) {
			$this->getLogger()->error(get_class($e) . ": " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get an instance of the Credit Card vault.
	 *
	 * @return ModelExtensionPaymentWirecardPGVault
	 * @since 1.1.0
	 */
	protected function getVault() {
		$this->load->model('extension/payment/wirecard_pg/vault');

		return $this->model_extension_payment_wirecard_pg_vault;
	}

	/**
	 * Get payment action
	 *
	 * @param string $action
	 * @return string
	 * @since 1.1.0
	 */
	public function getPaymentAction($action) {
		if ($action == 'pay') {
			return 'purchase';
		} else {
			return 'authorization';
		}
	}
}
