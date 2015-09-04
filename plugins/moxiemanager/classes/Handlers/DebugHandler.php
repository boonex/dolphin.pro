<?php
/**
 * DebugHandler.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Http handler that makes it possible to retrieve some debug data if debug is set to true
 *
 * @package MOXMAN_Handlers
 */
class MOXMAN_Handlers_DebugHandler implements MOXMAN_Http_IHandler {
	/**
	 * Process a request using the specified context.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$config = MOXMAN::getConfig();

		$response = $httpContext->getResponse();
		$response->disableCache();
		$response->setHeader('Content-type', 'text/html');

		if (!$config->get("general.debug")) {
			$response->sendContent("Debugging not configured, you need to set general.debug to true in config.php file.");
			return;
		}

		$request = $httpContext->getRequest();

		if ($request->get("info")) {
			phpinfo();
			return;
		}

		$sitepaths = MOXMAN_Util_PathUtils::getSitePaths();

		$scriptFilename = $_SERVER["SCRIPT_FILENAME"];
		if (realpath($scriptFilename) != $scriptFilename) {
			$scriptFilename = $scriptFilename . "<br />(". realpath($scriptFilename) .")";
		}

		if (function_exists("imagecreatefromjpeg")) {
			$gdInfo = gd_info();

			$outInfo = "Ver:". $gdInfo["GD Version"];
			$outInfo .= " GIF:". ($gdInfo["GIF Create Support"] ? "Y" : "N");
			$outInfo .= " PNG:". ($gdInfo["PNG Support"] ? "Y" : "N");
			$outInfo .= " JPEG:". ($gdInfo["JPEG Support"] ? "Y" : "N");
		} else {
			$outInfo = "N/A";
			$gdInfo = array();
		}

		$user = MOXMAN::getAuthManager()->getUser();

		$result = array(
			"MOXMAN_ROOT" => MOXMAN_ROOT,
			"realpath('.')" => realpath("."),
			"Config.php rootpath" => $config->get("filesystem.rootpath"),
			"Config.php wwwroot" => $config->get("filesystem.local.wwwroot"),
			"wwwroot resolve" => $sitepaths["wwwroot"],
			"wwwroot realpath" => realpath($sitepaths["wwwroot"]),
			"prefix resolve" => $sitepaths["prefix"],
			"storage path" => MOXMAN_Util_PathUtils::toAbsolute(MOXMAN_ROOT, $config->get("storage.path")),
			"storage writable" => is_writable(MOXMAN_Util_PathUtils::toAbsolute(MOXMAN_ROOT, $config->get("storage.path"))),
			"script filename" => $scriptFilename,
			"script name" => $_SERVER["SCRIPT_NAME"],
			"GD" => $outInfo,
			"memory_limit" => @ini_get("memory_limit"),
			"upload_max_filesize" => @ini_get("upload_max_filesize"),
			"post_max_size" => @ini_get("post_max_size"),
			"file_uploads" => @ini_get("file_uploads") ? "Yes" : "No",
			"PHP Version" => phpversion(),
			"Time" => date('Y-m-d H:i:s', time()),
			"Time UTC" => date('Y-m-d H:i:s', time() - date("Z")),
			"Authenticated" => MOXMAN::getAuthManager()->isAuthenticated(),
			"User" => $user ? $user->getName() : "N/A"
		);

		$out = "<html><body><table border='1'>";
		foreach($result as $name => $value) {
			if ($value === true) {
				$value = "True";
			} else if ($value === false) {
				$value = "False";
			}

			$out .= "<tr>";
			$out .= "<td>". $name ."&nbsp;</td><td>". $value ."&nbsp;</td>";
			$out .= "</tr>";
		}

		$out .= "</table><a href='?action=debug&info=true'>Show phpinfo</a>";
		$out .= "</body></html>";

		$response->sendContent($out);
	}
}

?>