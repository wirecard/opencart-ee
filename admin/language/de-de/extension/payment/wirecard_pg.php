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
$_['text_extension'] = '';

// Admin Panel
$_['heading_title'] = 'Wirecard Transaktionen';
$_['text_list'] = 'Transaktionen';

// Transaction Table
$_['panel_transaction'] = 'Transaktion';
$_['panel_order_number'] = 'Bestellnummer';
$_['panel_transcation_id'] = 'Transaktions-ID';
$_['panel_parent_transaction_id'] = 'Parent Transaction ID';
$_['panel_action'] = 'Aktion';
$_['panel_payment_method'] = 'Zahlungsmittel';
$_['panel_transaction_state'] = 'Transaktionsstatus';
$_['panel_amount'] = 'Betrag';
$_['panel_currency'] = 'Währung';
$_['panel_details'] = 'Details';

// Transaction Details
$_['text_transaction'] = 'Transaktion';
$_['heading_transaction_details'] = 'Transaktionsdetails';
$_['text_response_data'] = 'Responsedaten';
$_['text_backend_operations'] = '';
$_['text_request_amount'] = 'Betrag';
$_['error_no_transaction'] = 'Es wurde keine Transaktion gefunden';
$_['success_new_transaction'] = '';

// Configuration
$_['text_enabled'] = 'Aktiviert';
$_['text_disabled'] = 'Deaktiviert';
$_['text_credentials'] = 'Zugangsdaten';
$_['text_advanced'] = 'Erweiterte Einstellungen';
$_['test_credentials'] = 'Konfiguration testen';
$_['config_status'] = 'Status';
$_['config_title'] = 'Titel';
$_['config_title_desc'] = '';
$_['config_merchant_account_id'] = 'Händler-Konto-ID';
$_['config_merchant_account_id_desc'] = 'Eindeutige Händler-Konto-ID laut Vertrag mit Wirecard.';
$_['config_merchant_secret'] = 'Geheimschlüssel';
$_['config_merchant_secret_desc'] = 'Der Geheimschlüssel wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen';
$_['config_base_url'] = 'Base URL';
$_['config_base_url_desc'] = 'Wirecard Base URL (z.B. https://api.wirecard.com).';
$_['config_http_user'] = 'HTTP Benutzer';
$_['config_http_user_desc'] = 'Wirecard HTTP Benutzer laut Vertrag mit Wirecard.';
$_['config_http_password'] = 'HTTP Passwort';
$_['config_http_password_desc'] = 'Wirecard HTTP Passwort laut Vertrag mit Wirecard.';
$_['config_shopping_basket'] = 'Warenkorb';
$_['config_shopping_basket_desc'] = '';
$_['config_descriptor'] = 'Deskriptor';
$_['config_descriptor_desc'] = 'Aktivieren Sie den Deskriptor, um bei jeder Transaktion eine Referenz zur jeweiligen Bestellung mitzuschicken. Diese Referenz wird im Buchungstext angezeigt, der dem Endkunden vom Finanzdienstleister übermittelt wird.';
$_['config_additional_info'] = 'Zusätzliche Informationen senden';
$_['config_additional_info_desc'] = 'Zusätzliche Informationen werden für Betrugsschutz gesendet. In diesen Daten sind  Rechnungs- / Lieferadresse, Warenkorb und Deskriptor inkludiert.';
$_['config_payment_action'] = 'Zahlungsaktion';
$_['text_payment_action_pay'] = '';
$_['text_payment_action_reserve'] = '';
$_['config_payment_action_desc'] = 'Wählen Sie zwischen "Purchase" um automatisch eine Buchung durchzuführen oder "Authorization" um eine manuelle Buchung zu ermöglichen.';
$_['config_sort_order'] = '';
$_['config_sort_order_desc'] = '';
$_['config_delete_cancel_order'] = '';
$_['config_delete_cancel_order_desc'] = '';
$_['config_delete_failure_order'] = '';
$_['config_delete_failure_order_desc'] = '';

$_['text_success'] = '';
$_['success_credentials'] = 'Die Konfigurationseinstellungen wurden erfolgreich getestet.';
$_['error_credentials'] = 'Der Konfigurationstest ist fehlgeschlagen. Bitte überprüfen Sie Ihre Zugangsdaten.';
