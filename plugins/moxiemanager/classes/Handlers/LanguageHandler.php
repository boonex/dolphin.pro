<?php
/**
 * LanguageHandler.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * ...
 *
 * @package MOXMAN_Handlers
 */
class MOXMAN_Handlers_LanguageHandler implements MOXMAN_Http_IHandler {
	/**
	 * Process a request using the specified context.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$request = $httpContext->getRequest();
		$response = $httpContext->getResponse();

		$response->disableCache();
		$response->setHeader('Content-type', 'text/javascript');

		// Set prefix if it's a tinymce language pack or not
		$prefix = MOXMAN_ROOT . '/langs/moxman_';
		if ($request->get("tinymce")) {
			$prefix = MOXMAN_ROOT . '/langs/';
		}

		// Load TinyMCE specific pack if it exists
		$langCode = preg_replace('/[^a-z_\-]/i', '', $request->get('code'));
		if ($langCode) {
			$langFile = $prefix . $langCode . '.js';

			if (file_exists($langFile)) {
				$response->sendContent(file_get_contents($langFile));
				return;
			}
		}

		// Fallback to configured language pack
		$langCode = MOXMAN::getConfig()->get("general.language");
		if ($langCode) {
			$langFile = $prefix . $langCode . '.js';

			if (file_exists($langFile)) {
				$response->sendContent(file_get_contents($langFile));
				return;
			}
		}
	}
}
?>