<?php
/**
 * Response.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class wraps in the HTTP response in PHP and adds various methods
 * for streaming files and contents to the client.
 *
 * @codeCoverageIgnore
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Response {
	/**
	 * Constructs a HTTP response instance.
	 */
	public function __construct() {
	}

	/**
	 * Sets the HTTP response code for example 404.
	 *
	 * @param number $code Code to set.
	 * @param string $message Message to set.
	 */
	public function setStatus($code, $message = "") {
		header("HTTP/1.0 " . $code . " " . $message);
	}

	/**
	 * Sets the specifed HTTP header.
	 *
	 * @param string $name Name of the header to set.
	 * @param string $value Value to set to the header.
	 * @param Boolean $replace True/false state if the value should replace the default header.
	 */
	public function setHeader($name, $value, $replace = true) {
		if (!defined('PHPUNIT')) {
			header($name . ": " . $value, $replace);
		}
	}

	/**
	 * Sends the specified contents to client unencoded.
	 *
	 * @param string $content Content to send to client.
	 */
	public function sendContent($content) {
		echo $content;
	}

	/**
	 * Sends the specified contents HTML encoded.
	 *
	 * @param string $content Content to send to client HTML encoded.
	 */
	public function sendHtml($content) {
		$this->sendContent(htmlspecialchars($content));
	}

	/**
	 * Sends the specified object as JSON.
	 *
	 * @param Mixed $obj Json data to send.
	 */
	public function sendJson($obj) {
		$this->setHeader('Content-Type', 'application/json');

		$obj = MOXMAN_Util_Json::encode($obj);

		// IE 7 treats application/json download but in case some proxy/server
		// would ignore that content-type and deliver it as HTML trim a few things
		$obj = strtr($obj, array(
			'<' => '\u003c',
			'>' => '\u003e'
		));

		$this->sendContent($obj);
	}

	/**
	 * Redirects the browser to the specified URL.
	 *
	 * @param string $url Url to redirect to.
	 * @param Array $args Query string arguments to pass to page.
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	public function redirect($url, $args = false) {
		$this->disableCache();

		// Add arguments to url
		if ($args) {
			$url .= (strpos($url, "?") !== false ? '&' : '?') . http_build_query($args);
		}

		$this->setHeader("Location", $url);
		die();
	}

	/**
	 * Sets various headers for disabling the cache of the request.
	 */
	public function disableCache() {
		$this->setHeader("Expires", "0");
		$this->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
		$this->setHeader("Cache-Control", "private", false);
	}

	/**
	 * Sends the specified local file to the client.
	 *
	 * @param string $localPath Sets the local file to stream to client.
	 * @param Boolean $download True/false state if the file should be downloaded or not.
	 */
	public function sendLocalFile($localPath, $download = false) {
		if ($download) {
			$this->disableCache();
			$this->setHeader("Content-type", "application/octet-stream");
			$this->setHeader("Content-Disposition", "attachment; filename=\"" . basename($localPath) . "\"");
		} else {
			$this->setHeader("Content-type", MOXMAN_Util_Mime::get(basename($localPath)));
		}

		$this->streamLocalFile($localPath);
	}

	/**
	 * Sends the specified file to the client by streaming it.
	 *
	 * @param MOXMAN_Vfs_IFile $file File to stream to client.
	 * @param Boolean $download State if the file should be downloaded or not by the client.
	 */
	public function sendFile(MOXMAN_Vfs_IFile $file, $download = false) {
		// Check if the file is a local file is so use the faster method
		if ($file instanceof MOXMAN_Vfs_Local_File) {
			$this->sendLocalFile($file->getInternalPath(), $download);
			return;
		}

		// Check if the remote file system has a local temp path
		$localTempPath = MOXMAN::getFileSystemManager()->getLocalTempPath($file);
		if (file_exists($localTempPath)) {
			$this->sendLocalFile($localTempPath, $download);
			return;
		}

		if ($download) {
			$this->disableCache();
			$this->setHeader("Content-type", "application/octet-stream");
			$this->setHeader("Content-Disposition", "attachment; filename=\"" . $file->getName() . "\"");
		} else {
			$this->setHeader("Content-type", MOXMAN_Util_Mime::get($file->getName()));
		}

		// Non local file system then read and stream
		$stream = $file->open(MOXMAN_Vfs_IFileStream::READ);
		if ($stream) {
			// Read chunk by chunk and stream it
			while (($buff = $stream->read()) !== "") {
				$this->sendContent($buff);
			}

			$stream->close();
		}
	}

	/**
	 * Loads the specified file and streams it to http response without setting any headers. This method is used by mockup classes
	 * to override the default behavior.
	 *
	 * @param string $localPath Local path to send to client.
	 */
	protected function streamLocalFile($localPath) {
		readfile($localPath);
	}
}

?>