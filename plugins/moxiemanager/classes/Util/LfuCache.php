<?php
/**
 * LfuCache.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class provides a key based Least-Frequently Used cache algoritm.
 *
 * It will keep track of the most used cached items and when the cache is full it will remove the less used items.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_LfuCache {
	/** @ignore */
	private $indexes, $items, $maxSize, $ttl, $time;

	/**
	 * Constructs a new instance with a specified size.
	 *
	 * @param array $config Otional array with config items for the lfu cache.
	 */
	public function __construct($config = array()) {
		$this->indexes = array();
		$this->items = array();
		$this->maxSize = isset($config["size"]) ? $config["size"] : 100;
		$this->ttl = isset($config["ttl"]) ? $config["ttl"] : 60;
		$this->time = isset($config["time"]) ? $config["time"] : 0;
	}

	/**
	 * Puts the specified object reference into the cache.
	 *
	 * @param string $key Name to put the cache item at.
	 * @param Object $obj Object to put in cache.
	 * @return Object Object reference to the item that got put in cache.
	 */
	public function &put($key, &$obj) {
		// Find index for key
		if (isset($this->indexes[$key])) {
			$index = $this->indexes[$key];
			$this->items[$index][2] =& $obj;
		} else {
			// Remove last item from indexes and items arrays
			if (count($this->items) >= $this->maxSize) {
				unset($this->indexes[$this->items[$this->maxSize - 1][1]]);
				array_splice($this->items, $this->maxSize - 1);
			}

			// Index not found then put it in there
			$this->indexes[$key] = count($this->items);
			$this->items[] = array(0, $key, &$obj, $this->time ? $this->time : time());
		}

		return $obj;
	}

	/**
	 * Removes an item by key.
	 *
	 * @param string $key Key to remove.
	 * @return MOXMAN_Util_LfuCache Cache instance.
	 */
	public function &remove($key) {
		if (isset($this->indexes[$key])) {
			$index = $this->indexes[$key];
			unset($this->indexes[$key]);
			array_splice($this->items, $index, 1);
		}

		return $this;
	}

	/**
	 * Returns true/false if the item by key is in cache or not.
	 *
	 * @param string $key Key to check for in cache.
	 * @return Boolean true if the item exists in cache.
	 */
	public function has($key) {
		return isset($this->indexes[$key]) && $this->items[$this->indexes[$key]][3] > time() - $this->ttl;
	}

	/**
	 * Returns a cached item by key or null if it doesn't exist.
	 *
	 * @param string $key Key to get object by from cache.
	 * @return Object Cached item or null if it wasn't found.
	 */
	public function get($key) {
		// Find index for key
		if (isset($this->indexes[$key])) {
			// Get indexes and items
			$indexes =& $this->indexes;
			$items =& $this->items;

			// Resolve index and get item
			$index = $indexes[$key];
			$item =& $items[$index];
			$now = time();

			// Check if it has expired
			if ($item[3] > $now - $this->ttl) {
				// Increase usage count
				$item[0]++;
				$item[3] = $now;

				// Swap with previous index if needed makes the array sorted
				if ($index > 0 && $item[0] > $items[$index - 1][0]) {
					// Swap positions in items array
					$temp =& $items[$index - 1];
					$items[$index - 1] =& $item;
					$items[$index] =& $temp;

					// Swap indexes in key to index lookup
					$indexes[$item[1]] = $index - 1;
					$indexes[$temp[1]] = $index;
				}

				return $item[2];
			} else {
				$this->remove($key);
			}
		}

		// Return null if it doesn't exist
		$null = null;
		return $null;
	}

	/**
	 * Remove all cached items.
	 */
	public function clear() {
		$this->indexes = array();
		$this->items = array();
	}

	/**
	 * Converts the item into a string value.
	 *
	 * @return String Value with the items currently in cache.
	 */
	public function __toString() {
		// @codingStandardsIgnoreStart
		return print_r($this->items, true);
		// @codingStandardsIgnoreEnd
	}
}

?>