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

// Breadcrumb
$_['text_extension'] = 'Extensions';

// Admin Panel
$_['heading_title'] = 'Wirecard Transactions';
$_['text_list'] = 'Transactions';

// Transaction Table
$_['panel_transaction'] = 'Transaction';
$_['panel_order_number'] = 'Order number';
$_['panel_transcation_id'] = 'Transaction ID';
$_['panel_parent_transaction_id'] = 'Parent transaction ID';
$_['panel_action'] = 'Action';
$_['panel_payment_method'] = 'Payment method';
$_['panel_transaction_state'] = 'Transaction state';
$_['panel_amount'] = 'Amount';
$_['panel_currency'] = 'Currency';
$_['panel_details'] = 'Details';

// Transaction Details
$_['text_transaction'] = 'Transaction';
$_['heading_transaction_details'] = 'Transactiondetails';
$_['text_response_data'] = 'Response Data';
$_['text_backend_operations'] = 'Possible Post-Processing Operations';
$_['text_request_amount'] = 'Amount';
$_['error_no_transaction'] = 'No transaction available.';
$_['success_new_transaction'] = 'The post processing operation was successful. A new transaction was created';

// Configuration
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_credentials'] = 'Credentials';
$_['text_advanced'] = 'Advanced Options';
$_['test_credentials'] = 'Test Credentials';
$_['config_status'] = 'Status';
$_['config_title'] = 'Title';
$_['config_title_desc'] = 'Payment method name as displayed for the consumer during checkout.';
$_['config_merchant_account_id'] = 'Merchant Account ID';
$_['config_merchant_account_id_desc'] = 'Unique identifier assigned to your merchant account.';
$_['config_merchant_secret'] = 'Secret Key';
$_['config_merchant_secret_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for payments.';
$_['config_base_url'] = 'Base URL';
$_['config_base_url_desc'] = 'The Wirecard base URL. (e.g. https://api.wirecard.com)';
$_['config_http_user'] = 'HTTP User';
$_['config_http_user_desc'] = 'HTTP User as provided in your Wirecard contract.';
$_['config_http_password'] = 'HTTP Password';
$_['config_http_password_desc'] = 'HTTP Password as provided in your Wirecard contract.';
$_['config_shopping_basket'] = 'Shopping Basket';
$_['config_shopping_basket_desc'] = 'For the purpose of confirmation, payment supports shopping basket display during checkout. To enable this feature, activate Shopping Basket.';
$_['config_descriptor'] = 'Descriptor';
$_['config_descriptor_desc'] = 'Send text which is displayed on the bank statement issued to your consumer by the financial service provider.';
$_['config_additional_info'] = 'Send additional information';
$_['config_additional_info_desc'] = 'Additional data will be sent for the purpose of fraud protection. This additional data includes billing/shipping address, shopping basket and descriptor.';
$_['config_payment_action'] = 'Payment Action';
$_['text_payment_action_pay'] = 'Purchase';
$_['text_payment_action_reserve'] = 'Authorization';
$_['config_payment_action_desc'] = 'Select between "Purchase" to capture/invoice your order automatically or "Authorization" to capture/invoice manually.';
$_['config_sort_order'] = 'Sort Order';
$_['config_sort_order_desc'] = 'Order of the payment method on the checkout page';
$_['config_delete_cancel_order'] = 'Delete canceled order';
$_['config_delete_cancel_order_desc'] = 'Automatically delete order after canceled payment process.';
$_['config_delete_failure_order'] = 'Delete failed order';
$_['config_delete_failure_order_desc'] = 'Automatically delete order after failed payment process.';

$_['text_success'] = 'Your modifications are saved!';
$_['success_credentials'] = 'Merchant configuration was successfully tested.';
$_['error_credentials'] = 'Test failed, please check your credentials.';

$_['config_email'] = 'Your Email address:';
$_['config_message'] = 'Your message:';
$_['success_email'] = 'Your E-Mail was succesfully send!';
$_['error_email'] = 'There was an error sending you E-Mail!';
$_['send_email'] = 'Send';
$_['back_button'] = 'Back';
$_['support_email_title'] = 'Support Email';
