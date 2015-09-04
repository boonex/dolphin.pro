<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 */
class MOXMAN_Dropbox_Plugin implements MOXMAN_IPlugin {
	public function init() {
		MOXMAN::getPluginManager()->get("core")->bind("AuthInfo", "onAuthInfo", $this);
	}

	public function onAuthInfo($args) {
		$args->put("dropbox.app_id", MOXMAN::getConfig()->get("dropbox.app_id"));
	}
}

MOXMAN::getPluginManager()->add("dropbox", new MOXMAN_Dropbox_Plugin());
?>