<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

// Figure out where Symfony is installed
$symfonyRoot = MOXMAN_ROOT;
while ($symfonyRoot) {
	if (file_exists($symfonyRoot . '/app')) {
		break;
	}

	if (dirname($symfonyRoot) == $symfonyRoot) {
		$symfonyRoot = "";
		break;
	}

	$symfonyRoot = dirname($symfonyRoot);
}

// Load symfony bootstrap
if ($symfonyRoot) {
	$loader = require_once $symfonyRoot . '/app/bootstrap.php.cache';
	require_once $symfonyRoot . '/app/AppKernel.php';
} else {
	die("Could not find symfony root.");
}

/**
 * This class handles MoxieManager SymfonyAuthenticator (Symfony >= 2.x).
 */
class MOXMAN_SymfonyAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	private $isSessionLoaded;

	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();

		// Load environment and session logic
		if (!$this->isSessionLoaded) {
			$kernel = new AppKernel($config->get("SymfonyAuthenticator.environment", "prod"), false);
			$kernel->loadClassCache();

			$request = Request::createFromGlobals();
			$kernel->handle($request);

			$this->isSessionLoaded = true;
		}

		// Get all session data
		$session = new Session();
		$session = $session->all();

		// Check logged in key
		$loggedInKey = $config->get("SymfonyAuthenticator.logged_in_key", "isLoggedIn");
		$sessionValue = isset($session[$loggedInKey]) ? $session[$loggedInKey] : false;
		if (!$sessionValue || $sessionValue === "false") {
			return false;
		}

		// Extend config with session prefixed sessions
		$sessionConfig = array();
		$configPrefix = $config->get("SymfonyAuthenticator.config_prefix", "moxiemanager");
		if ($configPrefix) {
			foreach ($session as $key => $value) {
				if (strpos($key, $configPrefix) === 0) {
					$sessionConfig[substr($key, strlen($configPrefix) + 1)] = $value;
				}
			}
		}

		// Extend the config with the session config
		$config->extend($sessionConfig);

		// Replace ${user} with all config items
		$key = $config->get("SessionAuthenticator.user_key", "user");
		if ($key && isset($session[$key])) {
			$config->replaceVariable("user", $session[$key]);
			$user->setName($session[$key]);
		}

		return true;
	}
}

MOXMAN::getAuthManager()->add("SymfonyAuthenticator", new MOXMAN_SymfonyAuthenticator_Plugin());