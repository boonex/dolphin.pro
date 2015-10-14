<?php
/**
 * ImportFromUrlCommand.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Imports a file from the specified URL.
 *
 * @package MOXMAN_Commands
 */
class MOXMAN_Commands_ImportFromUrlCommand extends MOXMAN_Commands_BaseCommand {
	/**
	 * Executes the command logic with the specified RPC parameters.
	 *
	 * @param Object $params Command parameters sent from client.
	 * @return Object Result object to be passed back to client.
	 */
	public function execute($params) {
		$file = MOXMAN::getFile($params->path);
		$config = $file->getConfig();
		$resolution = $params->resolution;

		if ($config->get('general.demo')) {
			throw new MOXMAN_Exception(
				"This action is restricted in demo mode.",
				MOXMAN_Exception::DEMO_MODE
			);
		}

		$content = $this->getUrlContent($params->url, $config);

		// Fire before file action add event
		$args = $this->fireBeforeFileAction("add", $file, strlen($content));
		$file = $args->getFile();

		if (!$file->canWrite()) {
			throw new MOXMAN_Exception(
				"No write access to file: " . $file->getPublicPath(),
				MOXMAN_Exception::NO_WRITE_ACCESS
			);
		}

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig($config, "upload");
		if (!$filter->accept($file, true)) {
			throw new MOXMAN_Exception(
				"Invalid file name for: " . $file->getPublicPath(),
				MOXMAN_Exception::INVALID_FILE_NAME
			);
		}

		if ($resolution == "rename") {
			$file = MOXMAN_Util_FileUtils::uniqueFile($file);
		} else if ($resolution == "overwrite") {
			MOXMAN::getPluginManager()->get("core")->deleteFile($file);
		} else {
			throw new MOXMAN_Exception(
				"To file already exist: " . $file->getPublicPath(),
				MOXMAN_Exception::FILE_EXISTS
			);
		}

		$stream = $file->open(MOXMAN_Vfs_IFileStream::WRITE);
		$stream->write($content);
		$stream->close();

		$args = new MOXMAN_Vfs_FileActionEventArgs("add", $file);
		MOXMAN::getPluginManager()->get("core")->fire("FileAction", $args);

		return parent::fileToJson($file, true);
	}

	private function getUrlContent($url, $config) {
		$url = parse_url($url);

		$port = "";
		if (isset($url["port"])) {
			$port = ":". $url["port"];
		}

		$query = "";
		if (isset($url["query"])) {
			$query = "?". $url["query"];
		}

		$path = $url["path"] . $query;
		$host = $url["scheme"] . "://" . $url["host"] . $port;

		$httpClient = new MOXMAN_Http_HttpClient($host);
		$httpClient->setProxy($config->get("general.http_proxy"));
		$request = $httpClient->createRequest($path);
		$response = $request->send();

		// Handle redirects
		$location = $response->getHeader("location");
		if ($location) {
			$httpClient->close();

			$httpClient = new MOXMAN_Http_HttpClient($location);
			$request = $httpClient->createRequest($location);
			$response = $request->send();
		}

		// Read file into ram
		// TODO: This should not happen if we know the file size
		$content = "";
		while (($chunk = $response->read()) != "") {
			$content .= $chunk;
		}

		$httpClient->close();

		return $content;
	}
}

?>
