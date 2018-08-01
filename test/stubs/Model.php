<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
 */

/**
 * Model class
 */
abstract class Model {
    protected $registry;
    protected $load;
    public $tax;
    public $currency;
    public $model_localisation_currency;

    public function __construct($registry, $loader = null) {
        $this->registry = $registry;
        $this->tax = new Tax();
        $this->currency = new Currency();
        $this->load = new Loader($registry, $this);
        $this->model_localisation_currency = new Currency();
    }

    public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }
}