<?php
/**
 * Client.php
 *
 * Copyright 2003-2015, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Client for Amazon S3 REST API.
 *
 * Example:
 * $client = new MOXMAN_AmazonS3_Client(array(
 *     "bucket" => "moxiecodefrankfurt",
 *     "endpoint" => "s3.eu-central-1.amazonaws.com",
 *     "publickey" => "AKIAI5WCAA4FUDK3NU3A",
 *     "privatekey" => "qZ6GqBsjQX2/YYCd34nsTuZ2zBt5AlJoTTn9nJnz"
 * ));
 *
 * $files = $client->listFiles("/dir");
 * var_dump($files);
 *
 * $client->putFileContents("/dir/file.txt", "Hello world!");
 * echo "File contents:" . $client->getFileContents("/dir/file.txt");
 *
 * $client->delete("/dir/file.txt");
 */
class MOXMAN_AmazonS3_Client {
	private $httpClient, $bucket, $endPoint, $publicKey, $privateKey;
	private $service, $region, $proxy, $acl, $cacheControl, $scheme;

	/**
	 * Constructs a new Amazon S3 client instance.
	 *
	 * @param Array $config Config array with S3 options.
	 */
	public function __construct($config) {
		$service = "s3";
		$region = "us-east-1";
		$bucket = isset($config["bucket"]) ? $config["bucket"] : "";
		$endPoint = isset($config["endpoint"])  ? $config["endpoint"] : "";
		$publicKey = isset($config["publickey"]) ? $config["publickey"] : "";
		$privateKey = isset($config["privatekey"]) ? $config["privatekey"] : "";

		if (!$endPoint) {
			$endPoint = "s3.amazonaws.com";
		}

		$matches = array();
		if (preg_match('/^([^.]+)\.([^.]+)\.([^.]+)\.amazonaws\.com$/', $endPoint, $matches)) {
			$bucket = $matches[1];
			$service = $matches[2];
			$region = $matches[3];
		} else if (preg_match('/^([^.]+)\.([^.]+)\.amazonaws\.com$/', $endPoint, $matches)) {
			if ($matches[1] == "s3") {
				$endPoint = $bucket . "." . $endPoint;
				$service = $matches[1];
				$region = $matches[2];
			} else {
				$bucket = $matches[1];
				if ($matches[2] != "s3") {
					$region = $matches[2];
				}
			}
		} else if (preg_match('/^([^.]+)\.amazonaws\.com$/', $endPoint, $matches)) {
			if ($matches[1] != "s3") {
				$region = $matches[1];
			}

			$endPoint = $bucket . "." . $endPoint;
		}

		$this->bucket = $bucket;
		$this->endPoint = $endPoint;
		$this->publicKey = $publicKey;
		$this->privateKey = $privateKey;
		$this->service = $service;
		$this->region = preg_replace('/^s3-/', '', $region);
		$this->algorithm = "SHA256";
		$this->scheme = isset($config["scheme"]) ? $config["scheme"] : "tls";
		$this->proxy = isset($config["proxy"]) ? $config["proxy"] : "";
		$this->acl = isset($config["acl"]) ? $config["acl"] : "public-read";
		$this->cacheControl = isset($config["cache_control"]) ? $config["cache_control"] : "";

		// Verify that bucket name doesn't include any uppercase characters
		// See: http://docs.aws.amazon.com/AmazonS3/latest/dev/BucketRestrictions.html#bucketnamingrules
		if (preg_match('/[A-Z]+/', $bucket)) {
			throw new MOXMAN_Exception(
				"Invalid bucket name: " . $bucket . ". " .
				"Bucket names must be lowercase and DNS compliant. " .
				"Check the AWS documentation for details."
			);
		}

		if (!$publicKey || !$privateKey) {
			throw new MOXMAN_Exception("Private and public keys for bucket " . $bucket . " are required.");
		}
	}

	public function getInfo() {
		return array(
			"bucket" => $this->bucket,
			"endpoint" => $this->endPoint,
			"service" => $this->service,
			"region" => $this->region
		);
	}

	/**
	 * Returns file contents from a file at the specified path.
	 *
	 * @param String $path Path to the file to be read.
	 * @return String Contents of file.
	 */
	public function getFileContents($path) {
		return $this->sendRequest($this->createRequest($path))->getBody();
	}

