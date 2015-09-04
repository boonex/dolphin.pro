<?php
/**
 * DownloadHandler.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Http handler that makes it possible to download single or multiple files from a file system path.
 *
 * @package MOXMAN_Handlers
 */
class MOXMAN_Handlers_DownloadHandler implements MOXMAN_Http_IHandler {
	/**
	 * Process a request using the specified context.
	 *
	 * @param MOXMAN_Http_Context $httpContext Context instance to pass to use for the handler.
	 */
	public function processRequest(MOXMAN_Http_Context $httpContext) {
		$request = $httpContext->getRequest();
		$response = $httpContext->getResponse();

		$path = $request->get("path");
		$names = explode('/', $request->get("names", ""));
		$zipName = $request->get("zipname", "files.zip");

		if (count($names) === 1) {
			$file = MOXMAN::getFile(MOXMAN_Util_PathUtils::combine($path, $names[0]));

			$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig(MOXMAN::getFile($path)->getConfig(), "download");
			if (!$filter->accept($file)) {
				throw new MOXMAN_Exception(
					"Invalid file name for: " . $file->getPublicPath(),
					MOXMAN_Exception::INVALID_FILE_NAME
				);
			}

			if ($file->isFile()) {
				$response->sendFile($file, true);
				return;
			}
		}

		// Download multiple files as zip
		$zipWriter = new MOXMAN_Zip_ZipWriter(array(
			"compressionLevel" => 0
		));

		// Setup download headers
		$response->disableCache();
		$response->setHeader("Content-type", "application/octet-stream");
		$response->setHeader("Content-Disposition", 'attachment; filename="' . $zipName . '"');

		$filter = MOXMAN_Vfs_CombinedFileFilter::createFromConfig(MOXMAN::getFile($path)->getConfig(), "download");

		// Combine files to zip
		foreach ($names as $name) {
			$fromFile = MOXMAN::getFile(MOXMAN_Util_PathUtils::combine($path, $name));
			$this->addZipFiles($fromFile, $fromFile->getParent(), $filter, $zipWriter);
		}

		$response->sendContent($zipWriter->toString());
	}

	/** @ignore */
	private function addZipFiles($file, $rootPath, $filter, $zipWriter) {
		if ($filter->accept($file)) {
			$zipPath = substr($file->getPath(), strlen($rootPath));

			if ($file->isFile()) {
				if ($file instanceof MOXMAN_Vfs_Local_File) {
					$zipWriter->addFile($zipPath, $file->getPath());
				} else {
					$stream = $file->open(MOXMAN_Vfs_IFileStream::READ);
					if ($stream) {
						$zipWriter->addFileData($zipPath, $stream->readToEnd());
						$stream->close();
					}
				}
			} else {
				$zipWriter->addDirectory($zipPath);

				$files = $file->listFilesFiltered($filter);
				foreach ($files as $file) {
					$this->addZipFiles($file, $rootPath, $filter, $zipWriter);
				}
			}
		}
	}
}

?>