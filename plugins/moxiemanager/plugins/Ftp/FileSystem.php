<?php
/**
 * LocalFileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

class MOXMAN_Ftp_FileSystem extends MOXMAN_Vfs_FileSystem {
	private $connection, $account;

	public function __construct($scheme, $config, $root) {
		parent::__construct($scheme, $config, $root);

		$url = parse_url($this->getRootPath());
		$accounts = $this->getConfig()->get("ftp.accounts");

		if (!isset($accounts[$url["host"]])) {
			throw new MOXMAN_Exception("Could not find account name: " . $url["host"] . " for FTP.");
		}

		// Get account details and default them if needed
		$account = $accounts[$url["host"]];
		$account["host"] = isset($account["host"]) ? $account["host"] : "localhost";
		$account["port"] = isset($account["port"]) ? $account["port"] : 21;
		$account["timeout"] = isset($account["timeout"]) ? $account["timeout"] : 90;
		$account["user"] = isset($account["user"]) ? $account["user"] : "anonymous";
		$account["password"] = isset($account["password"]) ? $account["password"] : "anonymous@localhost";
		$account["rootpath"] = isset($account["rootpath"]) ? $account["rootpath"] : "/";
		$account["wwwroot"] = isset($account["wwwroot"]) ? $account["wwwroot"] : "";
		$this->account = $account;
	}

	public function getFile($path) {
		$file = new MOXMAN_Ftp_File($this, $path);
		return $file;
	}

	public function getConnection() {
		if (!is_resource($this->connection)) {
			// Open FTP connection
			$account = $this->account;
			$this->connection = ftp_connect($account["host"], $account["port"], $account["timeout"]);
			if (!$this->connection) {
				throw new MOXMAN_Exception("Could not connect to FTP server.");
			}

			if (!ftp_login($this->connection, $account["user"], $account["password"])) {
				throw new MOXMAN_Exception("Could not login to FTP server.");
			}

			// Enter passive mode
			if (isset($account["passive"]) && $account["passive"] === true) {
				ftp_pasv($this->connection, true);
			}
		}

		return $this->connection;
	}

	public function getAccountItem($name, $default = "") {
		return isset($this->account[$name]) ? $this->account[$name] : $default;
	}

	public function isCacheable() {
		return true;
	}

	public function close() {
		parent::close();

		if (is_resource($this->connection)) {
			ftp_close($this->connection);
			$this->connection = null;
		}
	}
}

?>