	/**
	 * Writes contents to the specified file.
	 *
	 * @param String $path Path to file to be written to.
	 * @param String $content File contents to be written to file.
	 */
	public function putFileContents($path, $content) {
		$request = $this->createRequest($path, "PUT");
		$request->setHeader("Content-Length", strlen($content));

		if ($this->cacheControl) {
			$request->setHeader("Cache-Control", $this->cacheControl);
		}

		$request->setHeader("x-amz-acl", $this->acl);
		$request->setHeader("Content-Type", MOXMAN_Util_Mime::get($path));
		$request->setBody($content);

		$this->sendRequest($request, $this->hash($content));

		return array(
			"name" => basename($path),
			"isdir" => false,
			"size" => strlen($content),
			"mdate" => time()
		);
	}

	/**
	 * Imports a local file into S3 by chunking the requests. This makes it possible to import
	 * larger files.
	 *
	 * @param String $path Path on S3 where the file will be written.
	 * @param String $localPath Local file system path to import from.
	 */
	public function importFrom($localPath, $path) {
		$contentLength = filesize($localPath);
		$chunkSize = 8192 * 4; // S3 Minimum chunk length = 8192
		$numberOfChunks = ceil($contentLength / $chunkSize);
		$date = $this->getCurrentUtcDate();

		$emptyChunkMetaLength = strlen($this->getBodyChunk($this->hash(""))) - 1; // Excluding length byte
		$metaLength = $emptyChunkMetaLength + 1;

		if ($contentLength > $chunkSize) {
			$chunkMetaSize = $emptyChunkMetaLength + strlen(dechex($chunkSize));
			$lastChunkSize = $chunkSize - ($numberOfChunks * $chunkSize - $contentLength);
			$lastChunkMetaSize = $emptyChunkMetaLength + strlen(dechex($lastChunkSize));
			$metaLength += $chunkMetaSize * ($numberOfChunks - 1) + $lastChunkMetaSize;
		} else {
			$metaLength += $emptyChunkMetaLength + strlen(dechex($contentLength));
		}

		$request = $this->createRequest($path, "PUT");
		$request->setHeader("X-Amz-Content-SHA256", "STREAMING-AWS4-HMAC-SHA256-PAYLOAD");
		$request->setHeader("X-Amz-Date", $date->format("Ymd\THis\Z"));
		$request->setHeader("x-amz-decoded-content-length", $contentLength);
		$request->setHeader("content-encoding", "aws-chunked");
		$request->setHeader("content-length", $contentLength + $metaLength);
		$request->setHeader("x-amz-acl", $this->acl);
		$request->setHeader("Content-Type", MOXMAN_Util_Mime::get($path));

		if ($this->cacheControl) {
			$request->setHeader("Cache-Control", $this->cacheControl);
		}

		$canonical = $this->getCanonicalRequest($request, "STREAMING-AWS4-HMAC-SHA256-PAYLOAD");
		$signature = $this->getSignature($this->getStringToSign($canonical, $date), $date);
		$request->setHeader("Authorization", $this->getAuthorization($request, $date, $signature));

		$request->open();

		// Write contents in chunks
		$fp = fopen($localPath, "rb");
		if ($fp) {
			for ($i = 0; $i < $numberOfChunks; $i++) {
				$content = fread($fp, $chunkSize);
				$signature = $this->getSignature($this->getPayLoadStringToSign($signature, $date, $content), $date);
				$request->write($this->getBodyChunk($signature, $content));
			}

			fclose($fp);
		}

		// Write end chunk
		$signature = $this->getSignature($this->getPayLoadStringToSign($signature, $date), $date);
		$request->write($this->getBodyChunk($signature));
		$response = $request->close();
		$this->handleError($response);
	}

	/**
	 * Exports a S3 file to a local file by chunking. This makes it possible to download larger files.
	 *
	 * @param String $path Path for the S3 file to be exported.
	 * @param String $localPath Local path to where the file will be written.
	 */
	public function exportTo($path, $localPath) {
		$response = $this->sendRequest($this->createRequest($path));

		// Read remote file and write the contents to local file
		$fp = fopen($localPath, "wb");
		if ($fp) {
			// Stream file down to disk
			while (($chunk = $response->read()) != "") {
				fwrite($fp, $chunk);
			}

			fclose($fp);
		}
	}

