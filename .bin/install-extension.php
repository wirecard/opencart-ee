<?php
/**
 * Temporary installation script for opencart-ee, to be improved
 *
 * Installation guide:
 *   - Extract opencart-ee.ocmod.zip
 *   - Set correct database credentials
 *   - Execute install-extension.php
 *   - Remove install.xml
 *   - Move files from upload folder to the specific directories (admin, catalog, system, image)
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
 * Add payment permission to database
 *
 * @param $db
 * @param $filename
 * @param int $extension_download_id
 * @return mixed
 */
function addExtensionPermission($db, $filename, $paymentMethod)
{
    $permission = $db->query("SELECT permission FROM oc_user_group WHERE permission LIKE '%wirecard%';");
    $permission->access = array_push($permission->access, 'extension\/payment\/wirecard_pg_' . $paymentMethod);


    $result = $db->query("UPDATE oc_user_group SET permission = $permission WHERE user_group_id = 1;");
    return $db->insert_id;
}


/**
 * Add extension to database
 *
 * @param $db
 * @param $filename
 * @param int $extension_download_id
 * @return mixed
 */
function addExtensionInstall($db, $filename, $extension_download_id = 0)
{
    $result = $db->query("INSERT INTO `" . DB_PREFIX . "extension_install` SET `filename` = '" . $filename . "', `extension_download_id` = '" . (int)$extension_download_id . "', `date_added` = NOW()");

    return $db->insert_id;
}

/**
 * Add extension paths to database
 *
 * @param $db
 * @param $extension_install_id
 * @param $path
 */
function addExtensionPath($db, $extension_install_id, $path)
{
    $db->query("INSERT INTO `" . DB_PREFIX . "extension_path` SET `extension_install_id` = '" . (int)$extension_install_id . "', `path` = '" . $path . "', `date_added` = NOW()");
}

/**
 * Add modification to database
 *
 * @param $db
 * @param $data
 */
function addModification($db, $data)
{
    $result = $db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `extension_install_id` = '" . (int)$data['extension_install_id'] . "', `name` = '" . $data['name'] . "', `code` = '" . $data['code'] . "', `author` = '" . $data['author'] . "', `version` = '" . $data['version'] . "', `link` = '" . $data['link'] . "', `xml` = '" . $db->real_escape_string($data['xml']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW()");
}

/**
 * Install opencart-ee extension in opencart including modification
 *
 * @param $db
 */
function install($db, $filename)
{
    $extension_install_id = addExtensionInstall($db, $filename);
    $directory = 'upload/';

    $files = array();

    // Get a list of files
    $path = array($directory . '*');

    while (count($path) != 0) {
        $next = array_shift($path);

        foreach ((array)glob($next) as $file) {
            if (is_dir($file)) {
                $path[] = $file . '/*';
            }
            $files[] = $file;
        }
    }

    foreach ($files as $file) {
        $destination = str_replace('\\', '/', substr($file, strlen($directory . '')));

        $path = '';
        if (substr($destination, 0, 5) == 'admin') {
            $path = 'admin/' . substr($destination, 6);
        }
        if (substr($destination, 0, 7) == 'catalog') {
            $path = 'catalog/' . substr($destination, 8);
        }
        if (substr($destination, 0, 5) == 'image') {
            $path = 'image/' . substr($destination, 6);
        }
        if (substr($destination, 0, 6) == 'system') {
            $path = 'system/' . substr($destination, 7);
        }
        if (is_dir($file) && !is_dir($path)) {
            if (mkdir($path, 0777)) {
                addExtensionPath($db, $extension_install_id, $destination);
            }
        }
        if (is_file($file)) {
            if (rename($file, $path)) {
                addExtensionPath($db, $extension_install_id, $destination);
            }
        }
    }

    $modification_data = array(
        'extension_install_id' => $extension_install_id,
        'name' => 'Wirecard Payment Processing Gateway',
        'code' => 'WirecardPG',
        'author' => 'Wirecard',
        'version' => VERSION,
        'link' => '',
        'xml' => file_get_contents('./install.xml'),
        'status' => 1
    );
    addModification($db, $modification_data);
}

// main script - read payment method from command line, build the config and write it into database
if (count($argv) < 2) {
    echo <<<END_USAGE
Usage: php install-extension.php <extension-file>
END_USAGE;
    exit(1);
}
$extensionFile = trim($argv[1]);
$db = connectDatabase();
install($db, $extensionFile);
$db->close();
print_r('Installation complete');

