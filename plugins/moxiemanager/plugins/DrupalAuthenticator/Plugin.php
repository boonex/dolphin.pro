<?php
/**
 * Plugin.php
 *
 * @author Moxiecode Systems AB
 * @copyright Copyright © 2013, Moxiecode Systems AB, All rights reserved.
 */

// Bootstap drupal
$cwd = getcwd();
@session_destroy();

define("DRUPAL_ROOT", MOXMAN_ROOT . "/../../../../");
chdir(DRUPAL_ROOT);
require_once("includes/bootstrap.inc");

global $base_url, $base_root, $base_path;

// Setup base_root, base_url and base_path so the sessions will work correctly
// NOTE: DO NOT REMOVE THIS, DRUPAL SESSION WONT WORK WITHOUT THIS.
$base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$base_url = $base_root .= '://'. preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);
$base_path = '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '\,/');
$base_path = substr($base_path, 0, strpos($base_path, '/sites/all/modules/'));
$base_url .= $base_path;

drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
$isDrupalAuth = false;

if (!isset($_SESSION['mc_drupal_auth']) || !$_SESSION['mc_drupal_auth']) {
	// Not cached in session check agains API
	drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
	$isDrupalAuth = user_access('create page content');
	$_SESSION['mc_drupal_auth'] = $isDrupalAuth;
} else {
	$isDrupalAuth = $_SESSION['mc_drupal_auth'];
}
// Restore path
chdir($cwd);

/**
 * This class handles MoxieManager Drupal authentication.
 */
class MOXMAN_DrupalAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		global $isDrupalAuth;
		global $user;

		$config = MOXMAN::getConfig();

		// If authenticated then
		if ($isDrupalAuth && isset($user)) {
			$config->replaceVariable("user", $user->uid);
		}

		return $isDrupalAuth;
	}
}

MOXMAN::getAuthManager()->add("DrupalAuthenticator", new MOXMAN_DrupalAuthenticator_Plugin());
?>