	/**
	 * Deletes a file by path this will remove the specified file from S3.
	 *
	 * @param String $path Path to the S3 file to remove.
	 */
	public function delete($path) {
		$this->sendRequest($this->createRequest($path, "DELETE"));
	}

	/**
	 * Copies the specified file from one S3 location to another.
	 *
	 * @param String $fromPath S3 path to copy from.
	 * @param String $toPath S3 path to copy to.
	 */
	public function copy($fromPath, $toPath) {
		$request = $this->createRequest($toPath, "PUT");

		if ($this->cacheControl) {
			$request->setHeader("Cache-Control", $this->cacheControl);
		}

		$request->setHeader("x-amz-acl", $this->acl);
		$request->setHeader("x-amz-copy-source", $this->uriEncode('/' . $this->bucket . $fromPath, false));
		$request->setHeader("Content-Length", 0);

		$this->sendRequest($request);
	}

	/**
	 * Creates an empty directory at the specified S3 path. Empty directories doesn't really exist on S3
	 * so it fakes it by creating an empty file with a slash at the end like "/mydir/".
	 *
	 * @param String $path Path to where the dir will be created.
	 */
	public function mkdir($path) {
		$stat = $this->putFileContents($path . "/", "");
		$stat["isdir"] = true;
		return $stat;
	}

	/**
	 * Lists files on the specified path.
	 *
	 * @param String $path
	 */
	public function listFiles($path) {
		$prefix = $this->getPrefixFromPath($path);
		$isTruncated = true;
		$marker = null;
		$files = array();

		while ($isTruncated) {
			$query = array(
				"delimiter" => "/",
				"max-keys" => "5000"
			);

			if ($prefix) {
				$query["prefix"] = $prefix;
			}

			if ($marker) {
				$query["marker"] = $marker;
			}

			$request = $this->createRequest("/");
			$request->setQuery($this->buildQuery($query));
			$response = $this->sendRequest($request);
			$xml = new SimpleXMLElement($response->getBody());

			// List directories
			if (isset($xml->CommonPrefixes)) {
				foreach ($xml->CommonPrefixes as $cprefix) {
					if ($prefix != $cprefix->Prefix) {
						$stat = array(
							"name" => basename($cprefix->Prefix),
							"isdir" => true,
							"size" => 0,
							"mdate" => 0
						);

						$path = MOXMAN_Util_PathUtils::combine($path, $stat["name"]);
						$files[] = $stat;
					}
				}
			}

			// List files
			if (isset($xml->Contents)) {
				foreach ($xml->Contents as $contents) {
					if ($prefix != $contents->Key) {
						$stat = array(
							"name" => basename($contents->Key),
							"isdir" => strrpos($contents->Key, "/") === strlen($contents->Key) - 1,
							"size" => intval($contents->Size),
							"mdate" => strtotime($contents->LastModified)
						);

						$path = MOXMAN_Util_PathUtils::combine($path, $stat["name"]);
						$files[] = $stat;
					}
				}
			}

			$isTruncated = ("" . $xml->IsTruncated) === "true";
			$marker = "" . $xml->NextMarker;
		}

		return $files;
	}

	public function stat($path) {
		if (strrpos($path, "/") === strlen($path) - 1) {
			return $this->statDir($path);
		}

		// Assume file first
		if (strrpos($path, '.') !== false) {
			$stat = $this->statFile($path);
			if (!$stat) {
				 $stat = $this->statDir($path);
			}
		} else {
			// Assume dir first
			$stat = $this->statDir($path);
			if (!$stat) {
				 $stat = $this->statFile($path);
			}
		}

		return $stat;
	}

	public function close() {
		if ($this->httpClient) {
			$this->httpClient->close();
			$this->httpClient = null;
		}
	}

	public function setLogFunction($func) {
		$this->getHttpClient()->setLogFunction($func);
	}

	public function setLogLevel($level) {
		$this->getHttpClient()->setLogLevel($level);
	}

	protected function getHttpClient() {
		if (!$this->httpClient) {
			$this->httpClient = new MOXMAN_Http_HttpClient($this->scheme . "://" . $this->endPoint);
			$this->httpClient->setProxy($this->proxy);
		}

		return $this->httpClient;
	}

