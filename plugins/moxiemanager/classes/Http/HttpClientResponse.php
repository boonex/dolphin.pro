<?php
/**
 * HttpClientResponse.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class represents the HTTP response from a MOXMAN_Http_HttpClient instance.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_HttpClientResponse {
	/** @ignore */
	private $client, $req, $stream, $chunkLength, $isEmptyBody, $contentIndex;

	/** @ignore */
	private $version, $code, $message, $headers, $transferEncoding, $contentLength, $contentEncoding, $chunkedContentLength;

	/**
	 * Constructs a new http client response instance this is normally done by the HTTP client.
	 *
	 * @param MOXMAN_Http_HttpClient $client HTTP client instance to connect to request.
	 * @param MOXMAN_Http_HttpClientRequest $req HTTP client request instance for the specified response.
	 */
	public function __construct(MOXMAN_Http_HttpClient $client, MOXMAN_Http_HttpClientRequest $req, $contentLength = 0, $stream = null) {
		$this->client = $client;
		$this->req = $req;
		$this->stream = $stream;
		$this->bufferSize = $client->getBufferSize();
		$this->chunkLength = 0;
		$this->contentIndex = 0;

		$this->readHead();
		$this->transferEncoding = strtolower($this->getHeader("transfer-encoding", ""));
		$this->contentEncoding = strtolower($this->getHeader("content-encoding", ""));
		$this->contentLength = $contentLength ? $contentLength : $this->getHeader("content-length", 0);
		$this->chunkedContentLength = $this->contentLength;

		$method = $req->getMethod();
		$code = $this->getCode();

		// These requests doesn't have a body
		if ($method == "head" || $code == 204 || $code == 304 || ($method == "connect" && $code >= 200 && $code < 300)) {
			$this->isEmptyBody = true;
		}
	}

	/**
	 * Automatically close the socket on destruction.
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Http version like 1.1.
	 *
	 * @return String Http version string.
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Returns the HTTP status code like 200 for an valid request.
	 *
	 * @return Int HTTP status code like 200.
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Returns the HTTP status message like OK for an valid request.
	 *
	 * @return Int HTTP status message like OK.
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Returns the whole HTTP response content body as a string.
	 *
	 * @return String HTTP response content body.
	 */
	public function getBody() {
		$body = "";
		$chunk = "";

		// Read all body contents into a string
		while (($chunk = $this->read())) {
			$body .= $chunk;
		}

		return $body;
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
	 * Returns a specific header by name if it's not found it will return the specified default value.
	 *
	 * @param string $name Name of the header to retrive.
	 * @param mixed $default Default value to return.
	 * @return mixed Specified header value or default value if it doesn't exist.
	 */
	public function getHeader($name, $default = null) {
		$name = strtolower($name);

		return isset($this->headers[$name]) ? $this->headers[$name] : $default;
	}

	/**
	 * Reads a chunk out of the response body stream and returns the result data.
	 *
	 * @return String Data from response stream or empty string if there is no more data to read.
	 */
	public function read() {
		// For head requests
		if ($this->isEmptyBody) {
			$this->close();
			return "";
		}

		// Currently we don't support any content encodings like gzip or deflate
		// TODO: Implement this if needed
		if ($this->contentEncoding) {
			throw new MOXMAN_Http_HttpClientException("Unsupported content encoding: " . $this->contentEncoding);
		}

		// Read uncompressed chunk
		$data = $this->readChunk();

		// Close connection when there is no more data
		if ($data === "") {
			$this->close();
		}

		return $data;
	}

	/**
	 * Closes the socket if it needs to be closed.
	 */
	private function close() {
		// Close socket connection if keep alive isn't supported
		if (!$this->canKeepAlive()) {
			$this->client->close();
		} else {
			// Read away socket data to prepare for next request
			if (!$this->isEmptyBody) {
				while ($this->readChunk() !== "") {
					// Empty body
				}
			}
		}
	}

	/**
	 * Reads a chunk out from the response body data stream or returns an empty string if the stream is at the end.
	 *
	 * @private
	 * @return mixed This is the return value description
	 */
	private function readChunk() {
		// Handle content length
		if ($this->contentLength > 0) {
			// End of buffer
			if ($this->contentIndex >= $this->contentLength) {
				return "";
			}

			// Calculate chunk size to read
			$size = $this->contentLength - $this->contentIndex;
			$size = $size < $this->bufferSize ? $size : $this->bufferSize;
			$data = $this->fread($size);

			// Debug chunk size
			if ($this->client->getLogLevel() >= 3) {
				$this->client->log("- Chunk size: " . strlen($data));
			}

			return $data;
		}

		// Handle chunked transfer encoding
		if ($this->transferEncoding === "chunked") {
			if ($this->chunkLength === 0) {
				$line = $this->readLine($this->bufferSize);

				// Curl can produce empty response if first chunk length is 0
				if ($line === '') {
					return '';
				}

				if (!preg_match('/^([0-9a-f]+)/i', $line, $matches)) {
					throw new MOXMAN_Http_HttpClientException("Invalid chunk length: " . $line);
				} else {
					$this->chunkLength = hexdec($matches[1]);
					$this->chunkedContentLength += $this->chunkLength;

					// Chunk with zero length indicates the end
					if ($this->chunkLength === 0) {
						$this->contentLength = $this->chunkedContentLength;
						$this->readLine();
						return '';
					}
				}
			}

			$data = $this->fread(min($this->chunkLength, $this->bufferSize));
			$this->chunkLength -= strlen($data);

			if ($this->chunkLength === 0) {
				$this->readLine(); // Trailing CRLF
			}

			// Debug chunk size
			if ($this->client->getLogLevel() >= 3) {
				$this->client->log("- Chunk size: " . strlen($data));
			}

			return $data;
		}

		return "";
	}

	/**
	 * Reads the header part of the request like the status line and any headers.
	 *
	 * @private
	 */
	private function readHead() {
		$matches = array();

		// Read and parse status line
		$status = $this->readLine();
		if (!preg_match('!^(?:HTTP/(\d\.\d)) (\d{3})(?: (.+))?!', $status, $matches)) {
			throw new MOXMAN_Http_HttpClientException("Malformed status line: " . $status);
		}

		// Debug status line
		if ($this->client->getLogLevel() >= 2) {
			$this->client->log("< " . trim($status));
		}

		$this->version = $matches[1];
		$this->code = intval($matches[2]);
		$this->message = $matches[3];

		// Read and parse headers
		do {
			$line = $this->readLine();

			// Debug header line
			if ($this->client->getLogLevel() >= 2) {
				$this->client->log("< " . trim($line));
			}

			if (preg_match('!^([^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+):(.+)$!', $line, $matches)) {
				$name = strtolower($matches[1]);
				$value = trim($matches[2]);

				// Put multiple headers with the same name into an array
				if (isset($this->headers[$name])) {
					if (is_array($this->headers[$name])) {
						$this->headers[$name][] = $value;
					} else {
						$this->headers[$name] = array($this->headers[$name], $value);
					}
				} else {
					$this->headers[$name] = $value;
				}
			}
		} while ($line !== "");
	}

	/**
	 * Reads the specified length out form the socket.
	 *
	 * @private
	 * @param int $length Number of bytes to read.
	 * @return String Data read or empty string at end of stream.
	 */
	private function fread($length) {
		$data = fread($this->stream, $length);
		$this->contentIndex += strlen($data);
		$this->checkReadTimeout();

		return $data;
	}

	/**
	 * Reads a single line from socket.
	 *
	 * @private
	 * @return String Single line of data or empty string if the socket is at the end of stream.
	 */
	private function readLine() {
		$line = "";

		while (!feof($this->stream)) {
			$line .= fgets($this->stream, $this->bufferSize);
			$this->checkReadTimeout();

			if (substr($line, -1) == "\n") {
				return rtrim($line, "\r\n");
			}
		}

		return $line;
	}

	/**
	 * Checks if the last socket read operation timed out if it has then it throws and exception.
	 */
	private function checkReadTimeout() {
		$info = stream_get_meta_data($this->stream);

		if (isset($info['timed_out']) && $info['timed_out']) {
			// @codeCoverageIgnoreStart
			throw new MOXMAN_Http_HttpClientException("Request timed out.");
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Returns true/false if the connection can be kept alive or not.
	 *
	 * @private
	 * @return Boolean True/false if the connection can be kept alive or not.
	 */
	private function canKeepAlive() {
		// Get method, version and code
		$method = $this->req->getMethod();
		$version = $this->getVersion();
		$code = $this->getCode();

		// If https connect with a valid http status code
		if ($method == "connect" && $code >= 200 && $code < 300) {
			return true;
		}

		// Check if keep-alive header is set or ommitted but with http 1.1
		if ($version == "1.1" && strtolower($this->getHeader("connection", "keep-alive")) !== "keep-alive") {
			return false;
		}

		// Chunked and body is empty or not read
		if ($this->transferEncoding == "chunked" && $this->contentLength == 0) {
			return false;
		}

		// Content length is known or request is head
		if (!($this->transferEncoding == "chunked" || $this->contentLength > 0 || $method == "head" || $code == 204 || $code == 304)) {
			return false;
		}

		// Keep it alive
		return true;
	}
}

?>