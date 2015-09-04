<?php
/**
 * InstallCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Command that installs the config.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_InstallCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$templatePath = MOXMAN_ROOT . '/install/config.template.php';

		if (file_exists($templatePath)) {
			// Get all data
			$license = trim($params->license);
			$authenticator = $params->authenticator;
			$username = $params->username;
			$password = $params->password;
			$loggedInKey = $params->logged_in_key;

			// Verify input
			if (!preg_match('/^([0-9A-Z]{4}\-){7}[0-9A-Z]{4}$/', $license)) {
				throw new MOXMAN_Exception("Invalid license: " . $license);
			}

			// Update the license since it will later be used by the csrf logic
			MOXMAN::getConfig()->put("general.license", $license);

			if ($authenticator == "BasicAuthenticator") {
				$params->authenticator = "BasicAuthenticator";

				if (!$username) {
					throw new MOXMAN_Exception("User name can't be empty.");
				}

				if (!$password) {
					throw new MOXMAN_Exception("Password can't be empty.");
				}
			}

			if ($authenticator == "SessionAuthenticator") {
				$params->authenticator = "SessionAuthenticator";

				if (!$loggedInKey) {
					throw new MOXMAN_Exception("Session name can't be empty.");
				}
			}

			// Replace template variables
			$template = file_get_contents($templatePath);
			foreach ($params as $key => $value) {
				$template = str_replace('<' . $key . '>', $value, $template);
			}

			if (!is_writable(MOXMAN_ROOT . "/config.php") || !file_put_contents(MOXMAN_ROOT . "/config.php", $template)) {
				return $template;
			}
		} else {
			throw new MOXMAN_Exception("Failed to locate config template.");
		}

		return true;
	}
}

?>