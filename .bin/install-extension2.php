<?php

define("DB_PREFIX", 'oc_');
define("DB_DATABASE", getenv('OPENCART_DATABASE_NAME'));
define("VERSION", getenv('BITNAMI_IMAGE_VERSION'));
define("DB_HOSTNAME", getenv('MARIADB_HOST'));
define("DB_USERNAME", getenv('MARIADB_ROOT_USER'));
define("DB_PASSWORD", getenv('MARIADB_ROOT_PASSWORD'));

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
function addExtension($db, $paymentMethod){
    $db->query("INSERT INTO `oc_extension` VALUES (45,'payment','wirecard_'". $paymentMethod .");");
}

/**
 * Add payment permission to database
 *
 * @param $db
 * @param $filename
 * @param int $extension_download_id
 * @return mixed
 */
function addExtensionPermission($db, $paymentMethod){

    $newPermission = 'extension/payment/wirecard_pg_' . $paymentMethod;

    $dbQuery = $db->query("SELECT permission FROM oc_user_group;");
    $dbResult = $dbQuery->fetch_all();
    $jsonPermissionObj = $dbResult[0][0];

    $permissionObj = json_decode($jsonPermissionObj, JSON_OBJECT_AS_ARRAY);
    $permissionObj["access"][] = $newPermission;
    $permissionObj["modify"][] = $newPermission;

    $newPermissionJson = json_encode($permissionObj);
    $dbUpdateQuery = "UPDATE oc_user_group SET permission = '" . $newPermissionJson . "'";
    $db->query($dbUpdateQuery);

    //
    //    $result = $db->query("UPDATE oc_user_group SET permission =" .  $string_access . " WHERE user_group_id = 1;");
    print_r($db->query("SELECT permission FROM oc_user_group;")->fetch_all());
    exit();
     //return $db->insert_id;

}



// main script - read payment method from command line, build the config and write it into database
$db = connectDatabase();
addExtensionPermission($db, 'paypal');
//install($db, $extensionFile);
$db->close();
print_r('Installation complete');

