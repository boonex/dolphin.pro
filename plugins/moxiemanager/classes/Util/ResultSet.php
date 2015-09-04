<?php
/**
 * ResultSet.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles tabular resultsets like a database table.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_ResultSet {
	/** @ignore */
	private $cols, $rows, $header;

	/**
	 * Constructs a new result set instance.
	 *
	 * @param string $cols Comma separated list of columns.
	 * @param Array $header Name/value header array to attach to result set.
	 */
	public function __construct($cols, $header = array()) {
		$this->cols = explode(',', $cols);
		$this->rows = array();
		$this->header = $header;
	}

	/**
	 * Adds a new row to the result set the number of arguments
	 * to this function must match the number of columns used in the instance creation.
	 */
	public function add() {
		$args = func_get_args();

		if (count($args) !== count($this->cols)) {
			throw new Exception("Number of row items doesn't match the number of columns.");
		}

		$this->rows[] = $args;
	}

	/**
	 * Returns the current column names.
	 *
	 * @return Array Current columns as an array.
	 */
	public function getCols() {
		return $this->cols;
	}

	/**
	 * Sets a header item by name and value.
	 *
	 * @param string $name Name to set in header.
	 * @param Mixed $value Value to set in header.
	 */
	public function setHeaderItem($name, $value) {
		$this->header[$name] = $value;
	}

	/**
	 * Get the header as an name/value array.
	 *
	 * @return Array Name/value array with header items.
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * Returns the current number of rows added to the result set.
	 *
	 * @return int Number of rows added to result set.
	 */
	public function getRowCount() {
		return count($this->rows);
	}

	/**
	 * Returns an indexed array with name/value items of the internal structure.
	 *
	 * @reutrn Array Indexed array with rows with name/value arrays.
	 */
	public function getRows() {
		$rowsArr = array();

		for ($i = 0, $l = count($this->rows); $i < $l; $i++) {
			$rowsArr[] = $this->getRow($i);
		}

		return $rowsArr;
	}

	/**
	 * Returns the specified row by index or null if it wasn't found.
	 *
	 * @param int $index Index of item to retrive.
	 * @return Array Name/value array with values.
	 */
	public function getRow($index) {
		if (!isset($this->rows[$index])) {
			$null = null;
			return $null;
		}

		$row = $this->rows[$index];
		$obj = array();

		for ($i = 0, $l = count($row); $i < $l; $i++) {
			$obj[$this->cols[$i]] = $row[$i];
		}

		return $obj;
	}

	/**
	 * Fills the result set with the specified array data.
	 *
	 * @param Object $json Json object to populate the ResultSet with.
	 * @return MOXMAN_Util_ResultSet Result set instance based on the data.
	 */
	public static function fromJson($json) {
		$resultSet = new MOXMAN_Util_ResultSet(
			implode(',', $json->columns),
			isset($json->header) ? $json->header : array()
		);

		foreach ($json->data as $row) {
			call_user_func_array(array($resultSet, 'add'), $row);
		}

		return $resultSet;
	}

	/**
	 * Converts the internal structure to an array. This can then be serialized using JSON.
	 *
	 * @return Object Name/value array with header, columns and data.
	 */
	public function toJson() {
		$output = (object) array(
			"header" => $this->header,
			"columns" => $this->cols,
			"data" => $this->rows
		);

		return $output;
	}
}

?>