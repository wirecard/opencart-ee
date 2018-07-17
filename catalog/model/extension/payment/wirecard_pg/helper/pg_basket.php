<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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
		$amount = new Amount(number_format($gross_amount, $currency['precision']), $currency[self::CURRENCYCODE]);
		$basket_item = new Item($item[self::NAME], $amount, $item[self::QUANTITY]);
		$basket_item->setDescription($item[self::NAME]);
		$basket_item->setArticleNumber($item[self::ID]);
		$basket_item->setTaxRate($tax_rate);
		$basket_item->setTaxAmount(new Amount(number_format($tax_amount, $currency['precision']), $currency[self::CURRENCYCODE]));
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
		$item = new Item('Shipping', new Amount(number_format($gross_amount, $currency['precision']), $currency[self::CURRENCYCODE]), 1);
		$item->setDescription('Shipping');
		$item->setArticleNumber('Shipping');
		$item->setTaxRate(number_format($tax_rate, $currency['precision']));
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
		$item = new Item('Coupon', new Amount(number_format($amount * -1, $currency['precision']), $currency[self::CURRENCYCODE]), 1);
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
		return number_format($this->model->currency->format($amount, $currency[self::CURRENCYCODE], $currency[self::CURRENCYVALUE], false), $currency['precision']);
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
		return number_format($this->model->tax->calculate($this->convert($amount, $currency), $taxClassId, 'P'), $currency['precision']);
	}
}
