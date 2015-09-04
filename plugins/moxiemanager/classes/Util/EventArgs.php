<?php
/**
 * EventArgs.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Base class for the event args classes. This has the basic core logic for an event and is supposed
 * to be extended by custom events.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_EventArgs {
	/** @ignore */
	private $cancelled, $stopped, $sender;

	/**
	 * Constructs a new EventArgs instance.
	 */
	public function __construct() {
		$this->cancelled = false;
		$this->stopped = false;
	}

	/**
	 * Returns the sender instance that dispatched the event.
	 *
	 * @return Object Sender instance reference that dispatched the event.
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * Sets the sender that dispatched the event.
	 *
	 * @param Object $sender Sender instance to set.
	 */
	public function setSender($sender) {
		$this->sender = $sender;
	}

	/**
	 * Returns true/false if the event is cancelled or not. This will block the action followed by the event. This is normally used to cancel
	 * an action on a event called before the action.
	 *
	 * @return Bool State if the event was cancelled or not.
	 */
	public function isCancelled() {
		return $this->cancelled;
	}

	/**
	 * Cancels the action to be executed after the event. This is normally used in events called just before an action is to be performed.
	 */
	public function cancel() {
		$this->cancelled = true;
	}

	/**
	 * Returns true/false if the event is stopped. If it's stopped other event handlers won't be executed.
	 *
	 * @return Boolean True/false if the event is stopped from being passed to other event handlers.
	 */
	public function isStopped() {
		return $this->stopped;
	}

	/**
	 * Stops the event from being passed to other event handlers.
	 */
	public function stop() {
		$this->stopped = true;
	}
}

?>