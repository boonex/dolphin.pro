<?php
/**
 * AuthInfoEventArgs.php
 *
 * Copyright 2003-2014, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ....
 *
 * @package MOXMAN_Auth
 */
class MOXMAN_Auth_AuthInfoEventArgs extends MOXMAN_Util_EventArgs {
	/**
	 * Name/value array with auth info details.
	 *
	 * @var Array
	 */
	protected $info;

	/**
	 * Constructs a new auth info event.
	 */
	public function __construct() {
		$this->info = array();
	}

	public function put($name, $value) {
		$this->info[$name] = $value;
	}

	/**
	 * Returns the info for the event. Add items to this that you want to pass out to the client.
	 *
	 * @return Array Custom auth info data.
	 */
	public function getInfo() {
		return $this->info;
	}
}

?>