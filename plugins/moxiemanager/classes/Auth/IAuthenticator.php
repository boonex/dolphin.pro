<?php
/**
 * IAuthenticator.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Implement this interface to provide an authenticator.
 *
 * @package MOXMAN_Auth
 */
interface MOXMAN_Auth_IAuthenticator {
	/**
	 * Gets called on a authentication request. This method should check sessions or similar to
	 * verify that the user has access to the backend.
	 *
	 * This method should return true if the current request is authenticated or false if it's not.
	 *
	 * @param MOXMAN_Auth_User $user User that wants to be authenticated.
	 * @return boolean State if the user is authenticated.
	 */
	public function authenticate(MOXMAN_Auth_User $user);
}

?>