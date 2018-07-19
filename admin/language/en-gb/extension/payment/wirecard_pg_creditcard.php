<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

// Page Title
$_['heading_title'] = 'Wirecard Credit Card';
$_['text_wirecard_pg_creditcard'] = '<img src="./view/image/wirecard_pg/creditcard.png" />';

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
$_['config_merchant_account_id_cc_desc'] = 'Unique identifier assigned to your merchant account. Can be set to "null" to force 3-D process.';
$_['config_merchant_secret_cc_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for payments. Can be set to "null" to force 3-D process.';
