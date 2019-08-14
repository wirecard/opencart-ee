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
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */


define("DB_PREFIX", 'oc_');
define("DB_DATABASE", getenv('OPENCART_DATABASE_NAME'));
define("DB_HOSTNAME", getenv('MARIADB_HOST'));
define("DB_USERNAME", getenv('MARIADB_ROOT_USER'));
define("DB_PASSWORD", getenv('MARIADB_ROOT_PASSWORD'));
// the path for different config files, each named as <paymentmethod>.json
define('GATEWAY_CONFIG_PATH', 'gateway_configs');


$gateway = getenv('GATEWAY');
if (!$gateway) {
    $gateway = 'API-TEST';
}

// the default config defines valid keys for each payment method and is prefilled with API-TEST setup by default
$defaultConfig = [
    'creditcard' => [
        'status' => 1,
        'title' => '{"en":"Wirecard Credit Card"}',
        'sort_order' => 1,
        'merchant_account_id' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
        'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
        'payment_action' => 'pay',
        'three_d_merchant_account_id' => '508b8896-b37d-4614-845c-26bf8bf2c948',
        'three_d_merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
        'ssl_max_limit' => 100,
        'three_d_min_limit' => 50,
        'base_url' => 'https://api-test.wirecard.com',
        'http_user' => '70000-APITEST-AP',
        'http_password' => 'qD2wzQ_hrc!8',
        'vault' => 0,
        'allow_changed_shipping' => 0,
        'descriptor' => 0,
        'additional_info' => 1,
        'delete_cancel_order' => 0,
        'delete_failure_order' => 0,
    ],
];

// main script - read payment method from command line, build the config and write it into database
if (count($argv) < 2) {
    $supportedPaymentMethods = implode("\n  ", array_keys($GLOBALS['defaultConfig']));
    echo <<<END_USAGE
Usage: php install-payment.php <payment-method>

Supported payment methods:
  $supportedPaymentMethods
  
END_USAGE;
    exit(1);
}

$paymentMethod = trim($argv[1]);
$dbConfig = buildConfigByPaymentMethod($paymentMethod, $gateway);
if (empty($dbConfig)) {
    echo "Payment method $paymentMethod is not supported\n";
    exit(1);
}

$db = connectDatabase();
//installExtension($db);
addPaymentMethodToDb($db, $paymentMethod);
//addPermissionToDb($db);
addPermissionToDb($db, $paymentMethod);
//installExtension($db);
configurePaymentMethodInDb($db, $dbConfig, $paymentMethod);
$db->close();
echo "Installation complete and " . $paymentMethod . " configured\n";


/**
 * Create connection to opencart database and return mysqli connection object
 *
 * @return mysqli $mysqli
 * @since   1.4.0
 */
function connectDatabase()
{
    echo "Connecting to database \n";
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    /* check connection */
    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }
    return $mysqli;
}

/**
 * Add extension to database
 *
 * @param mysqli $db
 * @since   1.4.0
 */
