<?php
/**
 * Pdo.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class extends the PHP PDO class and adds table prefix support to all queries. It also provides some helper functions.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_Pdo extends PDO {
	/** @ignore */
	private $tablePrefix, $sqliteFilePath;

	/**
	 * Constructs a new PDO intance.
	 *
	 * @param string $dsn Connection string.
	 * @param string $user User to login to database with.
	 * @param string $password Password to login to database with.
	 * @param Array $driverOptions Various driver option.
	 * @param string $prefix Table prefix to use instead of the default one.
	 */
	public function __construct($dsn, $user = null, $password = null, $driverOptions = array(), $prefix = null) {
		if (strpos($dsn, "sqlite:") === 0) {
			$this->sqliteFilePath = substr($dsn, strlen("sqlite:"));
		}

		if (strpos($dsn, "mysql:") === 0) {
			$newDsn = array();
			$parts = explode(';', substr($dsn, strlen("mysql:")));
			foreach ($parts as $part) {
				$part = explode('=', $part);

				if ($part[0] == 'username') {
					$user = trim($part[1]);
				} else if ($part[0] == 'password') {
					$password = trim($part[1]);
				} else {
					$newDsn[] = implode('=', $part);
				}
			}

			$dsn = "mysql:" . implode(';', $newDsn);
		}

		parent::__construct($dsn, $user, $password, $driverOptions);

		$this->tablePrefix = $prefix;
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	/**
	 * Returns the Sqlite filepath.
	 *
	 * @return String Sqlfile path.
	 */
	public function getSqliteFilePath() {
		return $this->sqliteFilePath;
	}

	/**
	 * Returns the name of the current driver. For example: mysql.
	 *
	 * @return String Currently used driver name.
	 */
	public function getDriverName() {
		return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Executes the specified query and returns a statement.
	 *
	 * @param string $statement This is a description
	 * @return PDOStatement Query statement.
	 */
	public function exec($statement) {
		return parent::exec($this->replacePrefix($statement));
	}

	/**
	 * Creates a new prepared statment instance.
	 *
	 * @param string $statement Statement to prepare.
	 * @param Array $driverOptions Array with driver options.
	 */
	public function prepare($statement, $driverOptions = array()) {
		return parent::prepare($this->replacePrefix($statement), $driverOptions);
	}

	/**
	 * Executes the specified query.
	 *
	 * @param string $statement This is a description
	 * @return PDOStatement Query statement.
	 */
	public function query($statement) {
		$args = func_get_args();
		$args[0] = $this->replacePrefix($args[0]);

		return call_user_func_array(array($this, 'parent::query'), $args);
	}

	/**
	 * Executes the specified query and returns the first column as an integer.
	 *
	 * @param string $statement Statement to execute.
	 * @return int First column as an integer.
	 */
	public function i($statement) {
		// Get arguments
		$args = array_slice(func_get_args(), 1);
		if (isset($args[0]) && is_array($args[0])) {
			$args = $args[0];
		}

		$result = $this->q($statement, $args)->fetch();

		return intval($result[0]);
	}

	/**
	 * Executes the specified query.
	 *
	 * @param string $statement This is a description
	 * @return PDOStatement Query statement.
	 */
	public function q($statement) {
		// Get arguments
		$args = array_slice(func_get_args(), 1);
		if (isset($args[0]) && is_array($args[0])) {
			$args = $args[0];
		}

		$statement = $this->prepare($statement);
		$statement->execute($args);

		return $statement;
	}

	/**
	 * Executes the specified query and returns and array with assoc arrays.
	 *
	 * @param string $statement This is a description
	 * @return Array Array of result items.
	 */
	public function qrs($statement) {
		// Get arguments
		$args = array_slice(func_get_args(), 1);
		if (isset($args[0]) && is_array($args[0])) {
			$args = $args[0];
		}

		$statement = $this->q($statement, $args);
		$result = array();

		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$result[] = $row;
		}

		return $result;
	}

	/** @ignore */
	private function replacePrefix($statement) {
		if ($this->tablePrefix) {
			return str_replace('moxman_', $this->tablePrefix, $statement);
		}

		return $statement;
	}
}

?>