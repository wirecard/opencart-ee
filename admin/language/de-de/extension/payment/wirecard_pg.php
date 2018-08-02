<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

// Breadcrumb
$_['text_extension'] = 'Erweiterungen';

// Admin Panel
$_['heading_title'] = 'Wirecard Transaktionen';
$_['text_list'] = 'Transaktionen';

// Transaction Table
$_['panel_transaction'] = 'Transaktion';
$_['panel_order_number'] = 'Bestellnummer';
$_['panel_transcation_id'] = 'Transaction ID';
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
$_['text_backend_operations'] = 'Mögliche Folgeoperationen';
$_['text_request_amount'] = 'Betrag';
$_['error_no_transaction'] = 'Es wurde keine Transaktion gefunden';
$_['success_new_transaction'] = 'Die Folgeoperation war erfolgreich. Eine neue Transaktion wurde erstellt.';

// Configuration
$_['text_enabled'] = 'Aktiviert';
$_['text_disabled'] = 'Deaktiviert';
$_['text_credentials'] = 'Zugangsdaten';
$_['text_advanced'] = 'Erweiterte Einstellungen';
$_['test_credentials'] = 'Konfiguration testen';
$_['config_status'] = 'Status';
$_['config_title'] = 'Titel';
$_['config_title_desc'] = 'Name des Zahlungsmittels wie auf der Bezahlseite angezeigt.';
$_['config_merchant_account_id'] = 'MAID';
$_['config_merchant_account_id_desc'] = 'Eindeutige Händler-Konto-ID (Merchant Account ID) laut Vertrag mit Wirecard';
$_['config_merchant_secret'] = 'Secret Key';
$_['config_merchant_secret_desc'] = 'Der Secret Key wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen';
$_['config_base_url'] = 'Wirecard Server Address';
$_['config_base_url_desc'] = 'Wirecard Base URL (z.B. https://api.wirecard.com).';
$_['config_http_user'] = 'HTTP Benutzer';
$_['config_http_user_desc'] = 'Wirecard HTTP Benutzer laut Vertrag mit Wirecard.';
$_['config_http_password'] = 'HTTP Passwort';
$_['config_http_password_desc'] = 'Wirecard HTTP Passwort laut Vertrag mit Wirecard.';
$_['config_shopping_basket'] = 'Warenkorb';
$_['config_shopping_basket_desc'] = 'Das Zahlungsmittel unterstützt die Anzeige des Warenkorbs während des Checkouts. Um dieses Feature zu verwenden, Shopping Basket aktivieren.';
$_['config_descriptor'] = 'Deskriptor';
$_['config_descriptor_desc'] = 'Aktivieren Sie den Deskriptor, um bei jeder Transaktion eine Referenz zur jeweiligen Bestellung mitzuschicken. Diese Referenz wird im Buchungstext angezeigt, der dem Endkunden vom Finanzdienstleister übermittelt wird.';
$_['config_additional_info'] = 'Zusätzliche Informationen senden';
$_['config_additional_info_desc'] = 'Zusätzliche Informationen werden für Betrugsschutz gesendet. In diesen Daten sind  Rechnungs- / Lieferadresse, Warenkorb und Deskriptor inkludiert.';
$_['config_payment_action'] = 'Zahlungsaktion';
$_['text_payment_action_pay'] = 'Purchase';
$_['text_payment_action_reserve'] = 'Authorization';
$_['config_payment_action_desc'] = 'Wählen Sie zwischen "Purchase" um automatisch eine Buchung durchzuführen oder "Authorization" um eine manuelle Buchung zu ermöglichen.';
$_['config_sort_order'] = 'Reihenfolge';
$_['config_sort_order_desc'] = 'Reihenfolge der angezeigten Zahlungsmittel auf der Bezahlseite.';
$_['config_delete_cancel_order'] = 'Abgebrochene Bestellung löschen';
$_['config_delete_cancel_order_desc'] = 'Bestellung nach Abbruch des Zahlungsprozesses automatisch löschen.';
$_['config_delete_failure_order'] = 'Fehlgeschlagene Bestellung löschen';
$_['config_delete_failure_order_desc'] = 'Bestellung nach fehlgeschlagenem Zahlungsprozess automatisch löschen.';

$_['text_success'] = 'Ihre Änderungen werden gespeichert.';
$_['success_credentials'] = 'Die Konfigurationseinstellungen wurden erfolgreich getestet.';
$_['error_credentials'] = 'Der Konfigurationstest ist fehlgeschlagen. Bitte überprüfen Sie Ihre Zugangsdaten.';
$_['wrong_url_format'] = 'Test fehlgeschlagen. Ungültiges Adressformat (z. B. https://api.wirecard.com).';

$_['config_email'] = 'Ihre E-Mail-Adresse:';
$_['config_message'] = 'Ihre Nachricht:';
$_['success_email'] = 'E-Mail wurde erfolgreich versendet.';
$_['error_email'] = 'E-Mail-Versand fehlgeschlagen. Bitte versuchen Sie es erneut.';
$_['send_email'] = 'Senden';
$_['back_button'] = 'Zurück';
$_['support_email_title'] = 'E-Mail an Support';

$_['terms_of_use'] = 'Nutzungsbedingungen';

$_['credit'] = "Kreditieren";
$_['pay'] = "Buchen";
$_['refund'] = "Refundieren";
$_['cancel'] = "Stornieren";
