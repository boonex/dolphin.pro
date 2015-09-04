<?php
/**
 * IpAuthenticator.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles MoxieManager IPAuthenticator.
 *
 * @package IpAuthenticator
 */
class MOXMAN_IpAuthenticator_Plugin implements MOXMAN_Auth_IAuthenticator {
	public function authenticate(MOXMAN_Auth_User $user) {
		$config = MOXMAN::getConfig();
		$validIpNumbers = explode(',', $config->get('IpAuthenticator.ip_numbers', ''));
		$currentIP = isset($_SERVER["REMOTE_ADDR"]) ? $this->ip2int($_SERVER["REMOTE_ADDR"]) : 0;

		// Loop though all ip number or ip ranges and verify them agains the remote ip
		foreach ($validIpNumbers as $validIp) {
			if ($validIp) {
				$ipRange = explode('-', $validIp);

				// Check if current IP is the single IP address specified
				if (count($ipRange) === 1 && $this->ip2int($ipRange[0]) === $currentIP) {
					return true;
				}

				// Check if the current ip is within the specified IP range
				if (count($ipRange) === 2 && $currentIP >= $this->ip2int($ipRange[0]) && $currentIP <= $this->ip2int($ipRange[1])) {
					return true;
				}
			}
		}

		// Not a valid IP then return false
		return false;
	}

	private function ip2int($ip) {
		$ip = explode(".", $ip);

		if (count($ip) != 4) {
			throw new MOXMAN_Exception("Not an IP4 address: " . implode(".", $ip));
		}

		$address = intval($ip[3]);
		$address += $ip[2] * (0xFF + 1);
		$address += $ip[1] * (0xFFFF + 1);
		$address += $ip[0] * (0xFFFFFF + 1);

		return $address;
	}
}

// Register authenticator
MOXMAN::getAuthManager()->add("IpAuthenticator", new MOXMAN_IpAuthenticator_Plugin());

?>