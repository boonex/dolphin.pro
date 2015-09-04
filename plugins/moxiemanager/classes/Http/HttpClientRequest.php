<?php
/**
 * HttpClientRequest.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class represents the HTTP request from a MOXMAN_Http_HttpClient instance.
 *
 * @package MOXMAN_Http
 */
abstract class MOXMAN_Http_HttpClientRequest {
	/** @ignore */
	protected $client, $url, $method, $headers, $basicAuthUser, $basicAuthPassword, $localFilePath, $body, $stream;

	/**
	 * Constructs a new HTTP client request instance.
	 *
	 * @param MOXMAN_Http_HttpClient $client HTTP client instance to connect to request.
	 * @param Array $url Url object that contains the host, port, path, querystring etc.
	 * @param string $method Request method head/get/post.
	 */
	public function __construct(MOXMAN_Http_HttpClient $client, $url, $method) {
		$this->client = $client;
		$this->url = $url;
		$this->method = strtolower($method);
		$this->headers = array();
		$this->multipartCount = 0;

		// Set host as a HTTP header
		$this->setHeader("host", $url["host"]);
	}

	/**
	 * Returns the request method like get/post/head etc.
	 *
	 * @return String Http request method.
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Sets a specific header by name to be sent to the server.
	 *
	 * @param string $name Name of the specified header to set.
	 * @param String/Array $value Value or multiple values if multiple headers of the same name is to be sent.
	 */
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	/**
	 * Returns a specific header by name to be sent to the server.
	 *
	 * @param string $name Name of the specified header to get.
	 * @param String/Array [$default] Default value to return if it's not set.
	 * @return String/Array $value Value or multiple values if multiple headers of the same name is to be sent.
	 */
	public function getHeader($name, $default = "") {
		if (!isset($this->headers[$name])) {
			return $default;
		}

		return $this->headers[$name];
	}

	/**
	 * Returns all http headers as an array.
	 *
	 * @return Array name/value array with all HTTP headers.
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Returns the URL instance containing path, host, query etc.
	 *
	 * @return Array Name/value array with url items.
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Sets the query string to a raw string or a name/value array.
	 *
	 * @param String/Array $query Query string or array to set.
	 * @return Instance of http request.
	 */
	public function setQuery($query) {
		if (is_array($query)) {
			$query = http_build_query($query);
		}

		$this->url["query"] = $query;

		return $this;
	}

	/**
	 * Sets a local file path to stream as content body of request.
	 *
	 * @param string $localFilePath Local file path to send as binary stream.
	 */
	public function setLocalFile($localFilePath) {
		$this->localFilePath = $localFilePath;
	}

	/**
	 * Sets http basic auth authentication data
	 *
	 * @param string $user
	 * @param string $password
	 */
	public function setAuth($user, $password) {
		$this->basicAuthUser = $user;
		$this->basicAuthPassword = $password;
	}

	/**
	 * Sets the raw body data of the request.
	 *
	 * @param string $data Raw body data to send.
	 */
	public function setBody($data) {
		$this->body = $data;
	}

	/**
	 * Sends the specified HTTP request to server and returns a HTTP response object.
	 *
	 * @param String/Array/MOXMAN_Http_HttpClientFormData Data to send to url like the request body or post parameters.
	 * @return MOXMAN_Http_HttpClientResponse HTTP client response instance.
	 */
	public function send($data = null) {
		if ($data instanceof MOXMAN_Http_HttpClientFormData && !$data->hasFileData()) {
			$data = $data->getItems();
		}

		if ($this->method == "get") {
			if ($data) {
				$query = array();

				if (isset($this->url["query"])) {
					parse_str($this->url["query"], $query);
				}

				foreach ($data as $key => $value) {
					$query[$key] = $value;
				}

				$this->url["query"] = http_build_query($query);

				$data = null;
			}
		}

		$this->open($data);

		if (is_string($this->body) && $data === null) {
			$this->write($this->body);
		}

		$response = $this->close();

		return $response;
	}

	public abstract function open();

	public abstract function close();

	public function write($buff) {
		$this->fputs($buff, true);
	}

	/**
	 * Sends the specified line of text to the server.
	 *
	 * @param string $line Data to send to client.
	 */
	protected function writeLine($line) {
		$this->fputs($line . "\r\n");
	}

	// Private methods

	/**
	 * Checks if the last socket write operation timed out if it has then it throws and exception.
	 */
	protected function checkWriteTimeout() {
		$info = stream_get_meta_data($this->stream);

		if (isset($info['timed_out']) && $info['timed_out']) {
			// @codeCoverageIgnoreStart
			throw new MOXMAN_Http_HttpClientException("Request timed out.");
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Sends the specified data to server.
	 *
	 * @param string $data Data to send to client.
	 * @param boolean $isBody Is header or body content.
	 */
	protected function fputs($data, $isBody = false) {
		if ($this->client->getLogLevel() >= 3) {
			$this->client->log("> " . trim($data));
		} else if ($this->client->getLogLevel() == 2 && !$isBody) {
			$this->client->log("> " . trim($data));
		}

		fwrite($this->stream, $data);
		$this->checkWriteTimeout();
	}
}

?>