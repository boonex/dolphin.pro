<?php
/**
 * Context.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class works as a factory for Request and response instances.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Context {
	/** @ignore */
	private static $current;

	/** @ignore */
	private $request, $response, $session;

	/**
	 * Constructs a new http context instance with the specified request and response instances to use.
	 *
	 * @param MOXMAN_Http_Request $request HTTP request instance to use for the context.
	 * @param MOXMAN_Http_Response $response HTTP response instance to use for the context.
	 * @param MOXMAN_Http_Session @session HTTP session instance to use for the context.
	 */
	public function __construct(MOXMAN_Http_Request $request, MOXMAN_Http_Response $response, MOXMAN_Http_Session $session) {
		$this->request = $request;
		$this->response = $response;
		$this->session = $session;
	}

	/**
	 * Sets the current http context instance. This makes it easier to unit test the application
	 * since you can replace the HTTP Request and HTTP Response with custom mockup logic.
	 *
	 * @param MOXMAN_Http_Context $context HTTP context instance to use instead of the default.
	 */
	public static function setCurrent(MOXMAN_Http_Context $context) {
		self::$current = $context;
	}

	/**
	 * Returns the current HTTP context. It will return the default context or a custom instance
	 * if you use the setCurrent method,
	 *
	 * @return MOXMAN_Http_Context HTTP context instance to use.
	 */
	public static function getCurrent() {
		// Setup default context
		// @codeCoverageIgnoreStart
		if (!self::$current) {
			$request = new MOXMAN_Http_Request();
			$response = new MOXMAN_Http_Response();
			$session = new MOXMAN_Http_Session();
			self::$current = new MOXMAN_Http_Context($request, $response, $session);
		}
		// @codeCoverageIgnoreEnd

		return self::$current;
	}

	/**
	 * Returns the HTTP request instance.
	 *
	 * @return MOXMAN_Http_Request HTTP request instance for the HTTP context.
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Returns the HTTP response instance.
	 *
	 * @return MOXMAN_Http_Response HTTP response instance for the HTTP context.
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Returns the HTTP session instance.
	 *
	 * @return MOXMAN_Http_Session HTTP response instance for the HTTP context.
	 */
	public function getSession() {
		return $this->session;
	}
}

?>