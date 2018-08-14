<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

// Page Title
$_['heading_title'] = 'Wirecard Guaranteed Invoice by Wirecard / Ratepay';
$_['text_wirecard_pg_ratepayinvoice'] = '<img src="./view/image/wirecard_pg/ratepayinvoice.png"/>';

// Payment specific configuration
$_['text_edit'] = 'Edit Guaranteed Invoice by Wirecard / Ratepay';
$_['config_status_desc'] = 'Activate payment method Guaranteed Invoice by Wirecard / Ratepay to make it available for your consumers.';


$_['config_billing_shipping'] = 'Billing/shipping address must be identical';
$_['config_billing_shipping_desc'] = 'If activated, payment method Guaranteed Invoice will only be displayed if billing/shipping address are identical.';
$_['config_billing_countries'] = 'Allowed billing countries';
$_['config_billing_countries_desc'] = 'Payment method Guaranteed Invoice is only displayed during checkout if the consumer\'s billing country is one of the selected countries. STRG-click to select. Default pre-selection: Austria and Germany.';
$_['config_shipping_countries'] = 'Allowed shipping countries';
$_['config_shipping_countries_desc'] = 'Payment method Guaranteed Invoice is only displayed if the consumer\'s shipping country equals one of these selected countries. STRG-click to select. Default pre-selection: Austria and Germany.';
$_['config_allowed_currencies'] = 'Allowed currencies';
$_['config_allowed_currencies_desc'] = 'Payment method Guaranteed Invoice will only be displayed if the active currency is one of these selected currencies.';
$_['config_basket_min'] = 'Minimum Amount';
$_['config_basket_min_desc'] = 'Payment method Guaranteed Invoice will only be displayed if the ordered amount exceeds the amount defined here. Amount in default shop currency.';
$_['config_basket_max'] = 'Maximum Amount';
$_['config_basket_max_desc'] = 'Payment method Guaranteed Invoice will only be displayed if the ordered amount is smaller than this defined amount. Amount in default shop currency.';

// Post-processing operations
$_['text_article_number'] = 'Article-Number';
$_['text_article_name'] = 'Product Name';
$_['text_tax_rate'] = 'Tax Rate';
$_['text_article_amount'] = '# Amount';
$_['text_quantity'] = 'Quantity';
