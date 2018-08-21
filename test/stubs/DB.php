<?php

class DB {
	private $adaptor;

	public function __construct($adaptor, $hostname, $username, $password, $database, $port = NULL) {
		$this->adaptor = new stdClass();
	}

	public function query($sql) {
		return $sql;
	}

	public function escape($value) {
		return $value;
	}

	public function countAffected() {
		return 1;
	}

	public function getLastId() {
		return 1;
	}

	public function connected() {
		return true;
	}
}