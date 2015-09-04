<?php
/**
 * GetConfigCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Returns client side config options for the specified file.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_GetConfigCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		if (isset($params->path) && $params->path) {
			return $this->getPublicConfig(MOXMAN::getFile($params->path));
		}

		return $this->getPublicConfig();
	}
}

?>