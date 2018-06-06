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

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

/**
 * Class ControllerExtensionPaymentGateway
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
abstract class ControllerExtensionPaymentGateway extends Controller{

    const BILLING = 'billing';

    const SHIPPING = 'shipping';

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
		$prefix = $this->prefix . $this->type;

		$this->load->model('checkout/order');

		$this->load->language('extension/payment/wirecard_pg');
		$this->load->language('extension/payment/wirecard_pg_' . $this->type);

		$data['active'] = $this->config->get($this->prefix . $this->type . '_status');
		$data['button_confirm'] = $this->language->get('button_confirm');
        $data['additional_info'] = $this->config->get($prefix . '_additional_info');
        if (strlen($this->config->get($prefix . '_session_string'))) {
            $data['session_id'] = $this->config->get($prefix . '_merchant_account_id') . '_' . $this->config->get($prefix . '_session_string');
        }

		return $this->load->view('extension/payment/wirecard_pg', $data);
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
			$this->load->language('extension/payment/wirecard_pg');
			$this->load->model('checkout/order');
			$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$amount = new \Wirecard\PaymentSdk\Entity\Amount( $order['total'], $order['currency_code']);
			$this->paymentConfig = $this->getConfig();
			$this->transaction->setRedirect($this->getRedirects());
			$this->transaction->setAmount($amount);

			$this->setIdentificationData($order);
			$this->setAdditionalInformation($order);

			$model = $this->getModel();
			$result = $model->sendRequest($this->paymentConfig, $this->transaction);

			if ($result instanceof \Wirecard\PaymentSdk\Response\Response) {
				//set response data temporarly -> should be redirect
				$json['response'] = json_encode($result->getData());
			} else {
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
		$baseUrl = $this->config->get($this->prefix . $this->type . '_base_url');
		$httpUser = $this->config->get($this->prefix . $this->type . '_http_user');
		$httpPassword = $this->config->get($this->prefix . $this->type . '_http_password');

		$config = new Config($baseUrl, $httpUser, $httpPassword);
		$config->setShopInfo('OpenCart', VERSION);
		$config->setPluginInfo('Wirecard_PaymentGateway', $this->pluginVersion);

		return $config;
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
			$this->url->link('extension/payment/' . $this->prefix . $this->type . '/checkout', null, 'SSL'),
			$this->url->link('extension/payment/' . $this->prefix . $this->type . '/failure', null, 'SSL'),
			$this->url->link('extension/payment/' . $this->prefix . $this->type . '/success', null, 'SSL')
		);

		return $redirectUrls;
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
	 * Create identification data
	 *
	 * @param array $order
	 * @since 1.0.0
	 */
	protected function setIdentificationData($order)
	{
		$customFields = new \Wirecard\PaymentSdk\Entity\CustomFieldCollection();
		$customFields->add(new \Wirecard\PaymentSdk\Entity\CustomField('orderId', $order['order_id']));
		$this->transaction->setCustomFields($customFields);
		$this->transaction->setLocale(substr($order['language_code'], 0, 2));

		if($this->config->get($this->prefix . $this->type . '_descriptor')) {
			$this->transaction->setDescriptor($this->createDescriptor($order));
		}
	}

    /**
     * Create additional information data
     *
     * @param array $order
     * @since 1.0.0
     */
	protected function setAdditionalInformation($order)
    {
        if($this->config->get($this->prefix . $this->type . '_additional_info')) {
            $this->transaction->setOrderDetail(sprintf(
                '%s %s %s',
                $order['email'],
                $order['firstname'],
                $order['lastname']
            ));
            if ($order['ip']) {
                $this->transaction->setIpAddress($order['ip']);
            } else {
                $this->transaction->setIpAddress($_SERVER['REMOTE_ADDR']);
            }
            if (strlen($order['customer_id'])) {
                $this->transaction->setConsumerId($order['customer_id']);
            }
            //Device Fingerprint
            //$this->transaction->setOrderNumber($order['order_id']);
            $this->transaction->setDescriptor($this->createDescriptor($order));
            $this->transaction->setAccountHolder($this->createAccountHolder($order, self::BILLING));
            $this->transaction->setShipping($this->createAccountHolder($order, self::SHIPPING));
        }
    }

	/**
	 * Create descriptor including shopname and ordernumber
	 *
	 * @param array $order
	 * @return string
	 * @since 1.0.0
	 */
	protected function createDescriptor($order) {
		return sprintf(
			'%s %s',
			substr( $order['store_name'], 0, 9),
			$order['order_id']
		);
	}

    /**
     * Create AccountHolder with specific address data
     *
     * @param array $order
     * @param string $type
     * @since 1.0.0
     */
	protected function createAccountHolder($order, $type = self::BILLING) {
	    $accountHolder = new AccountHolder();
	    if (self::SHIPPING == $type) {
	        $accountHolder->setAddress($this->createAddressData($order, $type));
	        $accountHolder->setFirstName($order['shipping_firstname']);
	        $accountHolder->setLastName($order['shipping_lastname']);
        } else {
	        $accountHolder->setAddress($this->createAddressData($order, $type));
	        $accountHolder->setFirstName($order['payment_firstname']);
	        $accountHolder->setLastName($order['payment_lastname']);
	        $accountHolder->setEmail($order['email']);
	        $accountHolder->setPhone($order['telephone']);
	        // following data is not available
	        //$accountHolder->setDateOfBirth();
	        //$accountHolder->setGender();
        }

        return $accountHolder;
    }

    /**
     * Create Address data based on order
     *
     * @param array $order
     * @param string $type
     * @return Address
     * @since 1.0.0
     */
    protected function createAddressData($order, $type) {
	    if (self::SHIPPING == $type) {
	        $address = new Address( $order['shipping_iso_code_2'], $order['shipping_city'], $order['shipping_address_1']);
	        $address->setPostalCode($order['shipping_postcode']);
        } else {
	        $address = new Address($order['payment_iso_code_2'], $order['payment_city'], $order['payment_address_1']);
	        $address->setPostalCode($order['payment_postcode']);
	        if (strlen($order['payment_address_2'])) {
	            $address->setStreet2($order['payment_address_2']);
            }
        }

        return $address;
    }
}
