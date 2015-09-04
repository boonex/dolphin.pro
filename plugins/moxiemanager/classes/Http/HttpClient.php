<?php
/**
 * HttpClient.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class wrapps in the HTTP Request and adds various useful options.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_HttpClient {
	/** @ignore */
	private $readTimeout, $bufferSize, $logLevel, $logFunction, $connection, $proxy;
	protected $host, $port, $scheme;

	/**
	 * Constructs a new HttpClient instance.
	 *
	 * @param string $host HTTP host name to connect to or URL string to grab host and port from.
	 * @param int $port Port number
	 */
	public function __construct($host, $port = 80) {
		$url = parse_url($host);
		$this->scheme = isset($url["scheme"]) ? $url["scheme"] : "http";
		$this->host = isset($url["host"]) ? $url["host"] : $host;
		$this->port = isset($url["port"]) ? $url["port"] : ($this->scheme == "https" ? ($port != 80 ? $port : 443) : $port);
		$this->readTimeout = 30;
		$this->bufferSize = 16384;
		$this->logLevel = 0;
	}

	/**
	 * Creates a new HTTP request instance for the specified path.
	 *
	 * @param string $path Path to create request instance for.
	 * @param string $method Method to send.
	 * @return MOXMAN_Http_HttpClientRequest HTTP request instance to create.
	 */
	public function createRequest($path, $method = "get") {
		$url = parse_url($path);

		// Set scheme, host and port to the specified one for the whole client
		$url["scheme"] = $this->scheme;
		$url["host"] = $this->host;
		$url["port"] = $this->port;

		if (function_exists('stream_socket_client')) {
			return new MOXMAN_Http_HttpClientSocketRequest($this, $url, $method);
		} else if (function_exists('curl_init')) {
			return new MOXMAN_Http_HttpClientCurlRequest($this, $url, $method);
		} else {
			throw new MOXMAN_Http_HttpClientException("Could not make HTTP request: No curl, no sockets in PHP.");
		}
	}

	/**
	 * Creates a new http form data instance.
	 *
	 * @return MOXMAN_Http_HttpClientFormData Http form data instance to be send to client.
	 */
	public function createFormData() {
		return new MOXMAN_Http_HttpClientFormData();
	}

	/**
	 * Returns the buffer size in bytes.
	 *
	 * @return Int Number of bytes to buffer.
	 */
	public function getBufferSize() {
		return $this->bufferSize;
	}

	/**
	 * Sets the log function to be called.
	 *
	 * @param mixed $func Name of function or array with instance and method name.
	 */
	public function setLogFunction($func) {
		$this->logFunction = $func;
	}

	/**
	 * Returns the current log level. 0 = no logging, 1 = basic logging, 2 = verbose logging.
	 *
	 * @return int Current log level.
	 */
	public function getLogLevel() {
		return $this->logLevel;
	}

	/**
	 * Sets the current log level. 0 = no logging, 1 = basic logging, 2 = verbose logging.
	 *
	 * @param $level int Current log level.
	 */
	public function setLogLevel($level) {
		$this->logLevel = $level;
	}

	/**
	 * Logs the specified string to log function if the level is grater than 0.
	 *
	 * @param string $str This is a description
	 */
	public function log($str) {
		// @codeCoverageIgnoreStart

		if ($this->logLevel > 0) {
			if (!$this->logFunction) {
				echo nl2br(trim($str) . "\n");
			} else {
				if (is_array($this->logFunction)) {
					// Call user function in class reference
					$class = $this->logFunction[0];
					$name = $this->logFunction[1];
					$func = $class->$name($str);
				} else {
					$func = $this->logFunction;
					$func($str);
				}
			}
		}

		// @codeCoverageIgnoreEnd
	}

	/**
	 * Sets the client connection this is used for keep alive connections where
	 * the client keeps one connection open for multiple requests to boost performance.
	 *
	 * @param resource $connection Resource handle for connection.
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}

	/**
	 * Returns the clients resource handle. This handle is used for keep alive connections.
	 *
	 * @return resource Resource handle for connection.
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Closes the internal connection for the HTTP client.
	 */
	public function close() {
		if (is_resource($this->connection)) {
			if ($this->getLogLevel() >= 2) {
				$this->log("- Socket closed.");
			}

			fclose($this->connection);
			$this->connection = null;
		}
	}

	public function getProxy() {
		return $this->proxy;
	}

	public function setProxy($proxy) {
		$this->proxy = $proxy;
	}

	/**
	 * Sets the read data timeout.
	 *
	 * @param $timeout int Read timeout.
	 */
	public function setReadTimeout($timeout) {
		$this->readTimeout = $timeout;
	}

	/**
	 * Returns the read data timeout.
	 *
	 * @return int Read timeout.
	 */
	public function getReadTimeout() {
		return $this->readTimeout;
	}
}

?>