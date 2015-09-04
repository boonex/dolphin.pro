<?php
/**
 * NameValueCollection.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class enables you to get/put values in based on keys.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_NameValueCollection implements IteratorAggregate {
	/**
	 * Name/value array with items within the collection.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Constructs a new NameValueCollection with the specified array as it's internal items.
	 *
	 * @param array $items Array with the internal items to set.
	 */
	public function __construct(array $items = array()) {
		$this->items = $items;
	}

	/**
	 * Extends the current collection with another collection or array.
	 *
	 * @param Mixed $items Name/Value array or other instance to extend with.
	 * @return MOXMAN_Util_NameValueCollection Returns the current extended instance.
	 */
	public function extend($items) {
		// If it's another instance then use it's items
		if ($items instanceof MOXMAN_Util_NameValueCollection) {
			$items = $items->getAll();
		}

		// Extend items with the specified items
		$this->items = array_merge($this->items, $items);

		return $this;
	}

	/**
	 * Returns the specified group as a new name/value array-
	 *
	 * @param string $prefix Prefix/group to export for example "filesystem".
	 * @return Array Name/value array with expored group.
	 */
	public function getGroup($prefix) {
		$group = array();
		$prefix = preg_replace('/\.$/', '', $prefix); // Remove traling dot

		foreach ($this->items as $key => $value) {
			if ($key === $prefix || strpos($key, $prefix . ".") !== false) {
				$group[$key] = $value;
			}
		}

		return $group;
	}

	/**
	 * Returns the specified item by name or the default value if
	 * it shouldn't be defined in the internal name/value array.
	 *
	 * @param string $name Name of the item to retrive.
	 * @param Mixed $default Default value to return if the item wasn't found.
	 * @return Mixed Item value or default value if it wasn't found.
	 */
	public function get($name, $default = false) {
		return isset($this->items[$name]) ? $this->items[$name] : $default;
	}

	/**
	 * Sets the value of a item by name.
	 *
	 * @param string $name Name of item to set.
	 * @param Mixed $value Value to set for the item.
	 * @return Current instance.
	 */
	public function put($name, $value) {
		$this->items[$name] = $value;

		return $this;
	}

	/**
	 * Removes a specific key by name.
	 *
	 * @param mixed $name Key to remove.
	 * @return Current instance.
	 */
	public function remove($name) {
		unset($this->items[$name]);

		return $this;
	}

	/**
	 * Returns the internal items array.
	 *
	 * @return Array Internal name/value array with items.
	 */
	public function getAll() {
		return $this->items;
	}

	/**
	 * Sets the internal items array.
	 *
	 * @param Array $items Internal name/value array with items.
	 */
	public function putAll($items) {
		$this->items = $items;
	}

	/**
	 * Returns an iterator intance for the internal array.
	 *
	 * @reutrn ArrayIterator Iterator instance.
	 */
	public function getIterator() {
		return new ArrayIterator($this->items);
	}
}

?>