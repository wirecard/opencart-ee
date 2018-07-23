<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/pg_basket.php');
require_once(dirname(__FILE__) . '/pg_account_holder.php');
include_once(DIR_SYSTEM . 'library/autoload.php');

use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class AdditionalInformationHelper
 *
 * @since 1.0.0
 */
class AdditionalInformationHelper extends Model {
	const OPENCART_GATEWAY_WIRECARD_VERSION = '1.0.0';
	const OPENCART_GATEWAY_WIRECARD_NAME = 'Wirecard OpenCart Extension';
	const CURRENCYCODE = 'currency_code';
	const CURRENCYVALUE = 'currency_value';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	private $prefix;

	/**
	 * @var Config
	 * @since 1.0.0
	 */
	private $config;

	/**
	 * AdditionalInformationHelper constructor.
	 * @param $registry
	 * @param $prefix
	 * @since 1.0.0
	 */
	public function __construct($registry, $prefix, $config) {
		parent::__construct($registry);
		$this->prefix = $prefix;
		$this->config = $config;
	}

	/**
	 * @param Transaction $transaction
	 * @param array $items
	 * @param array $shipping
	 * @param array $currency
	 * @param float $total
	 * @return Transaction
	 * @since 1.0.0
	 */
	public function addBasket($transaction, $items, $shipping, $currency, $total) {
		$basket_factory = new PGBasket($this);
		$transaction->setBasket($basket_factory->getBasket($transaction, $items, $shipping, $currency, $total));

		return $transaction;
	}

	/**
	 * Add account holder to transaction.
	 *
	 * @param Transaction $transaction
	 * @param $order
	 * @return Transaction
	 * @since 1.0.0
	 */
	public function addAccountHolder($transaction, $order) {
		$account_holder = new PGAccountHolder();

		$transaction->setAccountHolder($account_holder->createAccountHolder($order, $account_holder::BILLING));
		$transaction->setShipping($account_holder->createAccountHolder($order, $account_holder::SHIPPING));

		return $transaction;
	}

	/**
	 * Create identification data
	 *
	 * @param Transaction $transaction
	 * @param array $order
	 * @return Transaction
	 * @since 1.0.0
	 */
	public function setIdentificationData($transaction, $order) {
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$custom_fields = new \Wirecard\PaymentSdk\Entity\CustomFieldCollection();
		$custom_fields->add(new \Wirecard\PaymentSdk\Entity\CustomField('orderId', $order['order_id']));
		$custom_fields->add(new \Wirecard\PaymentSdk\Entity\CustomField('shopName', 'OpenCart'));
		$custom_fields->add(new \Wirecard\PaymentSdk\Entity\CustomField('shopVersion', VERSION));
		$custom_fields->add(new \Wirecard\PaymentSdk\Entity\CustomField('pluginName', self::OPENCART_GATEWAY_WIRECARD_NAME));
		$custom_fields->add(new \Wirecard\PaymentSdk\Entity\CustomField('pluginVersion', self::OPENCART_GATEWAY_WIRECARD_VERSION));
		$transaction->setCustomFields($custom_fields);
		$transaction->setLocale(substr($order['language_code'], 0, 2));

		return $transaction;
	}

	/**
	 * Create additional information data
	 *
	 * @param Transaction $transaction
	 * @param array $order
	 * @return Transaction
	 * @since 1.0.0
	 */
	public function setAdditionalInformation($transaction, $order) {
		if ($transaction instanceof \Wirecard\PaymentSdk\Transaction\PayPalTransaction) {
			$transaction->setOrderDetail(sprintf(
				'%s %s %s',
				$order['email'],
				$order['firstname'],
				$order['lastname']
			));
		}

		if ($order['ip']) {
			$transaction->setIpAddress($order['ip']);
		} else {
			$transaction->setIpAddress($_SERVER['REMOTE_ADDR']);
		}

		if (strlen($order['customer_id'])) {
			$transaction->setConsumerId($order['customer_id']);
		}
		$transaction->setOrderNumber($order['order_id']);
		$transaction->setDescriptor($this->createDescriptor($order));

		return $transaction;
	}

	/**
	 * Create descriptor including shopname and ordernumber
	 *
	 * @param array $order
	 * @return string
	 * @since 1.0.0
	 */
	public function createDescriptor($order) {
		return sprintf(
			'%s %s',
			substr( $order['store_name'], 0, 9),
			$order['order_id']
		);
	}

	/**
	 * Convert amount with currency format
	 *
	 * @param float $amount
	 * @param array $currency
	 * @return float
	 * @since 1.0.0
	 */
	public function convert($amount, $currency) {
		return ($currency[self::CURRENCYVALUE]) ? (float)$amount * $currency[self::CURRENCYVALUE] : (float)$amount;
	}

	/**
	 * Convert amount with currency format including tax
	 *
	 * @param float $amount
	 * @param array $currency
	 * @param int $taxClassId
	 * @return float
	 * @since 1.0.0
	 */
	public function convertWithTax($amount, $currency, $taxClassId) {
		return $this->tax->calculate($this->convert($amount, $currency), $taxClassId, 'P');
	}
}
