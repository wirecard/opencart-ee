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
$_['heading_title'] = 'Wirecard Kreditkarte';
$_['text_wirecard_pg_creditcard'] = '<img src="./view/image/wirecard_pg/creditcard.png" />';

// Payment specific configuration
$_['text_edit'] = 'Zahlungsmittel Kreditkarte bearbeiten';
$_['config_status_desc'] = 'Zahlungsmittel Kreditkarte aktivieren und dem Endkunden beim Checkout anbieten. ';
$_['config_three_d_merchant_account_id'] = '3-D Secure MAID';
$_['config_three_d_merchant_account_id_desc'] = 'Geben Sie Ihre 3-D Secure Händler-Konto-ID (Merchant Account ID) ein. Kann auf "Null" gesetzt werden, um einen SSL-Prozess zu erzwingen.';
$_['config_three_d_merchant_secret'] = '3-D Secure Geheimschlüssel';
$_['config_three_d_merchant_secret_desc'] = 'Der Geheimschlüssel wird benötigt um die Digitale Signatur für diese 3D Zahlung zu berechnen. Kann auf "Null" gesetzt werden, um einen SSL-Prozess zu erzwingen.';
$_['config_ssl_max_limit'] = 'Non 3-D Secure Maximal Limit';
$_['config_limit_desc'] = 'Betrag in Standard Shop Währung.';
$_['config_three_d_min_limit'] = '3-D Secure Minimum Limit';
$_['config_merchant_account_id_cc_desc'] = 'Eindeutige Händler-Konto-ID (Merchant Account ID) laut Vertrag mit Wirecard. Kann auf "Null" gesetzt werden, um einen 3-D-Prozess zu erzwingen.';
$_['config_merchant_secret_cc_desc'] = 'Der Geheimschlüssel wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen. Kann auf "Null" gesetzt werden, um einen 3-D-Prozess zu erzwingen.';
