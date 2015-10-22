<?php
/**
 * HttpClientSocketRequest.php
 *
 * Copyright 2003-2015, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Socket implementation of HttpClientRequest.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_HttpClientSocketRequest extends MOXMAN_Http_HttpClientRequest {
	private static $hostCache = array();

	public function open($data = null) {
		$this->stream = $this->getSocketStream();
		$multipartChunks = null;

		// Tell server we wan't to keep the connection going
		$this->setHeader("Connection", "Keep-Alive");

		// Handle request data
		if ($data !== null) {
			if ($data instanceof MOXMAN_Http_HttpClientFormData) {
				$multipartChunks = $this->prepareMultipartRequest($data);
			}

			// If http post then build the data
			if ($this->method == "post") {
				if (is_array($data)) {
					$data = http_build_query($data);
					$this->setHeader("Content-Type", "application/x-www-form-urlencoded");
					$this->setHeader("Content-Length", strlen($data));
				}
			}
		}

		$query = isset($this->url["query"]) ? $this->url["query"] : "";

		// If local file
		if ($this->localFilePath) {
			if ($this->getHeader("Content-Disposition", false) === false) {
				$this->setHeader("Content-Disposition", 'attachment; filename="' . basename($this->localFilePath) . '"');
			}

			$this->setHeader("Content-Length", filesize($this->localFilePath));
		}

		// Debug request URL
		if ($this->client->getLogLevel() >= 1) {
			$this->client->log("- HTTP: (" . strtoupper($this->method) . ") " . $this->url["path"] . ($query ? "?" . $query : ""));
		}

		// Setup base of request
		$this->writeLine(strtoupper($this->method) . " " . $this->url["path"] . ($query ? "?" . $query : "") . " HTTP/1.1");

		// Handle Basic Authentication
		if (!empty($this->basicAuthUser) && !empty($this->basicAuthPassword)) {
			$this->setHeader('Authorization', 'Basic ' . base64_encode($this->basicAuthUser . ':' . $this->basicAuthPassword));
		}

		// Add headers
		foreach ($this->headers as $key => $value) {
			// If header has multiple values
			if (is_array($value)) {
				$values = array_values($value);
				foreach ($values as $value2) {
					if (strlen($value2)) {
						$this->writeLine($key . ": " . $value2);
					}
				}
			} else {
				if (strlen($value)) {
					$this->writeLine($key . ": " . $value);
				}
			}
		}

		// Send request to client
		$this->writeLine("");

		$this->writeFile($data, $multipartChunks);
	}

	public function close() {
		$response = new MOXMAN_Http_HttpClientResponse($this->client, $this, 0, $this->stream);
		return $response;
	}

	public function getConnection() {
		return $this->socket;
	}

	public function closeConnection() {
		if (is_resource($this->socket)) {
			fclose($this->socket);
			$this->socket = 0;
		}
	}

	private function writeFile($data, $multipartChunks) {
		// Stream local file
		if ($this->method == "put" && $data === null && $this->localFilePath) {
			if ($this->client->getLogLevel() >= 2) {
				$this->client->log("- Stream local file: " . $this->localFilePath);
			}

			$fp = fopen($this->localFilePath, "rb");
			$bufferSize = $this->client->getBufferSize();
			$outputStream = $this->stream;

			while (($data = fread($fp, $bufferSize)) !== "") {
				fputs($outputStream, $data);
				$this->checkWriteTimeout();
			}

			fclose($fp);
		}

		// Send multipart request data or just normal form data
		if (($this->method == "post" || $this->method == "put") && $data !== null) {
			if ($multipartChunks) {
				$this->sendMultipartContent($multipartChunks);
			} else if (!is_array($data)) {
				$this->write($data);
			}
		}
	}

	/**
	 * Prepares a multipart request this will calculate the content-length of the request and setup and array with
	 * strings and item references to be used later on in the acutal request.
	 *
	 * @private
	 * @param MOXMAN_Http_HttpClientFormData $data Form data to send.
	 * @return Array Chunks to send.
	 */
	private function prepareMultipartRequest($data) {
		$chunks = array();
		$contentLength = 0;
		$boundary = "----moxiehttpclientboundary";

		$items = $data->getItems();
		foreach ($items as $name => $item) {
			if (is_string($item)) {
				// Normal name/value field
				$chunk = "--" . $boundary . "\r\n";
				$chunk .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n\r\n";
				$chunk .= $item . "\r\n";

				// Add chunk and increase length
				$contentLength += strlen($chunk);
				$chunks[] = $chunk;
			} else {
				if (!file_exists($item[0])) {
					throw new MOXMAN_Http_HttpClientException("Could not open file: " . $item[0] . " for upload using multipart.");
				}

				// File/stream field
				$chunk = "--" . $boundary . "\r\n";
				$chunk .= "Content-Disposition: form-data; name=\"" . $name . "\"; filename=\"" . rawurlencode($item[1]) . "\"\r\n";
				$chunk .= "Content-Type: " . $item[2] . "\r\n\r\n";

				// Add before chunk
				$contentLength += strlen($chunk);
				$chunks[] = $chunk;

				// Add blob and use the blob size
				$contentLength += filesize($item[0]);
				$chunks[] = $item;

				// Add after chunk
				$chunk = "\r\n--" . $boundary . "--\r\n";
				$contentLength += strlen($chunk);
				$chunks[] = $chunk;
			}
		}

		// Set content type, boundary and length
		$this->setHeader("Content-Type", "multipart/form-data; boundary=" . $boundary);
		$this->setHeader("Content-Length", $contentLength);

		return $chunks;
	}

	/**
	 * Sends the multipart chunk array to the server.
	 *
	 * @param Array $chunks Chunks to send to server.
	 */
	private function sendMultipartContent($chunks) {
		$bufferSize = $this->client->getBufferSize();

		for ($i = 0, $l = count($chunks); $i < $l; $i++) {
			$chunk = $chunks[$i];

			if (is_array($chunk)) {
				// Read file and send it chunk by chunk
				$fp = fopen($chunk[0], "rb");
				if ($fp) {
					while (!feof($fp)) {
						$this->fputs(fread($fp, $bufferSize), true);
					}

					fclose($fp);
				}
			} else {
				// Output simple text chunk
				$this->fputs($chunk, true);
			}
		}
	}

	/**
	 * Returns the clients internal socket resource.
	 *
	 * @return resource Resource for internal socket.
	 */
	private function getSocketStream() {
		$this->socket = $this->client->getConnection();

		// Open the socket if needed
		if (!is_resource($this->socket)) {
			if ($this->client->getLogLevel() >= 2) {
				$this->client->log("- Socket opened.");
			}

			$url = $this->url;
			$errno = $errstr = 0;
			$scheme = isset($url["scheme"]) ? strtolower($url["scheme"]) : "http";

			$host = $url["host"];
			$port = $url["port"];

			$proxy = $this->client->getProxy();
			if ($proxy) {
				$proxy = explode(":", $proxy);
				$host = $proxy[0];
				$port = isset($proxy[1]) ? $proxy[1] : 80;
			}

			// Cache host resolves since sometimes fsockopen is slow for example on localhost
			if (!isset(self::$hostCache[$host])) {
				$ip = gethostbyname($host);
				self::$hostCache[$host] = $ip;
			} else {
				$ip = self::$hostCache[$host];
			}

			$contextOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_host' => false
				),

				'tls' => array(
					'verify_peer' => false,
					'verify_host' => false
				)
			);

			$sslContext = stream_context_create($contextOptions);

			if ($scheme == "https") {
				$this->socket = stream_socket_client("ssl://" . $host . ":" . $port, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $sslContext);
			} else if ($scheme == "tls") {
				$port = 443;
				$this->socket = stream_socket_client("tls://" . $host . ":" . $port, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $sslContext);
			} else {
				$this->socket = stream_socket_client("tcp://" . $ip . ":" . $port, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $sslContext);
			}

			// Socket connection failed
			if ($this->socket === false) {
				throw new MOXMAN_Http_HttpClientException(
					"Failed to open socket connection to: " .
					$host . ":" . $port .
					". Err: [" . $errno . "] " . $errstr
				);
			}

			// Set socket read timeout
			stream_set_timeout($this->socket, $this->client->getReadTimeout());
			$this->client->setConnection($this->socket);
		}

		return $this->socket;
	}
}