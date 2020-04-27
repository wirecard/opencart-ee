<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once __DIR__ . '/../system/library/autoload.php';
require_once __DIR__ . '/../system/library/opencart/opencart/upload/system/engine/proxy.php';

//load opencart stubs
require_once __DIR__ . '/stubs/Config.php';
require_once __DIR__ . '/stubs/Controller.php';
require_once __DIR__ . '/stubs/Model.php';
require_once __DIR__ . '/stubs/Loader.php';
require_once __DIR__ . '/stubs/Registry.php';
require_once __DIR__ . '/stubs/Session.php';
require_once __DIR__ . '/stubs/Url.php';
require_once __DIR__ . '/stubs/Language.php';
require_once __DIR__ . '/stubs/Cart.php';
require_once __DIR__ . '/stubs/Tax.php';
require_once __DIR__ . '/stubs/Currency.php';
require_once __DIR__ . '/stubs/Log.php';
require_once __DIR__ . '/stubs/DB.php';

// Helpers
require_once __DIR__ . '/helper/ResponseProvider.php';

//Defines
define('DIR_SYSTEM', __DIR__ . '/../system/');
define('HELPER_DIR', __DIR__ . '/../catalog/model/extension/payment/wirecard_pg/helper/');
define('DIR_APPLICATION', __DIR__ . '/../catalog/');
define('DIR_ADMIN', __DIR__ . '/../admin/');
define('DB_PREFIX', 'oc_');
define('VERSION', '3.0.2.0');
define('PLUGIN_VERSION', '1.5.1');