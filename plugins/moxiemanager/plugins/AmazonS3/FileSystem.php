<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class returns file instances for the AmazonS3 File system.
 */
class MOXMAN_AmazonS3_FileSystem extends MOXMAN_Vfs_FileSystem {
	private $statCache, $client;

	/**
	 * Constructs a new AmazonS3 instance.
	 *
	 * @param String $scheme File system protocol scheme.
	 * @param MOXMAN_Util_Config $config Config instance for file system.
	 * @param String $root Root path for file system.
	 */
	public function __construct($scheme, $config, $root) {
		parent::__construct($scheme, $config, $root);

		$this->cache = new MOXMAN_Util_LfuCache();
		$this->setFileUrlResolver(new MOXMAN_AmazonS3_FileUrlResolver($this));

		// Parse URL and get buckets
		$url = parse_url($this->getRootPath());
		$bucketName = $url["host"];
		$bucketKey = $bucketName;
		$this->bucketConfigPrefix = "amazons3.buckets." . $bucketName . ".";
		$bucketName = $this->getBucketOption("bucket", $bucketName);

		$this->client = new MOXMAN_AmazonS3_Client(array(
			"bucket" => $bucketName,
			"endpoint" => $this->getBucketOption("endpoint"),
			"publickey" => $this->getBucketOption("publickey"),
			"privatekey" => $this->getBucketOption("secretkey"),
			"acl" => $this->getBucketOption("acl", "public-read"),
			"cache_control" => $this->getBucketOption("acl", "cache_control"),
			"proxy" => $config->get("general.http_proxy"),
			"scheme" => $this->getBucketOption("scheme", "tls")
		));

		// Setup urlprefix
		$urlPrefix = $this->getBucketOption("urlprefix");
		if (!$urlPrefix) {
			$info = $this->client->getInfo();
			$this->setBucketOption("urlprefix", "//" . $info["endpoint"]);
		}

		if ($this->getBucketOption("debug_level") > 0) {
			$this->client->setLogFunction(array($this, "logHttpClient"));
			$this->client->setLogLevel($this->getBucketOption("debug_level"));
		}

		$this->setBucketOption("key", $bucketKey);
	}

	/**
	 * Returns the true/false if the file system can be cached or not.
	 *
	 * @return True/false if the file system is cacheable or not.
	 */
	public function isCacheable() {
		return $this->getBucketOption("cache", true);
	}

	/**
	 * Returns a MOXMAN_Vfs_IFile file instance for the specified path.
	 *
	 * @param String $path Path of the file to get from file system.
	 * @return MOXMAN_Vfs_IFile File instance for the specified path.
	 */
	public function getFile($path) {
		return new MOXMAN_AmazonS3_File($this, $path);
	}

	/**
	 * Closes the file system. This will release any resources used by the file system.
	 */
	public function close() {
		if ($this->client) {
			$this->client->close();
			$this->client = null;
		}
	}

	public function getClient() {
		return $this->client;
	}

	public function getCache() {
		return $this->cache;
	}

	/**
	 * Returns a bucket option by name or the default value if it isn't defined.
	 *
	 * @param String $name Name of the option to get.
	 * @param mixed $default Default value to return if the option isn't defined.
	 * @return mixed Option value or default value if it doesn't exist.
	 */
	public function getBucketOption($name, $default = "") {
		return $this->config->get($this->bucketConfigPrefix . $name, $default);
	}

	public function getStatCache() {
		if (!$this->statCache) {
			$this->statCache = new MOXMAN_Util_LfuCache();
		}

		return $this->statCache;
	}

	/**
	 * Logs HTTP client messages to log file with a specific prefix.
	 *
	 * @param mixed $str String to log.
	 */
	public function logHttpClient($str) {
		MOXMAN::getLogger()->debug("[s3] " . $str);
	}

	private function setBucketOption($name, $value) {
		$this->config->put($this->bucketConfigPrefix . $name, $value);
	}
}

?>