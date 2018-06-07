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

/**
 * Class ModelExtensionPaymentGateway
 *
 * @since 1.0.0
 */
abstract class ModelExtensionPaymentGateway extends Model {

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
	 * Default payment method getter, method should only be returned if activated
	 *
	 * @param $address
	 * @param $total
	 * @return array
	 * @since 1.0.0
	 */
	public function getMethod($address, $total) {
		$prefix = $this->prefix . $this->type;

		$this->load->language('extension/payment/wirecard_pg_' . $this->type);
		$logo = '<img src="./image/wirecard_pg/'. $this->type .'.png" width="100"/>';
		$title = $logo . ' ' . $this->config->get($prefix . '_title');

		$method_data = array(
			'code'       => 'wirecard_pg_' . $this->type,
			'title'      => $title,
			'terms'      => '',
			'sort_order' => 1
		);

		return $method_data;
	}

	/**
	 * Process transaction request
	 *
	 * @param $config
	 * @param $transaction
	 * @param string $paymetAction
	 * @return \Wirecard\PaymentSdk\Response\Response
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function sendRequest($config, $transaction, $paymetAction) {
		$transactionService = new \Wirecard\PaymentSdk\TransactionService($config);

		try {
			/* @var \Wirecard\PaymentSdk\Response\Response $response */
			$response = $transactionService->process($transaction, $paymetAction);
		} catch (Exception $exception) {
			throw($exception);
		}

		$redirect = $this->url->link('checkout/checkout', '', true);
		if ($response instanceof \Wirecard\PaymentSdk\Response\InteractionResponse) {
			//$redirect = $response->getRedirectUrl();
			//Temporarly print responsedata
			$redirect = $response;
		} elseif ($response instanceof \Wirecard\PaymentSdk\Response\FailureResponse) {
			$errors = '';
			foreach ($response->getStatusCollection()->getIterator() as $item) {
				/** @var \Wirecard\PaymentSdk\Entity\Status $item */
				$errors .= $item->getDescription() . "<br>\n";
			}
			$this->session->data['error'] = $errors;
			$redirect = $this->url->link('checkout/checkout', '', true);
		}
		return $redirect;
	}
}
