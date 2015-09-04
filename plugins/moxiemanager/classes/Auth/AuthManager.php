<?php
/**
 * AuthManager.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles the authentication of the remote user.
 *
 * @package MOXMAN_Auth
 */
class MOXMAN_Auth_AuthManager {
	/** @ignore */
	private $authenticators, $user, $authenticatorOrder, $clientAuthInfo, $isAuthenticatedState;

	/**
	 * Constructs a new authentication manager instance.
	 *
	 * @param mixed $authenticatorOrder Separated list of authenticators that will be executed.
	 */
	public function __construct($authenticatorOrder = "") {
		$this->authenticatorOrder = $authenticatorOrder;
		$this->authenticators = array();
		$this->user = new MOXMAN_Auth_User();
	}

	/**
	 * Returns the current user.
	 *
	 * @return MOXMAN_Auth_User Current user instance.
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * This method will call the login method on all authenticators to
	 * provide a way for a user to login and authenticate them selfs.
	 *
	 * @param string $username User name to login.
	 * @param string $password Password to use in login.
	 * @param boolean $persistent Persistent user auth state.
	 * @return Boolean true/false if the user was logged in or not.
	 */
	public function login($username, $password, $persistent = false) {
		// Set user credentials
		$this->user->setName($username);
		$this->user->setPassword($password);
		$this->user->setPersistent($persistent);

		return $this->doAuthAction("login");
	}

	/**
	 * Returns true/false if one of the authenticators is standalone.
	 *
	 * @return Boolean true/false if any authenticator has standalone support.
	 */
	public function hasStandalone() {
		foreach ($this->authenticators as $authenticator) {
			if ($authenticator instanceof MOXMAN_Auth_IStandaloneAuthenticator) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true/false if the user if authenticated or not. It will call the authenticate method
	 * on all authenticator instances and depending on the auth order it will OR or AND them.
	 *
	 * @return ture/false if the user is logged in or not.
	 */
	public function isAuthenticated() {
		if (!$this->isAuthenticatedState) {
			$this->isAuthenticatedState = $this->doAuthAction("authenticate");
		}

		return $this->isAuthenticatedState;
	}

	/**
	 * Logs out the current user. This will call the logout method to all authenticators.
	 */
	public function logout() {
		foreach ($this->authenticators as $authenticator) {
			if ($authenticator instanceof MOXMAN_Auth_IStandaloneAuthenticator) {
				$authenticator->logout($this->user);
			}
		}
	}

	/**
	 * Adds a new authenticator by name.
	 *
	 * @param string $name Authenticator name for example SessionAuthenticator.
	 * @param MOXMAN_Auth_IAuthenticator $authenticator Authenticator instance to add.
	 */
	public function add($name, MOXMAN_Auth_IAuthenticator $authenticator) {
		$this->authenticators[strtolower($name)] = $authenticator;
		$this->clearCache();
	}

	/**
	 * Return a authenticator instance by name or null if it doesn't exist.
	 *
	 * @param string $name Authenticator by name to retrive.
	 * @return MOXMAN_Auth_IAuthenticator Authenticator instance or null.
	 */
	public function get($name) {
		if (isset($this->authenticators[strtolower($name)])) {
			return $this->authenticators[strtolower($name)];
		}

		return null;
	}

	/**
	 * Returns true/false if the specified authenticator by name exists or not.
	 *
	 * @param string $name Name of the authenticator to look for.
	 * @return Boolean true/false state of the authenticator exists or not.
	 */
	public function has($name) {
		return isset($this->authenticators[strtolower($name)]);
	}

	/**
	 * Removes the authenticator by name.
	 *
	 * @param string $name Name of the authenticator to remove.
	 */
	public function remove($name) {
		unset($this->authenticators[strtolower($name)]);
		$this->clearCache();
	}

	/**
	 * Sets access information passed form client. This might be accessTokens
	 * for oAuth taken from localStorage or other client specific access info like session id:s.
	 *
	 * @param stdClass $info Object with access data.
	 */
	public function setClientAuthData($info) {
		$this->clientAuthInfo = $info;
	}

	/**
	 * Returns client access data such as passed in session id:s or oAuth accessTokens.
	 *
	 * @return stdClass Access data instance.
	 */
	public function getClientAuthData() {
		return $this->clientAuthInfo;
	}

	public function setAuthenticationOrder($order) {
		$this->authenticatorOrder = $order;
	}

	/**
	 * Executes the specified action on all authenticators.
	 *
	 * @param string $action Action to peform login/authenticate
	 * @return boolean Result of action.
	 */
	private function doAuthAction($action) {
		$authOrder = strtolower($this->authenticatorOrder);

		if ($authOrder) {
			if (strpos($authOrder, '|') !== false) {
				// Handle OR statement
				$authOrder = explode('|', $authOrder);
				foreach ($authOrder as $authenticator) {
					if (isset($this->authenticators[$authenticator])) {
						$authenticatorInstance = $this->authenticators[$authenticator];
					} else {
						throw new MOXMAN_Exception("Could not find registred authenticator instance for: " . $authenticator);
					}

					if ($action === "authenticate" && $authenticatorInstance->authenticate($this->user)) {
						return true;
					}

					if ($action === "login" && $authenticatorInstance->login($this->user)) {
						return true;
					}
				}

				return false;
			} else {
				// Handle AND statement
				$authOrder = explode('+', $authOrder);
				foreach ($authOrder as $authenticator) {
					if (isset($this->authenticators[$authenticator])) {
						$authenticatorInstance = $this->authenticators[$authenticator];
					} else {
						throw new MOXMAN_Exception("Could not find registred authenticator instance for: " . $authenticator);
					}

					if ($action === "authenticate" && !$authenticatorInstance->authenticate($this->user)) {
						return false;
					}

					if ($action === "login") {
						if ($authenticatorInstance instanceof MOXMAN_Auth_IStandaloneAuthenticator && !$authenticatorInstance->login($this->user)) {
							return false;
						}
					}
				}
			}
		} else {
			foreach ($this->authenticators as $authenticator) {
				if ($action === "authenticate" && !$authenticator->authenticate($this->user)) {
					return false;
				}

				if ($action === "login") {
					if ($authenticator instanceof MOXMAN_Auth_IStandaloneAuthenticator && !$authenticator->login($this->user)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Clears the authentication cache.
	 */
	public function clearCache() {
		$this->isAuthenticatedState = null;
	}
}

?>