<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

namespace Helper;

use Codeception\Exception\ModuleException;

class DbHelper extends \Codeception\Module
{
	/**
	 * Method getColumnFromDatabaseNoCriteria
	 * @param string $table
	 * @param string $column
	 * @return array
	 *
	 * @throws ModuleException
	 * @since 1.5.0
	 */
	public function getColumnFromDatabaseNoCriteria($table, $column)
	{
	    var_dump("Envirnoment:". var_export($_ENV, true));
		$dbh = $this->getModule('Db')->dbh;
		$query = "select %s from %s";
		$query = sprintf($query, $column, $table);
		$this->debugSection('Query', $query);
		$sth = $dbh->prepare($query);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_COLUMN, 0);
	}
}