function installExtension($db)
{
    echo "Installing extension \n";
    $tableName = DB_PREFIX . "extension";
    //add extension module to DB
    $db->query("INSERT INTO " . $tableName . " (type, code) VALUES ('module','wirecard_pg')");
    //create transaction table
    $db->query("
          CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wirecard_ee_transactions` (
            `tx_id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `transaction_id` VARCHAR(128) NOT NULL,
            `parent_transaction_id` VARCHAR(128) DEFAULT NULL,
            `transaction_type` VARCHAR(32) NOT NULL,
            `payment_method` VARCHAR(32) NOT NULL,
            `transaction_state` VARCHAR(32) NOT NULL,
            `amount` DECIMAL(10, 6) NOT NULL,
            `currency` VARCHAR(3) NOT NULL,
            `response` TEXT default NULL,
            `transaction_link` VARCHAR(255) default NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`tx_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

    //create vault table
    $db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wirecard_ee_vault` (
			`vault_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` INT(10) NOT NULL,
			`address_id` INT(10) NOT NULL,
			`token` VARCHAR(20) NOT NULL,
			`masked_pan` VARCHAR(30) NOT NULL,
			`expiration_month` INT(10) NOT NULL,
			`expiration_year` INT(10) NOT NULL,
			PRIMARY KEY (`vault_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
}

/**
 * Add payment method to database
 *
 * @param mysqli $db
 * @param string $paymentMethod
 * @since   1.4.0
 */
function addPaymentMethodToDb($db, $paymentMethod)
{
    echo "Adding " . $paymentMethod . " payment method to database \n";
    $tableName = DB_PREFIX . "extension";
    $db->query("INSERT INTO " . $tableName . " (type, code) VALUES ('payment','wirecard_pg_" . $paymentMethod . "')");
}

/**
 * Add extension permission or payment method permission to database
 *
 * @param mysqli $db
 * @param string $paymentMethod
 * @since   1.4.0
 */
function addPermissionToDb($db, $paymentMethod = null)
{
    $tableName = DB_PREFIX . "user_group";
    $newPermissions = ['extension/module/wirecard_pg'];

    if (isset($paymentMethod)) {
        $newPermissions = ['extension/payment/wirecard_pg_' . $paymentMethod];
        echo "Adding permissions for " . $paymentMethod . " payment method to database \n";
    } else {
        echo "Adding permissions for extension to database \n";
    }

    $dbQuery = $db->query("SELECT permission FROM " . $tableName);

    $dbResult = $dbQuery->fetch_all();
    $jsonPermissionObj = $dbResult[0][0];

    $permissionObj = json_decode($jsonPermissionObj, JSON_OBJECT_AS_ARRAY);
    foreach ($newPermissions as $newPermission) {
        $permissionObj["access"][] = $newPermission;
        $permissionObj["modify"][] = $newPermission;
    }
    $newPermissionJson = json_encode($permissionObj);
    $dbUpdateQuery = "UPDATE " . $tableName . " SET permission = '" . $newPermissionJson . "'";
    $db->query($dbUpdateQuery);
}

/**
 * Build configuration array for payment method
 *
 * @param string $paymentMethod
 * @param string $gateway
 * @return array|null
 * @since   1.4.0
 */
function buildConfigByPaymentMethod($paymentMethod, $gateway)
{
    echo 'Getting configuration for gateway ' . $gateway . "\n";
    if (!array_key_exists($paymentMethod, $GLOBALS['defaultConfig'])) {
        return null;
    }
    $config = $GLOBALS['defaultConfig'][$paymentMethod];

    $jsonFile = GATEWAY_CONFIG_PATH . DIRECTORY_SEPARATOR . $paymentMethod . '.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile));
        if (!empty($jsonData) && !empty($jsonData->$gateway)) {
            foreach (get_object_vars($jsonData->$gateway) as $key => $data) {
                // only replace values from json if the key is defined in defaultDbValues
                if (array_key_exists($key, $config)) {
                    $config[$key] = $data;
                }
            }
        }
    }
    return $config;
}

/**
 * Configure payment method in database
 *
 * @param mysqli $db
 * @param array $dbConfig
 * @param string $paymentMethod
 * @since   1.4.0
 */
function configurePaymentMethodInDb($db, $dbConfig, $paymentMethod)
{
    echo 'Configuring ' . $paymentMethod . " payment method in the shop system \n";
    // table name
    $tableName = 'oc_setting';
    $paymentMethodCode = 'payment_wirecard_pg_' . $paymentMethod;
    $paymentMethodPrefix = $paymentMethodCode . '_';

    foreach ($dbConfig as $name => $value) {
        $fullName = $paymentMethodPrefix . $name;
        // remove existing config if any exists - or do nothing
        $db->query("DELETE FROM $tableName WHERE `key` = '" . $fullName . "'");
        $serialized = "0";
        if (strpos($fullName, "title") !== false) {
            $serialized = "1";
        }
        $db->query("INSERT INTO $tableName (`code`, `key`, `value`, `serialized`) VALUES ('" . $paymentMethodCode . "', '" . $fullName . "', '" . $value . "' , '" . $serialized . "')");
    }
}

///**
// * Emulate "install" button pressing in the shopsystem
// * @since   1.4.0
// */
//function installModuleButton()
//{
//    define('INSTALL_FILE_DIR', '');
//
//    require_once '/opt/bitnami/opencart/admin/controller/extension/module/wirecard_pg.php';
//    $classname = "ControllerExtensionModuleWirecardPG";
//    call_user_func(array($classname, 'install'));
//}