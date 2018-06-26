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

// Page Title
$_['heading_title'] = 'Wirecard Credit Card';
$_['text_wirecard_pg_creditcard'] = '<img src="./view/image/wirecard_pg/creditcard.png" width="100"/>';

// Payment specific configuration
$_['text_edit'] = 'Edit Credit Card';
$_['config_status_desc'] = 'Activate payment method Credit Card to make it available for your consumers.';
$_['config_three_d_merchant_account_id'] = '3-D Secure Merchant Account ID';
$_['config_three_d_merchant_account_id_desc'] = 'Unique identifier assigned to your 3-D Secure merchant account. Can be set to "null" to force SSL process.';
$_['config_three_d_merchant_secret'] = '3-D Secure Secret Key';
$_['config_three_d_merchant_secret_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for the 3-D Secure payment. Can be set to "null" to force SSL process.';
$_['config_ssl_max_limit'] = 'Non 3-D Secure Max. Limit';
$_['config_limit_desc'] = 'Amount in default shop currency';
$_['config_three_d_min_limit'] = '3-D Secure Min. Limit';
$_['config_merchant_account_id_cc_desc'] = 'Unique identifier assigned to your merchant account. Can be set to "null" to force SSL process.';
$_['config_merchant_secret_cc_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for payments. Can be set to "null" to force SSL process.';
