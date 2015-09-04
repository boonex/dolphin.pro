<?php
/**
 * IStandaloneAuthenticator.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Implement this interface to provide an authenticator with login/logout.
 *
 * @package MOXMAN_Auth
 */
interface MOXMAN_Auth_IStandaloneAuthenticator extends MOXMAN_Auth_IAuthenticator {
	/**
	 * Gets calles when the user is logging in using the built in login dialog.
	 *
	 * @param MOXMAN_Auth_User $user User that wants to login.
	 * @return boolean State if the user is logged in or not.
	 */
	public function login(MOXMAN_Auth_User $user);

	/**
	 * Gets called when the user is logging out. This could for example destroy sessions.
	 * 
	 * @param MOXMAN_Auth_User $user User that wants to logout.
	 */
	public function logout(MOXMAN_Auth_User $user);
}

?>