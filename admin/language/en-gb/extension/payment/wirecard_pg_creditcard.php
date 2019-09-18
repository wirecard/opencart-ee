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
$_['config_three_d_merchant_account_id'] = '3-D Secure MAID';
$_['config_three_d_merchant_account_id_desc'] = 'Unique identifier assigned to your 3-D Secure merchant account. Can be set to "null" to force SSL process.';
$_['config_three_d_merchant_secret'] = '3-D Secure Secret Key';
$_['config_three_d_merchant_secret_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for the 3-D Secure payment. Can be set to "null" to force SSL process.';
$_['config_ssl_max_limit'] = 'Non 3-D Secure Max. Limit';
$_['config_limit_desc'] = 'Amount in default shop currency';
$_['config_three_d_min_limit'] = '3-D Secure Min. Limit';
$_['config_merchant_account_id_cc_desc'] = 'Unique identifier assigned to your merchant account. Can be set to "null" to force 3-D process.';
$_['config_merchant_secret_cc_desc'] = 'Secret Key is mandatory to calculate the Digital Signature for payments. Can be set to "null" to force 3-D process.';
$_['text_vault'] = "One-Click Checkout";
$_['config_vault'] = "One-Click Checkout";
$_['config_vault_desc'] = "Credit Card details are saved for later use.";
$_['config_allow_changed_shipping'] = "Allow Shipping Address Change";
$_['config_allow_changed_shipping_desc'] = "If disabled, consumer is required to re-enter credit card details if the shipping address has changed between two orders.";

$_['config_challenge_indicator'] = 'Challenge Indicator';
$_['config_challenge_indicator_desc'] = 'Indicates whether a challenge is requested for this transaction.';
$_['config_challenge_no_preference'] = 'No preference';
$_['config_challenge_no_challenge'] = 'No challenge requested';
$_['config_challenge_challenge_threed'] = 'Challenge requested';
