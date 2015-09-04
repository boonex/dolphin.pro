<?php
/**
 * Plugin.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

if (session_id() == '') {
	@session_start();
}

/**
 * This class handles MoxieManager SessionAuthenticator stuff.
 */
class MOXMAN_BasicAuthenticator_Plugin implements MOXMAN_Auth_IStandaloneAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		if (isset($_SESSION["moxman_authUser"]) && $_SESSION["moxman_authUser"]) {
			$user->setName($_SESSION["moxman_authUser"]);
			return true;
		}

		if (isset($_COOKIE["moxmanauth"]) && $_COOKIE["moxmanauth"]) {
			$config = MOXMAN::getConfig();
			$userKey = $_COOKIE["moxmanauth"];

			foreach ($config->get('basicauthenticator.users') as $userItem) {
				if ($userKey === $this->hashUserItem($userItem)) {
					$this->updateCookie($userItem);
					$user->setName($userItem["username"]);
					return true;
				}
			}
		}

		return false;
	}

	public function login(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();

		foreach ($config->get('basicauthenticator.users') as $userItem) {
			if ($userItem["username"] == $user->getName() && $userItem["password"] == $user->getPassword()) {
				if ($user->isPersistent()) {
					$this->updateCookie($userItem);
				} else {
					$_SESSION["moxman_authUser"] = $user->getName();
				}

				return true;
			}
		}

		return false;
	}

	public function logout(MOXMAN_Auth_User $user) {
		unset($_SESSION["moxman_authUser"]);
		setcookie("moxmanauth", "", time() - 3600);
	}

	private function hashUserItem($userItem) {
		$config = MOXMAN::getConfig();

		return hash("sha256",
			$userItem["username"] .
			$userItem["password"] .
			$config->get('general.license')
		);
	}

	private function updateCookie($userItem) {
		setcookie("moxmanauth", $this->hashUserItem($userItem), time() + 3600 * 24 * 30);
	}
}

MOXMAN::getAuthManager()->add("BasicAuthenticator", new MOXMAN_BasicAuthenticator_Plugin());

?>