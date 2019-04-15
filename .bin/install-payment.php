<?php
/**
 * Temporary installation script for opencart-ee, to be improved
 */

define("DB_PREFIX", 'oc_');
define("DB_DATABASE", getenv('OPENCART_DATABASE_NAME'));
define("VERSION", getenv('BITNAMI_IMAGE_VERSION'));
define("DB_HOSTNAME", getenv('MARIADB_HOST'));
define("DB_USERNAME", getenv('MARIADB_ROOT_USER'));
define("DB_PASSWORD", getenv('MARIADB_ROOT_PASSWORD'));


/**
 * Create connection to opencart database and return mysqli connection object
 *
 * @return mysqli
 */
function connectDatabase()
{
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
 * @param $db
 * @param $paymentMethod
 * @return mixed
 */
function addExtension($db, $paymentMethod){
    $db->query("INSERT INTO oc_extension (type, code) VALUES ('payment','wirecard_pg_". $paymentMethod ."')");
    return $db->insert_id;
}

/**
 * Add extension permission to database
 *
 * @param $db
 * @param $paymentMethod
 * @return mixed
 */
function addExtensionPermission($db, $paymentMethod){

    $newPermission = 'extension/payment/wirecard_pg_' . $paymentMethod;

    $dbQuery = $db->query("SELECT permission FROM oc_user_group");
    $dbResult = $dbQuery->fetch_all();
    $jsonPermissionObj = $dbResult[0][0];

    $permissionObj = json_decode($jsonPermissionObj, JSON_OBJECT_AS_ARRAY);
    $permissionObj["access"][] = $newPermission;
    $permissionObj["modify"][] = $newPermission;

    $newPermissionJson = json_encode($permissionObj);
    $dbUpdateQuery = "UPDATE oc_user_group SET permission = '" . $newPermissionJson . "'";
    $db->query($dbUpdateQuery);
    return $db->insert_id;

}

// main script - read payment method from command line, build the config and write it into database
if (count($argv) < 2) {
    echo <<<END_USAGE
Usage: php install-payment.php <payment-method>
END_USAGE;
    exit(1);
}
$paymentMethod = trim($argv[1]);
// main script - read payment method from command line, configure it in the database
$db = connectDatabase();
addExtension($db, $paymentMethod);
addExtensionPermission($db, $paymentMethod);
$db->close();
print_r('Installation complete');

