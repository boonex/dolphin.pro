<?php
/**
 * JsonRpcHandler.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Http handler that takes JSON-RPC calls and executed MOXMAN_ICommand instances based in that input.
 *
 * @package MOXMAN_Handlers
 */
class MOXMAN_Handlers_JsonRpcHandler implements MOXMAN_Http_IHandler {
	/**
	 * Process a request using the specified context.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$request = $httpContext->getRequest();
		$response = $httpContext->getResponse();

		$response->disableCache();
		$response->setHeader('Content-type', 'application/json');

		$id = null;

		try {
			if ($request->getMethod() != 'POST') {
				throw new MOXMAN_Exception("Not a HTTP post request.");
			}

			if (MOXMAN::getConfig()->get('general.csrf', true)) {
				if (!MOXMAN_Http_Csrf::verifyToken(MOXMAN::getConfig()->get('general.license'), $request->get('csrf'))) {
					throw new MOXMAN_Exception("Invalid csrf token.");
				}
			}

			$json = MOXMAN_Util_Json::decode($request->get("json"));

			// Check if we should install
			if ($json && $json->method != "install") {
				$config = MOXMAN::getConfig()->getAll();

				if (empty($config) || !isset($config["general.license"])) {
					$exception = new MOXMAN_Exception("Installation needed.", MOXMAN_Exception::NEEDS_INSTALLATION);
					throw $exception;
				}

				if (!preg_match('/^([0-9A-Z]{4}\-){7}[0-9A-Z]{4}$/', trim($config["general.license"]))) {
					throw new MOXMAN_Exception("Invalid license: " . $config["general.license"]);
				}
			}

			// Check if the user is authenticated or not
			if (!MOXMAN::getAuthManager()->isAuthenticated()) {
				if (!isset($json->method) || !preg_match('/^(login|logout|install)$/', $json->method)) {
					throw new MOXMAN_Exception("Access denied by authenticator(s).", MOXMAN_Exception::NO_ACCESS);
				}
			}

			if ($json && isset($json->id) && isset($json->method) && isset($json->params)) {
				$id = $json->id;
				$params = $json->params;
				$result = null;

				if (isset($params->access)) {
					MOXMAN::getAuthManager()->setClientAuthData($params->access);
				}

				$plugins = MOXMAN::getPluginManager()->getAll();
				foreach ($plugins as $plugin) {
					if ($plugin instanceof MOXMAN_ICommandHandler) {
						$result = $plugin->execute($json->method, $json->params);
						if ($result !== null) {
							break;
						}
					}
				}

				if ($result === null) {
					throw new Exception("Method not found: " . $json->method, -32601);
				}

				$response->sendJson((object) array(
					"jsonrpc" => "2.0",
					"result" => $result,
					"id" => $id,
					"token" => MOXMAN_Http_Csrf::createToken(MOXMAN::getConfig()->get('general.license'))
				));
			} else {
				throw new Exception("Invalid Request.", -32600);
			}

			MOXMAN::dispose();
		} catch (Exception $e) {
			MOXMAN::dispose();

			$message = $e->getMessage();
			$data = null;

			if (MOXMAN::getConfig()->get("general.debug")) {
				$message .= "\n\nStacktrace:\n";
				$trace = $e->getTrace();
				array_shift($trace);
				$message .= $e->getFile() . ":" . $e->getLine() . "\n";
				foreach ($trace as $item) {
					if (isset($item["file"]) && isset($item["line"])) {
						$message .= $item["file"] . ":" . $item["line"] . "\n";
					}
				}
			}

			if ($e instanceof MOXMAN_Exception && !$data) {
				$data = $e->getData();
			}

			$response->sendJson((object) array(
				"jsonrpc" => "2.0",
				"error" => array(
					"code" => $e->getCode(),
					"message" => $message,
					"data" => $data
				),
				"id" => $id
			));
		}
	}
}

?>