<?php
/**
 * StreamFile.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Http handler that streams files from the file system.
 *
 * @package MOXMAN_Handlers
 */
class MOXMAN_Handlers_StreamFileHandler implements MOXMAN_Http_IHandler {
	/** @ignore */
	private $plugin;

	/** @ignore */
	public function __construct(MOXMAN_IPlugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Sends the specified file with the correct mime type back to the browser.
	 * This method gets called from the client side using the stream file.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$request = $httpContext->getRequest();
		$response = $httpContext->getResponse();

		try {
			$file = MOXMAN::getFile($request->get("path"));
		} catch (Exception $e) {
			$response->setStatus("500", "Could not resolve path: " . $request->get("path"));

			if (MOXMAN::getLogger()) {
				MOXMAN::getLogger()->debug("Could not resolve path: " . $request->get("path"));
			}

			return;
		}

		// Create thumbnail
		if ($request->get("thumb")) {
			try {
				$file = $this->plugin->createThumbnail($file);
			} catch (Exception $e) {
				$response->setStatus("500", "Could not generate thumbnail.");
				$response->sendContent("Could not generate thumbnail.");
				return;
			}
		}

		// Fire before stream event
		$args = new MOXMAN_Vfs_StreamEventArgs($httpContext, $file);
		$this->plugin->fire("BeforeStream", $args);
		$file = $args->getFile();

		// Stream temp file if it exists
		if ($tempName = $request->get("tempname")) {
			$ext = MOXMAN_Util_PathUtils::getExtension($file->getName());
			$tempName = "mcic_" . md5(session_id() . $file->getName()) . "." . $ext;
			$tempFilePath = MOXMAN_Util_PathUtils::combine(MOXMAN_Util_PathUtils::getTempDir(), $tempName);
			if (file_exists($tempFilePath)) {
				$response->sendLocalFile($tempFilePath);
				return;
			}
		}

		$url = $file->getUrl();
		if ($url && !$request->get("stream", false)) {
			$response->redirect($url);
		} else {
			// Force 48h cache time
			$offset = 48 * 60 * 60;
			$response->setHeader("Cache-Control", "max-age=" . $offset);
			$response->setHeader("Date", gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT");
			$response->setHeader("Expires", gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT");
			$response->setHeader("Pragma", "public");

			$response->sendFile($file);
		}
	}
}

?>