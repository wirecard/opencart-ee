<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\RiskInfo;
use Wirecard\PaymentSdk\Transaction\Transaction;

require_once(dirname(__FILE__) . '/../vault.php');
include_once(DIR_SYSTEM . 'library/autoload.php');

class PGRiskInfo extends Model
{
	/** @var int $customer_id */
	protected $customer_id;
	/** @var Transaction $transaction */
	protected $transaction;

	public function __construct($registry, $transaction) {
		parent::__construct($registry);
		$this->load->model('account/customer');
		$this->transaction = $transaction;
	}

	/**
	 * Create SDK\RiskInfo
	 * Map all existing data
	 *
	 * @since 1.5.0
	 */
	public function mapRiskInfo() {
		// Map all settings and create SDK account info

		//@TODO Clarification pending
		if (false) {
			$this->transaction->setRiskInfo($this->initializeRiskInfo());
		}
	}

	/**
	 * Initialize SDK\RiskInfo
	 *
	 * @return RiskInfo
	 *
	 * @since 1.5.0
	 */
	protected function initializeRiskInfo() {
		$riskInfo = new RiskInfo();

		if ($this->customer->isLogged()) {
			$this->addDeliveryEmailAddress($riskInfo);
			$this->addReorderItemsIndicator($riskInfo);
		}
		$this->addInfoAvailability($riskInfo);

		return $riskInfo;
	}

	/**
	 * Add delivery email address to risk info
	 *
	 * @param RiskInfo $risk_info
	 *
	 * @since 1.5.0
	 */
	protected function addDeliveryEmailAddress($risk_info) {
		//@TODO If clarified - send if atleast 1 item in basket is digital good?
		$risk_info->setDeliveryEmailAddress($this->customer->getEmail());
	}

	/**
	 * Add if preorder flag
	 *
	 * @param RiskInfo $risk_info
	 *
	 * @since 1.5.0
	 */
	protected function addInfoAvailability($risk_info) {
		//@TODO If clarified - send if atleast 1 item in basket is out of stock?
		$risk_info->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
		// if product status_id != 7 (in stock)
		$risk_info->setAvailability(RiskInfoAvailability::FUTURE_AVAILABILITY);
		$this->addDateAvailable($risk_info);
		//endif
	}

	/**
	 * Add product date available of preordered product
	 *
	 * @param RiskInfo $risk_info
	 *
	 * @since 1.5.0
	 */
	protected function addDateAvailable($risk_info) {
		//@TODO If clarified - send if atleast 1 item in basket is out of stock?
		// if product stock_status_id = 8 (Pre-Order) or 6 (2-3 Days) set date available = product date_available
		$date = new DateTime();
		$risk_info->setPreOrderDate($date);
	}

	/**
	 * Add reordered flag
	 *
	 * @param RiskInfo $risk_info
	 *
	 * @since 1.5.0
	 */
	protected function addReorderItemsIndicator($risk_info) {
		$risk_info->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);
		//@TODO If clarified - send if atleast 1 item in basket is reordered?
		//@TODO Check if item reordered
		$risk_info->setReorderItems(RiskInfoReorder::REORDERED);
	}
}