	// Private methods

	private function hash($content) {
		return hash("SHA256", $content);
	}

	private function getBodyChunk($signature, $content = "") {
		$content = dechex(strlen($content)) . ";chunk-signature=" . $signature . "\r\n" . $content . "\r\n";
		return $content;
	}

	private function getCanonicalRequest(MOXMAN_Http_HttpClientRequest $request, $payloadHash = "") {
		$url = $request->getUrl();

		// Generate default payload hash
		if (!$payloadHash) {
			$payloadHash = $this->hash("");
		}

		// Generate canonical request
		$canonicalRequest = implode("\n", array(
			strtoupper($request->getMethod()),
			$url["path"],
			$this->getCanonicalQuery($request),
			$this->getCanonicalHeaders($request),
			$this->getSignedHeaders($request),
			$payloadHash
		));

		return $canonicalRequest;
	}

	private function getCanonicalQuery(MOXMAN_Http_HttpClientRequest $request) {
		$url = $request->getUrl();

		// Generate Canonical query string
		$canonicalQuery = "";
		$query = array();
		parse_str(isset($url["query"]) ? $url["query"] : "", $query);
		uksort($query, "strcmp");
		foreach ($query as $key => $value) {
			if ($canonicalQuery) {
				$canonicalQuery .= "&";
			}

			$canonicalQuery .= $this->uriEncode($key) . "=" . $this->uriEncode($value);
		}

		return $canonicalQuery;
	}

	private function getCanonicalHeaders(MOXMAN_Http_HttpClientRequest $request) {
		$headers = array();
		foreach ($request->getHeaders() as $key => $value) {
			$headers[strtolower($key)] = $value;
		}
		uksort($headers, "strcmp");

		$canonicalHeaders = "";
		foreach ($headers as $key => $value) {
			$canonicalHeaders .= strtolower($key) . ":" . trim($value) . "\n";
		}

		return $canonicalHeaders;
	}

	private function getSignedHeaders(MOXMAN_Http_HttpClientRequest $request) {
		$signedHeaders = array();

		foreach (array_keys($request->getHeaders()) as $key) {
			$signedHeaders[] = strtolower($key);
		}

		sort($signedHeaders);

		return strtolower(implode(";", $signedHeaders));
	}

	private function getStringToSign($canonicalRequest, DateTime $date) {
		$stringToSign = implode("\n", array(
			"AWS4-HMAC-" . $this->algorithm,
			$date->format("Ymd\THis\Z"),
			$date->format("Ymd") . "/" . $this->region . "/s3/aws4_request",
			$this->hash($canonicalRequest)
		));

		return $stringToSign;
	}

	private function getPayLoadStringToSign($signature, DateTime $date, $content = "") {
		$stringToSign = implode("\n", array(
			"AWS4-HMAC-" . $this->algorithm . "-PAYLOAD",
			$date->format("Ymd\THis\Z"),
			$date->format("Ymd") . "/" . $this->region . "/s3/aws4_request",
			$signature,
			$this->hash(""),
			$this->hash($content)
		));

		return $stringToSign;
	}

	private function getSignature($stringToSign, DateTime $date) {
		$secretKey = "AWS4" . $this->privateKey;
		$dateKey = hash_hmac($this->algorithm, $date->format("Ymd"), $secretKey, true);
		$regionKey = hash_hmac($this->algorithm, $this->region, $dateKey, true);
		$serviceKey = hash_hmac($this->algorithm, $this->service, $regionKey, true);
		$signingKey = hash_hmac($this->algorithm, "aws4_request", $serviceKey, true);
		$signature = hash_hmac($this->algorithm, $stringToSign, $signingKey);

		return $signature;
	}

	private function getScope(DateTime $date) {
		return implode("/", array(
			$date->format("Ymd"),
			$this->region,
			$this->service,
			"aws4_request"
		));
	}

	public function createRequest($path, $method = "GET") {
		return $this->getHttpClient()->createRequest($this->uriEncode($path, false), $method);
	}

	private function sendRequest(MOXMAN_Http_HttpClientRequest $request, $payloadHash = "") {
		return $this->handleError($this->sendRawRequest($request, $payloadHash));
	}

