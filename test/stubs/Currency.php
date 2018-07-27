<?php

/**
 * Currency class
 */
class Currency {
	public function format($x, $y) {
		return $x;
	}

	public function getCurrencyByCode($code) {
	    return [
            'code' => 'EUR',
            'value' => 1
        ];
    }
}