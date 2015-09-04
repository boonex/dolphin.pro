<?php
/**
 * FileSystem.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class returns file instances for the Azure File system.
 */
class MOXMAN_Azure_FileSystem extends MOXMAN_Vfs_FileSystem {
	private $httpClient;

	/**
	 * Constructs a new Azure instance.
	 *
	 * @param String $scheme File system protocol scheme.
	 * @param MOXMAN_Util_Config $config Config instance for file system.
	 * @param String $root Root path for file system.
	 */	
	public function __construct($scheme, $config, $root) {
		parent::__construct($scheme, $config, $root);

		$this->setFileUrlResolver(new MOXMAN_Azure_FileUrlResolver($this));

		// Parse URL and get containers
		$url = parse_url($this->getRootPath());
		$containerName = $url["host"];
		$this->containerConfigPrefix = "azure.containers." . $containerName . ".";
		$this->setContainerOption("key", $containerName);
		$containerName = $this->getContainerOption("container", $containerName);
		$this->setContainerOption("name", $containerName);

		// Handle development mode
		if ($this->getContainerOption("development")) {
			$this->setContainerOption("url", "http://127.0.0.1:10000");
			$this->setContainerOption("account", "devstoreaccount1");
			$this->setContainerOption("sharedkey", "Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==");
		}

		// Verify that container is valid
		$account = $this->getContainerOption("account");
		if (!$account || !$this->getContainerOption("sharedkey")) {
			throw new MOXMAN_Exception("Could not find account/sharedkey options for container " . $containerName . ".");
		}

		if (!$this->getContainerOption("url")) {
			$this->setContainerOption("url", "http://" . $account . ".blob.core.windows.net");
			$this->setContainerOption("urlprefix", "http://" . $account . ".blob.core.windows.net/");
			$this->setContainerOption("path", "/" . $containerName);
		} else {
			$this->setContainerOption("path", "/" .  $account . "/" . $containerName);
			$this->setContainerOption("urlprefix", "http://localhost:10000/devstoreaccount1/");
		}

		// Setup HTTP client
		$this->httpClient = new MOXMAN_Http_HttpClient($this->getContainerOption("url"));

		// Debug output
		if ($this->getContainerOption("debug_level") > 0) {
			$this->httpClient->setLogFunction(array($this, "logHttpClient"));
			$this->httpClient->setLogLevel($this->getContainerOption("debug_level"));
		}
	}

	/**
	 * Returns the true/false if the file system can be cached or not.
	 *
	 * @return True/false if the file system is cacheable or not.
	 */
	public function isCacheable() {
		return $this->getContainerOption("cache", true);
	}

	/**
	 * Returns a MOXMAN_Vfs_IFile file instance for the specified path.
	 *
	 * @param String $path Path of the file to get from file system.
	 * @return MOXMAN_Vfs_IFile File instance for the specified path.
	 */
	public function getFile($path) {
		$file = new MOXMAN_Azure_File($this, $path);

		return $file;
	}

	/**
	 * Closes the file system. This will release any resources used by the file system.
	 */	
	public function close() {
		if ($this->httpClient) {
			$this->httpClient->close();
			$this->httpClient = null;
		}
	}

	public function createRequest($params) {
		$path = isset($params["path"]) ? $params["path"] : '/';
		$path = $this->getContainerOption("path") . $path;
		$params["method"] = isset($params["method"]) ? $params["method"] : 'GET';

		// Replace spaces in file names
		$path = str_replace(' ', '%20', $path);

		$request = $this->httpClient->createRequest($path, $params["method"]);

		if (isset($params["headers"])) {
			foreach ($params["headers"] as $key => $value) {
				$request->setHeader($key, $value);
			}
		}

		if (isset($params["query"])) {
			$request->setQuery($params["query"]);
		}

		return $request;
	}

	public function sendRequest(MOXMAN_Http_HttpClientRequest $request, $data = null) {
		$this->signRequest($request);

		$response = $request->send($data);
		if ($response->getCode() >= 400) {
			$body = $response->getBody();

			if ($body && strpos($body, '<?xml') !== false) {
				$xml = new SimpleXMLElement($body);
				$debugInfo = "";

				if (MOXMAN::getConfig()->get("general.debug")) {
					$debugInfo = "\n\n" . $body;
				}

				throw new MOXMAN_Exception(
					"Azure: (" . $xml->Code . ") " . $xml->Message . $debugInfo
				);
			} else {
				throw new MOXMAN_Exception(
					"Azure error: " . $response->getCode()
				);
			}
		}

		return $response;
	}

	public function getContainerOption($name, $default = false) {
		return $this->config->get($this->containerConfigPrefix . $name, $default);
	}

	/**
	 * signRequest
	 *
	 * @param MOXMAN_Http_HttpClientRequest $request Request from HttpClient to sign.
	 * @return MOXMAN_Http_HttpClientRequest HttpClient Request returned with right headers signed.
	 */
	private function signRequest(MOXMAN_Http_HttpClientRequest $request) {
		$signData = array(
			"Content-Encoding",
			"Content-Language",
			"Content-Length",
			"Content-MD5",
			"Content-Type",
			"Date",
			"If-Modified-Since",
			"If-Match",
			"If-None-Match",
			"If-Unmodified-Since",
			"Range"
		);

		$signed = strtoupper($request->getMethod()) . "\n";
		foreach ($signData as $name) {
			$signed .= $request->getHeader($name) . "\n";
		}

		$request->setHeader("x-ms-date", gmdate('D, d M Y H:i:s T', time()));
		$request->setHeader("x-ms-version", "2009-09-19");

		foreach ($request->getHeaders() as $name => $val) {
			if (strpos($name, "x-ms-") === 0) {
				$signed .= $name . ":" . $val . "\n";
			}
		}

		$url = $request->getUrl();

		$signed .= "/" . $this->getContainerOption("account") . $url["path"];

		if (isset($url["query"])) {
			$queryParts = array();
			parse_str($url["query"], $queryParts);
			$keys = array_keys($queryParts);
			sort($keys);
			foreach ($keys as $key) {
				$signed .= "\n" . $key . ":" . $queryParts[$key];
			}
		}

	 	$hash = hash_hmac("sha256", $signed, base64_decode($this->getContainerOption("sharedkey")), true);
		$signature = base64_encode($hash);

		$request->setHeader("Authorization", "SharedKey " . $this->getContainerOption("account") . ":" . $signature);

		return $request;
	}

	/**
	 * Logs HTTP client messages to log file with a specific prefix.
	 *
	 * @param mixed $str String to log.
	 */
	public function logHttpClient($str) {
		MOXMAN::getLogger()->debug("[azure] " . $str);
	}

	private function setContainerOption($name, $value) {
		$this->config->put($this->containerConfigPrefix . $name, $value);
	}
}

?>