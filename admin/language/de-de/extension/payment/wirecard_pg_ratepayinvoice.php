<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

// Page Title
$_['heading_title_ratepayinvoice'] = 'Wirecard Garantierter Kauf auf Rechnung by Wirecard';
$_['text_wirecard_pg_ratepayinvoice'] = '<img src="./view/image/wirecard_pg/ratepayinvoice.png"/>';

// Payment specific configuration
$_['text_edit_ratepayinvoice'] = 'Zahlungsmittel Garantierter Kauf auf Rechnung by Wirecard bearbeiten';
$_['config_status_desc_ratepayinvoice'] = 'Zahlungsmittel Garantierter Kauf auf Rechnung by Wirecard aktivieren und dem Endkunden beim Checkout anbieten.';


$_['config_billing_shipping'] = 'Rechnungsadresse und Versandadresse müssen übereinstimmen';
$_['config_billing_shipping_desc'] = 'Bei Aktivierung wird Garantierter Kauf auf Rechnung nur im Zahlungsprozess angezeigt, wenn Rechnungs- und Lieferadresse übereinstimmen.';
$_['config_billing_countries'] = 'Erlaubte Rechnungsempfängerländer';
$_['config_billing_countries_desc'] = 'Garantierter Kauf auf Rechnung wird dem Endkunden beim Checkout nur angezeigt, wenn die Rechnungsadresse einem der hier gewählten Länder entspricht. Auswahl mehrerer Länder mit STRG-click. Voreingestelle Auswahl: Österreich und Deutschland';
$_['config_shipping_countries'] = 'Erlaubte Lieferländer';
$_['config_shipping_countries_desc'] = 'Garantierter Kauf auf Rechnung wird dem Endkunden beim Checkout nur angezeigt, wenn die Lieferadresse einem der hier gewählten Länder entspricht. Auswahl mehrerer Länder mit STRG-click. Voreingestelle Auswahl: Österreich und Deutschland.';
$_['config_allowed_currencies'] = 'Erlaubte Währungen';
$_['config_allowed_currencies_desc'] = 'Garantierter Kauf auf Rechnung wird nur angezeigt, wenn eine der ausgewählten Währungen genutzt wird.';
$_['config_basket_min'] = 'Minimaler Betrag';
$_['config_basket_min_desc'] = 'Garantierter Kauf auf Rechnung wird nur angezeigt, wenn der Bestellbetrag höher ist als der hier definierte Wert (in Standardwährung).';
$_['config_basket_max'] = 'Maximaler Betrag';
$_['config_basket_max_desc'] = 'Garantierter Kauf auf Rechnung wird nur angezeigt, wenn der Bestellbetrag niedriger ist als der hier definierte Wert (in Standardwährung).';

// Post-processing operations
$_['text_article_number'] = 'Artikelnummber';
$_['text_article_name'] = 'Produktname';
$_['text_tax_rate'] = 'Steuersatz';
$_['text_article_amount'] = '# Preis';
$_['text_quantity'] = 'Stück';

$_['heading_title'] = $_['heading_title_ratepayinvoice'];
$_['text_edit'] = $_['text_edit_ratepayinvoice'];
$_['config_status_desc'] = $_['config_status_desc_ratepayinvoice'];