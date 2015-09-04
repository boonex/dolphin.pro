<?php
/**
 * IPlugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface is to be implemented by custom plugins. It contains basic functionallity
 * for binding events and some event handler methods that can be overrided to implement custom behavior.
 *
 * @package MOXMAN
 */
interface MOXMAN_IPlugin {
	/**
	 * Gets executed when the plugin is to be initialized. You can bind custom events in this
	 * method if you override it in subclasses.
	 */
	public function init();
}

?>