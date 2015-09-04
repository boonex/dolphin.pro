<?php
/**
 * EventDispatcher.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is used for event delegation. You simply add event listeners to it and then
 * you can fire events to those listeners.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_EventDispatcher {
	/** @ignore */
	private $observers;

	/**
	 * Constructs a new EventDispatcher instance.
	 */
	public function __construct() {
		$this->observers = array();
	}

	/**
	 * Returns true/false if there is any listeners/observers for the specific event name.
	 *
	 * @param string $name Event name to check for listeners on.
	 * @return Bool true/false if there is any registred listeners.
	 */
	public function hasListeners($name) {
		$name = strtolower($name);

		return isset($this->observers[$name]) && count($this->observers[$name]) > 0;
	}

	/**
	 * Add a new event listener/observer to the specified event name.
	 *
	 * @param string $name Event name to add listeners/observers to.
	 * @param string $func Function name to execute ones the event occurs.
	 * @param Object $obj Object to execute the specified function on.
	 * @return Array Array item with the observer function and object.
	 */
	public function add($name, $func, $obj) {
		$name = strtolower($name);

		if (!isset($this->observers[$name])) {
			$this->observers[$name] = array();
		}

		$observer = array($func, $obj);
		$this->observers[$name][] = $observer;

		return $observer;
	}

	/**
	 * Remove a new event listener/observer from the event specified by name.
	 *
	 * @param string $name Event name remote listener/observer from.
	 * @param string $func Function name to no longer execute ones the event occurs.
	 * @param Object $obj Object to no longer execute the specified function on.
	 * @return Array Array item with the observer function and object that got removed or null if it wasn't found.
	 */
	public function remove($name, $func, $obj) {
		$name = strtolower($name);

		if (isset($this->observers[$name])) {
			for ($i = count($this->observers[$name]) - 1; $i >= 0; $i--) {
				$observer = $this->observers[$name][$i];

				// Check for matching observer
				if ($observer[0] === $func && $observer[1] === $obj) {
					array_splice($this->observers[$name], $i, 1); // Remove observer
					return $observer;
				}
			}
		}

		return null;
	}

	/**
	 * Remove all registred event listeners from the specified object.
	 *
	 * @param Object $obj Object to remove event listeners from.
	 */
	public function clear($obj) {
		foreach (array_keys($this->observers) as $name) {
			for ($i = count($this->observers[$name]) - 1; $i >= 0; $i--) {
				$observer = $this->observers[$name][$i];

				// Check for matching observer
				if ($observer[1] === $obj) {
					array_splice($this->observers[$name], $i, 1); // Remove observer
				}
			}
		}
	}

	/**
	 * Dispatches an event out to any listeners/observers.
	 *
	 * @param Object $sender Sender reference to send as the first argument to all listeners/observers.
	 * @param string $name Event name to dispatch for example "FileAction".
	 * @param Object $args Event arguments object to pass to each listener/observer.
	 * @return Object the event arguments object that got passed around.
	 */
	public function dispatch($sender, $name, $args) {
		$name = strtolower($name);

		if (isset($this->observers[$name])) {
			$observers = $this->observers[$name];
			$args->setSender($sender);

			for ($i = 0, $l = count($observers); $i < $l; $i++) {
				$value = $observers[$i][1]->$observers[$i][0]($args);

				// Is stopped then break the loop
				if ($value === false || $args->isStopped()) {
					return $args;
				}
			}
		}

		return $args;
	}
}

?>