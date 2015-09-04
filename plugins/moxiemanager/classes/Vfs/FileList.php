<?php
/**
 * FileList.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 *
 * @package MOXMAN_Vfs
 */
class MOXMAN_Vfs_FileList implements IteratorAggregate {
	protected $offset = 0, $length, $isOrdered;
	protected $files, $orderBy, $desc, $count, $last;

	public function __construct($files) {
		$this->files = $files;
		$this->orderBy = "name";
		$this->count = count($files);
		$this->last = false;
	}

	public function orderBy($column, $desc = false) {
		$this->orderBy = strtolower($column);
		$this->desc = $desc;

		return $this;
	}

	public function limit($offset, $length = null) {
		$this->offset = $offset;
		$this->length = $length;

		return $this;
	}

	public function getIterator() {
		return new ArrayIterator($this->getFiles());
	}

	public function toArray() {
		return $this->getFiles();
	}

	public function getFiles() {
		// @codeCoverageIgnoreStart
		if ($this->isOrdered) {
			return $this->files;
		}
		// @codeCoverageIgnoreEnd

		$orderBy = $this->orderBy;
		$desc = $this->desc;
		$dirs = array();
		$files = array();

	 	foreach ($this->files as $file) {
			switch ($orderBy) {
				case "name":
					$orderValue = $file->getName();
					break;

				case "size":
					$orderValue = $file->getSize();
					break;

				case "lastmodified":
					$orderValue = $file->getLastModified();
					break;

				case "extension":
					$orderValue = pathinfo($file->getName(), PATHINFO_EXTENSION);
					break;
			}

			if ($file->isDirectory()) {
				$dirs[] = array($file, $orderValue);
			} else {
				$files[] = array($file, $orderValue);
			}
		}

		if ($orderBy == "name" || $orderBy == "extension") {
			if ($orderBy != "extension") {
				usort($dirs, array($this, "compareString"));

				if ($desc) {
					$dirs = array_reverse($dirs);
				}
			}

			usort($files, array($this, "compareString"));
		} else {
			if ($orderBy != "size") {
				usort($dirs, array($this, "compareNumbers"));

				if ($desc) {
					$dirs = array_reverse($dirs);
				}
			}

			usort($files, array($this, "compareNumbers"));
		}

		if ($desc) {
			$files = array_reverse($files);
		}

		$files = array_merge($dirs, $files);
		$files = array_slice($files, $this->offset, $this->length);

		for ($i = 0; $i < count($files); $i++) {
			$files[$i] = $files[$i][0];
		}

		$this->files = $files;
		$this->isOrdered = true;
		$this->last = $this->offset + count($files) >= $this->count;
		$this->offset += count($files);

		return $this->files;
	}

	public function getOffset() {
		if (!$this->isOrdered) {
			$this->getFiles();
		}

		return $this->offset;
	}

	public function isLast() {
		if (!$this->isOrdered) {
			$this->getFiles();
		}

		return $this->last;
	}

	// Private comparator functions

	/**
	 * Compares two numbers for sorting.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function compareNumbers($a, $b) {
		if ($a[1] === $b[1]) {
			return 0;
		}

		return ($a[1] < $b[1]) ? -1 : 1;
	}

	/**
	 * Compares two strings for sorting.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function compareString($a, $b) {
		return strcasecmp($a[1], $b[1]);
	}
}

?>