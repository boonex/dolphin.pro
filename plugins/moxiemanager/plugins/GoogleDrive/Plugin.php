<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 */
class MOXMAN_GoogleDrive_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("AuthInfo", "onAuthInfo", $this);
	}

	public function onAuthInfo($args) {
		$args->put("googledrive.client_id", MOXMAN::getConfig()->get("googledrive.client_id"));
	}
}

MOXMAN::getPluginManager()->add("googledrive", new MOXMAN_GoogleDrive_Plugin());
?>