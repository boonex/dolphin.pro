<?php
/**
 * HttpClientCurlRequest.php
 *
 * Copyright 2003-2015, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Curl implementation of HttpClientRequest.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_HttpClientCurlRequest extends MOXMAN_Http_HttpClientRequest {
	private $curlContentLength, $isLastCurlResponse, $curlHandle, $responseStream, $bodyStream, $data;

	public function open($data = null) {
		$query = isset($this->url["query"]) ? $this->url["query"] : "";

		// Debug request URL
		if ($this->client->getLogLevel() >= 1) {
			$this->client->log("- HTTP: (" . strtoupper($this->method) . ") " . $this->url["path"] . ($query ? "?" . $query : ""));
		}

		$this->curlContentLength = 0;
		$this->stream = fopen('php://temp', "w+");
		$this->responseStream = fopen('php://temp', "w+");

		$url = $this->url;
		$scheme = isset($url["scheme"]) ? strtolower($url["scheme"]) : "http";

		if ($scheme == "tls") {
			$scheme = "https";
			curl_setopt($this->curlHandle, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv2);
		}

		$url = $scheme . '://' . $url["host"] . $url["path"] . (isset($url["query"]) && $url["query"] ? '?' . $url["query"] : '');

		$this->curlHandle = curl_init();
		curl_setopt_array($this->curlHandle, array(
			CURLOPT_HEADERFUNCTION => array($this, 'curlCallbackWriteHeader'),
			CURLOPT_WRITEFUNCTION  => array($this, 'curlCallbackWriteBody'),
			CURLOPT_BUFFERSIZE     => $this->client->getBufferSize(),
			CURLOPT_CONNECTTIMEOUT => $this->client->getReadTimeout(),
			CURLINFO_HEADER_OUT    => true,
			CURLOPT_URL            => $url,
			CURLOPT_PORT           => $this->url["port"],
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_TIMEOUT        => $this->client->getReadTimeout()
		));

		if (!empty($this->basicAuthUser) && !empty($this->basicAuthPassword)) {
			curl_setopt($this->curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->curlHandle, CURLOPT_USERPWD, $this->basicAuthUser . ':' . $this->basicAuthPassword);
		}

		$proxy = $this->client->getProxy();
		if ($proxy) {
			curl_setopt($this->curlHandle, CURLOPT_PROXY, $proxy);
		}

		// Remove 100 expect header from request
		if (!$this->getHeader("Expect")) {
			$this->setHeader("Expect", "");
		}

		$headers = array();
		foreach ($this->headers as $key => $value) {
			$headers[] = $key . ':' . $value;
		}

		curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);

		$this->data = $data;
	}

	public function write($buff) {
		if (!$this->bodyStream) {
			$this->bodyStream = fopen("php://temp", "w+");
		}

		fwrite($this->bodyStream, $buff);
	}

	public function close() {
		$data = $this->data;

		// Fake log of request data
		if ($this->client->getLogLevel() >= 2) {
			$query = isset($this->url["query"]) ? $this->url["query"] : "";
			$this->client->log("> " . strtoupper($this->method) . " " . $this->url["path"] . ($query ? "?" . $query : "") . " HTTP/1.1");

			foreach ($this->getHeaders() as $key => $value) {
				$this->client->log("> " . $key . ": " . $value);
			}
		}

		switch ($this->method) {
			case "get":
				curl_setopt($this->curlHandle, CURLOPT_HTTPGET, true);
				break;

			case "post":
				curl_setopt($this->curlHandle, CURLOPT_POST, true);

				// TODO: Implement this properly
				if ($data instanceof MOXMAN_Http_HttpClientFormData) {
					$postData = array();

					foreach ($data->getItems() as $item) {
						$postData[] = $item;
					}

					curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $postData);
				} else {
					curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, http_build_query($data));
				}

				break;

			case "put":
				curl_setopt($this->curlHandle, CURLOPT_UPLOAD, true);
				curl_setopt($this->curlHandle, CURLOPT_INFILESIZE, 0);

				if (is_resource($this->bodyStream)) {
					$size = ftell($this->bodyStream);
					rewind($this->bodyStream);
					curl_setopt($this->curlHandle, CURLOPT_INFILE, $this->bodyStream);
					curl_setopt($this->curlHandle, CURLOPT_INFILESIZE, $size);
				}

				break;

			case "head":
				curl_setopt($this->curlHandle, CURLOPT_NOBODY, true);
				break;

			default:
				curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
		}

		if ($this->localFilePath) {
			curl_setopt($this->curlHandle, CURLOPT_INFILE, fopen($this->localFilePath, 'rb'));
			curl_setopt($this->curlHandle, CURLOPT_INFILESIZE, filesize($this->localFilePath));
		}

		if (ftell($this->stream) > 0) {
			rewind($this->stream);
			curl_setopt($this->curlHandle, CURLOPT_INFILE, $this->stream);
		}

		if (curl_exec($this->curlHandle) === false) {
			throw new MOXMAN_Http_HttpClientException('Curl error: ' . curl_error($this->curlHandle));
		}

		curl_close($this->curlHandle);

		rewind($this->responseStream);

		return new MOXMAN_Http_HttpClientResponse($this->client, $this, $this->curlContentLength, $this->responseStream);
	}

	/**
	 * Called by curl when a header is parsed in HTTP socket stream.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function curlCallbackWriteHeader($curlHandle, $string) {
		// Auth and continue requests will produce multiple header chunks
		if (preg_match('!^(?:HTTP/(\d\.\d)) (\d{3})(?: (.+))?!', $string, $matches)) {
			if ($matches[2] >= 200) {
				$this->isLastCurlResponse = true;
			}
		}

		// Only add headers to stream if we are in the last response headers chunk
		if ($this->isLastCurlResponse) {
			fputs($this->responseStream, $string);
		}

		return strlen($string);
	}

	/**
	 * Called by curl when a body is parsed in HTTP socket stream.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function curlCallbackWriteBody($curlHandle, $string) {
		$len = strlen($string);

		if ($this->isLastCurlResponse) {
			$this->curlContentLength .= $len;
			fputs($this->responseStream, $string);
		}

		return $len;
	}
}