	public function getCurrentUtcDate() {
		return new DateTime("now", new DateTimeZone("UTC"));
	}

	public function sendRawRequest(MOXMAN_Http_HttpClientRequest $request, $payloadHash = "") {
		$date = $this->getCurrentUtcDate();

		// Generate default payload hash
		if (!$payloadHash) {
			$payloadHash = $this->hash("");
		}

		$request->setHeader("X-Amz-Content-SHA256", $payloadHash);
		$request->setHeader("X-Amz-Date", $date->format("Ymd\THis\Z"));

		$canonicalRequest = $this->getCanonicalRequest($request, $payloadHash);
		$stringToSign = $this->getStringToSign($canonicalRequest, $date);
		$signature = $this->getSignature($stringToSign, $date);

		$request->setHeader("Authorization", $this->getAuthorization($request, $date, $signature));

		return $request->send();
	}

	public function getAuthorization(MOXMAN_Http_HttpClientRequest $request, DateTime $date, $signature) {
		$authorization = array(
			"Credential=" . $this->publicKey . "/" . $this->getScope($date),
			"SignedHeaders=" . $this->getSignedHeaders($request),
			"Signature=" . $signature,
		);

		return "AWS4-HMAC-SHA256" . " " . implode(",", $authorization);
	}

	private function handleError($response) {
		// Handle errors
		if ($response->getCode() >= 400) {
			$body = $response->getBody();

			if (strpos($body, "<Error>") !== false) {
				$body = new SimpleXMLElement($body);

				throw new MOXMAN_Exception(
					"AmazonS3 error: " . $response->getCode() . " ". $body->Message
				);
			}

			throw new MOXMAN_Exception("AmazonS3 error: AmazonS3 returned an error: " . $response->getCode());
		}

		return $response;
	}

	private function statFile($path) {
		$request = $this->createRequest($path, "HEAD");
		$response = $this->sendRawRequest($request);

		if ($response->getCode() == 200) {
			return array(
				"name" => basename($path),
				"isdir" => false,
				"size" => $response->getHeader("Content-Length"),
				"mdate" => strtotime($response->getHeader("Last-Modified"))
			);
		}

		return null;
	}

	private function statDir($path) {
		$prefix = $this->getPrefixFromPath($path);
		$request = $this->createRequest("/");
		$request->setQuery($this->buildQuery(array(
			"prefix" => $prefix,
			"delimiter" => "/",
			"max-keys" => "1"
		)));

		$response = $this->sendRequest($request);
		$xml = new SimpleXMLElement($response->getBody());

		$stat = null;
		$isTruncated = ("" . $xml->IsTruncated) == "true";

		// Check if prefix has any data then the directory exists
		if (isset($xml->Contents) || $isTruncated || isset($xml->CommonPrefixes)) {
			$hasContents = $isTruncated || (isset($xml->Contents) && count($xml->Contents) > 1);

			$stat = array(
				"name" => basename($path),
				"isdir" => true,
				"size" => 0,
				"mdate" => 0,
				"empty" => !$hasContents
			);
		}

		return $stat;
	}

	private function getPrefixFromPath($path) {
		$prefix = substr($path, 1);

		if (strrpos($prefix, "/") !== strlen($prefix) - 1) {
			$prefix .= "/";
		}

		if ($prefix == "/") {
			$prefix = "";
		}

		return $prefix;
	}

	private function buildQuery($query) {
		$queryStr = "";
		uksort($query, "strcmp");

		foreach ($query as $key => $value) {
			if ($queryStr) {
				$queryStr .= "&";
			}

			$queryStr .= $this->uriEncode($key) . "=" . $this->uriEncode($value);
		}

		return $queryStr;
	}

	private function uriEncode($input, $encodeSlash = true) {
		$output = "";

		for ($i = 0; $i < strlen($input); $i++) {
			$ch = $input[$i];

			if (preg_match('/^[A-Z0-9_\-~\.]$/i', $ch)) {
				$output .= $ch;
			} else if ($ch == '/') {
				$output .= $encodeSlash ? "%2F" : "/";
			} else {
				$output .= "%" . strtoupper(dechex(ord($ch)));
			}
		}

		return $output;
	}
}

?>