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

use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Entity\Amount;

/**
 * Class PGBasket
 *
 * @since 1.0.0
 */
class PGBasket {
	const CURRENCYCODE = 'currency_code';
	const CURRENCYVALUE = 'currency_value';
	const PRICE = 'price';
	const COST = 'cost';
	const NAME = 'name';
	const QUANTITY = 'quantity';
	const ID = 'product_id';
	const TAXCLASSID = 'tax_class_id';

	private $model;

	public function __construct($model) {
		$this->model = $model;
	}

	/**
	 * @param Transaction $transaction
	 * @param array $items
	 * @param array $shipping
	 * @param array $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	public function getBasket($transaction, $items, $shipping, $currency) {
		$basket = new Basket();
		$basket->setVersion($transaction);

		foreach ($items as $item) {
			$basket = $this->setBasketItem(
				$basket,
				$item,
				$currency
			);
		}

		$this->setShippingItem($basket, $shipping, $currency);

		return $basket;
	}

	/**
	 * @param Basket $basket
	 * @param array $item
	 * @param array $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	private function setBasketItem($basket, $item, $currency) {
		$grossAmount = $this->convertWithTax(
			$item[self::PRICE],
			$currency,
			$item[self::TAXCLASSID]
		);
		$taxAmount = $grossAmount - $this->convert($item[self::PRICE], $currency);
		$taxRate = $this->convert($taxAmount / $grossAmount * 100, $currency);

		$amount = new Amount($grossAmount, $currency[self::CURRENCYCODE]);
		$basketItem = new Item($item[self::NAME], $amount, $item[self::QUANTITY]);
		$basketItem->setDescription($item[self::NAME]);
		$basketItem->setArticleNumber($item[self::ID]);
		$basketItem->setTaxRate($taxRate);
		$basketItem->setTaxAmount(new Amount($taxAmount, $currency[self::CURRENCYCODE]));
		$basket->add($basketItem);

		return $basket;
	}

	/**
	 * @param Basket $basket
	 * @param array $shipping
	 * @param array $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	private function setShippingItem($basket, $shipping, $currency) {
		$grossAmount = $this->convertWithTax(
			$shipping[self::COST],
			$currency,
			$shipping[self::TAXCLASSID]
		);
		$taxAmount = $this->model->tax->getTax($shipping[self::COST], $shipping[self::TAXCLASSID]);
		$taxRate = $this->convert($taxAmount / $grossAmount * 100, $currency);

		$item = new Item('Shipping', new Amount($grossAmount, $currency[self::CURRENCYCODE]), 1);
		$item->setDescription('Shipping');
		$item->setArticleNumber('Shipping');
		$item->setTaxRate($taxRate);
		$basket->add($item);

		return $basket;
	}

	/**
	 * @param float $amount
	 * @param array $currency
	 * @return float
	 * @since 1.0.0
	 */
	private function convert($amount, $currency) {
		return $this->model->currency->format($amount, $currency[self::CURRENCYCODE], $currency[self::CURRENCYVALUE], false);
	}

	/**
	 * @param float $amount
	 * @param array $currency
	 * @param int $taxClassId
	 * @return float
	 * @since 1.0.0
	 */
	private function convertWithTax($amount, $currency, $taxClassId) {
		return $this->model->tax->calculate($this->convert($amount, $currency), $taxClassId, 'P');
	}
}