<?php

/**
 * Tax class
 */
class Tax {
	public function calculate($x, $y, $z) {
		return $x;
	}
	public function getTax($amount, $id) {
		return $amount;
	}

	public function getRates($x, $d) {
	    return  array(1 => array('amount' => 10, 'rate' => 20));
    }
}