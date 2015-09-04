<?php
/**
 * GetAppKeysCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that returns an name/value array of app keys for third party services.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_GetAppKeysCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$config = MOXMAN::getConfig();

		return (object) array(
			"skydrive.client_id" => $config->get("skydrive.client_id"),
			"googledrive.client_id" => $config->get("googledrive.client_id"),
			"dropbox.app_id" => $config->get("dropbox.app_id")
		);
	}
}

?>