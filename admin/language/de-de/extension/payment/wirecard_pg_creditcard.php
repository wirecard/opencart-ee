<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

// Page Title
$_['heading_title'] = 'Wirecard Kreditkarte';
$_['text_wirecard_pg_creditcard'] = '<img src="./view/image/wirecard_pg/creditcard.png" />';

// Payment specific configuration
$_['text_edit'] = 'Zahlungsmittel Kreditkarte bearbeiten';
$_['config_status_desc'] = 'Zahlungsmittel Kreditkarte aktivieren und dem Endkunden beim Checkout anbieten. ';
$_['config_three_d_merchant_account_id'] = '3-D Secure MAID';
$_['config_three_d_merchant_account_id_desc'] = 'Geben Sie Ihre 3-D Secure Händler-Konto-ID (Merchant Account ID) ein. Kann auf "Null" gesetzt werden, um einen SSL-Prozess zu erzwingen.';
$_['config_three_d_merchant_secret'] = '3-D Secure Secret Key';
$_['config_three_d_merchant_secret_desc'] = 'Der Secret Key wird benötigt um die Digitale Signatur für diese 3D Zahlung zu berechnen. Kann auf "Null" gesetzt werden, um einen SSL-Prozess zu erzwingen.';
$_['config_ssl_max_limit'] = 'Non 3-D Secure Maximal Limit';
$_['config_limit_desc'] = 'Betrag in Standard Shop Währung.';
$_['config_three_d_min_limit'] = '3-D Secure Minimum Limit';
$_['config_merchant_account_id_cc_desc'] = 'Eindeutige Händler-Konto-ID (Merchant Account ID) laut Vertrag mit Wirecard. Kann auf "Null" gesetzt werden, um einen 3-D-Prozess zu erzwingen.';
$_['config_merchant_secret_cc_desc'] = 'Der Secret Key wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen. Kann auf "Null" gesetzt werden, um einen 3-D-Prozess zu erzwingen.';
$_['config_merchant_secret_cc_desc'] = 'Der Geheimschlüssel wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen. Kann auf "Null" gesetzt werden, um einen 3-D-Prozess zu erzwingen.';
$_['text_vault'] = "One-Click Checkout";
$_['config_vault'] = "One-Click Checkout";
$_['config_vault_desc'] = "Kreditkarten können gespeichert und später wieder verwendet werden.";
$_['config_allow_changed_shipping'] = "Geänderte Lieferadresse zulassen";
$_['config_allow_changed_shipping_desc'] = "Wenn diese Option deaktiviert ist und sich die Lieferadresse des Kunden zwischen Transaktionen ändert, müssen sie ihre Kreditkarteninformationen erneut eingeben.";
$_['config_psd_two_heading'] = 'PSD 2';
$_['config_psd_two_text'] = 'Angesichts der Bestimmungen der PSD 2 sollten Sie beim <a href="https://github.com/wirecard/opencart-ee/wiki/Credit-Card" target="_blank">Checkout</a> bestimmte persönliche Daten von Ihren Kunden anfordern, um das Risiko, dass Transaktionen abgelehnt werden, zu reduzieren.';

$_['config_challenge_indicator'] = 'Challenge Indicator';
$_['config_challenge_indicator_desc'] = 'Indicates whether a challenge is requested for this transaction.';
$_['config_challenge_no_preference'] = 'No preference';
$_['config_challenge_no_challenge'] = 'No challenge requested';
$_['config_challenge_challenge_threed'] = 'Challenge requested';
