<?php
/**
 * ICommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Call RPC classes should implement this interface.
 *
 * @package MOXMAN
 */
interface MOXMAN_ICommand {
	/**
	 * Gets executed when a RPC call is made.
	 *
	 * @param Object $params Object passed in from RPC handler.
	 * @return Object Return object that gets passed back to client.
	 */
	public function execute($params);
}

?>