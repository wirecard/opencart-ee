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

	/**
	 * @var Model
	 * @since 1.0.0
	 */
	private $model;

	/**
	 * @var int
	 * @since 1.0.0
	 */
	private $sum;

	/**
	 * PGBasket constructor.
	 * @param $model
	 * @since 1.0.0
	 */
	public function __construct($model) {
		$this->model = $model;
		$this->sum = 0;
	}

	/**
	 * Create basket including shipping and discounts/coupons
	 *
	 * @param Transaction $transaction
	 * @param array $items
	 * @param array $shipping
	 * @param array $currency
	 * @param float $total
	 * @return Basket
	 * @since 1.0.0
	 */
	public function getBasket($transaction, $items, $shipping, $currency, $total) {
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

		if ($this->sum - $total > 0) {
			$this->setCouponItem(
				$basket,
				$this->sum - $total,
				$currency
			);
		}

		return $basket;
	}

	/**
	 * Create basket item
	 *
	 * @param Basket $basket
	 * @param array $item
	 * @param array $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	private function setBasketItem($basket, $item, $currency) {
		$gross_amount = $this->convertWithTax(
			$item[self::PRICE],
			$currency,
			$item[self::TAXCLASSID]
		);
		$tax_amount = $gross_amount - $this->convert($item[self::PRICE], $currency);
		$tax_rate = $this->convert($tax_amount / $gross_amount * 100, $currency);

		$this->sum += $gross_amount * $item[self::QUANTITY];
		$amount = new Amount($gross_amount, $currency[self::CURRENCYCODE]);
		$basket_item = new Item($item[self::NAME], $amount, $item[self::QUANTITY]);
		$basket_item->setDescription($item[self::NAME]);
		$basket_item->setArticleNumber($item[self::ID]);
		$basket_item->setTaxRate($tax_rate);
		$basket_item->setTaxAmount(new Amount($tax_amount, $currency[self::CURRENCYCODE]));
		$basket->add($basket_item);

		return $basket;
	}

	/**
	 * Create shipping basket item
	 *
	 * @param Basket $basket
	 * @param array $shipping
	 * @param array $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	private function setShippingItem($basket, $shipping, $currency) {
		$gross_amount = $this->convertWithTax(
			$shipping[self::COST],
			$currency,
			$shipping[self::TAXCLASSID]
		);
		$tax_amount = $this->model->tax->getTax($shipping[self::COST], $shipping[self::TAXCLASSID]);
		$tax_rate = $this->convert($tax_amount / $gross_amount * 100, $currency);

		$this->sum += $gross_amount;
		$item = new Item('Shipping', new Amount($gross_amount, $currency[self::CURRENCYCODE]), 1);
		$item->setDescription('Shipping');
		$item->setArticleNumber('Shipping');
		$item->setTaxRate($tax_rate);
		$basket->add($item);

		return $basket;
	}

	/**
	 * Set coupon/discount item
	 *
	 * @param $basket
	 * @param $amount
	 * @param $currency
	 * @return Basket
	 * @since 1.0.0
	 */
	private function setCouponItem($basket, $amount, $currency) {
		$item = new Item('Coupon', new Amount($amount * -1, $currency[self::CURRENCYCODE]), 1);
		$item->setDescription('Coupon');
		$item->setArticleNumber('Coupon');
		$basket->add($item);

		return $basket;
	}

	/**
	 * Convert amount with currency format
	 *
	 * @param float $amount
	 * @param array $currency
	 * @return float
	 * @since 1.0.0
	 */
	private function convert($amount, $currency) {
		return $this->model->currency->format($amount, $currency[self::CURRENCYCODE], $currency[self::CURRENCYVALUE], false);
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
	private function convertWithTax($amount, $currency, $taxClassId) {
		return $this->model->tax->calculate($this->convert($amount, $currency), $taxClassId, 'P');
	}
}
